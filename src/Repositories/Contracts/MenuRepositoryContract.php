<?php namespace WebEd\Base\Menu\Repositories\Contracts;

use WebEd\Base\Menu\Models\Contracts\MenuModelContract;

interface MenuRepositoryContract
{
    /**
     * Create menu
     * @param $data
     * @return array
     */
    public function createMenu($data);

    /**
     * Update menu
     * @param $id
     * @param $data
     * @param bool $allowCreateNew
     * @param bool $justUpdateSomeFields
     * @return array
     */
    public function updateMenu($id, $data, $allowCreateNew = false, $justUpdateSomeFields = true);

    /**
     * Update menu structure
     * @param $menuId
     * @param $menuStructure
     */
    public function updateMenuStructure($menuId, $menuStructure);

    /**
     * Get menu
     * @param $id
     * @return mixed|null|MenuModelContract
     */
    public function getMenu($id);

    /**
     * Get menu nodes
     * @param $menuId
     * @param null $parentId
     * @return mixed|null
     */
    public function getMenuNodes($menuId, $parentId = null);
}
