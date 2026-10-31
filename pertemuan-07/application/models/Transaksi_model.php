<?php
class Transaksi_model
{
    private mysqli $db;
    public function __construct()
    {
        global $db;
        $this->db = $db;
    }
    private function ambilKunci(
        string $namaKunci,
        int $timeout = 10
    ): void {
        $stmt = $this->db->prepare(
            'SELECT GET_LOCK(?, ?) AS lock_status'
        );
        if (!$stmt) {
            throw new RuntimeException(
                'Kunci proses gagal disiapkan.'
            );
        }
        $stmt->bind_param('si', $namaKunci, $timeout);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException(
                'Kunci proses gagal dijalankan.'
            );
        }
        $hasil = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ((int) ($hasil['lock_status'] ?? 0) !== 1) {
            throw new RuntimeException(
                'Kunci proses tidak diperoleh. Silakan coba lagi.'
            );
        }
    }
    private function lepasKunci(string $namaKunci): void
    {
        $stmt = $this->db->prepare(
            'SELECT RELEASE_LOCK(?)'
        );
        if (!$stmt) {
            throw new RuntimeException(
                'Pelepasan kunci proses gagal disiapkan.'
            );
        }
        $stmt->bind_param('s', $namaKunci);
        $berhasil = $stmt->execute();
        $stmt->close();
        if (!$berhasil) {
            throw new RuntimeException(
                'Pelepasan kunci proses gagal dijalankan.'
            );
        }
    }

    private function kunciDanBuatKodeSewa(): array
    {
        $tanggal = (
            new DateTimeImmutable(
                'now',
                new DateTimeZone('Asia/Jakarta')
            )
        )->format('Ymd');
        $prefix = 'INV/' . $tanggal . '/';
        $namaKunci = 'dpwl_inv_' . $tanggal;
        $this->ambilKunci($namaKunci);
        try {
            $stmt = $this->db->prepare(
                "SELECT kode_sewa
FROM t_sewa
WHERE kode_sewa LIKE ?
ORDER BY kode_sewa DESC
LIMIT 1"
            );
            if (!$stmt) {
                throw new RuntimeException(
                    'Pencarian nomor transaksi gagal disiapkan.'
                );
            }
            $pola = $prefix . '%';
            $stmt->bind_param('s', $pola);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException(
                    'Pencarian nomor transaksi gagal dijalankan.'
                );
            }
            $baris = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $nomorTerakhir = $baris === null
                ? 0
                : (int) substr($baris['kode_sewa'], -2);
            $nomorBerikutnya = $nomorTerakhir + 1;
            if ($nomorBerikutnya > 99) {
                throw new RuntimeException(
                    'Nomor transaksi harian telah mencapai batas 99.'
                );
            }
            return [
                'kode_sewa' => $prefix . str_pad(
                    (string) $nomorBerikutnya,
                    2,
                    '0',
                    STR_PAD_LEFT
                ),
                'nama_kunci' => $namaKunci
            ];
        } catch (Throwable $e) {
            $this->lepasKunci($namaKunci);
            throw $e;
        }
    }

    public function getJumlahMobilKeranjang(string $userPlg): int
    {
        $userPlg = trim($userPlg);
        if ($userPlg === '') {
            return 0;
        }
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS jumlah
FROM t_pilih p
INNER JOIN t_sewa s ON s.kode_sewa = p.kode_sewa
WHERE s.user_plg = ?
AND s.status_sewa = 'keranjang'"
        );
        if (!$stmt) {
            throw new RuntimeException(
                'Penghitungan kendaraan dalam keranjang gagal disiapkan.'
            );
        }
        $stmt->bind_param('s', $userPlg);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException(
                'Penghitungan kendaraan dalam keranjang gagal dijalankan.'
            );
        }
        $hasil = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) ($hasil['jumlah'] ?? 0);
    }
    private function namaKunciKeranjang(string $userPlg): string
    {
        return 'dpwl_cart_' . substr(hash('sha256', $userPlg), 0, 48);
    }
    private function kunciKendaraan(string $noPlat): array
    {
        $stmt = $this->db->prepare(
            "SELECT no_plat, hrg_hari, status_unit
FROM t_mobil
WHERE no_plat = ?
LIMIT 1
FOR UPDATE"
        );
        if (!$stmt) {
            throw new RuntimeException(
                'Query penguncian kendaraan gagal disiapkan.'
            );
        }
        $stmt->bind_param('s', $noPlat);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException(
                'Penguncian kendaraan gagal dijalankan.'
            );
        }
        $kendaraan = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($kendaraan === null) {
            throw new RuntimeException('Kendaraan tidak ditemukan.');
        }
        return $kendaraan;
    }

    private function hitungHariSewa(
        string $tglMulai,
        string $tglSelesai
    ): int {
        $mulai = DateTimeImmutable::createFromFormat('!Y-m-d', $tglMulai);
        $errorMulai = DateTimeImmutable::getLastErrors();
        if (
            $mulai === false ||
            (
                $errorMulai !== false &&
                (
                    $errorMulai['warning_count'] > 0 ||
                    $errorMulai['error_count'] > 0
                )
            ) ||
            $mulai->format('Y-m-d') !== $tglMulai
        ) {
            throw new InvalidArgumentException(
                'Tanggal mulai tidak valid.'
            );
        }
        $selesai = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $tglSelesai
        );
        $errorSelesai = DateTimeImmutable::getLastErrors();
        if (
            $selesai === false ||
            (
                $errorSelesai !== false &&
                (
                    $errorSelesai['warning_count'] > 0 ||
                    $errorSelesai['error_count'] > 0
                )
            ) ||
            $selesai->format('Y-m-d') !== $tglSelesai
        ) {
            throw new InvalidArgumentException(
                'Tanggal selesai tidak valid.'
            );
        }
        if ($selesai <= $mulai) {
            throw new InvalidArgumentException(
                'Tanggal selesai harus setelah tanggal mulai.'
            );
        }
        $hari = (int) $mulai->diff($selesai)->days;
        if ($hari < 1 || $hari > 127) {
            throw new InvalidArgumentException(
                'Lama sewa harus antara 1 dan 127 hari.'
            );
        }
        return $hari;
    }
    private function normalisasiWaktu(string $waktu): string
    {
        foreach (['H:i:s', 'H:i'] as $format) {
            $hasil = DateTimeImmutable::createFromFormat(
                '!' . $format,
                $waktu
            );
            $errors = DateTimeImmutable::getLastErrors();
            if (
                $hasil !== false &&
                (
                    $errors === false ||
                    (
                        $errors['warning_count'] === 0 &&
                        $errors['error_count'] === 0
                    )
                ) &&
                $hasil->format($format) === $waktu
            ) {
                return $hasil->format('H:i:s');
            }
        }
        throw new InvalidArgumentException(
            'Waktu pengambilan tidak valid.'
        );
    }

    public function tambahKeKeranjang(
        string $userPlg,
        string $noPlat
    ): string {
        $userPlg = trim($userPlg);
        $noPlat = trim($noPlat);
        if ($userPlg === '' || $noPlat === '') {
            throw new InvalidArgumentException(
                'Identitas penyewa dan kendaraan wajib diisi.'
            );
        }
        $this->db->begin_transaction();
        $namaKunci = null;
        $namaKunciKeranjang = null;
        $kunciKeranjangDidapat = false;
        try {
            $kendaraan = $this->kunciKendaraan($noPlat);
            if ($kendaraan['status_unit'] !== 'tersedia') {
                throw new RuntimeException(
                    'Kendaraan tidak berstatus tersedia.'
                );
            }
            $namaKunciKeranjang = $this->namaKunciKeranjang($userPlg);
            $this->ambilKunci($namaKunciKeranjang);
            $kunciKeranjangDidapat = true;
            $stmt = $this->db->prepare(
                "SELECT kode_sewa, tgl_mulai, tgl_selesai, wkt_ambil
FROM t_sewa
WHERE user_plg = ?
AND status_sewa = 'keranjang'
ORDER BY kode_sewa ASC
LIMIT 1
FOR UPDATE"
            );
            if (!$stmt) {
                throw new RuntimeException(
                    'Pencarian keranjang aktif gagal disiapkan.'
                );
            }
            $stmt->bind_param('s', $userPlg);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException(
                    'Pencarian keranjang aktif gagal dijalankan.'
                );
            }
            $keranjang = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($keranjang !== null) {
                $kodeSewa = (string) $keranjang['kode_sewa'];
                $tglMulai = (string) $keranjang['tgl_mulai'];
                $tglSelesai = (string) $keranjang['tgl_selesai'];
                $wktAmbil = $this->normalisasiWaktu(
                    (string) $keranjang['wkt_ambil']
                );
            } else {
                $sekarang = new DateTimeImmutable('now');
                $tglMulai = $sekarang->format('Y-m-d');
                $tglSelesai = $sekarang->modify('+1 day')->format('Y-m-d');
                $wktAmbil = $sekarang->format('H:i:s');
                $dataKode = $this->kunciDanBuatKodeSewa();
                $kodeSewa = $dataKode['kode_sewa'];
                $namaKunci = $dataKode['nama_kunci'];
                $stmt = $this->db->prepare(
                    "INSERT INTO t_sewa (
kode_sewa,
tgl_mulai,
tgl_selesai,
wkt_ambil,
status_sewa,
user_plg
) VALUES (?, ?, ?, ?, 'keranjang', ?)"
                );
                if (!$stmt) {
                    throw new RuntimeException(
                        'Pembuatan transaksi gagal disiapkan.'
                    );
                }
                $stmt->bind_param(
                    'sssss',
                    $kodeSewa,
                    $tglMulai,
                    $tglSelesai,
                    $wktAmbil,
                    $userPlg
                );
                if (!$stmt->execute()) {
                    $stmt->close();
                    throw new RuntimeException(
                        'Transaksi keranjang gagal dibuat.'
                    );
                }
                $stmt->close();
            }
            // Cegah kendaraan yang sama dimasukkan dua kali ke keranjang aktif.
            $stmt = $this->db->prepare(
                "SELECT 1
FROM t_pilih
WHERE kode_sewa = ?
AND no_plat = ?
LIMIT 1
FOR UPDATE"
            );
            if (!$stmt) {
                throw new RuntimeException(
                    'Pemeriksaan kendaraan dalam keranjang gagal disiapkan.'
                );
            }
            $stmt->bind_param('ss', $kodeSewa, $noPlat);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException(
                    'Pemeriksaan kendaraan dalam keranjang gagal dijalankan.'
                );
            }
            $kendaraanSudahDipilih =
                $stmt->get_result()->fetch_assoc() !== null;
            $stmt->close();
            if ($kendaraanSudahDipilih) {
                throw new RuntimeException(
                    'Kendaraan sudah ada dalam keranjang ini.'
                );
            }
            $hari = $this->hitungHariSewa($tglMulai, $tglSelesai);
            $stmt = $this->db->prepare(
                "INSERT INTO t_pilih (
kode_sewa,
no_plat,
hrg_sewa,
n_hari
) VALUES (?, ?, ?, ?)"
            );
            if (!$stmt) {
                throw new RuntimeException(
                    'Detail transaksi gagal disiapkan.'
                );
            }
            $harga = (int) $kendaraan['hrg_hari'];
            $stmt->bind_param(
                'ssii',
                $kodeSewa,
                $noPlat,
                $harga,
                $hari
            );
            if (!$stmt->execute()) {
                $errno = $stmt->errno;
                $stmt->close();
                if ($errno === 1062) {
                    throw new RuntimeException(
                        'Kendaraan sudah ada dalam keranjang ini.'
                    );
                }
                throw new RuntimeException(
                    'Kendaraan gagal ditambahkan ke keranjang.'
                );
            }
            $stmt->close();
            $this->db->commit();
            if ($namaKunci !== null) {
                $this->lepasKunci($namaKunci);
                $namaKunci = null;
            }
            if ($kunciKeranjangDidapat && $namaKunciKeranjang !== null) {
                $this->lepasKunci($namaKunciKeranjang);
                $kunciKeranjangDidapat = false;
            }
            return $kodeSewa;
        } catch (Throwable $e) {
            $this->db->rollback();
            if ($namaKunci !== null) {
                $this->lepasKunci($namaKunci);
            }
            if ($kunciKeranjangDidapat && $namaKunciKeranjang !== null) {
                $this->lepasKunci($namaKunciKeranjang);
            }
            throw $e;
        }
    }
}
