<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    //
    protected $fillable=[
      'name',
      'initial_stock',
      'stock',
      'email_sent'
    ];
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('amount')->withTimestamps();
    }

    public function isLowStock(): bool
    {
        return $this->stock < ($this->initial_stock / 2);
    }
}
