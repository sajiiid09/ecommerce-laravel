<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'type',
        'url',
        'route_name',
        'enabled',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'enabled' => 'boolean',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function targets()
    {
        return $this->hasMany(MenuItemTarget::class);
    }
}
