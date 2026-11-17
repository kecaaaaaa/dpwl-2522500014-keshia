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
}
