<?php namespace WebEd\Base\Menu\Repositories;

use Illuminate\Support\Collection;
use WebEd\Base\Caching\Services\Traits\Cacheable;
use WebEd\Base\Core\Repositories\Eloquent\EloquentBaseRepository;
use WebEd\Base\Caching\Services\Contracts\CacheableContract;

use WebEd\Base\Menu\Models\Contracts\MenuModelContract;
use WebEd\Base\Menu\Repositories\Contracts\MenuNodeRepositoryContract;

class MenuNodeRepository extends EloquentBaseRepository implements MenuNodeRepositoryContract, CacheableContract
{
    use Cacheable;

    protected $rules = [

    ];

    protected $editableFields = [
        '*',
    ];

    /**
     * @var Collection
     */
    protected $allRelatedNodes;

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

    /**
     * Get menu nodes
     * @param $menuId
     * @param null|int $parentId
     * @return mixed|null
     */
    public function getMenuNodes($menuId, $parentId = null)
    {
        if($menuId instanceof MenuModelContract) {
            $menu = $menuId;
        } else {
            $menu = $this->find($menuId);
        }
        if(!$menu) {
            return null;
        }

        if (!$this->allRelatedNodes) {
            $this->allRelatedNodes = $this
                ->where('menu_id', $menuId->id)
                ->select(['id', 'menu_id', 'parent_id', 'related_id', 'type', 'url', 'title', 'icon_font', 'css_class', 'target'])
                ->orderBy('sort_order', 'ASC')
                ->get();
        }

        $nodes = $this->allRelatedNodes->where('parent_id', $parentId);

        $result = [];

        foreach ($nodes as $node) {
            $node->model_title = $node->title;
            $node->children = $this->getMenuNodes($menuId, $node->id);
            $result[] = $node;
            /**
             * Reset related nodes when done
             */
            if ($node->id == $nodes->last()->id && $parentId === null) {
                $this->allRelatedNodes = null;
            }
        }

        return collect($result);
    }
}
