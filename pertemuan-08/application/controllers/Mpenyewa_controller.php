<?php
// P8: Manajemen Penyewa (CRUD oleh admin)
class Mpenyewa_controller extends Controller
{
  private Penyewa_model $model;

  public function __construct()
  {
    require_once APPPATH . 'models/Penyewa_model.php';
    require_once APPPATH . 'helpers/auth_helper.php';
    require_once APPPATH . 'helpers/flash_helper.php';
    $this->model = new Penyewa_model();
  }

  public function index(): void
  {
    require_admin_login();
    $this->render(null, null, null, get_flash_message());
  }

  public function tambah(): void
  {
    require_admin_login();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      $this->render('tambah', []);
      return;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      exit('Metode request tidak diizinkan.');
    }

    $in = $this->readInput();
    $password = $_POST['sandi'] ?? '';

    $error = $this->validateInput($in, $password, false);
    if ($error === null && $this->model->findByUserPlg($in['user_plg']) !== null) {
      $error = 'Username sudah digunakan.';
    }
    if ($error !== null) {
      $this->render('tambah', $in, $error);
      return;
    }

    try {
      $ok = $this->model->create([
        'user_plg'   => $in['user_plg'],
        'sandi'      => password_hash($password, PASSWORD_DEFAULT),
        'nik'        => $in['nik'],
        'nm_lngkp'   => $in['nm_lngkp'],
        'no_hp'      => $in['no_hp'],
        'almt_lngkp' => $in['almt_lngkp'],
      ]);
    } catch (Throwable $e) {
      error_log('Tambah penyewa gagal: ' . $e->getMessage());
      $ok = false;
    }
    if (!$ok) {
      $this->render('tambah', $in, 'Data penyewa gagal disimpan.');
      return;
    }

    set_flash_message('success', 'Data penyewa berhasil ditambahkan.');
    header('Location: ' . site_url('admin/mpenyewa'));
    exit;
  }

  public function ubah(string $userPlg): void
  {
    require_admin_login();

    $penyewa = $this->model->getProfil($userPlg);
    if ($penyewa === null) {
      set_flash_message('danger', 'Data penyewa tidak ditemukan.');
      header('Location: ' . site_url('admin/mpenyewa'));
      exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      $this->render('ubah', $penyewa);
      return;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      exit('Metode request tidak diizinkan.');
    }

    $in = $this->readInput();
    $in['user_plg'] = $userPlg;               // kunci dari route, bukan dari form
    $password = $_POST['sandi'] ?? '';

    $error = $this->validateInput($in, $password, true);
    if ($error !== null) {
      $this->render('ubah', $in, $error);
      return;
    }

    $hash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null;

    try {
      $ok = $this->model->updateByAdmin(
        $userPlg,
        $in['nik'],
        $in['nm_lngkp'],
        $in['no_hp'],
        $in['almt_lngkp'],
        $hash
      );
    } catch (Throwable $e) {
      error_log('Ubah penyewa gagal: ' . $e->getMessage());
      $ok = false;
    }
    if (!$ok) {
      $this->render('ubah', $in, 'Data penyewa gagal diperbarui.');
      return;
    }

    set_flash_message('success', 'Data penyewa berhasil diperbarui.');
    header('Location: ' . site_url('admin/mpenyewa'));
    exit;
  }

  public function hapus(string $userPlg): void
  {
    require_admin_login();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      exit('Metode request tidak diizinkan.');
    }

    $penyewa = $this->model->getProfil($userPlg);
    if ($penyewa === null) {
      set_flash_message('danger', 'Data penyewa tidak ditemukan.');
      header('Location: ' . site_url('admin/mpenyewa'));
      exit;
    }

    try {
      $ok = $this->model->delete($userPlg);
    } catch (Throwable $e) {
      // Biasanya ditolak foreign key: penyewa masih punya data di t_sewa.
      error_log('Hapus penyewa gagal: ' . $e->getMessage());
      $ok = false;
    }
    if (!$ok) {
      set_flash_message(
        'danger',
        'Penyewa tidak dapat dihapus. Kemungkinan masih memiliki riwayat transaksi sewa.'
      );
      header('Location: ' . site_url('admin/mpenyewa'));
      exit;
    }

    // Hapus file foto KTP/SIM hanya setelah baris database terhapus.
    $this->hapusFoto([
      ['ft_ktp', $penyewa['ft_ktp'] ?? null],
      ['ft_sim_a', $penyewa['ft_sim_a'] ?? null],
    ]);

    set_flash_message('success', 'Data penyewa berhasil dihapus.');
    header('Location: ' . site_url('admin/mpenyewa'));
    exit;
  }

  // ---------- helper privat ----------

  private function readInput(): array
  {
    return [
      'user_plg'   => trim($_POST['user_plg'] ?? ''),
      'nik'        => trim($_POST['nik'] ?? ''),
      'nm_lngkp'   => trim($_POST['nm_lngkp'] ?? ''),
      'no_hp'      => trim($_POST['no_hp'] ?? ''),
      'almt_lngkp' => trim($_POST['almt_lngkp'] ?? ''),
    ];
  }

  private function render(?string $mode, ?array $dipilih = null, ?string $error = null, ?array $flash = null): void
  {
    $this->view('admin/mpenyewa_view', [
      'username'       => $_SESSION['admin_username'],
      'penyewa'        => $this->model->getAll(),
      'flash'          => $flash,
      'mode'           => $mode,
      'penyewa_dipilih' => $dipilih,
      'error'          => $error,
    ]);
  }

  // Aturan sama dengan Auth::register agar data konsisten.
  private function validateInput(array $in, string $password, bool $isEdit): ?string
  {
    if (!$isEdit && !preg_match('/\A[A-Za-z0-9._-]{1,15}\z/', $in['user_plg'])) {
      return 'Username harus 1–15 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.';
    }
    if (!$isEdit && strlen($password) < 8) {
      return 'Password minimal 8 karakter.';
    }
    if ($isEdit && $password !== '' && (strlen($password) < 8 || strlen($password) > 72)) {
      return 'Password baru harus 8–72 karakter (kosongkan jika tidak diganti).';
    }
    if (!preg_match('/\A[0-9]{16}\z/', $in['nik'])) {
      return 'NIK harus terdiri dari 16 digit angka.';
    }
    if ($in['nm_lngkp'] === '' || mb_strlen($in['nm_lngkp']) > 100) {
      return 'Nama lengkap wajib diisi, maksimal 100 karakter.';
    }
    if (
      $in['no_hp'] === '' || strlen($in['no_hp']) > 15 ||
      !preg_match('/\A[0-9+(). -]+\z/', $in['no_hp'])
    ) {
      return 'Nomor HP wajib diisi dan formatnya tidak valid.';
    }
    if ($in['almt_lngkp'] === '' || mb_strlen($in['almt_lngkp']) > 300) {
      return 'Alamat wajib diisi, maksimal 300 karakter.';
    }
    return null;
  }

  // Pola nama file sama dengan Penyewa_controller::hapusFotoPenyewa().
  private function hapusFoto(array $items): void
  {
    $dir = dirname(APPPATH) . DIRECTORY_SEPARATOR . 'uploads'
      . DIRECTORY_SEPARATOR . 'penyewa' . DIRECTORY_SEPARATOR;

    foreach ($items as [$field, $filename]) {
      if (!is_string($filename) || $filename === '') {
        continue;
      }
      $pattern = '/^' . preg_quote($field, '/') .
        '_\d{10,14}_[a-f0-9]{12,16}\.(jpg|jpeg|png|webp)$/';
      if (preg_match($pattern, $filename) && is_file($dir . $filename)) {
        @unlink($dir . $filename);
      }
    }
  }
}
