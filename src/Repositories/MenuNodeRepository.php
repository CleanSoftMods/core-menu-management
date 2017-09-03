<?php namespace CleanSoft\Modules\Core\Menu\Repositories;

use Illuminate\Support\Collection;
use CleanSoft\Modules\Core\Menu\Models\Menu;
use CleanSoft\Modules\Core\Repositories\Eloquent\EloquentBaseRepository;
use CleanSoft\Modules\Core\Menu\Repositories\Contracts\MenuNodeRepositoryContract;

class MenuNodeRepository extends EloquentBaseRepository implements MenuNodeRepositoryContract
{
    /**
     * @var Collection
     */
    protected $allRelatedNodes;

    /**
     * @param int $menuId
     * @param array $nodeData
     * @param int $order
     * @param null $parentId
     * @return int|null
     */
    public function updateMenuNode($menuId, array $nodeData, $order, $parentId = null)
    {
        $result = $this->createOrUpdate(array_get($nodeData, 'id'), [
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'entity_id' => array_get($nodeData, 'entity_id') ?: null,
            'type' => array_get($nodeData, 'type'),
            'title' => array_get($nodeData, 'title'),
            'icon_font' => array_get($nodeData, 'icon_font'),
            'css_class' => array_get($nodeData, 'css_class'),
            'target' => array_get($nodeData, 'target'),
            'url' => array_get($nodeData, 'url'),
            'order' => $order,
        ]);

        if(!$result) {
            return $result;
        }

        $children = array_get($nodeData, 'children', null);

        /**
         * Save the children
         */
        if($result && is_array($children)) {
            foreach ($children as $key => $child) {
                $this->updateMenuNode($menuId, $child, $key, $result);
            }
        }
        return $result;
    }

    /**
     * @param Menu|int $menuId
     * @param null|int $parentId
     * @return Collection|null
     */
    public function getMenuNodes($menuId, $parentId = null)
    {
        if($menuId instanceof Menu) {
            $menu = $menuId;
        } else {
            $menu = $this->find($menuId);
        }
        if(!$menu) {
            return null;
        }

        if (!$this->allRelatedNodes) {
            $this->allRelatedNodes = $this->model
                ->where('menu_id', $menuId->id)
                ->select(['id', 'menu_id', 'parent_id', 'entity_id', 'type', 'url', 'title', 'icon_font', 'css_class', 'target'])
                ->orderBy('order', 'ASC')
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
