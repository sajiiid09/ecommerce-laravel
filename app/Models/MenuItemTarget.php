<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItemTarget extends Model
{
    protected $fillable = [
        'menu_item_id',
        'target_type',
        'target_id',
        'sort_order',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function target()
    {
        return $this->morphTo();
    }
}
