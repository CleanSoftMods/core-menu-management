<?php namespace WebEd\Base\Menu\Repositories;

use WebEd\Base\Caching\Repositories\AbstractRepositoryCacheDecorator;

use WebEd\Base\Menu\Models\Contracts\MenuModelContract;
use WebEd\Base\Menu\Repositories\Contracts\MenuRepositoryContract;

class MenuRepositoryCacheDecorator extends AbstractRepositoryCacheDecorator  implements MenuRepositoryContract
{
    /**
     * Create menu
     * @param $data
     * @return array
     */
    public function createMenu($data)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args());
    }

    /**
     * Update menu
     * @param $id
     * @param $data
     * @param bool $allowCreateNew
     * @param bool $justUpdateSomeFields
     * @return array
     */
    public function updateMenu($id, $data, $allowCreateNew = false, $justUpdateSomeFields = true)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args());
    }

    /**
     * Update menu structure
     * @param $menuId
     * @param $menuStructure
     */
    public function updateMenuStructure($menuId, $menuStructure)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args());
    }

    /**
     * Get menu
     * @param $id
     * @return mixed|null|MenuModelContract
     */
    public function getMenu($id)
    {
        return $this->beforeGet(__FUNCTION__, func_get_args());
    }

    /**
     * Get menu nodes
     * @param $menuId
     * @param null $parentId
     * @return mixed|null
     */
    public function getMenuNodes($menuId, $parentId = null)
    {
        return $this->beforeGet(__FUNCTION__, func_get_args());
    }
}
