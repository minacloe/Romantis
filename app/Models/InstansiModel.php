<?php

namespace App\Models;

use CodeIgniter\Model;

class InstansiModel extends Model
{
    protected $table      = 'instansi';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['nama_instansi', 'singkatan'];
    
    protected $validationRules    = [
        'nama_instansi' => 'required|min_length[3]|max_length[100]',
        'singkatan'     => 'max_length[20]',
    ];
    protected $validationMessages = [];
    protected $skipValidation     = false;
}