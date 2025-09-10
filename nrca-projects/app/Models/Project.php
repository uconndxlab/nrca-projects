<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['year', 'program', 'title', 'county', 'thumbnail', 'primary_product', 'primary_product_url', 'secondary_product', 'secondary_product_url', 'third_download', 'third_download_url'];


    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
