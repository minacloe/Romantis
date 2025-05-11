<?php

namespace App\Models;

use CodeIgniter\Model;

class JenisKompetensiModel extends Model
{
    protected $table = 'jenis_kompetensi';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id',
        'nama',
        'kode'
    ];

    public function getAll()
    {
        return $this->findAll();
    }
}
