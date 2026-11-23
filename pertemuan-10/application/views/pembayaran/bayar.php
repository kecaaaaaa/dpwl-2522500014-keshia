<?php
// P9 BARU - application/views/pembayaran/bayar.php
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
$p = $pembayaran;
$st = $labelStatus[$p['sttus_byr']] ?? [$p['sttus_byr'], 'bg-secondary'];
$detail = is_array($p['detail'] ?? null) ? $p['detail'] : [];
$total = (int) $p['total'];
$nominal = (int) $p['nominal'];
$bisaUnggah = $p['status_sewa'] === 'diajukan'
    && in_array($p['sttus_byr'], ['pending', 'invalid'], true);
$adaBukti = !empty($p['ft_byr']);
$urlBukti = site_url('pembayaran/bukti?kode_bayar=' . rawurlencode($p['kode_bayar']));
?>
<section class="bg-success py-4">
    <div class="container">
        <h1 class="h2 text-white mt-3">Pembayaran DP</h1>
        <p class="text-white mb-0">Transfer uang muka, lalu unggah bukti pembayaran untuk diverifikasi admin.</p>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h2 class="h5 mb-0">Rincian Transaksi</h2>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Kode transaksi</dt>
                            <dd class="col-sm-7"><?= $e($p['kode_sewa']) ?></dd>
                            <dt class="col-sm-5">Kode pembayaran</dt>
                            <dd class="col-sm-7"><?= $e($p['kode_bayar']) ?></dd>
                            <dt class="col-sm-5">Periode sewa</dt>
                            <dd class="col-sm-7"><?= $e($p['tgl_mulai']) ?> s/d <?= $e($p['tgl_selesai']) ?></dd>
                            <dt class="col-sm-5">Status transaksi</dt>
                            <dd class="col-sm-7"><?= $e($p['status_sewa']) ?></dd>
                            <dt class="col-sm-5">Status pembayaran</dt>
                            <dd class="col-sm-7"><span class="badge <?= $e($st[1]) ?>"><?= $e($st[0]) ?></span></dd>
                        </dl>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle bg-white">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Kendaraan</th>
                                <th scope="col" class="text-center">Durasi</th>
                                <th scope="col" class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detail as $item): ?>
                                <tr>
                                    <td><?= $e($item['nm_mobil'] ?? $item['no_plat']) ?> (<?= $e($item['no_plat']) ?>)</td>
                                    <td class="text-center"><?= $e($item['n_hari']) ?> hari</td>
                                    <td class="text-end text-nowrap"><?= $e($formatRupiah((int) $item['hrg_sewa'] * (int) $item['n_hari'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-success mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total sewa</span>
                            <strong><?= $e($formatRupiah($total)) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>DP yang harus dibayar (30%)</span>
                            <strong class="text-success"><?= $e($formatRupiah($nominal)) ?></strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>Sisa setelah DP</span>
                            <strong><?= $e($formatRupiah($total - $nominal)) ?></strong>
                        </div>
                    </div>
                </div>

                <?php if ($adaBukti): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h2 class="h6 mb-0">Bukti yang diunggah</h2>
                        </div>
                        <div class="card-body">
                            <a href="<?= $e($urlBukti) ?>" target="_blank" rel="noopener">
                                <img src="<?= $e($urlBukti) ?>" alt="Bukti pembayaran" class="img-fluid rounded border">
                            </a>
                            <p class="small text-muted mt-2 mb-0">
                                Diunggah: <?= $e($p['tgl_wkt_byr']) ?>
                                <?php if (!empty($p['tgl_wkt_ver'])): ?>
                                    <br>Diverifikasi: <?= $e($p['tgl_wkt_ver']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($p['sttus_byr'] === 'valid'): ?>
                    <div class="alert alert-success">Pembayaran DP Anda sudah diterima dan transaksi telah disetujui.</div>
                <?php elseif ($p['sttus_byr'] === 'invalid'): ?>
                    <div class="alert alert-danger">Pembayaran DP ditolak admin. Silakan unggah ulang bukti transfer yang benar.</div>
                <?php endif; ?>

                <?php if ($bisaUnggah): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h2 class="h6 mb-0"><?= $adaBukti ? 'Ganti Bukti Pembayaran' : 'Unggah Bukti Pembayaran' ?></h2>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted">Transfer sebesar nominal DP ke rekening rental sesuai informasi dari admin, lalu unggah bukti transfer.</p>
                            <form method="post" enctype="multipart/form-data" action="<?= $e(site_url('pembayaran/simpan')) ?>">
                                <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                                <input type="hidden" name="kode_sewa" value="<?= $e($p['kode_sewa']) ?>">
                                <input type="hidden" name="kode_bayar" value="<?= $e($p['kode_bayar']) ?>">
                                <div class="mb-3">
                                    <label for="ft_byr" class="form-label">Bukti transfer (JPEG, PNG, atau WEBP, maks. 5 MB)</label>
                                    <input type="file" class="form-control" id="ft_byr" name="ft_byr"
                                        accept="image/jpeg,image/png,image/webp" required>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-upload me-1" aria-hidden="true"></i> Kirim Bukti Pembayaran
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-4">
            <a class="btn btn-outline-secondary" href="<?= $e(site_url('pembayaran')) ?>">
                <i class="fas fa-arrow-left me-1" aria-hidden="true"></i> Daftar Pembayaran
            </a>
        </div>
    </div>
</section>
<?php require APPPATH . 'views/katalog/footer.php'; ?>