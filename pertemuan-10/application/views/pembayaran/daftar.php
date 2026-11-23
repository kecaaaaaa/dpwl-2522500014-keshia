<?php
// P9 BARU - application/views/pembayaran/daftar.php
require APPPATH . 'views/katalog/header.php';
$e = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
require APPPATH . 'views/katalog/navbar.php';
$formatRupiah = static fn($n): string => 'Rp ' . number_format((float) $n, 0, ',', '.');
$labelStatus = [
    'pending' => ['Menunggu verifikasi', 'bg-warning text-dark'],
    'valid' => ['Diterima', 'bg-success'],
    'invalid' => ['Ditolak', 'bg-danger'],
];
$daftar = is_array($daftar ?? null) ? $daftar : [];
?>
<section class="bg-success py-4">
    <div class="container">
        <h1 class="h2 text-white mt-3">Pembayaran DP Saya</h1>
        <p class="text-white mb-0">Unggah bukti transfer uang muka dan pantau hasil verifikasi admin.</p>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <?php if (count($daftar) === 0): ?>
            <div class="alert alert-info">Belum ada pembayaran DP. Pembayaran dibuat saat Anda melakukan checkout.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle bg-white">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Kode Transaksi</th>
                            <th scope="col">Kode Bayar</th>
                            <th scope="col">Periode</th>
                            <th scope="col" class="text-end">Nominal DP</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftar as $row): ?>
                            <?php
                            $st = $labelStatus[$row['sttus_byr']] ?? [$row['sttus_byr'], 'bg-secondary'];
                            $bisaBayar = $row['status_sewa'] === 'diajukan'
                                && in_array($row['sttus_byr'], ['pending', 'invalid'], true);
                            $urlBayar = site_url('pembayaran/bayar?kode_sewa=' . rawurlencode($row['kode_sewa']));
                            ?>
                            <tr>
                                <td><?= $e($row['kode_sewa']) ?></td>
                                <td><?= $e($row['kode_bayar']) ?></td>
                                <td><?= $e($row['tgl_mulai']) ?> s/d <?= $e($row['tgl_selesai']) ?></td>
                                <td class="text-end text-nowrap"><?= $e($formatRupiah($row['nominal'])) ?></td>
                                <td>
                                    <span class="badge <?= $e($st[1]) ?>"><?= $e($st[0]) ?></span>
                                    <?php if (empty($row['ft_byr']) && $row['sttus_byr'] === 'pending'): ?>
                                        <div class="small text-muted">Bukti belum diunggah</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a class="btn btn-sm <?= $bisaBayar ? 'btn-success' : 'btn-outline-secondary' ?>"
                                        href="<?= $e($urlBayar) ?>">
                                        <?= $bisaBayar ? 'Bayar / Unggah Bukti' : 'Lihat' ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require APPPATH . 'views/katalog/footer.php'; ?>