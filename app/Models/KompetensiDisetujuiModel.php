<?php

namespace App\Models;

use CodeIgniter\Model;

class KompetensiDisetujuiModel extends Model
{
    protected $table = 'kompetensi_disetujui';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id',
        'rekomendasi_id',
        'tingkat_kompetensi_id',
        'jumlah'
    ];

    public function getAll()
    {
        return $this->findAll();
    }

        // Sum of 'jumlah' for Prakom (1–7)
        public function sumPrakomByCategory($rekomendasiIds = null)
        {
            $builder = $this->builder();
            $builder->select('tingkat_kompetensi_id, SUM(jumlah) AS total_jumlah');
            $builder->where('tingkat_kompetensi_id >=', 1)
                    ->where('tingkat_kompetensi_id <=', 7);
    
            $builder->groupBy('tingkat_kompetensi_id');
            
            if ($rekomendasiIds) {
                $builder->whereIn('rekomendasi_id', $rekomendasiIds);
            }
    
            $result = $builder->get()->getResultArray();
    
            // Organize the result by category
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
                    case 1:
                        $categoryData['Utama'] = $row['total_jumlah'];
                        break;
                    case 2:
                        $categoryData['Madya'] = $row['total_jumlah'];
                        break;
                    case 3:
                        $categoryData['Muda'] = $row['total_jumlah'];
                        break;
                    case 4:
                        $categoryData['Pertama'] = $row['total_jumlah'];
                        break;
                    case 5:
                        $categoryData['Penyelia'] = $row['total_jumlah'];
                        break;
                    case 6:
                        $categoryData['Mahir'] = $row['total_jumlah'];
                        break;
                    case 7:
                        $categoryData['Terampil'] = $row['total_jumlah'];
                        break;
                }
            }
    
            return $categoryData;
        }
    
        // Sum of 'jumlah' for Statistisi (8–14)
        public function sumStatistisiByCategory($rekomendasiIds = null)
        {
            $builder = $this->builder();
            $builder->select('tingkat_kompetensi_id, SUM(jumlah) AS total_jumlah');
            $builder->where('tingkat_kompetensi_id >=', 8)
                    ->where('tingkat_kompetensi_id <=', 14);
    
            $builder->groupBy('tingkat_kompetensi_id');
    
            if ($rekomendasiIds) {
                $builder->whereIn('rekomendasi_id', $rekomendasiIds);
            }
    
            $result = $builder->get()->getResultArray();
    
            // Organize the result by category
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
                    case 8:
                        $categoryData['Utama'] = $row['total_jumlah'];
                        break;
                    case 9:
                        $categoryData['Madya'] = $row['total_jumlah'];
                        break;
                    case 10:
                        $categoryData['Muda'] = $row['total_jumlah'];
                        break;
                    case 11:
                        $categoryData['Pertama'] = $row['total_jumlah'];
                        break;
                    case 12:
                        $categoryData['Penyelia'] = $row['total_jumlah'];
                        break;
                    case 13:
                        $categoryData['Mahir'] = $row['total_jumlah'];
                        break;
                    case 14:
                        $categoryData['Terampil'] = $row['total_jumlah'];
                        break;
                }
            }
    
            return $categoryData;
        }
    
        public function countAllKompetensiDisetujui($rekomendasiIds = null)
        {
            $builder = $this->builder();
        
            if ($rekomendasiIds) {
                $builder->whereIn('rekomendasi_id', $rekomendasiIds);
            }
        
            return $builder->countAllResults();
        }
}
