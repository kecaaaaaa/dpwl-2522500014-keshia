<?php
// P9 BARU - application/views/admin/verifikasi_view.php (mode: daftar | detail)
require APPPATH . '/views/templates/header.php';
require APPPATH . '/views/templates/sidebar.php';

$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$rp = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$flash = $flash ?? null;
$flashClass = (($flash['type'] ?? '') === 'success') ? 'alert-success' : 'alert-danger';
$labelStatus = [
  'pending' => ['Menunggu verifikasi', 'label-warning'],
  'valid' => ['Diterima', 'label-success'],
  'invalid' => ['Ditolak', 'label-danger'],
];
$isDetail = ($mode ?? 'daftar') === 'detail';
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Verifikasi Pembayaran <small>DP transfer penyewa</small></h1>
    <ol class="breadcrumb">
      <li><a href="<?= $e(site_url('admin')) ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Verifikasi Pembayaran</li>
    </ol>
  </section>

  <section class="content">
    <?php if (!empty($flash)): ?>
      <div class="alert <?= $flashClass ?> alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fa <?= (($flash['type'] ?? '') === 'success') ? 'fa-check' : 'fa-warning' ?>"></i>
        <?= $e($flash['message'] ?? '') ?>
      </div>
    <?php endif; ?>

    <?php if (!$isDetail): ?>
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">Daftar Pembayaran DP</h3>
          <div class="box-tools">
            <?php
            $filter = ['' => 'Semua', 'pending' => 'Menunggu', 'valid' => 'Diterima', 'invalid' => 'Ditolak'];
            foreach ($filter as $k => $label):
              $url = site_url('admin/verifikasi' . ($k === '' ? '' : '?status=' . $k));
            ?>
              <a href="<?= $e($url) ?>" class="btn btn-sm <?= ($status ?? '') === $k ? 'btn-primary' : 'btn-default' ?>"><?= $e($label) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="box-body">
          <div class="table-responsive">
            <table class="table table-bordered table-striped js-data-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Kode Bayar</th>
                  <th>Kode Transaksi</th>
                  <th>Penyewa</th>
                  <th>Nominal DP</th>
                  <th>Waktu Bayar</th>
                  <th>Bukti</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($daftar as $no => $row): ?>
                  <?php $st = $labelStatus[$row['sttus_byr']] ?? [$row['sttus_byr'], 'label-default']; ?>
                  <tr>
                    <td><?= (int) $no + 1 ?></td>
                    <td><?= $e($row['kode_bayar']) ?></td>
                    <td><?= $e($row['kode_sewa']) ?></td>
                    <td><?= $e($row['nm_lngkp'] ?? $row['user_plg']) ?> (<?= $e($row['user_plg']) ?>)</td>
                    <td><?= $e($rp($row['nominal'])) ?></td>
                    <td><?= $e($row['tgl_wkt_byr']) ?></td>
                    <td><?= empty($row['ft_byr']) ? '<span class="text-muted">Belum ada</span>' : 'Ada' ?></td>
                    <td><span class="label <?= $e($st[1]) ?>"><?= $e($st[0]) ?></span></td>
                    <td>
                      <a class="btn btn-info btn-xs"
                        href="<?= $e(site_url('admin/verifikasi/detail?kode_bayar=' . rawurlencode($row['kode_bayar']))) ?>">
                        <i class="fa fa-search"></i> Detail
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    <?php else: ?>
      <?php
      $d = $detail;
      $st = $labelStatus[$d['sttus_byr']] ?? [$d['sttus_byr'], 'label-default'];
      $adaBukti = !empty($d['ft_byr']);
      $urlBukti = site_url('pembayaran/bukti?kode_bayar=' . rawurlencode($d['kode_bayar']));
      $bisaVerifikasi = $d['sttus_byr'] === 'pending' && $adaBukti && $d['status_sewa'] === 'diajukan';
      $nominalSesuai = (int) $d['nominal'] === (int) $d['dp_seharusnya'];
      ?>
      <div class="row">
        <div class="col-md-6">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Data Pembayaran</h3>
            </div>
            <div class="box-body">
              <table class="table table-condensed">
                <tr>
                  <th style="width:40%">Kode pembayaran</th>
                  <td><?= $e($d['kode_bayar']) ?></td>
                </tr>
                <tr>
                  <th>Kode transaksi</th>
                  <td><?= $e($d['kode_sewa']) ?></td>
                </tr>
                <tr>
                  <th>Penyewa</th>
                  <td><?= $e($d['nm_lngkp'] ?? '-') ?> (<?= $e($d['user_plg']) ?>)</td>
                </tr>
                <tr>
                  <th>No. HP</th>
                  <td><?= $e($d['no_hp'] ?? '-') ?></td>
                </tr>
                <tr>
                  <th>Periode sewa</th>
                  <td><?= $e($d['tgl_mulai']) ?> s/d <?= $e($d['tgl_selesai']) ?>, ambil <?= $e(substr((string) $d['wkt_ambil'], 0, 5)) ?></td>
                </tr>
                <tr>
                  <th>Status transaksi</th>
                  <td><?= $e($d['status_sewa']) ?></td>
                </tr>
                <tr>
                  <th>Waktu bayar / unggah</th>
                  <td><?= $e($d['tgl_wkt_byr']) ?></td>
                </tr>
                <tr>
                  <th>Waktu verifikasi</th>
                  <td><?= $e($d['tgl_wkt_ver'] ?? '-') ?></td>
                </tr>
                <tr>
                  <th>Status pembayaran</th>
                  <td><span class="label <?= $e($st[1]) ?>"><?= $e($st[0]) ?></span></td>
                </tr>
              </table>
            </div>
          </div>
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Rincian Sewa</h3>
            </div>
            <div class="box-body table-responsive">
              <table class="table table-bordered table-condensed">
                <thead>
                  <tr>
                    <th>Kendaraan</th>
                    <th>Durasi</th>
                    <th class="text-right">Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($d['detail'] as $item): ?>
                    <tr>
                      <td><?= $e($item['nm_mobil'] ?? $item['no_plat']) ?> (<?= $e($item['no_plat']) ?>)</td>
                      <td><?= $e($item['n_hari']) ?> hari</td>
                      <td class="text-right"><?= $e($rp((int) $item['hrg_sewa'] * (int) $item['n_hari'])) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="2">Total sewa</th>
                    <th class="text-right"><?= $e($rp($d['total'])) ?></th>
                  </tr>
                  <tr>
                    <th colspan="2">DP 30% (seharusnya)</th>
                    <th class="text-right"><?= $e($rp($d['dp_seharusnya'])) ?></th>
                  </tr>
                  <tr>
                    <th colspan="2">Nominal DP tercatat</th>
                    <th class="text-right"><?= $e($rp($d['nominal'])) ?></th>
                  </tr>
                </tfoot>
              </table>
              <?php if (!$nominalSesuai): ?>
                <p class="text-red"><i class="fa fa-warning"></i> Nominal DP tercatat tidak sama dengan 30% total sewa.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Bukti Pembayaran</h3>
            </div>
            <div class="box-body text-center">
              <?php if ($adaBukti): ?>
                <a href="<?= $e($urlBukti) ?>" target="_blank" rel="noopener">
                  <img src="<?= $e($urlBukti) ?>" alt="Bukti pembayaran" class="img-responsive img-thumbnail" style="max-height:520px;margin:0 auto;">
                </a>
              <?php else: ?>
                <p class="text-muted">Penyewa belum mengunggah bukti pembayaran.</p>
              <?php endif; ?>
            </div>
            <div class="box-footer">
              <?php if ($bisaVerifikasi): ?>
                <?php foreach (
                  [
                    'valid' => ['Terima Pembayaran', 'btn-success', 'fa-check', 'Terima pembayaran DP ini dan setujui transaksi?'],
                    'invalid' => ['Tolak Pembayaran', 'btn-danger', 'fa-times', 'Tolak pembayaran DP ini? Penyewa dapat mengunggah ulang bukti.']
                  ] as $nilai => $aksi
                ): ?>
                  <form method="post" style="display:inline"
                    action="<?= $e(site_url('admin/verifikasi/proses')) ?>"
                    onsubmit="return confirm('<?= $e($aksi[3]) ?>');">
                    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
                    <input type="hidden" name="kode_bayar" value="<?= $e($d['kode_bayar']) ?>">
                    <input type="hidden" name="keputusan" value="<?= $e($nilai) ?>">
                    <button type="submit" class="btn <?= $e($aksi[1]) ?>"><i class="fa <?= $e($aksi[2]) ?>"></i> <?= $e($aksi[0]) ?></button>
                  </form>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-muted" style="margin:0">Pembayaran ini tidak dapat diverifikasi (sudah diverifikasi, belum ada bukti, atau transaksi tidak berstatus diajukan).</p>
              <?php endif; ?>
              <a class="btn btn-default pull-right" href="<?= $e(site_url('admin/verifikasi')) ?>">Kembali</a>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </section>
</div>
<?php require APPPATH . '/views/templates/footer.php'; ?>