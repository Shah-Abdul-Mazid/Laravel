<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['title', 'description', 'category', 'feature_video'];

    public function modules()
    {
        return $this->hasMany(Module::class);
    }
}