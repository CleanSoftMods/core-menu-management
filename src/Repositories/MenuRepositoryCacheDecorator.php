<?php namespace CleanSoft\Modules\Core\Menu\Repositories;

use CleanSoft\Modules\Core\Menu\Models\Menu;
use CleanSoft\Modules\Core\Repositories\Eloquent\EloquentBaseRepositoryCacheDecorator;

use CleanSoft\Modules\Core\Menu\Repositories\Contracts\MenuRepositoryContract;

class MenuRepositoryCacheDecorator extends EloquentBaseRepositoryCacheDecorator  implements MenuRepositoryContract
{
    /**
     * @param array $data
     * @param array|null $menuStructure
     * @return int|null
     */
    public function createMenu(array $data, array $menuStructure = null)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args());
    }

    /**
     * @param $id
     * @param array $data
     * @param array|null $menuStructure
     * @param array|null $deletedNodes
     * @return int|null
     */
    public function updateMenu($id, array $data, array $menuStructure = null, array $deletedNodes = null)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args());
    }

    /**
     * @param $menuId
     * @param array $menuStructure
     */
    public function updateMenuStructure($menuId, array $menuStructure)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args());
    }

    /**
     * @param Menu|int $id
     * @return \Illuminate\Database\Eloquent\Builder|null|Menu|\CleanSoft\Modules\Core\Models\EloquentBase
     */
    public function getMenu($id)
    {
        return $this->beforeGet(__FUNCTION__, func_get_args());
    }
}
