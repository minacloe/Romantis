<?php

namespace App\Controllers;

use App\Models\UsulanModel;
use App\Models\KompetensiExistingModel;
use App\Models\KompetensiUsulanModel;
use App\Models\KompetensiDisetujuiModel;
use App\Models\AkunModel;
use App\Models\InstansiModel;
use App\Models\DisposisiModel;
use App\Models\RekomendasiModel;
use CodeIgniter\Database\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class DisposisiController extends BaseController
{
    protected $usulanModel;
    protected $kompetensiExistingModel;
    protected $kompetensiUsulanModel;
    protected $kompetensiDisetujuiModel;
    protected $akunModel;
    protected $instansiModel;
    protected $firebase;
    protected $disposisiModel;
    protected $rekomendasiModel;
    protected $table = 'disposisi';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'usulan_id',
        'admin_id',
        'tanggal_disposisi',
        'jadwal_rapat',
    ];
    
    public function __construct()
    {
        // Inisialisasi model-model yang dibutuhkan
        $this->usulanModel = new UsulanModel();
        $this->kompetensiExistingModel = new KompetensiExistingModel();
        $this->kompetensiUsulanModel = new KompetensiUsulanModel();
        $this->kompetensiDisetujuiModel = new KompetensiDisetujuiModel();
        $this->akunModel = new AkunModel();
        $this->instansiModel = new InstansiModel();
        $this->disposisiModel = new DisposisiModel();
        $this->rekomendasiModel = new RekomendasiModel();
    }


    public function index($usulanId)
    {
        // Ambil data usulan + akun + instansi
        $usulan = $this->usulanModel
            ->select('
                usulan.id,
                usulan.no_surat,
                usulan.tanggal_usulan,
                usulan.surat_usulan,
                usulan.berkas_usulan,
                akun.nama AS nama_operator,
                akun.email,
                akun.nomor_hp,
                instansi.nama_instansi
            ')
            ->join('akun', 'akun.id = usulan.akun_id')
            ->join('instansi', 'instansi.id = akun.id_instansi')
            ->where('usulan.id', $usulanId)
            ->first();
    
        if (!$usulan) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Usulan tidak ditemukan');
        }
    
        // Ambil hanya akun bertipe admin
        $akunList = $this->akunModel
            ->where('tipe_akun', 'admin')
            ->findAll();
    
        $data = [
            'usulan' => $usulan,
            'akunList' => $akunList, // Dikirim untuk dropdown select admin
        ];
    
        return view('disposisi', $data);
    }
    
    
    public function tambah()
    {
        $validation = \Config\Services::validation();
    
        $rules = [
            'disposisi_admin' => 'required|integer',
            'tanggal_disposisi' => 'required|valid_date',
            'jadwal_rapat' => 'permit_empty|valid_date',
            'usulan_id' => 'required|integer',
        ];
    
        if (!$this->validate($rules)) {
            return redirect()->back()
                ->with('error', 'Data tidak lengkap atau salah format.')
                ->withInput();
        }
    
        // Siapkan data sebelum insert
        $data = [
            'usulan_id'         => $this->request->getPost('usulan_id'),
            'admin_id'          => $this->request->getPost('disposisi_admin'),
            'tanggal_disposisi' => $this->request->getPost('tanggal_disposisi'),
            'jadwal_rapat'      => $this->request->getPost('jadwal_rapat') ?: null,
        ];
    
        try {
            $this->disposisiModel->insert($data);
            
            // Kirim email notifikasi ke operator setelah disposisi berhasil dibuat
            $this->kirimEmailOperator($data['usulan_id']);
            $this->kirimEmailAdmin($data['usulan_id'], $data['admin_id']);
    
            return redirect()->to(previous_url())->with('success', 'Disposisi berhasil ditambahkan.');
        } catch (\Throwable $e) {
            log_message('error', $e->getMessage());
            $errorMessage = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            return redirect()->back()->with('error', $errorMessage);
        }
    }
    
    // Fungsi untuk mengirimkan email ke semua operator
    private function kirimEmailOperator($usulanId)
    {
        $mail = new PHPMailer(true);
    
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'romantisprakomstatistisi@gmail.com';
            $mail->Password = 'kacz agty niwl kzvx';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
    
            $usulan = $this->usulanModel
                ->select('usulan.*, akun.nama as nama_operator, akun.id_instansi, instansi.nama_instansi')
                ->join('akun', 'akun.id = usulan.akun_id')
                ->join('instansi', 'instansi.id = akun.id_instansi')
                ->where('usulan.id', $usulanId)
                ->first();
    
            $disposisi = $this->disposisiModel
                ->select('disposisi.*, akun.nama as nama_admin')
                ->join('akun', 'akun.id = disposisi.admin_id')
                ->where('disposisi.usulan_id', $usulanId)
                ->orderBy('disposisi.id', 'DESC')
                ->first();
    
            if (!$usulan || !$disposisi) {
                log_message('error', 'Data usulan atau disposisi tidak ditemukan!');
                return;
            }
    
            $operators = $this->akunModel
                ->where('tipe_akun', 'operator')
                ->where('id_instansi', $usulan['id_instansi'])
                ->findAll();
    
            if (empty($operators)) {
                log_message('error', 'Tidak ada operator dengan instansi yang sama ditemukan!');
                return;
            }
    
            $fromEmail = 'romantisprakomstatistisi@gmail.com';
            $fromName = "Sistem Romantis";
    
            foreach ($operators as $operator) {
                $mail->setFrom($fromEmail, $fromName);
                $mail->addAddress($operator['email']);
                $mail->Subject = 'Disposisi Baru untuk Usulan Instansi Anda';
    
                $mail->Body = <<<EOD
                Yth. {$operator['nama']},
    
                Telah dibuat disposisi baru oleh Admin {$disposisi['nama_admin']} untuk menangani usulan dari instansi Anda ({$usulan['nama_instansi']}).
    
                📌 Detail Usulan:
                • Operator Pengusul : {$usulan['nama_operator']}
                • No. Surat         : {$usulan['no_surat']}
                • Tanggal Usulan    : {$this->tanggalWITA($usulan['tanggal_usulan'])}
    
                📋 Detail Disposisi:
                • Tanggal Disposisi : {$this->tanggalWITA($disposisi['tanggal_disposisi'])}
                EOD;
    
                if ($disposisi['jadwal_rapat']) {
                    $mail->Body .= "\n• Jadwal Rapat      : " . $this->tanggalWITA($disposisi['jadwal_rapat']);
                }
    
                $mail->Body .= <<<EOD
    
                Silakan login ke sistem untuk melihat detail lengkap dan melakukan tindak lanjut.
    
                Hormat kami,
                Sistem Romantis
                EOD;
    
                if (!$mail->send()) {
                    log_message('error', 'Email tidak terkirim ke ' . $operator['email']);
                    log_message('error', 'Error: ' . $mail->ErrorInfo);
                } else {
                    log_message('info', 'Email berhasil dikirim ke ' . $operator['email']);
                }
    
                $mail->clearAddresses();
            }
        } catch (Exception $e) {
            log_message('error', 'Email gagal dikirim. Error: ' . $mail->ErrorInfo);
        }
    }
    
    private function kirimEmailAdmin($usulanId, $adminId)
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
    
            $usulan = $this->usulanModel
                ->select('usulan.*, akun.nama as nama_operator, instansi.nama_instansi')
                ->join('akun', 'akun.id = usulan.akun_id')
                ->join('instansi', 'instansi.id = akun.id_instansi')
                ->where('usulan.id', $usulanId)
                ->first();
    
            $admin = $this->akunModel->find($adminId);
    
            if (!$usulan || !$admin) {
                log_message('error', 'Data usulan atau admin tidak ditemukan!');
                return;
            }
    
            $disposisi = $this->disposisiModel
                ->where('usulan_id', $usulanId)
                ->where('admin_id', $adminId)
                ->orderBy('id', 'DESC')
                ->first();
    
            if (!$disposisi) {
                log_message('error', 'Data disposisi tidak ditemukan!');
                return;
            }
    
            $fromEmail = ''; // Ganti dengan email pengirim
            $fromName = "Sistem Romantis";
    
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($admin['email']);
            $mail->Subject = 'Disposisi Baru untuk Anda - ' . $usulan['nama_instansi'];
    
            $mail->Body = <<<EOD
            Halo {$admin['nama']},
    
            Anda telah menerima disposisi baru untuk menangani usulan dari instansi *{$usulan['nama_instansi']}*.
    
            📌 Detail Usulan:
            • Operator Pengusul : {$usulan['nama_operator']}
            • No. Surat         : {$usulan['no_surat']}
            • Tanggal Usulan    : {$this->tanggalWITA($usulan['tanggal_usulan'])}
    
            📋 Detail Disposisi:
            • Tanggal Disposisi : {$this->tanggalWITA($disposisi['tanggal_disposisi'])}
            EOD;
    
            if ($disposisi['jadwal_rapat']) {
                $mail->Body .= "\n• Jadwal Rapat      : " . $this->tanggalWITA($disposisi['jadwal_rapat']);
            }
    
            $mail->Body .= <<<EOD
    
            Silakan login ke sistem untuk melihat detail lebih lanjut dan melakukan tindak lanjut sesuai kebutuhan.
    
            Salam hormat,
            Sistem Romantis
            EOD;
    
            if (!$mail->send()) {
                log_message('error', 'Email tidak terkirim ke ' . $admin['email']);
                log_message('error', 'Error: ' . $mail->ErrorInfo);
            } else {
                log_message('info', 'Email berhasil dikirim ke ' . $admin['email']);
            }
    
        } catch (Exception $e) {
            log_message('error', 'Email gagal dikirim. Error: ' . $mail->ErrorInfo);
        }
    }
    
 
    private function tanggalWITA($tanggal)
    {
        date_default_timezone_set('Asia/Makassar');
        return date('d-m-Y', strtotime($tanggal)); // hanya tanggal
    }    

}