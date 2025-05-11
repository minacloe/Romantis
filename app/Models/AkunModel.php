<?php

namespace App\Models;

use App\Models\InstansiModel;
use CodeIgniter\Model;

class AkunModel extends Model
{
    protected $table      = 'akun';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'id',
        'id_instansi', 
        'nama',  
        'nip', 
        'nomor_hp',
        'email', 
        'password',
        'password_temp', 
        'tipe_akun'
    ];

    protected $validationRules    = [
        'id_instansi' => 'permit_empty|integer',
        'nama'        => 'required|min_length[3]|max_length[100]',
        'nip'         => 'required|min_length[16]|max_length[20]|is_unique[akun.nip]',
        'nomor_hp'    => 'required|min_length[10]|max_length[15]',
        'email'       => 'required|valid_email|max_length[100]|is_unique[akun.email]',
        'password'    => 'required|min_length[6]|max_length[255]',
        'tipe_akun'  => 'required|in_list[Operator,Admin,Super Admin]',
    ];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    
    // Relasi ke instansi
    public function getInstansi($id_instansi)
    {
        $instansiModel = new InstansiModel();
        return $instansiModel->find($id_instansi);
    }

    public function getAllWithInstansi()
    {
        return $this->select('akun.*, instansi.nama_instansi')
                    ->join('instansi', 'instansi.id = akun.id_instansi', 'left')
                    ->findAll();
    }

    public function updateAkun($id, $data)
    {
        try {
            $builder = $this->db->table('akun');
            $builder->where('id', $id);
            
            // Jika ada password baru
            if (isset($data['password'])) {
                $plainPassword = $data['password'];
                $data['password'] = password_hash($plainPassword, PASSWORD_DEFAULT);
                $data['password_temp'] = $plainPassword;
            }
            
            return $builder->update($data);
        } catch (\Exception $e) {
            log_message('error', 'Error updating account: ' . $e->getMessage());
            return false;
        }
    }

    // In your UserModel
    public function delete($id = null, bool $purge = false)
    {
        // Prevent deleting super admin accounts
        if ($id !== null) {
            $user = $this->find($id);
            if ($user && $user['tipe_akun'] === 'Super Admin') {
                return false;
            }
        }
    
        return parent::delete($id, $purge);
    }

    public function getWithInstansi($id)
    {
        $builder = $this->db->table('akun');
        $builder->select('akun.*, instansi.nama_instansi');
        $builder->join('instansi', 'instansi.id = akun.id_instansi', 'left');
        $builder->where('akun.id', $id);
        
        $query = $builder->get();
        
        if ($query->getNumRows() > 0) {
            return $query->getRowArray();
        }
        
        return null;
    }

    public function getAdminEmails()
    {
        return $this->db->table('akun')
            ->select('email')
            ->where('tipe_akun', 'Admin')
            ->where('email IS NOT NULL')
            ->get()
            ->getResultArray();
    }
}