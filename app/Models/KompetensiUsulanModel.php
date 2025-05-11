<?php

namespace App\Models;

use CodeIgniter\Model;

class KompetensiUsulanModel extends Model
{
    protected $table = 'kompetensi_usulan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'usulan_id',
        'tingkat_kompetensi_id',
        'jumlah'
    ];

    public function getAll()
    {
        return $this->findAll();
    }
    public function countAllUsulanKompetensi($usulanIds = null)
    {
        $builder = $this->builder();

        if ($usulanIds) {
            $builder->whereIn('usulan_id', $usulanIds);
        }

        return $builder->countAllResults();
    }

    

public function sumStatistisiByCategory($usulanIds = null)
{
    $builder = $this->builder();
    $builder->select('tingkat_kompetensi_id, SUM(jumlah) AS total_jumlah');
    $builder->where('tingkat_kompetensi_id >=', 8)
            ->where('tingkat_kompetensi_id <=', 14);

    if (!empty($usulanIds)) {
        $builder->whereIn('usulan_id', $usulanIds); // ✅ yang benar pakai USULAN_ID
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
            case 8: $categoryData['Utama'] = $row['total_jumlah']; break;
            case 9: $categoryData['Madya'] = $row['total_jumlah']; break;
            case 10: $categoryData['Muda'] = $row['total_jumlah']; break;
            case 11: $categoryData['Pertama'] = $row['total_jumlah']; break;
            case 12: $categoryData['Penyelia'] = $row['total_jumlah']; break;
            case 13: $categoryData['Mahir'] = $row['total_jumlah']; break;
            case 14: $categoryData['Terampil'] = $row['total_jumlah']; break;
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
        $builder->whereIn('usulan_id', $usulanIds); // ✅ yang benar pakai USULAN_ID
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
            case 1: $categoryData['Utama'] = $row['total_jumlah']; break;
            case 2: $categoryData['Madya'] = $row['total_jumlah']; break;
            case 3: $categoryData['Muda'] = $row['total_jumlah']; break;
            case 4: $categoryData['Pertama'] = $row['total_jumlah']; break;
            case 5: $categoryData['Penyelia'] = $row['total_jumlah']; break;
            case 6: $categoryData['Mahir'] = $row['total_jumlah']; break;
            case 7: $categoryData['Terampil'] = $row['total_jumlah']; break;
        }
    }

    return $categoryData;
}


}
