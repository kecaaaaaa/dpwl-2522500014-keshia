<?php
// P8: Manajemen Penyewa (CRUD oleh admin, termasuk foto KTP dan SIM A)
class Mpenyewa_controller extends Controller
{
  private const FIELD_FOTO = ['ft_ktp', 'ft_sim_a'];

  private Penyewa_model $model;

  public function __construct()
  {
    require_once APPPATH . 'models/Penyewa_model.php';
    require_once APPPATH . 'helpers/auth_helper.php';
    require_once APPPATH . 'helpers/flash_helper.php';
    $this->model = new Penyewa_model();
  }

  // READ: tabel seluruh penyewa
  public function index(): void
  {
    require_admin_login();
    $this->render(null, null, null, get_flash_message());
  }

  // CREATE
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

    // Unggah foto. Jika salah satu gagal, file yang sudah terunggah dibersihkan.
    $baru = [];   // [field => nama file]
    $upload = $this->prosesUploadFoto($baru);
    if ($upload !== null) {
      $this->render('tambah', $in, $upload);
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
        'ft_ktp'     => $baru['ft_ktp'] ?? null,
        'ft_sim_a'   => $baru['ft_sim_a'] ?? null,
      ]);
    } catch (Throwable $e) {
      error_log('Tambah penyewa gagal: ' . $e->getMessage());
      $ok = false;
    }
    if (!$ok) {
      $this->hapusFoto($baru);   // database gagal -> file baru dibuang
      $this->render('tambah', $in, 'Data penyewa gagal disimpan.');
      return;
    }

    set_flash_message('success', 'Data penyewa berhasil ditambahkan.');
    header('Location: ' . site_url('admin/mpenyewa'));   // PRG
    exit;
  }

  // UPDATE
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
    $in['user_plg'] = $userPlg;   // kunci dari route, bukan dari form
    // Foto lama tetap ditampilkan di form saat terjadi error.
    $in['ft_ktp'] = $penyewa['ft_ktp'] ?? null;
    $in['ft_sim_a'] = $penyewa['ft_sim_a'] ?? null;
    $password = $_POST['sandi'] ?? '';

    $error = $this->validateInput($in, $password, true);
    if ($error !== null) {
      $this->render('ubah', $in, $error);
      return;
    }

    $baru = [];
    $upload = $this->prosesUploadFoto($baru);
    if ($upload !== null) {
      $this->render('ubah', $in, $upload);
      return;
    }

    $hash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null;
    // Foto lama dipertahankan jika tidak ada file baru.
    $ftKtp = $baru['ft_ktp'] ?? ($penyewa['ft_ktp'] ?? null);
    $ftSim = $baru['ft_sim_a'] ?? ($penyewa['ft_sim_a'] ?? null);

    try {
      $ok = $this->model->updateByAdmin(
        $userPlg,
        $in['nik'],
        $in['nm_lngkp'],
        $in['no_hp'],
        $in['almt_lngkp'],
        $ftKtp,
        $ftSim,
        $hash
      );
    } catch (Throwable $e) {
      error_log('Ubah penyewa gagal: ' . $e->getMessage());
      $ok = false;
    }
    if (!$ok) {
      $this->hapusFoto($baru);   // database gagal -> file baru dibuang
      $this->render('ubah', $in, 'Data penyewa gagal diperbarui.');
      return;
    }

    // Foto lama baru dihapus setelah database berhasil diperbarui.
    $lama = [];
    foreach ($baru as $field => $namaFile) {
      if (!empty($penyewa[$field])) {
        $lama[$field] = $penyewa[$field];
      }
    }
    $this->hapusFoto($lama);

    set_flash_message('success', 'Data penyewa berhasil diperbarui.');
    header('Location: ' . site_url('admin/mpenyewa'));   // PRG
    exit;
  }

  // DELETE
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

    // File foto dihapus hanya setelah baris database berhasil dihapus.
    $this->hapusFoto([
      'ft_ktp'   => $penyewa['ft_ktp'] ?? null,
      'ft_sim_a' => $penyewa['ft_sim_a'] ?? null,
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
      'username'        => $_SESSION['admin_username'],
      'penyewa'         => $this->model->getAll(),
      'flash'           => $flash,
      'mode'            => $mode,
      'penyewa_dipilih' => $dipilih,
      'error'           => $error,
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
    if (strlen($password) > 72) {
      return 'Password maksimal 72 byte.';
    }
    if ($isEdit && $password !== '' && strlen($password) < 8) {
      return 'Password baru minimal 8 karakter (kosongkan jika tidak diganti).';
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

  // Mengunggah foto KTP dan SIM A. Mengembalikan pesan error, atau null jika
  // berhasil. File yang berhasil diunggah dicatat di $baru (by reference).
  private function prosesUploadFoto(array &$baru): ?string
  {
    foreach (self::FIELD_FOTO as $field) {
      $hasil = $this->uploadFoto($field);
      if (!$hasil['success']) {
        $this->hapusFoto($baru);   // bersihkan file yang sempat terunggah
        $baru = [];
        return $hasil['error'];
      }
      if ($hasil['filename'] !== null) {
        $baru[$field] = $hasil['filename'];
      }
    }
    return null;
  }

  // Validasi dan simpan satu file foto. Pola sama dengan Penyewa_controller.
  private function uploadFoto(string $field): array
  {
    $gagal = fn(string $pesan) => ['success' => false, 'filename' => null, 'error' => $pesan];
    $label = $field === 'ft_ktp' ? 'Foto KTP' : 'Foto SIM A';

    if (!in_array($field, self::FIELD_FOTO, true)) {
      return $gagal('Field foto tidak valid.');
    }
    // Tidak ada file dipilih: dianggap sukses (foto bersifat opsional).
    if (
      !isset($_FILES[$field]) ||
      ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE
    ) {
      return ['success' => true, 'filename' => null, 'error' => null];
    }

    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
      return $gagal($label . ': unggah gagal. Periksa ukuran file dan konfigurasi PHP.');
    }
    if (
      !isset($file['tmp_name'], $file['size']) ||
      !is_uploaded_file($file['tmp_name']) ||
      (int) $file['size'] < 1 ||
      (int) $file['size'] > 5 * 1024 * 1024
    ) {
      return $gagal($label . ' harus berukuran lebih dari 0 byte dan maksimal 5 MB.');
    }

    // Cek tipe asli dari isi file, bukan dari ekstensi/nama yang dikirim browser.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $diizinkan = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($diizinkan[$mime]) || @getimagesize($file['tmp_name']) === false) {
      return $gagal($label . ' harus berformat JPEG, PNG, atau WEBP yang valid.');
    }

    $dir = dirname(APPPATH) . DIRECTORY_SEPARATOR . 'uploads'
      . DIRECTORY_SEPARATOR . 'penyewa' . DIRECTORY_SEPARATOR;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
      return $gagal('Folder penyimpanan foto penyewa tidak dapat dibuat.');
    }
    if (!is_writable($dir)) {
      return $gagal('Folder penyimpanan foto penyewa tidak dapat ditulis.');
    }

    try {
      // Nama acak: tidak memakai nama asli file dari pengguna.
      $nama = $field . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $diizinkan[$mime];
    } catch (Throwable $e) {
      return $gagal('Nama file foto gagal dibuat.');
    }
    if (!move_uploaded_file($file['tmp_name'], $dir . $nama)) {
      return $gagal($label . ' gagal dipindahkan ke folder penyimpanan.');
    }
    return ['success' => true, 'filename' => $nama, 'error' => null];
  }

  // Menghapus file foto. Parameter: [field => nama file]. Nama file dicek dengan
  // pola ketat agar tidak mungkin menghapus file lain (path traversal).
  private function hapusFoto(array $files): void
  {
    $dir = dirname(APPPATH) . DIRECTORY_SEPARATOR . 'uploads'
      . DIRECTORY_SEPARATOR . 'penyewa' . DIRECTORY_SEPARATOR;

    foreach ($files as $field => $nama) {
      if (!in_array($field, self::FIELD_FOTO, true) || !is_string($nama) || $nama === '') {
        continue;
      }
      $pola = '/^' . preg_quote($field, '/') . '_\d{10,14}_[a-f0-9]{12,16}\.(jpg|jpeg|png|webp)$/';
      if (preg_match($pola, $nama) && is_file($dir . $nama)) {
        @unlink($dir . $nama);
      }
    }
  }
}
