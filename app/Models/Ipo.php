<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ipo extends Model
{
    use HasFactory;

    // Veritabanına toplu olarak eklenebilecek (fillable) alanlar
    protected $fillable = [
        'company_name',
        'stock_code',
        'price',
        'total_lots',
        'start_date',
        'end_date',
        'is_participation_index',
        'status',
    ];
}