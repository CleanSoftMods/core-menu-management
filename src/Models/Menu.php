<?php namespace CleanSoft\Modules\Core\Menu\Models;

use CleanSoft\Modules\Core\Menu\Models\Contracts\MenuModelContract;
use CleanSoft\Modules\Core\Models\EloquentBase as BaseModel;

class Menu extends BaseModel implements MenuModelContract
{
    protected $table = 'menus';

    protected $primaryKey = 'id';

    protected $fillable = [
        'title', 'slug', 'status', 'created_by', 'updated_by',
    ];

    public $timestamps = true;
}
