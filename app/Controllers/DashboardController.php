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

class DashboardController extends BaseController
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

    public function operator()
    {
        $akunId = session()->get('id');
        
        // Cari ID Instansi dari akun login
        $akun = $this->akunModel->find($akunId);
        $instansiId = $akun['id_instansi'];
        
        // Cari semua akun lain yang 1 instansi
        $akunIds = $this->akunModel->where('id_instansi', $instansiId)->findColumn('id') ?? [];
        if (empty($akunIds)) {
            $akunIds = [0];
        }
        
        // Cari usulan terakhir dari instansi
        $lastUsulan = $this->usulanModel
            ->whereIn('akun_id', $akunIds)
            ->orderBy('id', 'DESC')
            ->first();
        $lastUsulanId = $lastUsulan['id'] ?? 0;
        
        // 🔥 Data untuk kartu dashboard
        $data['totalPengajuan'] = $this->usulanModel->whereIn('akun_id', $akunIds)->countAllResults();
        $data['totalPrakom'] = $this->kompetensiExistingModel->countPrakom([$lastUsulanId]);
        $data['totalStatistisi'] = $this->kompetensiExistingModel->countStatistisi([$lastUsulanId]);
        
        // Hitung persentase persetujuan
        $totalUsulan = $this->kompetensiUsulanModel
            ->where('usulan_id', $lastUsulanId)
            ->selectSum('jumlah')
            ->get()
            ->getRow()
            ->jumlah ?? 0;
        
        $disposisiModel = new \App\Models\DisposisiModel();
        $rekomendasiModel = new \App\Models\RekomendasiModel();
        
        $lastDisposisi = $disposisiModel->where('usulan_id', $lastUsulanId)->first();
        $lastDisposisiId = $lastDisposisi['id'] ?? 0;
        
        $lastRekomendasi = $rekomendasiModel->where('disposisi_id', $lastDisposisiId)->first();
        $lastRekomendasiId = $lastRekomendasi['id'] ?? 0;
        
        $totalDisetujui = $this->kompetensiDisetujuiModel
            ->where('rekomendasi_id', $lastRekomendasiId)
            ->selectSum('jumlah')
            ->get()
            ->getRow()
            ->jumlah ?? 0;
        
        $data['persetujuanRate'] = ($totalUsulan > 0) ? round(($totalDisetujui / $totalUsulan) * 100, 2) : 0;
        
        // 🔥 Data untuk chart dashboard - DIUBAH menggunakan kompetensi_usulan
        $data['usulanStatistisi'] = $this->kompetensiUsulanModel->sumStatistisiByCategory([$lastUsulanId]);
        $data['usulanPrakom'] = $this->kompetensiUsulanModel->sumPrakomByCategory([$lastUsulanId]);
        $data['existingStatistisi'] = $this->kompetensiExistingModel->sumStatistisiByCategory([$lastUsulanId]);
        $data['existingPrakom'] = $this->kompetensiExistingModel->sumPrakomByCategory([$lastUsulanId]);
        
        // 🔥 Data untuk tabel pengajuan
        $keyword = $this->request->getGet('q');
        $perPage = 10;
        
        $usulanQuery = $this->usulanModel
            ->select('usulan.*, akun.nama, instansi.nama_instansi')
            ->join('akun', 'akun.id = usulan.akun_id')
            ->join('instansi', 'instansi.id = akun.id_instansi')
            ->whereIn('usulan.akun_id', $akunIds);
        
        if ($keyword) {
            $usulanQuery->groupStart()
                ->like('akun.nama', $keyword)
                ->orLike('instansi.nama_instansi', $keyword)
                ->groupEnd();
        }
        
        $usulanList = $usulanQuery->orderBy('usulan.id', 'DESC')->paginate($perPage, 'usulan');
        $data['pager'] = $this->usulanModel->pager;
        
        $tabelData = [];
        
        foreach ($usulanList as $usulan) {
            $usulanId = $usulan['id'];
        
            $jumlahPrakomUsulan = $this->kompetensiUsulanModel
                ->where('usulan_id', $usulanId)
                ->where('tingkat_kompetensi_id >=', 1)
                ->where('tingkat_kompetensi_id <=', 7)
                ->selectSum('jumlah')
                ->get()->getRow()->jumlah ?? 0;
        
            $jumlahStatistisiUsulan = $this->kompetensiUsulanModel
                ->where('usulan_id', $usulanId)
                ->where('tingkat_kompetensi_id >=', 8)
                ->where('tingkat_kompetensi_id <=', 14)
                ->selectSum('jumlah')
                ->get()->getRow()->jumlah ?? 0;
        
            // Cari disposisi untuk usulan ini
            $disposisi = $disposisiModel->where('usulan_id', $usulanId)->first();
        
            $jumlahPrakomRekomendasi = 0;
            $jumlahStatistisiRekomendasi = 0;
            $suratRekomendasi = null;

            if ($disposisi) {
                $rekomendasi = $rekomendasiModel->where('disposisi_id', $disposisi['id'])->first();
        
                if ($rekomendasi) {
                    $suratRekomendasi = $rekomendasi['surat_rekomendasi'] ?? null;
                    $rekomendasiId = $rekomendasi['id'];
        
                    $jumlahPrakomRekomendasi = $this->kompetensiDisetujuiModel
                        ->where('rekomendasi_id', $rekomendasiId)
                        ->where('tingkat_kompetensi_id >=', 1)
                        ->where('tingkat_kompetensi_id <=', 7)
                        ->selectSum('jumlah')
                        ->get()->getRow()->jumlah ?? 0;
        
                    $jumlahStatistisiRekomendasi = $this->kompetensiDisetujuiModel
                        ->where('rekomendasi_id', $rekomendasiId)
                        ->where('tingkat_kompetensi_id >=', 8)
                        ->where('tingkat_kompetensi_id <=', 14)
                        ->selectSum('jumlah')
                        ->get()->getRow()->jumlah ?? 0;
                }
            }
        
            // Tentukan status
            if (!$disposisi) {
                $status = 'Diterima';
            } elseif ($disposisi && ($jumlahPrakomRekomendasi + $jumlahStatistisiRekomendasi) == 0) {
                $status = 'Diproses';
            } else {
                $status = 'Selesai';
            }
        
            $tabelData[] = [
                'id' => $usulan['id'],
                'instansi' => $usulan['nama_instansi'],
                'nama_akun' => $usulan['nama'],
                'tanggal_usulan' => $usulan['tanggal_usulan'],
                'jumlah_prakom_usulan' => $jumlahPrakomUsulan ?? 0,
                'jumlah_statistisi_usulan' => $jumlahStatistisiUsulan ?? 0,
                'jumlah_prakom_rekomendasi' => $jumlahPrakomRekomendasi ?? 0,
                'jumlah_statistisi_rekomendasi' => $jumlahStatistisiRekomendasi ?? 0,
                'status' => $status,
                'surat_usulan' => $usulan['surat_usulan'] ?? null,
                'berkas_usulan' => $usulan['berkas_usulan'] ?? null,
                'surat_rekomendasi' => $suratRekomendasi,

            ];
        }
        
        $data['tabelData'] = $tabelData;
        
        return view('dashboard-operator', $data);
    }

    public function admin()
    {
        $akunModel = new AkunModel();
        $instansiModel = new InstansiModel();
        $rekomendasiModel = new \App\Models\RekomendasiModel();
        $kompetensiDisetujuiModel = new \App\Models\KompetensiDisetujuiModel();
        $tingkatKompetensiModel = new \App\Models\TingkatKompetensiModel();  // Model untuk tabel tingkat_kompetensi
        
        // Info Card
        $totalPengajuan = $this->usulanModel->countAll();
        $totalPrakom = 0;
        $totalStatistisi = 0;
    
        $existingPrakom = [];
        $existingStatistisi = [];
    
        // Ambil semua instansi
        $instansiList = $instansiModel->findAll();
    
        // Ambil data tingkat_kompetensi untuk map ID -> Nama
        $tingkatKompetensiList = $tingkatKompetensiModel->findAll();
        $tingkatKompetensiMap = [];
        foreach ($tingkatKompetensiList as $kompetensi) {
            $tingkatKompetensiMap[$kompetensi['id']] = $kompetensi['nama'];
        }
    
        // Mengambil jumlah per tingkat kompetensi untuk Prakom dan Statistisi
        foreach ($instansiList as $instansi) {
            $akunIds = $akunModel->where('id_instansi', $instansi['id'])->findColumn('id') ?? [];
    
            if (empty($akunIds)) {
                continue;
            }
    
            $lastUsulan = $this->usulanModel
                ->whereIn('akun_id', $akunIds)
                ->orderBy('id', 'DESC')
                ->first();
    
            if (!$lastUsulan) {
                continue;
            }
    
            $lastUsulanId = $lastUsulan['id'];
    
            // Mengambil jumlah per tingkat kompetensi untuk Prakom
            $sumPrakomPerTingkat = $this->kompetensiExistingModel
                ->select('tingkat_kompetensi_id, SUM(jumlah) as total')
                ->where('usulan_id', $lastUsulanId)
                ->where('tingkat_kompetensi_id >=', 1)
                ->where('tingkat_kompetensi_id <=', 7)
                ->groupBy('tingkat_kompetensi_id')
                ->findAll();
    
            // Mengambil jumlah per tingkat kompetensi untuk Statistisi
            $sumStatistisiPerTingkat = $this->kompetensiExistingModel
                ->select('tingkat_kompetensi_id, SUM(jumlah) as total')
                ->where('usulan_id', $lastUsulanId)
                ->where('tingkat_kompetensi_id >=', 8)
                ->where('tingkat_kompetensi_id <=', 14)
                ->groupBy('tingkat_kompetensi_id')
                ->findAll();
    
            // Map data tingkat kompetensi id ke nama
            foreach ($sumPrakomPerTingkat as $row) {
                $existingPrakom[$tingkatKompetensiMap[$row['tingkat_kompetensi_id']]] = ($existingPrakom[$tingkatKompetensiMap[$row['tingkat_kompetensi_id']]] ?? 0) + $row['total'];
            }
    
            foreach ($sumStatistisiPerTingkat as $row) {
                $existingStatistisi[$tingkatKompetensiMap[$row['tingkat_kompetensi_id']]] = ($existingStatistisi[$tingkatKompetensiMap[$row['tingkat_kompetensi_id']]] ?? 0) + $row['total'];
            }
    
            $totalPrakom += array_sum(array_column($sumPrakomPerTingkat, 'total'));
            $totalStatistisi += array_sum(array_column($sumStatistisiPerTingkat, 'total'));
        }
    
        // Prepare Labels for the charts
        $existingPrakomLabels = array_keys($existingPrakom);
        $existingStatistisiLabels = array_keys($existingStatistisi);
    
        // Mengambil nama tingkat kompetensi untuk chart rekomendasi (Prakom dan Statistisi)
        $rekomendasiPrakom = $kompetensiDisetujuiModel
            ->select('tingkat_kompetensi_id, SUM(jumlah) as total')
            ->where('tingkat_kompetensi_id >=', 1)
            ->where('tingkat_kompetensi_id <=', 7)
            ->groupBy('tingkat_kompetensi_id')
            ->findAll();
    
        $rekomendasiStatistisi = $kompetensiDisetujuiModel
            ->select('tingkat_kompetensi_id, SUM(jumlah) as total')
            ->where('tingkat_kompetensi_id >=', 8)
            ->where('tingkat_kompetensi_id <=', 14)
            ->groupBy('tingkat_kompetensi_id')
            ->findAll();
    
        // Map data tingkat kompetensi id ke nama untuk chart rekomendasi
        $rekomendasiPrakomLabels = [];
        $rekomendasiStatistisiLabels = [];
        $rekomendasiPrakomData = [];
        $rekomendasiStatistisiData = [];
    
        foreach ($rekomendasiPrakom as $row) {
            $rekomendasiPrakomLabels[] = $tingkatKompetensiMap[$row['tingkat_kompetensi_id']];
            $rekomendasiPrakomData[] = $row['total'];
        }
    
        foreach ($rekomendasiStatistisi as $row) {
            $rekomendasiStatistisiLabels[] = $tingkatKompetensiMap[$row['tingkat_kompetensi_id']];
            $rekomendasiStatistisiData[] = $row['total'];
        }
    
        // Send data to the view
        $data['totalPengajuan'] = $totalPengajuan;
        $data['totalPrakom'] = $totalPrakom;
        $data['totalStatistisi'] = $totalStatistisi;
        $data['existingPrakom'] = $existingPrakom;
        $data['existingStatistisi'] = $existingStatistisi;
        $data['existingPrakomLabels'] = $existingPrakomLabels;
        $data['existingStatistisiLabels'] = $existingStatistisiLabels;
        $data['rekomendasiPrakomLabels'] = $rekomendasiPrakomLabels;
        $data['rekomendasiStatistisiLabels'] = $rekomendasiStatistisiLabels;
        $data['rekomendasiPrakomData'] = $rekomendasiPrakomData;
        $data['rekomendasiStatistisiData'] = $rekomendasiStatistisiData;
    
        // Search & Pagination
        $keyword = $this->request->getGet('q');
        $perPage = 10;
        $page = (int) ($this->request->getGet('page') ?? 1);
    
        $usulanQuery = $this->usulanModel
            ->select('usulan.*, akun.nama, instansi.nama_instansi')
            ->join('akun', 'akun.id = usulan.akun_id')
            ->join('instansi', 'instansi.id = akun.id_instansi');
    
        if ($keyword) {
            $usulanQuery->groupStart()
                ->like('akun.nama', $keyword)
                ->orLike('instansi.nama_instansi', $keyword)
                ->groupEnd();
        }
    
        $usulanList = $usulanQuery->orderBy('usulan.id', 'DESC')->paginate($perPage, 'usulan', $page);
        $data['pager'] = $this->usulanModel->pager;
    
        // Tabel Data
        $tabelData = [];
        foreach ($usulanList as $usulan) {
            $usulanId = $usulan['id'];
    
            $disposisi = $this->disposisiModel->where('usulan_id', $usulanId)->first();
            $sudahDisposisi = $disposisi ? true : false;
            $disposisiId = $disposisi['id'] ?? null;
    
            $jumlahPrakomUsulan = $this->kompetensiUsulanModel
                ->where('usulan_id', $usulanId)
                ->where('tingkat_kompetensi_id >=', 1)
                ->where('tingkat_kompetensi_id <=', 7)
                ->selectSum('jumlah')
                ->get()->getRow()->jumlah ?? 0;
    
            $jumlahStatistisiUsulan = $this->kompetensiUsulanModel
                ->where('usulan_id', $usulanId)
                ->where('tingkat_kompetensi_id >=', 8)
                ->where('tingkat_kompetensi_id <=', 14)
                ->selectSum('jumlah')
                ->get()->getRow()->jumlah ?? 0;
    
            $rekomendasi = $rekomendasiModel->where('disposisi_id', $disposisiId ?? 0)->first();
            $suratRekomendasi = $rekomendasi['surat_rekomendasi'] ?? null;
                
            $jumlahPrakomRekomendasi = 0;
            $jumlahStatistisiRekomendasi = 0;
    
            if ($rekomendasi) {
                $jumlahPrakomRekomendasi = $kompetensiDisetujuiModel
                    ->where('rekomendasi_id', $rekomendasi['id'])
                    ->where('tingkat_kompetensi_id >=', 1)
                    ->where('tingkat_kompetensi_id <=', 7)
                    ->selectSum('jumlah')
                    ->get()->getRow()->jumlah ?? 0;
    
                $jumlahStatistisiRekomendasi = $kompetensiDisetujuiModel
                    ->where('rekomendasi_id', $rekomendasi['id'])
                    ->where('tingkat_kompetensi_id >=', 8)
                    ->where('tingkat_kompetensi_id <=', 14)
                    ->selectSum('jumlah')
                    ->get()->getRow()->jumlah ?? 0;
            }
    
            // Penentuan Status yang benar
            if (!$disposisi) {
                $status = 'Diterima';
            } elseif ($disposisi && !$rekomendasi) {
                $status = 'Diproses';
            } elseif ($disposisi && $rekomendasi) {
                $status = 'Selesai';
            }
    
            $tabelData[] = [
                'id' => $usulan['id'],
                'instansi' => $usulan['nama_instansi'],
                'nama_akun' => $usulan['nama'],
                'tanggal_usulan' => $usulan['tanggal_usulan'],
                'jumlah_prakom_usulan' => $jumlahPrakomUsulan,
                'jumlah_statistisi_usulan' => $jumlahStatistisiUsulan,
                'jumlah_prakom_rekomendasi' => $jumlahPrakomRekomendasi,
                'jumlah_statistisi_rekomendasi' => $jumlahStatistisiRekomendasi,
                'status' => $status,
                'surat_usulan' => $usulan['surat_usulan'] ?? null,
                'berkas_usulan' => $usulan['berkas_usulan'] ?? null,
                'sudah_disposisi' => $sudahDisposisi,
                'disposisi_id' => $disposisiId,
                'surat_rekomendasi' => $suratRekomendasi,
            ];
        }
    
        // Filtering manual jika ada keyword
        if ($keyword) {
            $keywordLower = strtolower($keyword);
            $tabelData = array_filter($tabelData, function ($row) use ($keywordLower) {
                return strpos(strtolower($row['status']), $keywordLower) !== false
                    || strpos(strtolower($row['instansi']), $keywordLower) !== false
                    || strpos(strtolower($row['nama_akun']), $keywordLower) !== false;
            });
        }
    
        $data['tabelData'] = $tabelData;
    
        return view('dashboard-admin', $data);
    }

    public function superAdmin()
    {
        $akunModel = new AkunModel();
        $instansiModel = new InstansiModel();
        $rekomendasiModel = new \App\Models\RekomendasiModel();
        $kompetensiDisetujuiModel = new \App\Models\KompetensiDisetujuiModel();
        
        // Info Card
        $data['totalPengajuan'] = $this->usulanModel->countAll();
        
        // Ambil semua instansi
        $instansiList = $instansiModel->findAll();
    
        // Inisialisasi variabel untuk menyimpan total
        $totalPrakom = 0;
        $totalStatistisi = 0;
        $existingPrakom = [];
        $existingStatistisi = [];
    
        foreach ($instansiList as $instansi) {
            $akunIds = $akunModel->where('id_instansi', $instansi['id'])->findColumn('id') ?? [];
    
            if (empty($akunIds)) {
                continue; // skip kalau instansi tidak punya akun
            }
    
            $lastUsulan = $this->usulanModel
                ->whereIn('akun_id', $akunIds)
                ->orderBy('id', 'DESC')
                ->first();
    
            if (!$lastUsulan) {
                continue; // skip kalau instansi belum ada usulan
            }
    
            $lastUsulanId = $lastUsulan['id'];
    
            // Ambil Existing berdasarkan usulan terakhir instansi ini
            $sumPrakom = $this->kompetensiExistingModel
                ->select('tingkat_kompetensi_id, SUM(jumlah) as total')
                ->where('usulan_id', $lastUsulanId)
                ->where('tingkat_kompetensi_id >=', 1)
                ->where('tingkat_kompetensi_id <=', 7)
                ->groupBy('tingkat_kompetensi_id')
                ->findAll();
            
            $sumStatistisi = $this->kompetensiExistingModel
                ->select('tingkat_kompetensi_id, SUM(jumlah) as total')
                ->where('usulan_id', $lastUsulanId)
                ->where('tingkat_kompetensi_id >=', 8)
                ->where('tingkat_kompetensi_id <=', 14)
                ->groupBy('tingkat_kompetensi_id')
                ->findAll();
    
            // Gabungkan semua existing dari tiap instansi
            foreach ($sumPrakom as $row) {
                if (!isset($existingPrakom[$row['tingkat_kompetensi_id']])) {
                    $existingPrakom[$row['tingkat_kompetensi_id']] = 0;
                }
                $existingPrakom[$row['tingkat_kompetensi_id']] += $row['total'];
            }
    
            foreach ($sumStatistisi as $row) {
                if (!isset($existingStatistisi[$row['tingkat_kompetensi_id']])) {
                    $existingStatistisi[$row['tingkat_kompetensi_id']] = 0;
                }
                $existingStatistisi[$row['tingkat_kompetensi_id']] += $row['total'];
            }
    
            $totalPrakom += array_sum(array_column($sumPrakom, 'total'));
            $totalStatistisi += array_sum(array_column($sumStatistisi, 'total'));
        }
    
        // Info Cards
        $data['totalPrakom'] = $totalPrakom;
        $data['totalStatistisi'] = $totalStatistisi;
        $data['existingPrakom'] = $existingPrakom;
        $data['existingStatistisi'] = $existingStatistisi;
    
        // Chart Data
        $data['rekomendasiPrakom'] = $kompetensiDisetujuiModel->sumPrakomByCategory();
        $data['rekomendasiStatistisi'] = $kompetensiDisetujuiModel->sumStatistisiByCategory();
    
        // Search & Pagination
        $keyword = $this->request->getGet('q');
        $perPage = 10;
        $page = (int) ($this->request->getGet('page') ?? 1);
    
        // Query dasar
        $usulanQuery = $this->usulanModel
            ->select('usulan.*, akun.nama, instansi.nama_instansi')
            ->join('akun', 'akun.id = usulan.akun_id')
            ->join('instansi', 'instansi.id = akun.id_instansi');
    
        if ($keyword) {
            $usulanQuery->groupStart()
                ->like('akun.nama', $keyword)
                ->orLike('instansi.nama_instansi', $keyword)
                ->groupEnd();
        }
    
        $usulanList = $usulanQuery->orderBy('usulan.id', 'DESC')->paginate($perPage, 'usulan', $page);
        $data['pager'] = $this->usulanModel->pager;
    
        // Tabel Data
        $tabelData = [];
        foreach ($usulanList as $usulan) {
            $usulanId = $usulan['id'];
    
            // Jumlah Usulan Prakom dan Statistisi
            $jumlahPrakomUsulan = $this->kompetensiUsulanModel
                ->where('usulan_id', $usulanId)
                ->where('tingkat_kompetensi_id >=', 1)
                ->where('tingkat_kompetensi_id <=', 7)
                ->selectSum('jumlah')
                ->get()->getRow()->jumlah ?? 0;
    
            $jumlahStatistisiUsulan = $this->kompetensiUsulanModel
                ->where('usulan_id', $usulanId)
                ->where('tingkat_kompetensi_id >=', 8)
                ->where('tingkat_kompetensi_id <=', 14)
                ->selectSum('jumlah')
                ->get()->getRow()->jumlah ?? 0;
    
            // Ambil disposisi_id dari tabel disposisi yang berhubungan dengan usulan
            $disposisi = $this->disposisiModel->where('usulan_id', $usulanId)->first();
    
            // Ambil rekomendasi berdasarkan disposisi_id
            $rekomendasi = $rekomendasiModel->where('disposisi_id', $disposisi['id'] ?? 0)->first();
            $suratRekomendasi = $rekomendasi['surat_rekomendasi'] ?? null;

            $jumlahPrakomRekomendasi = 0;
            $jumlahStatistisiRekomendasi = 0;
    
            if ($rekomendasi) {
                // Ambil jumlah rekomendasi Prakom
                $jumlahPrakomRekomendasi = $kompetensiDisetujuiModel
                    ->where('rekomendasi_id', $rekomendasi['id'])
                    ->where('tingkat_kompetensi_id >=', 1)
                    ->where('tingkat_kompetensi_id <=', 7)
                    ->selectSum('jumlah')
                    ->get()->getRow()->jumlah ?? 0;
    
                // Ambil jumlah rekomendasi Statistisi
                $jumlahStatistisiRekomendasi = $kompetensiDisetujuiModel
                    ->where('rekomendasi_id', $rekomendasi['id'])
                    ->where('tingkat_kompetensi_id >=', 8)
                    ->where('tingkat_kompetensi_id <=', 14)
                    ->selectSum('jumlah')
                    ->get()->getRow()->jumlah ?? 0;
            }
    
            // Tentukan status berdasarkan rekomendasi
            $status = 'Diterima'; // Default status jika tidak ada disposisi_id
            if ($disposisi && !$rekomendasi) {
                $status = 'Diproses'; // Jika ada disposisi tetapi belum ada rekomendasi
            } elseif ($disposisi && $rekomendasi) {
                $status = 'Selesai'; // Jika ada disposisi dan rekomendasi
            }
    
            $tabelData[] = [
                'id' => $usulan['id'],
                'instansi' => $usulan['nama_instansi'],
                'nama_akun' => $usulan['nama'],
                'tanggal_usulan' => $usulan['tanggal_usulan'],
                'jumlah_prakom_usulan' => $jumlahPrakomUsulan,
                'jumlah_statistisi_usulan' => $jumlahStatistisiUsulan,
                'jumlah_prakom_rekomendasi' => $jumlahPrakomRekomendasi,
                'jumlah_statistisi_rekomendasi' => $jumlahStatistisiRekomendasi,
                'status' => $status,
                'surat_usulan' => $usulan['surat_usulan'] ?? null,
                'berkas_usulan' => $usulan['berkas_usulan'] ?? null,
                'surat_rekomendasi' => $suratRekomendasi,
            ];
        }
    
        // Filtering manual berdasarkan keyword status
        if ($keyword) {
            $keywordLower = strtolower($keyword);
            $tabelData = array_filter($tabelData, function ($row) use ($keywordLower) {
                return strpos(strtolower($row['status']), $keywordLower) !== false
                    || strpos(strtolower($row['instansi']), $keywordLower) !== false
                    || strpos(strtolower($row['nama_akun']), $keywordLower) !== false;
            });
        }
    
        $data['tabelData'] = $tabelData;
    
        return view('dashboard-super', $data);
    }
    
    public function detail($id)
    {
        // Ambil data Usulan
        $usulan = $this->usulanModel
            ->select('usulan.*, akun.nama, akun.email, akun.nomor_hp, instansi.nama_instansi')
            ->join('akun', 'akun.id = usulan.akun_id')
            ->join('instansi', 'instansi.id = akun.id_instansi')
            ->where('usulan.id', $id)
            ->first();
        
        if (!$usulan) {
            return redirect()->to(site_url('dashboard/operator'))->with('error', 'Data usulan tidak ditemukan.');
        }
        
        // Ambil data Disposisi
        $disposisi = $this->disposisiModel
            ->select('disposisi.*, akun.nama as nama_admin')
            ->join('akun', 'akun.id = disposisi.admin_id')
            ->where('disposisi.usulan_id', $id)
            ->first();
        
        // Ambil data Rekomendasi
        $rekomendasi = null;
        if ($disposisi) {
            $rekomendasi = $this->rekomendasiModel
                ->select('*')
                ->where('disposisi_id', $disposisi['id'])
                ->first();
        }
        
        // Daftar Jabatan (Tingkatan Kompetensi)
        $tingkatan = [
            ['id' => 1, 'nama' => 'Utama'],
            ['id' => 2, 'nama' => 'Madya'],
            ['id' => 3, 'nama' => 'Muda'],
            ['id' => 4, 'nama' => 'Pertama'],
            ['id' => 5, 'nama' => 'Penyelia'],
            ['id' => 6, 'nama' => 'Mahir'],
            ['id' => 7, 'nama' => 'Terampil'],
        ];
        
        // Siapkan array kosong
        $usulanPrakom = [];
        $usulanStatistisi = [];
        $existingPrakom = [];
        $existingStatistisi = [];
        $rekomendasiPrakom = [];
        $rekomendasiStatistisi = [];
        
        // Ambil semua data Kompetensi Usulan
        $usulanKompetensi = $this->kompetensiUsulanModel->where('usulan_id', $id)->findAll();
        foreach ($usulanKompetensi as $item) {
            if ($item['tingkat_kompetensi_id'] >= 1 && $item['tingkat_kompetensi_id'] <= 7) {
                $usulanPrakom[$item['tingkat_kompetensi_id']] = $item['jumlah'];
            } elseif ($item['tingkat_kompetensi_id'] >= 8 && $item['tingkat_kompetensi_id'] <= 14) {
                $usulanStatistisi[$item['tingkat_kompetensi_id'] - 7] = $item['jumlah'];
            }
        }
        
        // Ambil semua data Kompetensi Existing
        $existingKompetensi = $this->kompetensiExistingModel->where('usulan_id', $id)->findAll();
        foreach ($existingKompetensi as $item) {
            if ($item['tingkat_kompetensi_id'] >= 1 && $item['tingkat_kompetensi_id'] <= 7) {
                $existingPrakom[$item['tingkat_kompetensi_id']] = $item['jumlah'];
            } elseif ($item['tingkat_kompetensi_id'] >= 8 && $item['tingkat_kompetensi_id'] <= 14) {
                $existingStatistisi[$item['tingkat_kompetensi_id'] - 7] = $item['jumlah'];
            }
        }
        
        // Jika ada rekomendasi, ambil kompetensi disetujui
        $disetujuiKompetensi = [];
        if ($rekomendasi) {
            $disetujuiKompetensi = $this->kompetensiDisetujuiModel
                ->where('rekomendasi_id', $rekomendasi['id'])
                ->findAll();
        
            foreach ($disetujuiKompetensi as $item) {
                if ($item['tingkat_kompetensi_id'] >= 1 && $item['tingkat_kompetensi_id'] <= 7) {
                    $rekomendasiPrakom[$item['tingkat_kompetensi_id']] = $item['jumlah'];
                } elseif ($item['tingkat_kompetensi_id'] >= 8 && $item['tingkat_kompetensi_id'] <= 14) {
                    $rekomendasiStatistisi[$item['tingkat_kompetensi_id'] - 7] = $item['jumlah'];
                }
            }
        }
        
        // Kirim ke View
        return view('detail', [
            'usulan' => $usulan,
            'disposisi' => $disposisi,
            'rekomendasi' => $rekomendasi, // Pastikan ini ada
            'tingkatan' => $tingkatan,
            'usulanPrakom' => $usulanPrakom,
            'usulanStatistisi' => $usulanStatistisi,
            'existingPrakom' => $existingPrakom,
            'existingStatistisi' => $existingStatistisi,
            'rekomendasiPrakom' => $rekomendasiPrakom,
            'rekomendasiStatistisi' => $rekomendasiStatistisi,
            'disetujuiKompetensi' => $disetujuiKompetensi
        ]);
    }

    

}


    
    
    