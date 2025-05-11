<?php

namespace App\Models;

use CodeIgniter\Model;

class UsulanModel extends Model
{
    protected $table = 'usulan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id',
        'akun_id',
        'no_surat',
        'tanggal_usulan',
        'surat_usulan',
        'berkas_usulan'
    ];

    public function getAll()
    {
        return $this->findAll();
    }

    // Hitung total pengajuan semua
    public function countAllUsulan()
    {
        return $this->countAllResults();
    }

    // Hitung total pengajuan berdasarkan akun login
    // UsulanModel.php

    public function countUsulanByAkun($akunId)
    {
        return $this->where('akun_id', $akunId)->countAllResults();
    }

}
