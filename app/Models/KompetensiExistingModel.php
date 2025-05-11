<?php

namespace App\Models;

use CodeIgniter\Model;

class KompetensiExistingModel extends Model
{
    protected $table = 'kompetensi_existing';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'usulan_id',
        'tingkat_kompetensi_id',
        'jumlah'
    ];

    // Hitung total existing Prakom (1-7) berdasarkan jumlah
    // Sum of existing Prakom (1–7)
    public function countPrakom($usulanIds = null)
    {
        $builder = $this->builder();
        $builder->selectSum('jumlah');
        $builder->where('tingkat_kompetensi_id >=', 1)
                ->where('tingkat_kompetensi_id <=', 7);
        
        if ($usulanIds) {
            $builder->whereIn('usulan_id', $usulanIds);
        }

        $result = $builder->get()->getRow();
        return $result ? $result->jumlah : 0; 
    }

    // Sum of existing Statistisi (8–14)
    public function countStatistisi($usulanIds = null)
    {
        $builder = $this->builder();
        $builder->selectSum('jumlah');
        $builder->where('tingkat_kompetensi_id >=', 8)
                ->where('tingkat_kompetensi_id <=', 14);
        
        if (!empty($usulanIds)) {
            $builder->whereIn('usulan_id', $usulanIds);
        }

        $result = $builder->get()->getRow();
        return $result ? $result->jumlah : 0;
    }



    // Sum of 'jumlah' for Statistisi (8–14)
    public function sumStatistisiByCategory($usulanIds = null)
    {
        $builder = $this->builder();
        $builder->select('tingkat_kompetensi_id, SUM(jumlah) AS total_jumlah');
        $builder->where('tingkat_kompetensi_id >=', 8)
                ->where('tingkat_kompetensi_id <=', 14);
    
        if (!empty($usulanIds)) {
            $builder->whereIn('usulan_id', $usulanIds); // ✅ pakai usulan_id
        }
    
        $builder->groupBy('tingkat_kompetensi_id');
        $result = $builder->get()->getResultArray();
    
        $categoryData = [
            'Utama' => 0,
            'Madya' => 0,
            'Muda'  => 0,
            'Pertama' => 0,
            'Penyelia' => 0,
            'Mahir' => 0,
            'Terampil' => 0,
        ];
    
        foreach ($result as $row) {
            switch ($row['tingkat_kompetensi_id']) {
                case 8: $categoryData['Utama'] = (int) $row['total_jumlah']; break;
                case 9: $categoryData['Madya'] = (int) $row['total_jumlah']; break;
                case 10: $categoryData['Muda'] = (int) $row['total_jumlah']; break;
                case 11: $categoryData['Pertama'] = (int) $row['total_jumlah']; break;
                case 12: $categoryData['Penyelia'] = (int) $row['total_jumlah']; break;
                case 13: $categoryData['Mahir'] = (int) $row['total_jumlah']; break;
                case 14: $categoryData['Terampil'] = (int) $row['total_jumlah']; break;
            }
        }
    
        return $categoryData;
    }
    
    public function sumPrakomByCategory($usulanIds = null)
    {
        $builder = $this->builder();
        $builder->select('tingkat_kompetensi_id, SUM(jumlah) AS total_jumlah');
        $builder->where('tingkat_kompetensi_id >=', 1)
                ->where('tingkat_kompetensi_id <=', 7);
    
        if (!empty($usulanIds)) {
            $builder->whereIn('usulan_id', $usulanIds); // ✅ pakai usulan_id
        }
    
        $builder->groupBy('tingkat_kompetensi_id');
        $result = $builder->get()->getResultArray();
    
        $categoryData = [
            'Utama' => 0,
            'Madya' => 0,
            'Muda'  => 0,
            'Pertama' => 0,
            'Penyelia' => 0,
            'Mahir' => 0,
            'Terampil' => 0,
        ];
    
        foreach ($result as $row) {
            switch ($row['tingkat_kompetensi_id']) {
                case 1: $categoryData['Utama'] = (int) $row['total_jumlah']; break;
                case 2: $categoryData['Madya'] = (int) $row['total_jumlah']; break;
                case 3: $categoryData['Muda'] = (int) $row['total_jumlah']; break;
                case 4: $categoryData['Pertama'] = (int) $row['total_jumlah']; break;
                case 5: $categoryData['Penyelia'] = (int) $row['total_jumlah']; break;
                case 6: $categoryData['Mahir'] = (int) $row['total_jumlah']; break;
                case 7: $categoryData['Terampil'] = (int) $row['total_jumlah']; break;
            }
        }
    
        return $categoryData;
    }
    

}

