<?php namespace WebEd\Base\Menu\Repositories;

use WebEd\Base\Caching\Repositories\AbstractRepositoryCacheDecorator;

use WebEd\Base\Menu\Repositories\Contracts\MenuNodeRepositoryContract;

class MenuNodeRepositoryCacheDecorator extends AbstractRepositoryCacheDecorator  implements MenuNodeRepositoryContract
{
    /**
     * $messages
     * @param $menuId
     * @param $node
     * @param null $parentId
     */
    public function updateMenuNode($menuId, $node, $order, $parentId = null)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args(), true, true);
    }
}
