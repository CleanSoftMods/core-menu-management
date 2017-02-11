<?php namespace WebEd\Base\Menu\Repositories\Contracts;

interface MenuNodeRepositoryContract
{
    /**
     * $messages
     * @param $menuId
     * @param $node
     * @param null $parentId
     */
    public function updateMenuNode($menuId, $node, $order, $parentId = null);

    /**
     * Get menu nodes
     * @param $menuId
     * @param null|int $parentId
     * @return mixed|null
     */
    public function getMenuNodes($menuId, $parentId = null);
}
