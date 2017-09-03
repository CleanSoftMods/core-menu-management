<?php namespace CleanSoft\Modules\Core\Menu\Facades;

use Illuminate\Support\Facades\Facade;
use CleanSoft\Modules\Core\Menu\Support\DashboardMenu;

class DashboardMenuFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return DashboardMenu::class;
    }
}
