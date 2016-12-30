<?php namespace WebEd\Base\Menu\Repositories\Contracts;

interface MenuNodeRepositoryContract
{
    /**
     * $messages
     * @param $menuId
     * @param $node
     * @param array $messages
     * @param null $parentId
     */
    public function updateMenuNode($menuId, $node, $order, array &$messages, $parentId = null);
}
