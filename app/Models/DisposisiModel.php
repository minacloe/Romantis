<?php

namespace App\Models;

use CodeIgniter\Model;

class DisposisiModel extends Model
{
    protected $table = 'disposisi';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'id',
        'usulan_id',
        'admin_id',
        'tanggal_disposisi',
        'jadwal_rapat'
    ];

    public function getAll()
    {
        return $this->findAll();
    }

    
}
