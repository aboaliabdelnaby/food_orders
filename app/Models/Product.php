<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    //
    protected $fillable=[
        'name',
        'price'
    ];
    public function ingredients():BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)
            ->withPivot('amount')->withTimestamps();
    }
    public function orders()
    {
        return $this->belongsToMany(Order::class)
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
