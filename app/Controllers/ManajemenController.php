<?php

namespace App\Controllers;

use App\Models\AkunModel;
use App\Models\InstansiModel;
use App\Libraries\FirebaseService;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class ManajemenController extends BaseController
{
    protected $akunModel;
    protected $instansiModel;
    protected $firebase;


    public function __construct()
    {
        $this->akunModel = new AkunModel();
        $this->instansiModel = new InstansiModel();
        $this->firebase = new FirebaseService();
    }

    public function index()
    {
        $keyword = $this->request->getGet('q');
        $perPage = 20;
    
        $akunQuery = $this->akunModel
            ->select('akun.*, instansi.nama_instansi')
            ->join('instansi', 'instansi.id = akun.id_instansi', 'left');
    
        if ($keyword) {
            $akunQuery->groupStart()
                ->like('akun.nama', $keyword)
                ->orLike('akun.email', $keyword)
                ->orLike('instansi.nama_instansi', $keyword)
                ->orLike('akun.tipe_akun', $keyword)
                ->groupEnd();
        }
    
        $akun = $akunQuery->paginate($perPage, 'akun');
        $pager = $this->akunModel->pager;
    
        $data = [
            'title' => 'Manajemen Akun',
            'akun' => $akun,
            'pager' => $pager,
            'instansi' => $this->instansiModel->findAll(),
            'keyword' => $keyword // ✅ ini yang penting!
        ];
        return view('manajemen', $data);
    }
    
    public function tambah()
    {
        if (!$this->validate([
            'nama' => 'required|min_length[3]',
            'nip' => 'required|min_length[18]|is_unique[akun.nip]',
            'email' => 'required|valid_email|is_unique[akun.email]',
            'instansi' => 'required|min_length[3]',
            'password' => 'required|min_length[6]',
            'tipe_akun' => 'required|in_list[Super Admin,Admin,Operator]',
            'nomor_hp' => 'required|min_length[10]|max_length[15]|is_unique[akun.nomor_hp]'
        ])) {
            $errorMessages = $this->validator->getErrors();
            $formattedMessage = '';
            foreach ($errorMessages as $field => $error) {
                $formattedMessage .= ucfirst($field) . ': ' . $error . "\n";
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => $formattedMessage
            ]);
        }
    
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $nomor_hp = $this->request->getPost('nomor_hp'); // Menambahkan nomor_hp
    
        try {
            // ✅ 1. Tambah ke Firebase
            $firebaseUser = $this->firebase->createUser($email, $password);
    
            // ✅ 2. Cek/buat instansi
            $instansiModel = new InstansiModel();
            $instansi = $instansiModel->where('nama_instansi', $this->request->getPost('instansi'))->first();
            $instansiId = $instansi ? $instansi['id'] : $instansiModel->insert([
                'nama_instansi' => $this->request->getPost('instansi'),
                'singkatan' => substr($this->request->getPost('instansi'), 0, 5)
            ]);
    
            if (!$instansiId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan instansi'
                ]);
            }

            // ✅ 3. Simpan ke SQL
            $data = [
                'nama' => $this->request->getPost('nama'),
                'nip' => $this->request->getPost('nip'),
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'password_temp' => $password,
                'id_instansi' => $instansiId,
                'tipe_akun' => $this->request->getPost('tipe_akun'),
                'nomor_hp' => $nomor_hp
            ];
    
            if ($this->akunModel->save($data)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Akun berhasil dibuat'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menambahkan akun ke database lokal'
                ]);
            }
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menambahkan akun: ' . $e->getMessage()
            ]);
        }
    }
    
    public function edit($id)
    {
        $akun = $this->akunModel->getWithInstansi($id);
        
        if (!$akun) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Akun tidak ditemukan'
            ]);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'id' => $akun['id'],
                'nama' => $akun['nama'],
                'nip' => $akun['nip'],
                'email' => $akun['email'],
                'instansi' => $akun['nama_instansi'],
                'tipe_akun' => $akun['tipe_akun'],
                'password_temp' => $akun['password_temp'] ?? '',
                'nomor_hp' => $akun['nomor_hp'] ?? '' 
            ]
        ]);
    }

    public function update($id = null)
    {
        // Validasi ID
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID tidak valid'
            ]);
        }

        // Cek apakah akun ada
        $akun = $this->akunModel->find($id);
        if (!$akun) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Akun tidak ditemukan'
            ]);
        }

        // Validasi input
        log_message('debug', 'Edit ID: ' . $id);

        $validationRules = [
            'nama' => 'required|min_length[3]',
            'nip' => "required|min_length[18]|is_unique[akun.nip,id,$id]",
            'email' => "required|valid_email|is_unique[akun.email,id,$id]",
            'instansi' => 'required|min_length[3]',
            'tipe_akun' => 'required|in_list[Super Admin,Admin,Operator]',
            'password' => 'permit_empty|min_length[6]',
            'nomor_hp' => 'required|min_length[10]|max_length[15]|is_unique[akun.nomor_hp,id,' . $id . ']'
        ];

        if (!$this->validate($validationRules)) {
            $errors = $this->validator->getErrors();
            $formattedMessage = '';
            foreach ($errors as $field => $msg) {
                $formattedMessage .= ucfirst($field) . ': ' . $msg . "\n";
            }
    
            return $this->response->setJSON([
                'success' => false,
                'message' => $formattedMessage
            ]);
        }

        // Proses instansi
        $instansiName = trim($this->request->getPost('instansi'));
        $instansi = $this->instansiModel->where('nama_instansi', $instansiName)->first();
        
        if (!$instansi) {
            $instansiId = $this->instansiModel->insert([
                'nama_instansi' => $instansiName,
                'singkatan' => substr($instansiName, 0, 5)
            ]);
        } else {
            $instansiId = $instansi['id'];
        }

        $nomor_hp = $this->request->getPost('nomor_hp'); 
        // Data untuk update
        $data = [
            'nama' => $this->request->getPost('nama'),
            'nip' => $this->request->getPost('nip'),
            'email' => $this->request->getPost('email'),
            'id_instansi' => $instansiId,
            'tipe_akun' => $this->request->getPost('tipe_akun'),
            'nomor_hp' => $nomor_hp
        ];

        // Handle password jika diisi
        if ($this->request->getPost('password')) {
            $passwordPlain = $this->request->getPost('password');
            $data['password_temp'] = $passwordPlain; // hanya jika kamu butuh simpan versi teks aslinya (tidak disarankan di production)
            $data['password'] = password_hash($passwordPlain, PASSWORD_DEFAULT); // password disimpan dalam bentuk hash
        }

        try {
            $emailBaru = $this->request->getPost('email');
            $passwordBaru = $this->request->getPost('password');

            $firebaseData = [];
            if ($emailBaru !== $akun['email']) {
                $firebaseData['email'] = $emailBaru;
            }
            if (!empty($passwordBaru)) {
                $firebaseData['password'] = $passwordBaru;
            }

            if (!empty($firebaseData)) {
                try {
                    $this->firebase->updateUser($akun['email'], $firebaseData);
                } catch (\Exception $e) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal update Firebase: ' . $e->getMessage()
                    ]);
                }
            }

            // Gunakan DB langsung untuk memastikan update
            $db = \Config\Database::connect();
            $builder = $db->table('akun');
            $updated = $builder->where('id', $id)->update($data);
            
            if ($updated) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Data berhasil diupdate',
                    'data' => $builder->where('id', $id)->get()->getRowArray()
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak ada perubahan data'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
        }
    
        $akunModel = new \App\Models\AkunModel();
        $usulanModel = new \App\Models\UsulanModel();
        $disposisiModel = new \App\Models\DisposisiModel();
        $rekomendasiModel = new \App\Models\RekomendasiModel();
        $kompetensiExistingModel = new \App\Models\KompetensiExistingModel();
        $kompetensiUsulanModel = new \App\Models\KompetensiUsulanModel();
        $kompetensiDisetujuiModel = new \App\Models\KompetensiDisetujuiModel();
        $firebaseService = new \App\Libraries\FirebaseService();
    
        $akun = $akunModel->find($id);
    
        if (!$akun) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Akun tidak ditemukan di database'
            ]);
        }
    
        try {
            // 1. Cari semua usulan milik akun
            $usulanIds = $usulanModel->where('akun_id', $id)->findColumn('id') ?? [];
    
            if (!empty($usulanIds)) {
                // 2. Hapus kompetensi terkait usulan
                $kompetensiUsulanModel->whereIn('usulan_id', $usulanIds)->delete();
                $kompetensiExistingModel->whereIn('usulan_id', $usulanIds)->delete();
    
                // 3. Cari semua disposisi dari usulan
                $disposisiIds = $disposisiModel->whereIn('usulan_id', $usulanIds)->findColumn('id') ?? [];
    
                if (!empty($disposisiIds)) {
                    // 4. Hapus rekomendasi dan kompetensi_disetujui dari disposisi
                    $rekomendasiModel->whereIn('disposisi_id', $disposisiIds)->delete();
                    $kompetensiDisetujuiModel->whereIn('disposisi_id', $disposisiIds)->delete();
    
                    // 5. Hapus disposisi
                    $disposisiModel->whereIn('id', $disposisiIds)->delete();
                }
    
                // 6. Hapus usulan
                $usulanModel->whereIn('id', $usulanIds)->delete();
            }
    
            // 7. Hapus akun dari Firebase
            $firebaseService->deleteUserByEmail($akun['email']);
    
            // 8. Hapus akun dari database
            $akunModel->delete($id);
    
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Akun dan semua data terkait berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus akun: ' . $e->getMessage()
            ]);
        }
    }
    

    public function detail($id)
    {
        // Melakukan join antara tabel akun dan instansi
        $akun = $this->akunModel
            ->select('akun.*, instansi.nama_instansi') // Menambahkan nama_instansi dari tabel instansi
            ->join('instansi', 'instansi.id = akun.id_instansi', 'left') // Melakukan join dengan tabel instansi
            ->find($id);

        // Jika akun tidak ditemukan, kembalikan error
        if (!$akun) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akun tidak ditemukan']);
        }

        // Jika akun ditemukan, kembalikan data akun beserta nama_instansi dan password_temp
        return $this->response->setJSON(['success' => true, 'data' => $akun]);
    }

    public function cetakPdf()
    {
        $akun = $this->akunModel
            ->select('akun.*, instansi.nama_instansi')
            ->join('instansi', 'instansi.id = akun.id_instansi', 'left')
            ->findAll();
    
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        
        $html = '<h2 style="text-align:center;">Daftar Akun</h2>
                <table border="1" cellpadding="5" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NIP</th>
                            <th>Instansi</th>
                            <th>Nomor HP</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Tipe Akun</th>
                        </tr>
                    </thead>
                    <tbody>';
        $no = 1;
        foreach ($akun as $a) {
            $html .= '<tr>
                        <td>' . $no++ . '</td>
                        <td>' . htmlspecialchars($a['nama']) . '</td>
                        <td>' . htmlspecialchars($a['nip']) . '</td>
                        <td>' . htmlspecialchars($a['nama_instansi']) . '</td>
                        <td>' . htmlspecialchars($a['nomor_hp']) . '</td>
                        <td>' . htmlspecialchars($a['email']) . '</td>
                        <td>' . htmlspecialchars($a['password_temp']) . '</td>
                        <td>' . htmlspecialchars($a['tipe_akun']) . '</td>
                      </tr>';
        }
        $html .= '</tbody></table>';
    
        $mpdf->WriteHTML($html);
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output('Daftar-Akun.pdf', 'I'); // 'I' untuk tampil di browser
        exit; // jangan lupa exit
    }
    
    public function cetakExcel()
    {
        $akun = $this->akunModel
            ->select('akun.*, instansi.nama_instansi')
            ->join('instansi', 'instansi.id = akun.id_instansi', 'left')
            ->findAll();
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        $sheet->setCellValue('A1', 'No')
              ->setCellValue('B1', 'Nama')
              ->setCellValue('C1', 'NIP')
              ->setCellValue('D1', 'Instansi')
              ->setCellValue('E1', 'Nomor HP')
              ->setCellValue('F1', 'Email')
              ->setCellValue('G1', 'Password')
              ->setCellValue('H1', 'Tipe Akun');
    
        $no = 1;
        $row = 2;
        foreach ($akun as $a) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $a['nama']);
            $sheet->setCellValue('C' . $row, $a['nip']);
            $sheet->setCellValue('D' . $row, $a['nama_instansi']);
            $sheet->setCellValue('E' . $row, $a['nomor_hp']);
            $sheet->setCellValue('F' . $row, $a['email']);
            $sheet->setCellValue('G' . $row, $a['password_temp']);
            $sheet->setCellValue('H' . $row, $a['tipe_akun']);
            $row++;
        }
    
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Daftar-Akun.xlsx"');
        header('Cache-Control: max-age=0');
    
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function cetakCsv()
    {
        $akun = $this->akunModel
            ->select('akun.*, instansi.nama_instansi')
            ->join('instansi', 'instansi.id = akun.id_instansi', 'left')
            ->findAll();
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        $sheet->setCellValue('A1', 'No')
              ->setCellValue('B1', 'Nama')
              ->setCellValue('C1', 'NIP')
              ->setCellValue('D1', 'Instansi')
              ->setCellValue('E1', 'Nomor HP')
              ->setCellValue('F1', 'Email')
              ->setCellValue('G1', 'Password')
              ->setCellValue('H1', 'Tipe Akun');
    
        $no = 1;
        $row = 2;
        foreach ($akun as $a) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $a['nama']);
            $sheet->setCellValue('C' . $row, $a['nip']);
            $sheet->setCellValue('D' . $row, $a['nama_instansi']);
            $sheet->setCellValue('E' . $row, $a['nomor_hp']);
            $sheet->setCellValue('F' . $row, $a['email']);
            $sheet->setCellValue('G' . $row, $a['password_temp']);
            $sheet->setCellValue('H' . $row, $a['tipe_akun']);
            $row++;
        }
    
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="Daftar-Akun.csv"');
        header('Cache-Control: max-age=0');
    
        $writer = new Csv($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
}