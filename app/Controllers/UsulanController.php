<?php

namespace App\Controllers;

use App\Models\AkunModel;
use App\Models\InstansiModel;
use App\Models\UsulanModel;
use App\Models\KompetensiUsulanModel;
use App\Models\KompetensiExistingModel;
use App\Models\TingkatKompetensiModel;
use App\Libraries\FirebaseService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UsulanController extends BaseController
{
    protected $akunModel;
    protected $instansiModel;
    protected $firebase;
    protected $usulanModel;
    protected $kompetensiUsulanModel;
    protected $kompetensiExistingModel;
    protected $tingkatKompetensiModel;

    public function __construct()
    {
        $this->akunModel = new AkunModel();
        $this->instansiModel = new InstansiModel();
        $this->firebase = new FirebaseService();
        $this->usulanModel = new UsulanModel();
        $this->kompetensiUsulanModel = new KompetensiUsulanModel();
        $this->kompetensiExistingModel = new KompetensiExistingModel();
        $this->tingkatKompetensiModel = new TingkatKompetensiModel();
    }

    public function index()
    {
        $akunId = session()->get('id'); // Pastikan user sudah login dan id tersimpan di session

        $akun = $this->akunModel
            ->select('akun.*, instansi.nama_instansi')
            ->join('instansi', 'instansi.id = akun.id_instansi')
            ->where('akun.id', $akunId)
            ->first();

        return view('usulan', [
            'akun' => $akun
        ]);
    }


    public function tambah()
    {
        $validation = \Config\Services::validation();
    
        $validation->setRules([
            'tanggal_surat' => 'required',
            'nomor_surat' => 'required',
            'berkas_usulan' => [
                'label' => 'Berkas Usulan',
                'rules' => 'uploaded[berkas_usulan]|max_size[berkas_usulan,20480]|ext_in[berkas_usulan,zip,pdf,doc,docx]'
            ],
            'surat_usulan' => [
                'label' => 'Surat Usulan',
                'rules' => 'uploaded[surat_usulan]|max_size[surat_usulan,20480]|ext_in[surat_usulan,pdf,doc,docx]'
            ]
        ]);
    
        // Debug biar kalau error keluar
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        }        
    
        // Upload Files
        $berkasUsulan = $this->request->getFile('berkas_usulan');
        $suratUsulan = $this->request->getFile('surat_usulan');
    
        $berkasUsulanName = $berkasUsulan->getRandomName();
        $suratUsulanName = $suratUsulan->getRandomName();
    
        $berkasUsulan->move('writable/uploads', $berkasUsulanName);
        $suratUsulan->move('writable/uploads', $suratUsulanName);
    
        $this->usulanModel->insert([
            'akun_id' => session()->get('id'),
            'no_surat' => $this->request->getPost('nomor_surat'),
            'tanggal_usulan' => $this->request->getPost('tanggal_surat'),
            'surat_usulan' => $suratUsulanName,
            'berkas_usulan' => $berkasUsulanName
        ]);
    
        $usulanId = $this->usulanModel->getInsertID();
    
        // 🔥 Data input field yang akan ditangkap (14 tingkat, 2 jabatan)
        $tingkatan = ['utama', 'madya', 'muda', 'pertama', 'penyelia', 'mahir', 'terampil'];
    
        $tingkatMap = [
            'utama' => 1,
            'madya' => 2,
            'muda' => 3,
            'pertama' => 4,
            'penyelia' => 5,
            'mahir' => 6,
            'terampil' => 7,
        ];
    
        foreach ($tingkatan as $tingkat) {
            $prakomTingkatId = $tingkatMap[$tingkat];      // 1-7
            $statistisiTingkatId = $tingkatMap[$tingkat] + 7; // 8-14
    
            // Statistisi
            $statistisiUsulan = (int) $this->request->getPost("statistisi_{$tingkat}_usulan");
            $statistisiExisting = (int) $this->request->getPost("statistisi_{$tingkat}_existing");
    
            // Prakom
            $prakomUsulan = (int) $this->request->getPost("prakom_{$tingkat}_usulan");
            $prakomExisting = (int) $this->request->getPost("prakom_{$tingkat}_existing");
    
            // Insert Statistisi Usulan
            $this->kompetensiUsulanModel->insert([
                'usulan_id' => $usulanId,
                'tingkat_kompetensi_id' => $statistisiTingkatId, // Perbaikan disini
                'jumlah' => $statistisiUsulan
            ]);
    
            // Insert Statistisi Existing
            $this->kompetensiExistingModel->insert([
                'usulan_id' => $usulanId,
                'tingkat_kompetensi_id' => $statistisiTingkatId, // Perbaikan disini
                'jumlah' => $statistisiExisting
            ]);
    
            // Insert Prakom Usulan
            $this->kompetensiUsulanModel->insert([
                'usulan_id' => $usulanId,
                'tingkat_kompetensi_id' => $prakomTingkatId, // Prakom tetap 1–7
                'jumlah' => $prakomUsulan
            ]);
    
            // Insert Prakom Existing
            $this->kompetensiExistingModel->insert([
                'usulan_id' => $usulanId,
                'tingkat_kompetensi_id' => $prakomTingkatId, // Prakom tetap 1–7
                'jumlah' => $prakomExisting
            ]);
        }

        // Kirim email pemberitahuan ke semua admin setelah usulan berhasil dikirim
        $this->kirimEmailAdmin();
    
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Usulan berhasil dikirim.'
        ]);
    }

    // Fungsi untuk mengirimkan email ke semua admin
    private function kirimEmailAdmin()
    {
        $mail = new PHPMailer(true);
    
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = ''; // Ganti dengan email pengirim
            $mail->Password = ''; // Ganti dengan password email pengirim
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
    
            $akunId = session()->get('id');
            $akun = $this->akunModel
                ->select('akun.*, instansi.nama_instansi')
                ->join('instansi', 'instansi.id = akun.id_instansi')
                ->where('akun.id', $akunId)
                ->first();
    
            if (!$akun) {
                log_message('error', 'Akun tidak ditemukan!');
                return;
            }
    
            $admins = $this->akunModel->where('tipe_akun', 'Admin')->findAll();
            if (empty($admins)) {
                log_message('error', 'Tidak ada admin yang ditemukan!');
                return;
            }
    
            $fromEmail = ''; // Ganti dengan email pengirim
            $fromName = "Sistem Romantis";
    
            $namaOperator = $akun['nama'];
            $namaInstansi = $akun['nama_instansi'] ?? 'Instansi Tidak Diketahui';
    
            foreach ($admins as $admin) {
                $mail->setFrom($fromEmail, $fromName);
                $mail->addAddress($admin['email']);
                $mail->Subject = 'Usulan Baru Telah Diajukan';
    
                $namaAdmin = $admin['nama'];
                $mail->Body = <<<EOD
                Yth. {$namaAdmin},
    
                Kami ingin memberitahukan bahwa terdapat usulan baru yang telah diajukan melalui Sistem Romantis.
                
                📌 Nama Pengusul : {$namaOperator}
                🏢 Instansi      : {$namaInstansi}
                📅 Waktu         : {$this->tanggalSekarangIndonesia()}
                
                Silakan masuk ke dalam sistem untuk meninjau dan memproses usulan tersebut.
                
                Hormat kami,
                Sistem Romantis
                EOD;
    
                if (!$mail->send()) {
                    log_message('error', 'Email tidak terkirim ke ' . $admin['email']);
                    log_message('error', 'Error: ' . $mail->ErrorInfo);
                } else {
                    log_message('info', 'Email berhasil dikirim ke ' . $admin['email']);
                }
    
                $mail->clearAddresses();
            }
        } catch (Exception $e) {
            log_message('error', 'Email gagal dikirim. Error: ' . $mail->ErrorInfo);
        }
    }
    
    // Tambahkan helper tanggal lokal (opsional)
    private function tanggalSekarangIndonesia()
    {
        date_default_timezone_set('Asia/Makassar');
        return date('d-m-Y H:i:s');
    }

}