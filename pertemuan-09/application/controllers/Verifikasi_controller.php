<?php
// P9 BARU - application/controllers/Verifikasi_controller.php
// Verifikasi pembayaran DP oleh admin.
require_once APPPATH . 'models/Pembayaran_model.php';
require_once APPPATH . 'helpers/auth_helper.php';
require_once APPPATH . 'helpers/flash_helper.php';
require_once APPPATH . 'helpers/csrf_helper.php';

class Verifikasi_controller extends Controller
{
    private Pembayaran_model $model;

    public function __construct()
    {
        $this->model = new Pembayaran_model();
    }

    // GET admin/verifikasi[?status=pending|valid|invalid]
    public function index(): void
    {
        require_admin_login();
        $this->wajibMetode('GET');
        $status = (string) ($_GET['status'] ?? '');
        if (!in_array($status, ['pending', 'valid', 'invalid'], true)) {
            $status = '';
        }
        try {
            $this->view('admin/verifikasi_view', [
                'username' => $_SESSION['admin_username'],
                'mode' => 'daftar',
                'daftar' => $this->model->getDaftarVerifikasi($status),
                'status' => $status,
                'detail' => null,
                'csrf' => '',
                'flash' => get_flash_message()
            ]);
        } catch (Throwable $e) {
            error_log('Daftar verifikasi gagal: ' . $e->getMessage());
            http_response_code(500);
            exit('Daftar verifikasi belum dapat ditampilkan.');
        }
    }

    // GET admin/verifikasi/detail?kode_bayar=...
    public function detail(): void
    {
        require_admin_login();
        $this->wajibMetode('GET');
        $kodeBayar = trim((string) ($_GET['kode_bayar'] ?? ''));
        try {
            $detail = $kodeBayar === '' ? null : $this->model->getDetailVerifikasi($kodeBayar);
            if ($detail === null) {
                set_flash_message('danger', 'Data pembayaran tidak ditemukan.');
                header('Location: ' . site_url('admin/verifikasi'));
                exit;
            }
            $this->view('admin/verifikasi_view', [
                'username' => $_SESSION['admin_username'],
                'mode' => 'detail',
                'daftar' => [],
                'status' => '',
                'detail' => $detail,
                'csrf' => csrf_token(),
                'flash' => get_flash_message()
            ]);
        } catch (Throwable $e) {
            error_log('Detail verifikasi gagal: ' . $e->getMessage());
            http_response_code(500);
            exit('Detail verifikasi belum dapat ditampilkan.');
        }
    }

    // POST admin/verifikasi/proses
    public function proses(): void
    {
        require_admin_login();
        $this->wajibMetode('POST');
        if (!csrf_valid($_POST['csrf'] ?? null)) {
            http_response_code(403);
            exit('Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
        }
        $kodeBayar = trim((string) ($_POST['kode_bayar'] ?? ''));
        $keputusan = (string) ($_POST['keputusan'] ?? '');
        if ($kodeBayar === '' || !in_array($keputusan, ['valid', 'invalid'], true)) {
            http_response_code(400);
            exit('Data verifikasi tidak lengkap atau tidak valid.');
        }
        $uriDetail = 'admin/verifikasi/detail?kode_bayar=' . rawurlencode($kodeBayar);
        try {
            $this->model->verifikasi($kodeBayar, $keputusan);
            // Skema t_bayar tidak memiliki kolom admin pemverifikasi; jejak dicatat di log.
            error_log(sprintf(
                'Verifikasi pembayaran %s = %s oleh admin %s',
                $kodeBayar,
                $keputusan,
                (string) $_SESSION['admin_username']
            ));
            set_flash_message(
                'success',
                $keputusan === 'valid'
                    ? 'Pembayaran DP diterima. Transaksi disetujui.'
                    : 'Pembayaran DP ditolak. Penyewa dapat mengunggah ulang bukti.'
            );
        } catch (InvalidArgumentException $e) {
            set_flash_message('danger', $e->getMessage());
        } catch (Throwable $e) {
            error_log('Verifikasi pembayaran gagal: ' . $e->getMessage());
            set_flash_message('danger', 'Verifikasi belum berhasil. Silakan coba kembali.');
        }
        header('Location: ' . site_url($uriDetail));
        exit;
    }

    private function wajibMetode(string $metodeDiizinkan): void
    {
        $aktif = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if ($aktif !== $metodeDiizinkan) {
            http_response_code(405);
            header('Allow: ' . $metodeDiizinkan);
            exit('Method Not Allowed');
        }
    }
}
