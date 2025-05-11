<?php

namespace App\Models;

use CodeIgniter\Model;

class TingkatKompetensiModel extends Model
{
    protected $table = 'tingkat_kompetensi';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id',
        'tingkat_kompetensi_id',
        'nama',
        'tingkat',
        'urutan',
    ];

    public function getAll()
    {
        return $this->findAll();
    }
}
