<?php
// P9 BARU - application/models/Pembayaran_model.php
// Mengelola pembayaran DP penyewa dan verifikasi admin.
// Record DP dibuat oleh Transaksi_model::checkout() (P7). Model ini hanya
// MEMPERBARUI record tersebut; tidak pernah melakukan INSERT ke t_bayar.
class Pembayaran_model
{
    private mysqli $db;

    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    // ---------- helper privat ----------

    private function siapkan(string $sql, string $pesan): mysqli_stmt
    {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException($pesan . ' gagal disiapkan.');
        }
        return $stmt;
    }

    private function jalankan(mysqli_stmt $stmt, string $pesan): void
    {
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException($pesan . ' gagal dijalankan.');
        }
    }

    private function hitungTotal(array $detail): int
    {
        $total = 0;
        foreach ($detail as $item) {
            $total += (int) $item['hrg_sewa'] * (int) $item['n_hari'];
        }
        return $total;
    }

    private function ambilDetailSewa(string $kodeSewa): array
    {
        $stmt = $this->siapkan(
            "SELECT p.no_plat, p.hrg_sewa, p.n_hari, m.nm_mobil
FROM t_pilih p
LEFT JOIN t_mobil m ON m.no_plat = p.no_plat
WHERE p.kode_sewa = ?
ORDER BY p.no_plat ASC",
            'Pengambilan detail sewa'
        );
        $stmt->bind_param('s', $kodeSewa);
        $this->jalankan($stmt, 'Pengambilan detail sewa');
        $detail = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $detail;
    }

    // ---------- sisi penyewa ----------

    /** Daftar pembayaran DP milik satu penyewa. */
    public function getDaftarDpPenyewa(string $userPlg): array
    {
        $stmt = $this->siapkan(
            "SELECT b.kode_bayar, b.kode_sewa, b.nominal, b.sttus_byr,
b.ft_byr, b.tgl_wkt_byr, b.tgl_wkt_ver,
s.status_sewa, s.tgl_mulai, s.tgl_selesai
FROM t_bayar b
INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
WHERE s.user_plg = ?
AND b.jns_byr = 'dp'
AND b.mtode_byr = 'transfer'
ORDER BY b.tgl_wkt_byr DESC, b.kode_bayar DESC",
            'Pengambilan daftar pembayaran'
        );
        $stmt->bind_param('s', $userPlg);
        $this->jalankan($stmt, 'Pengambilan daftar pembayaran');
        $hasil = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $hasil;
    }

    /** Record DP satu transaksi milik penyewa, lengkap dengan rincian sewa. */
    public function getDpPenyewa(string $kodeSewa, string $userPlg): ?array
    {
        $stmt = $this->siapkan(
            "SELECT b.kode_bayar, b.kode_sewa, b.nominal, b.sttus_byr,
b.ft_byr, b.tgl_wkt_byr, b.tgl_wkt_ver, b.mtode_byr,
s.status_sewa, s.tgl_mulai, s.tgl_selesai, s.wkt_ambil
FROM t_bayar b
INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
WHERE b.kode_sewa = ?
AND s.user_plg = ?
AND b.jns_byr = 'dp'
LIMIT 1",
            'Pengambilan pembayaran DP'
        );
        $stmt->bind_param('ss', $kodeSewa, $userPlg);
        $this->jalankan($stmt, 'Pengambilan pembayaran DP');
        $baris = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($baris === null) {
            return null;
        }
        $baris['detail'] = $this->ambilDetailSewa($kodeSewa);
        $baris['total'] = $this->hitungTotal($baris['detail']);
        return $baris;
    }

    /**
     * Menyimpan bukti pembayaran pada record DP yang SUDAH ADA.
     * Mengembalikan nama berkas bukti lama (atau null) agar controller dapat
     * menghapusnya setelah transaksi database berhasil.
     */
    public function simpanBukti(
        string $kodeBayar,
        string $userPlg,
        string $namaFile
    ): ?string {
        $this->db->begin_transaction();
        try {
            $stmt = $this->siapkan(
                "SELECT b.ft_byr, b.sttus_byr, b.jns_byr, b.mtode_byr,
s.status_sewa
FROM t_bayar b
INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
WHERE b.kode_bayar = ?
AND s.user_plg = ?
LIMIT 1
FOR UPDATE",
                'Penguncian pembayaran'
            );
            $stmt->bind_param('ss', $kodeBayar, $userPlg);
            $this->jalankan($stmt, 'Penguncian pembayaran');
            $bayar = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($bayar === null || $bayar['jns_byr'] !== 'dp') {
                throw new InvalidArgumentException(
                    'Data pembayaran tidak ditemukan.'
                );
            }
            if ($bayar['mtode_byr'] !== 'transfer') {
                throw new InvalidArgumentException(
                    'Pembayaran ini bukan pembayaran transfer.'
                );
            }
            if ($bayar['status_sewa'] !== 'diajukan') {
                throw new InvalidArgumentException(
                    'Transaksi tidak lagi berstatus diajukan, bukti tidak dapat diunggah.'
                );
            }
            if ($bayar['sttus_byr'] === 'valid') {
                throw new InvalidArgumentException(
                    'Pembayaran sudah diterima dan tidak dapat diubah.'
                );
            }
            if (!in_array($bayar['sttus_byr'], ['pending', 'invalid'], true)) {
                throw new InvalidArgumentException(
                    'Status pembayaran tidak valid untuk unggah bukti.'
                );
            }

            // UPDATE record DP yang sama; status kembali pending untuk
            // diverifikasi ulang, waktu verifikasi dikosongkan.
            $stmt = $this->siapkan(
                "UPDATE t_bayar
SET ft_byr = ?, tgl_wkt_byr = NOW(),
sttus_byr = 'pending', tgl_wkt_ver = NULL
WHERE kode_bayar = ?
AND jns_byr = 'dp'
AND sttus_byr IN ('pending', 'invalid')",
                'Penyimpanan bukti pembayaran'
            );
            $stmt->bind_param('ss', $namaFile, $kodeBayar);
            $this->jalankan($stmt, 'Penyimpanan bukti pembayaran');
            $berubah = $stmt->affected_rows === 1;
            $stmt->close();
            if (!$berubah) {
                throw new RuntimeException(
                    'Bukti pembayaran tidak tersimpan.'
                );
            }
            $this->db->commit();
            $lama = $bayar['ft_byr'] ?? null;
            return ($lama === null || $lama === '') ? null : (string) $lama;
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /** Nama berkas bukti beserta pemilik transaksi (untuk penyajian berkas). */
    public function getBukti(string $kodeBayar): ?array
    {
        $stmt = $this->siapkan(
            "SELECT b.ft_byr, s.user_plg
FROM t_bayar b
INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
WHERE b.kode_bayar = ?
LIMIT 1",
            'Pengambilan bukti pembayaran'
        );
        $stmt->bind_param('s', $kodeBayar);
        $this->jalankan($stmt, 'Pengambilan bukti pembayaran');
        $baris = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $baris;
    }

    // ---------- sisi admin ----------

    /** Daftar DP transfer; $status kosong berarti semua status. */
    public function getDaftarVerifikasi(string $status = ''): array
    {
        $sql = "SELECT b.kode_bayar, b.kode_sewa, b.nominal, b.sttus_byr,
b.ft_byr, b.tgl_wkt_byr, b.tgl_wkt_ver,
s.status_sewa, s.user_plg, p.nm_lngkp
FROM t_bayar b
INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
LEFT JOIN t_penyewa p ON p.user_plg = s.user_plg
WHERE b.jns_byr = 'dp'
AND b.mtode_byr = 'transfer'";
        $diizinkan = ['pending', 'valid', 'invalid'];
        $pakaiFilter = in_array($status, $diizinkan, true);
        if ($pakaiFilter) {
            $sql .= ' AND b.sttus_byr = ?';
        }
        $sql .= " ORDER BY (b.sttus_byr = 'pending') DESC,
b.tgl_wkt_byr DESC, b.kode_bayar DESC";
        $stmt = $this->siapkan($sql, 'Pengambilan daftar verifikasi');
        if ($pakaiFilter) {
            $stmt->bind_param('s', $status);
        }
        $this->jalankan($stmt, 'Pengambilan daftar verifikasi');
        $hasil = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $hasil;
    }

    public function getDetailVerifikasi(string $kodeBayar): ?array
    {
        $stmt = $this->siapkan(
            "SELECT b.kode_bayar, b.kode_sewa, b.nominal, b.sttus_byr,
b.ft_byr, b.tgl_wkt_byr, b.tgl_wkt_ver, b.mtode_byr,
s.status_sewa, s.tgl_mulai, s.tgl_selesai, s.wkt_ambil,
s.user_plg, p.nm_lngkp, p.no_hp
FROM t_bayar b
INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
LEFT JOIN t_penyewa p ON p.user_plg = s.user_plg
WHERE b.kode_bayar = ?
AND b.jns_byr = 'dp'
AND b.mtode_byr = 'transfer'
LIMIT 1",
            'Pengambilan detail verifikasi'
        );
        $stmt->bind_param('s', $kodeBayar);
        $this->jalankan($stmt, 'Pengambilan detail verifikasi');
        $baris = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($baris === null) {
            return null;
        }
        $baris['detail'] = $this->ambilDetailSewa((string) $baris['kode_sewa']);
        $baris['total'] = $this->hitungTotal($baris['detail']);
        $baris['dp_seharusnya'] = (int) round($baris['total'] * 30 / 100);
        return $baris;
    }

    /**
     * Verifikasi admin. $keputusan: 'valid' (diterima) atau 'invalid' (ditolak).
     * - valid   : t_bayar.sttus_byr = 'valid', t_sewa.status_sewa = 'disetujui'.
     * - invalid : t_bayar.sttus_byr = 'invalid'; t_sewa tetap 'diajukan' agar
     *             penyewa dapat mengunggah ulang bukti pada record DP yang sama.
     */
    public function verifikasi(string $kodeBayar, string $keputusan): void
    {
        if (!in_array($keputusan, ['valid', 'invalid'], true)) {
            throw new InvalidArgumentException('Keputusan verifikasi tidak valid.');
        }
        $this->db->begin_transaction();
        try {
            $stmt = $this->siapkan(
                "SELECT b.kode_sewa, b.ft_byr, b.sttus_byr, b.jns_byr,
b.mtode_byr, s.status_sewa
FROM t_bayar b
INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
WHERE b.kode_bayar = ?
LIMIT 1
FOR UPDATE",
                'Penguncian verifikasi'
            );
            $stmt->bind_param('s', $kodeBayar);
            $this->jalankan($stmt, 'Penguncian verifikasi');
            $bayar = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (
                $bayar === null ||
                $bayar['jns_byr'] !== 'dp' ||
                $bayar['mtode_byr'] !== 'transfer'
            ) {
                throw new InvalidArgumentException(
                    'Data pembayaran tidak ditemukan.'
                );
            }
            if ($bayar['sttus_byr'] !== 'pending') {
                throw new InvalidArgumentException(
                    'Pembayaran ini sudah diverifikasi sebelumnya.'
                );
            }
            if ($bayar['ft_byr'] === null || $bayar['ft_byr'] === '') {
                throw new InvalidArgumentException(
                    'Bukti pembayaran belum diunggah penyewa.'
                );
            }
            if ($bayar['status_sewa'] !== 'diajukan') {
                throw new InvalidArgumentException(
                    'Transaksi tidak berstatus diajukan, verifikasi dibatalkan.'
                );
            }

            $stmt = $this->siapkan(
                "UPDATE t_bayar
SET sttus_byr = ?, tgl_wkt_ver = NOW()
WHERE kode_bayar = ?
AND sttus_byr = 'pending'",
                'Pembaruan status pembayaran'
            );
            $stmt->bind_param('ss', $keputusan, $kodeBayar);
            $this->jalankan($stmt, 'Pembaruan status pembayaran');
            $berubah = $stmt->affected_rows === 1;
            $stmt->close();
            if (!$berubah) {
                throw new RuntimeException('Status pembayaran tidak berubah.');
            }

            if ($keputusan === 'valid') {
                $kodeSewa = (string) $bayar['kode_sewa'];
                $stmt = $this->siapkan(
                    "UPDATE t_sewa
SET status_sewa = 'disetujui'
WHERE kode_sewa = ?
AND status_sewa = 'diajukan'",
                    'Pembaruan status transaksi'
                );
                $stmt->bind_param('s', $kodeSewa);
                $this->jalankan($stmt, 'Pembaruan status transaksi');
                $berubah = $stmt->affected_rows === 1;
                $stmt->close();
                if (!$berubah) {
                    throw new RuntimeException('Status transaksi tidak berubah.');
                }
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * P10: Mengambil daftar transaksi yang dapat diproses pelunasannya.
     *
     * Total transaksi menggunakan snapshot tarif pada t_pilih.
     * Pembayaran yang mengurangi kewajiban hanya yang berstatus valid.
     */
    public function getDaftarPelunasan(): array
    {
        $stmt = $this->db->prepare(
            "SELECT
s.kode_sewa,
s.tgl_mulai,
s.tgl_selesai,
s.status_sewa,
p.user_plg,
p.nm_lngkp,
p.no_hp,
COALESCE((
SELECT SUM(tp.hrg_sewa * tp.n_hari)
FROM t_pilih tp
WHERE tp.kode_sewa = s.kode_sewa
), 0) AS total_transaksi,
COALESCE((
SELECT SUM(b.nominal)
FROM t_bayar b
WHERE b.kode_sewa = s.kode_sewa
AND b.sttus_byr = 'valid'
), 0) AS total_terbayar
FROM t_sewa s
INNER JOIN t_penyewa p
ON p.user_plg = s.user_plg
WHERE s.status_sewa IN ('disetujui', 'lunas')
AND EXISTS (
SELECT 1
FROM t_pilih tp
WHERE tp.kode_sewa = s.kode_sewa
)
ORDER BY s.tgl_mulai DESC, s.kode_sewa DESC"
        );
        if (!$stmt) {
            throw new RuntimeException(
                'Query daftar pelunasan gagal disiapkan.'
            );
        }
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException(
                'Daftar pelunasan gagal diambil.'
            );
        }
        $daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        foreach ($daftar as &$item) {
            $item['total_transaksi'] = (int) $item['total_transaksi'];
            $item['total_terbayar'] = (int) $item['total_terbayar'];
            $item['sisa_kewajiban'] = max(
                0,
                $item['total_transaksi'] - $item['total_terbayar']
            );
        }
        unset($item);
        return array_values(array_filter(
            $daftar,
            static fn(array $item): bool =>
            $item['sisa_kewajiban'] > 0 ||
                $item['status_sewa'] === 'lunas'
        ));
    }
    /**
     * P10: Mengambil detail transaksi, penyewa, dan riwayat pembayaran
     * untuk halaman pelunasan.
     */
    public function getDetailPelunasan(string $kodeSewa): ?array
    {
        $kodeSewa = trim($kodeSewa);
        if ($kodeSewa === '') {
            throw new InvalidArgumentException(
                'Kode transaksi sewa wajib diisi.'
            );
        }
        $stmt = $this->db->prepare(
            "SELECT
s.kode_sewa,
s.tgl_mulai,
s.tgl_selesai,
s.wkt_ambil,
s.status_sewa,
p.user_plg,
p.nm_lngkp,
p.no_hp,
p.nik,
p.almt_lngkp,
COALESCE((
SELECT SUM(tp.hrg_sewa * tp.n_hari)
FROM t_pilih tp
WHERE tp.kode_sewa = s.kode_sewa
), 0) AS total_transaksi,
COALESCE((
SELECT SUM(b.nominal)
FROM t_bayar b
WHERE b.kode_sewa = s.kode_sewa
AND b.sttus_byr = 'valid'
), 0) AS total_terbayar
FROM t_sewa s
INNER JOIN t_penyewa p
ON p.user_plg = s.user_plg
WHERE s.kode_sewa = ?
LIMIT 1"
        );
        if (!$stmt) {
            throw new RuntimeException(
                'Query detail pelunasan gagal disiapkan.'
            );
        }
        $stmt->bind_param('s', $kodeSewa);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException(
                'Detail transaksi pelunasan gagal diambil.'
            );
        }
        $detail = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($detail === null) {
            return null;
        }
        $detail['total_transaksi'] = (int) $detail['total_transaksi'];
        $detail['total_terbayar'] = (int) $detail['total_terbayar'];
        $detail['sisa_kewajiban'] = max(
            0,
            $detail['total_transaksi'] - $detail['total_terbayar']
        );
        $stmt = $this->db->prepare(
            "SELECT
kode_bayar,
tgl_wkt_byr,
mtode_byr,
jns_byr,
nominal,
sttus_byr,
ft_byr,
tgl_wkt_ver
FROM t_bayar
WHERE kode_sewa = ?
ORDER BY tgl_wkt_byr ASC, kode_bayar ASC"
        );
        if (!$stmt) {
            throw new RuntimeException(
                'Query riwayat pembayaran gagal disiapkan.'
            );
        }
        $stmt->bind_param('s', $kodeSewa);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException(
                'Riwayat pembayaran gagal diambil.'
            );
        }
        $detail['riwayat_pembayaran'] =
            $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $stmt = $this->db->prepare(
            "SELECT
m.no_plat,
m.nm_mobil,
tp.hrg_sewa,
tp.n_hari,
(tp.hrg_sewa * tp.n_hari) AS subtotal
FROM t_pilih tp
INNER JOIN t_mobil m
ON m.no_plat = tp.no_plat
WHERE tp.kode_sewa = ?
ORDER BY m.nm_mobil ASC, m.no_plat ASC"
        );
        if (!$stmt) {
            throw new RuntimeException(
                'Query detail kendaraan pelunasan gagal disiapkan.'
            );
        }
        $stmt->bind_param('s', $kodeSewa);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException(
                'Detail kendaraan pelunasan gagal diambil.'
            );
        }
        $detail['daftar_kendaraan'] =
            $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $detail;
    }

    /**
     * P10: Membuat kode pembayaran dengan format PAY/YYYYMMDD/NN.
     * Pemanggil harus memegang named lock pembayaran harian.
     */
    private function buatKodeBayarPelunasan(string $tanggalKode): string
    {
        $pola = 'PAY/' . $tanggalKode . '/%';
        $stmt = $this->db->prepare(
            "SELECT kode_bayar
FROM t_bayar
WHERE kode_bayar LIKE ?
ORDER BY kode_bayar DESC
LIMIT 1"
        );
        if (!$stmt) {
            throw new RuntimeException(
                'Pembuatan kode pembayaran gagal disiapkan.'
            );
        }
        $stmt->bind_param('s', $pola);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException(
                'Pembuatan kode pembayaran gagal dijalankan.'
            );
        }
        $terakhir = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $nomorBerikutnya = 1;
        if ($terakhir !== null) {
            $bagianKode = explode('/', (string) $terakhir['kode_bayar']);
            $nomorBerikutnya = ((int) end($bagianKode)) + 1;
        }
        if ($nomorBerikutnya > 99) {
            throw new RuntimeException(
                'Nomor pembayaran harian telah mencapai batas 99.'
            );
        }
        return sprintf('PAY/%s/%02d', $tanggalKode, $nomorBerikutnya);
    }

    /**
* P10: Mencatat pelunasan langsung oleh admin.
*
* Pembayaran langsung dicatat sebagai valid.
* Status transaksi diperbarui menjadi lunas dalam transaksi database
* yang sama. Status kendaraan tidak diubah pada P10.
*/
public function simpanPelunasan(
string $kodeSewa,
string $metodeBayar,
?string $namaFileBukti = null
): string {
$kodeSewa = trim($kodeSewa);
$metodeBayar = trim($metodeBayar);
if ($kodeSewa === '') {
throw new InvalidArgumentException(
'Kode transaksi sewa wajib diisi.'
);
}
if (!in_array($metodeBayar, ['tunai', 'transfer'], true)) {
throw new InvalidArgumentException(
'Metode pembayaran tidak valid.'
);
}
if ($metodeBayar === 'transfer') {
$namaFileBukti = trim((string) $namaFileBukti);
if ($namaFileBukti === '') {
throw new InvalidArgumentException(
'Bukti transfer wajib tersedia.'
);
}
} else {
$namaFileBukti = null;
}
// Harus sama dengan kunci yang digunakan Transaksi_model::checkout()
// ketika membuat kode pembayaran DP.
$namaKunci = 'dpwl_payment_code_sequence';
$kunciDiperoleh = false;
$transaksiDimulai = false;
try {
$stmt = $this->db->prepare(
'SELECT GET_LOCK(?, 10) AS lock_status'
);
if (!$stmt) {
throw new RuntimeException(
'Kunci pembuatan kode pembayaran gagal disiapkan.'
);
}
$stmt->bind_param('s', $namaKunci);
if (!$stmt->execute()) {
$stmt->close();
throw new RuntimeException(
'Kunci pembuatan kode pembayaran gagal dijalankan.'
);
}
$hasilKunci = $stmt->get_result()->fetch_assoc();
$stmt->close();
if ((int) ($hasilKunci['lock_status'] ?? 0) !== 1) {
throw new RuntimeException(
'Proses pembayaran sedang digunakan. Silakan coba kembali.'
);
}
$kunciDiperoleh = true;
$this->db->begin_transaction();
$transaksiDimulai = true;
// Kunci transaksi agar dua permintaan pelunasan tidak berjalan
// bersamaan untuk transaksi yang sama.
$stmt = $this->db->prepare(
"SELECT
s.kode_sewa,
s.status_sewa,
COALESCE((
SELECT SUM(tp.hrg_sewa * tp.n_hari)
FROM t_pilih tp
WHERE tp.kode_sewa = s.kode_sewa
), 0) AS total_transaksi,
COALESCE((
SELECT SUM(b.nominal)
FROM t_bayar b
WHERE b.kode_sewa = s.kode_sewa
AND b.sttus_byr = 'valid'
), 0) AS total_terbayar
FROM t_sewa s
WHERE s.kode_sewa = ?
LIMIT 1
FOR UPDATE"
);
if (!$stmt) {
throw new RuntimeException(
'Pemeriksaan transaksi pelunasan gagal disiapkan.'
);
}
$stmt->bind_param('s', $kodeSewa);
if (!$stmt->execute()) {
$stmt->close();
throw new RuntimeException(
'Pemeriksaan transaksi pelunasan gagal dijalankan.'
);
}
$transaksi = $stmt->get_result()->fetch_assoc();
$stmt->close();
if ($transaksi === null) {
throw new RuntimeException(
'Transaksi sewa tidak ditemukan.'
);
}
if (!in_array(
$transaksi['status_sewa'],
['disetujui', 'lunas'],
true
)) {
throw new RuntimeException(
'Status transaksi belum memenuhi syarat pelunasan.'
);
}
$totalTransaksi = (int) $transaksi['total_transaksi'];
$totalTerbayar = (int) $transaksi['total_terbayar'];
$sisaKewajiban = $totalTransaksi - $totalTerbayar;
if ($totalTransaksi <= 0 || $sisaKewajiban <= 0) {
throw new RuntimeException(
'Transaksi tidak memiliki sisa kewajiban untuk dilunasi.'
);
}
if ($sisaKewajiban > 8388607) {
throw new RuntimeException(
'Sisa kewajiban melebihi kapasitas kolom nominal pada database.'
);
}
// Pastikan transaksi belum memiliki record pelunasan.
$stmt = $this->db->prepare(
"SELECT kode_bayar
FROM t_bayar
WHERE kode_sewa = ?
AND jns_byr = 'pelunasan'
LIMIT 1
FOR UPDATE"
);
if (!$stmt) {
throw new RuntimeException(
'Pemeriksaan pelunasan sebelumnya gagal disiapkan.'
);
}
$stmt->bind_param('s', $kodeSewa);
if (!$stmt->execute()) {
$stmt->close();
throw new RuntimeException(
'Pemeriksaan pelunasan sebelumnya gagal dijalankan.'
);
}
$pelunasanAda = $stmt->get_result()->fetch_assoc() !== null;
$stmt->close();
if ($pelunasanAda) {
throw new RuntimeException(
'Transaksi ini sudah memiliki record pelunasan.'
);
}
$hasilWaktu = $this->db->query(
"SELECT
DATE_FORMAT(NOW(), '%Y%m%d') AS tanggal_kode,
NOW() AS waktu_pembayaran"
);
if (!$hasilWaktu) {
throw new RuntimeException(
'Waktu pembayaran gagal dibaca.'
);
}
$waktu = $hasilWaktu->fetch_assoc();
$hasilWaktu->free();
$tanggalKode = (string) ($waktu['tanggal_kode'] ?? '');
$waktuPembayaran = (string) ($waktu['waktu_pembayaran'] ?? '');
if (
!preg_match('/^\d{8}$/', $tanggalKode) ||
$waktuPembayaran === ''
) {
throw new RuntimeException(
'Waktu pembayaran tidak valid.'
);
}
$kodeBayar = $this->buatKodeBayarPelunasan($tanggalKode);
$stmt = $this->db->prepare(
"INSERT INTO t_bayar (
kode_bayar,
tgl_wkt_byr,
ft_byr,
mtode_byr,
jns_byr,
nominal,
sttus_byr,
tgl_wkt_ver,
kode_sewa
) VALUES (?, ?, ?, ?, 'pelunasan', ?, 'valid', NOW(), ?)"
);
if (!$stmt) {
throw new RuntimeException(
'Pencatatan pelunasan gagal disiapkan.'
);
}
$stmt->bind_param(
'ssssis',
$kodeBayar,
$waktuPembayaran,
$namaFileBukti,
$metodeBayar,
$sisaKewajiban,
$kodeSewa
);
if (!$stmt->execute()) {
$stmt->close();
throw new RuntimeException(
'Pelunasan gagal disimpan.'
);
}
$stmt->close();
// Pelunasan berhasil dicatat; perbarui status transaksi
// dalam transaksi database yang sama.
$stmt = $this->db->prepare(
"UPDATE t_sewa
SET status_sewa = 'lunas'
WHERE kode_sewa = ?
AND status_sewa IN ('disetujui', 'lunas')"
);
if (!$stmt) {
throw new RuntimeException(
'Pembaruan status transaksi menjadi lunas gagal disiapkan.'
);
}
$stmt->bind_param('s', $kodeSewa);
if (!$stmt->execute()) {
$stmt->close();
throw new RuntimeException(
'Status transaksi gagal diperbarui menjadi lunas.'
);
}
if ($stmt->affected_rows !== 1) {
$stmt->close();
throw new RuntimeException(
'Status transaksi tidak berubah menjadi lunas. '
. 'Periksa kembali status transaksi.'
);
}
$stmt->close();
$this->db->commit();
$transaksiDimulai = false;
return $kodeBayar;
} catch (Throwable $e) {
if ($transaksiDimulai) {
$this->db->rollback();
}
throw $e;
} finally {
if ($kunciDiperoleh) {
$stmt = $this->db->prepare(
'SELECT RELEASE_LOCK(?)'
);
if ($stmt) {
$stmt->bind_param('s', $namaKunci);
$stmt->execute();
$stmt->close();
}
}
}
}
}
