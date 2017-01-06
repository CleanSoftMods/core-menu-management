<?php namespace WebEd\Base\Menu\Repositories;

use WebEd\Base\Core\Repositories\AbstractBaseRepository;
use WebEd\Base\Caching\Services\Contracts\CacheableContract;

use WebEd\Base\Menu\Repositories\Contracts\MenuNodeRepositoryContract;

class MenuNodeRepository extends AbstractBaseRepository implements MenuNodeRepositoryContract, CacheableContract
{
    protected $rules = [

    ];

    protected $editableFields = [
        '*',
    ];

    /**
     * $messages
     * @param $menuId
     * @param $node
     * @param null $parentId
     */
    public function updateMenuNode($menuId, $node, $order, $parentId = null)
    {
        $result = $this->editWithValidate(array_get($node, 'id'), [
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'related_id' => array_get($node, 'related_id') ?: null,
            'type' => array_get($node, 'type'),
            'title' => array_get($node, 'title'),
            'icon_font' => array_get($node, 'icon_font'),
            'css_class' => array_get($node, 'css_class'),
            'target' => array_get($node, 'target'),
            'url' => array_get($node, 'url'),
            'sort_order' => $order,
        ], true, true);

        /**
         * Add messages when some error occurred
         */
        if($result['error']) {
            flash_messages()->addMessages($result['messages'], 'danger');
            return;
        }

        $children = array_get($node, 'children', null);
        /**
         * Save the children
         */
        if(!$result['error'] && is_array($children)) {
            foreach ($children as $key => $child) {
                $this->updateMenuNode($menuId, $child, $key, $result['data']->id);
            }
        }
    }
}
