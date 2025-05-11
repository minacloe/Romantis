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

class RekomendasiController extends BaseController
{
    protected $usulanModel;
    protected $kompetensiExistingModel;
    protected $kompetensiUsulanModel;
    protected $kompetensiDisetujuiModel;
    protected $akunModel;
    protected $instansiModel;
    protected $disposisiModel;
    protected $rekomendasiModel;
    
    public function __construct()
    {
        $this->usulanModel = new UsulanModel();
        $this->kompetensiExistingModel = new KompetensiExistingModel();
        $this->kompetensiUsulanModel = new KompetensiUsulanModel();
        $this->kompetensiDisetujuiModel = new KompetensiDisetujuiModel();
        $this->akunModel = new AkunModel();
        $this->instansiModel = new InstansiModel();
        $this->disposisiModel = new DisposisiModel();
        $this->rekomendasiModel = new RekomendasiModel();
    }

    public function index($disposisiId)
    {
        // Ambil data disposisi beserta usulan, akun, dan instansi
        $disposisi = $this->disposisiModel
            ->select('
                disposisi.*,
                usulan.no_surat,
                usulan.tanggal_usulan,
                usulan.surat_usulan,
                usulan.berkas_usulan,
                akun_operator.nama AS nama_operator,
                akun_admin.nama AS nama_admin,
                instansi.nama_instansi
            ')
            ->join('usulan', 'usulan.id = disposisi.usulan_id')
            ->join('akun as akun_operator', 'akun_operator.id = usulan.akun_id')
            ->join('akun as akun_admin', 'akun_admin.id = disposisi.admin_id')
            ->join('instansi', 'instansi.id = akun_operator.id_instansi')
            ->where('disposisi.id', $disposisiId)
            ->first();


        if (!$disposisi) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Disposisi tidak ditemukan.');
        }

        $kompetensiUsulan = $this->kompetensiUsulanModel
            ->where('usulan_id', $disposisi['usulan_id'])
            ->findAll();

        // Siapkan array: indeks 1–7 (id Prakom) dan 8–14 (id Statistisi)
        $usulanPrakom = array_fill(0, 7, 0); // Default 0 semua
        $usulanStatistisi = array_fill(0, 7, 0);

        foreach ($kompetensiUsulan as $ku) {
            $id = $ku['tingkat_kompetensi_id'];
            if ($id >= 1 && $id <= 7) {
                $usulanPrakom[$id - 1] = $ku['jumlah'];
            } elseif ($id >= 8 && $id <= 14) {
                $usulanStatistisi[$id - 8] = $ku['jumlah'];
            }
        }

        // Cari rekomendasi yang sudah dibuat (kalau ada)
        $rekomendasi = $this->rekomendasiModel
            ->where('disposisi_id', $disposisiId)
            ->first();

        // Cari kompetensi yang disetujui (kalau ada rekomendasi)
        $kompetensiDisetujui = [];
        if ($rekomendasi) {
            $kompetensiDisetujui = $this->kompetensiDisetujuiModel
                ->where('rekomendasi_id', $rekomendasi['id'])
                ->findAll();
        }

        $data = [
            'disposisi' => $disposisi,
            'rekomendasi' => $rekomendasi,
            'kompetensiDisetujui' => $kompetensiDisetujui,
            'usulanPrakom' => $usulanPrakom,
            'usulanStatistisi' => $usulanStatistisi,
        ];

        return view('rekomendasi', $data);
    }

    public function tambah()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'disposisi_id' => 'required|integer',
            'tanggal_rekomendasi' => 'required|valid_date',
            'no_rekomendasi' => 'required|string',
            'surat_rekomendasi' => 'uploaded[surat_rekomendasi]|max_size[surat_rekomendasi,5120]|ext_in[surat_rekomendasi,pdf,doc,docx]',
        ]);
    
        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal. Pastikan semua input benar dan file PDF tidak lebih dari 2MB.');
        }
    
        try {
            $disposisiId = $this->request->getPost('disposisi_id');
            $tanggal = $this->request->getPost('tanggal_rekomendasi');
            $nomor = $this->request->getPost('no_rekomendasi');
    
            // Upload surat rekomendasi
            $suratFile = $this->request->getFile('surat_rekomendasi');
            $suratName = $suratFile->getRandomName();
            $suratFile->move(FCPATH . 'writable/uploads', $suratName);
    
            // Simpan rekomendasi
            $rekomendasiId = $this->rekomendasiModel->insert([
                'disposisi_id' => $disposisiId,
                'tanggal_rekomendasi' => $tanggal,
                'no_rekomendasi' => $nomor,
                'surat_rekomendasi' => $suratName
            ]);
    
            if (!$rekomendasiId) {
                throw new \Exception('Gagal menyimpan rekomendasi.');
            }
    
            // Ambil usulan_id dan data kompetensi usulan
            $disposisi = $this->disposisiModel->find($disposisiId);
            if (!$disposisi) {
                throw new \Exception('Disposisi tidak ditemukan.');
            }
    
            $usulanId = $disposisi['usulan_id'];
            $kompetensiUsulan = $this->kompetensiUsulanModel
                ->where('usulan_id', $usulanId)
                ->findAll();
    
            // Susun array: [tingkat_kompetensi_id => jumlah]
            $mapUsulan = [];
            foreach ($kompetensiUsulan as $k) {
                $mapUsulan[$k['tingkat_kompetensi_id']] = $k['jumlah'];
            }
    
            // Simpan kompetensi disetujui dengan validasi batas maksimal
            $kompetensiDisetujuiData = [];
            for ($i = 1; $i <= 7; $i++) {
                $jumlahStatistisi = (int) $this->request->getPost('statistisi_' . $i) ?? 0;
                $jumlahPrakom = (int) $this->request->getPost('prakom_' . $i) ?? 0;
    
                $idPrakom = $i;
                $idStatistisi = $i + 7;
    
                if ($jumlahPrakom > 0) {
                    $maks = $mapUsulan[$idPrakom] ?? 0;
                    if ($jumlahPrakom > $maks) {
                        throw new \Exception("Jumlah Prakom tingkat $i melebihi usulan ($jumlahPrakom > $maks)");
                    }
    
                    $kompetensiDisetujuiData[] = [
                        'rekomendasi_id' => $rekomendasiId,
                        'tingkat_kompetensi_id' => $idPrakom,
                        'jumlah' => $jumlahPrakom,
                    ];
                }
    
                if ($jumlahStatistisi > 0) {
                    $maks = $mapUsulan[$idStatistisi] ?? 0;
                    if ($jumlahStatistisi > $maks) {
                        throw new \Exception("Jumlah Statistisi tingkat $i melebihi usulan ($jumlahStatistisi > $maks)");
                    }
    
                    $kompetensiDisetujuiData[] = [
                        'rekomendasi_id' => $rekomendasiId,
                        'tingkat_kompetensi_id' => $idStatistisi,
                        'jumlah' => $jumlahStatistisi,
                    ];
                }
            }
    
            if (!empty($kompetensiDisetujuiData)) {
                $this->kompetensiDisetujuiModel->insertBatch($kompetensiDisetujuiData);
            }
    
            // Update kompetensi existing
            $existingKompetensi = $this->kompetensiExistingModel->where('usulan_id', $usulanId)->findAll();
    
            foreach ($kompetensiDisetujuiData as $kompetensi) {
                foreach ($existingKompetensi as $existing) {
                    if ($kompetensi['tingkat_kompetensi_id'] == $existing['tingkat_kompetensi_id']) {
                        $newJumlah = $existing['jumlah'] + $kompetensi['jumlah'];
                        $this->kompetensiExistingModel->update($existing['id'], ['jumlah' => $newJumlah]);
                    }
                }
            }
    
            // Kirim notifikasi email
            $this->kirimEmailRekomendasi($disposisiId, $rekomendasiId);
    
            return redirect()->to('dashboard/admin')->with('success', 'Rekomendasi berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    
    private function kirimEmailRekomendasi($disposisiId, $rekomendasiId)
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
    
            $rekomendasi = $this->rekomendasiModel->find($rekomendasiId);
            $disposisi = $this->disposisiModel
                ->select('disposisi.*, akun_admin.nama as nama_admin, akun_admin.email as email_admin')
                ->join('akun as akun_admin', 'akun_admin.id = disposisi.admin_id')
                ->where('disposisi.id', $disposisiId)
                ->first();
            $usulan = $this->usulanModel
                ->select('usulan.*, akun_operator.nama as nama_operator, akun_operator.email as email_operator, akun_operator.id_instansi, instansi.nama_instansi')
                ->join('akun as akun_operator', 'akun_operator.id = usulan.akun_id')
                ->join('instansi', 'instansi.id = akun_operator.id_instansi')
                ->where('usulan.id', $disposisi['usulan_id'])
                ->first();
    
            if (!$rekomendasi || !$disposisi || !$usulan) {
                log_message('error', 'Data usulan, disposisi, atau rekomendasi tidak ditemukan!');
                return;
            }
    
            $operatorsSameInstansi = $this->akunModel
                ->where('tipe_akun', 'operator')
                ->where('id_instansi', $usulan['id_instansi'])
                ->findAll();
    
            $fromEmail = ''; // Ganti dengan email pengirim
            $fromName = "Sistem Romantis";
    
            $recipients = [
                [
                    'email' => $usulan['email_operator'],
                    'nama' => $usulan['nama_operator'],
                    'role' => 'Operator Pengusul'
                ]
            ];
    
            foreach ($operatorsSameInstansi as $operator) {
                if ($operator['email'] != $usulan['email_operator']) {
                    $recipients[] = [
                        'email' => $operator['email'],
                        'nama' => $operator['nama'],
                        'role' => 'Operator ' . $usulan['nama_instansi']
                    ];
                }
            }
    
            foreach ($recipients as $recipient) {
                $mail->setFrom($fromEmail, $fromName);
                $mail->addAddress($recipient['email']);
                $mail->Subject = 'Rekomendasi Usulan Telah Diterbitkan - ' . $usulan['nama_instansi'];
    
                $mail->Body = <<<EOD
                Yth. {$recipient['nama']},
    
                Kami informasikan bahwa telah diterbitkan rekomendasi baru terkait usulan dari instansi Anda ({$usulan['nama_instansi']}), yang diajukan oleh:
    
                📌 Operator Pengusul   : {$usulan['nama_operator']}
                📎 No. Surat           : {$usulan['no_surat']}
                📅 Tanggal Usulan      : {$this->tanggalWITA($usulan['tanggal_usulan'])}
    
                📝 Detail Rekomendasi:
                • Nomor Rekomendasi : {$rekomendasi['no_rekomendasi']}
                • Tanggal Rekomendasi : {$this->tanggalWITA($rekomendasi['tanggal_rekomendasi'])}
    
                Anda menerima notifikasi ini sebagai {$recipient['role']}.
    
                Silakan masuk ke sistem untuk melihat dokumen rekomendasi dan melakukan tindak lanjut yang diperlukan.
    
                Hormat kami,
                Sistem Romantis
                EOD;
    
                if (!$mail->send()) {
                    log_message('error', 'Email tidak terkirim ke ' . $recipient['email']);
                    log_message('error', 'Error: ' . $mail->ErrorInfo);
                } else {
                    log_message('info', 'Email berhasil dikirim ke ' . $recipient['email']);
                }
    
                $mail->clearAddresses();
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
