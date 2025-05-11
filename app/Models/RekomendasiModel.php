<?php

namespace App\Models;

use CodeIgniter\Model;

class RekomendasiModel extends Model
{
    protected $table = 'rekomendasi';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id',
        'disposisi_id',
        'tanggal_rekomendasi',
        'no_rekomendasi',
        'surat_rekomendasi'
    ];

    public function getAll()
    {
        return $this->findAll();
    }
}
