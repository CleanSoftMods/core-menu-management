<?php namespace WebEd\Base\Menu\Repositories;

use WebEd\Base\Core\Models\Contracts\BaseModelContract;
use WebEd\Base\Core\Repositories\AbstractBaseRepository;
use WebEd\Base\Caching\Services\Contracts\CacheableContract;

use WebEd\Base\Menu\Models\Contracts\MenuModelContract;
use WebEd\Base\Menu\Repositories\Contracts\MenuNodeRepositoryContract;
use WebEd\Base\Menu\Repositories\Contracts\MenuRepositoryContract;

class MenuRepository extends AbstractBaseRepository implements MenuRepositoryContract, CacheableContract
{
    protected $rules = [
        'title' => 'string|max:255|required',
        'slug' => 'string|max:255|alpha_dash|required',
        'status' => 'string|required|in:activated,disabled',
        'created_by' => 'integer|min:0|nullable',
        'updated_by' => 'integer|min:0|nullable',
    ];

    protected $editableFields = [
        'title',
        'slug',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * @var MenuNodeRepository|MenuNodeRepositoryCacheDecorator
     */
    protected $menuNodeRepository;

    public function __construct(BaseModelContract $model)
    {
        parent::__construct($model);

        $this->menuNodeRepository = app(MenuNodeRepositoryContract::class);
    }

    /**
     * Create menu
     * @param $data
     * @return array
     */
    public function createMenu($data)
    {
        return $this->updateMenu(0, $data, true, false);
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
        $menuStructure = array_get($data, 'menu_structure');
        $deletedNodes = json_decode(array_get($data, 'deleted_nodes', '[]'));
        array_forget($data, ['menu_structure', 'deleted_nodes']);

        if($deletedNodes) {
            $this->menuNodeRepository->delete($deletedNodes);
        }

        $result = $this->editWithValidate($id, $data, $allowCreateNew, $justUpdateSomeFields);

        if ($result['error'] || !$menuStructure) {
            return $result;
        }

        $this->updateMenuStructure($result['data']->id, $menuStructure);

        return $result;
    }

    /**
     * Update menu structure
     * @param $menuId
     * @param $menuStructure
     */
    public function updateMenuStructure($menuId, $menuStructure)
    {
        if (!is_array($menuStructure)) {
            $menuStructure = json_decode($menuStructure, true);
        }

        foreach ($menuStructure as $order => $node) {
            $this->menuNodeRepository->updateMenuNode($menuId, $node, $order);
        }
    }

    /**
     * Get menu
     * @param $id
     * @return mixed|null|MenuModelContract
     */
    public function getMenu($id)
    {
        if($id instanceof MenuModelContract) {
            $menu = $id;
        } else {
            $menu = $this->find($id);
        }
        if(!$menu) {
            return null;
        }

        $menu->all_menu_nodes = $this->getMenuNodes($menu);

        return $menu;
    }

    /**
     * Get menu nodes
     * @param $menuId
     * @param null $parentId
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

        $nodes = $this->menuNodeRepository
            ->orderBy('sort_order', 'ASC')
            ->where('menu_id', '=', $menuId->id)
            ->where('parent_id', '=', $parentId)
            ->select('id', 'menu_id', 'parent_id', 'related_id', 'type', 'url', 'title', 'icon_font', 'css_class', 'target')
            ->get();

        foreach ($nodes as &$node) {
            $node->model_title = $node->title;
            $node->children = $this->getMenuNodes($menuId, $node->id);
        }

        return $nodes;
    }
}
