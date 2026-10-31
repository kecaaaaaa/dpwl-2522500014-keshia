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
}
