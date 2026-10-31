<?php
require_once APPPATH . 'models/Transaksi_model.php';
class Transaksi_controller extends Controller
{
    private Transaksi_model $transaksiModel;
    public function __construct()
    {
        $this->transaksiModel = new Transaksi_model();
    }
    public function tambah(): void
    {
        $userPlg = $this->wajibLoginPenyewa();
        $this->wajibMetode('POST');
        $noPlat = trim((string) ($_POST['no_plat'] ?? ''));
        $asal = (string) ($_POST['return_to'] ?? 'katalog');
        $asal = $asal === 'detail' ? 'detail' : 'katalog';
        if ($noPlat === '') {
            $this->redirectTambahDenganPesan(
                $asal,
                $noPlat,
                'error',
                'Kendaraan wajib dipilih terlebih dahulu.'
            );
        }
        try {
            $this->transaksiModel->tambahKeKeranjang(
                $userPlg,
                $noPlat
            );
            $this->redirectTambahDenganPesan(
                $asal,
                $noPlat,
                'success',
                'Mobil berhasil ditambahkan ke keranjang.'
            );
        } catch (InvalidArgumentException $e) {
            $this->redirectTambahDenganPesan(
                $asal,
                $noPlat,
                'error',
                $e->getMessage()
            );
        } catch (Throwable $e) {
            error_log(
                'Tambah keranjang gagal: ' . $e->getMessage()
            );
            $this->redirectTambahDenganPesan(
                $asal,
                $noPlat,
                'error',
                $this->pesanBisnisAman($e->getMessage())

            );
        }
    }
    private function wajibLoginPenyewa(): string
    {
        $userPlg = trim(
            (string) ($_SESSION['penyewa_user_plg'] ?? '')
        );
        if ($userPlg === '') {
            $_SESSION['flash_penyewa_nav'] =
                'Silakan login terlebih dahulu untuk mengakses transaksi.';
            header('Location: ' . site_url('auth/login-penyewa'));
            exit;
        }
        return $userPlg;
    }
    private function wajibMetode(string $metodeDiizinkan): void
    {
        $metodeAktif = strtoupper(
            (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')
        );
        if ($metodeAktif !== $metodeDiizinkan) {
            http_response_code(405);
            header('Allow: ' . $metodeDiizinkan);
            exit('Method Not Allowed');
        }
    }
    private function redirectTambahDenganPesan(
        string $asal,
        string $noPlat,
        string $jenis,
        string $pesan
    ): never {
        $_SESSION['flash_transaksi'] = [
            'type' => $jenis,
            'message' => $pesan
        ];
        if ($asal === 'detail' && $noPlat !== '') {
            $uri = 'katalog/detail/' . rawurlencode($noPlat);
        } else {
            $uri = 'katalog';
        }
        header('Location: ' . site_url($uri));
        exit;
    }
    private function pesanBisnisAman(string $pesan): string
    {
        $pesanDikenal = [
            'Kendaraan tidak berstatus tersedia.',
            'Kendaraan sudah ada dalam keranjang ini.',
            'Kendaraan tidak ditemukan.',
            'Nomor transaksi harian telah mencapai batas 99.',
            'Kunci proses tidak diperoleh. Silakan coba lagi.'
        ];
        return in_array($pesan, $pesanDikenal, true)
            ? $pesan
            : 'Permintaan transaksi belum berhasil. Periksa data dan coba kembali.';
    }
}
