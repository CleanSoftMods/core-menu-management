<?php namespace WebEd\Base\Menu\Http\Controllers;

use WebEd\Base\Core\Http\Controllers\BaseAdminController;
use WebEd\Base\Menu\Http\DataTables\MenusListDataTable;
use WebEd\Base\Menu\Http\Requests\CreateMenuRequest;
use WebEd\Base\Menu\Http\Requests\UpdateMenuRequest;
use WebEd\Base\Menu\Repositories\Contracts\MenuRepositoryContract;
use WebEd\Base\Menu\Repositories\MenuRepository;

class MenuController extends BaseAdminController
{
    protected $module = 'webed-menu';

    /**
     * @param MenuRepository $repository
     */
    public function __construct(MenuRepositoryContract $repository)
    {
        parent::__construct();

        $this->repository = $repository;

        $this->getDashboardMenu($this->module);

        $this->breadcrumbs->addLink('Menus', 'admin::menus.index.get');
    }

    public function getIndex(MenusListDataTable $menusListDataTable)
    {
        $this->setPageTitle('Menus management');

        $this->dis['dataTable'] = $menusListDataTable->run();

        return do_filter('menus.index.get', $this)->viewAdmin('list');
    }

    public function postListing(MenusListDataTable $menusListDataTable)
    {
        return do_filter('datatables.menu.index.post', $menusListDataTable, $this);
    }

    /**
     * Update page status
     * @param $id
     * @param $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function postUpdateStatus($id, $status)
    {
        $data = [
            'status' => $status
        ];
        $result = $this->repository->editWithValidate($id, $data, false, true);

        return response()->json($result, $result['response_code']);
    }

    /**
     * Go to create menu page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreate()
    {
        $this->assets
            ->addStylesheets('jquery-nestable')
            ->addStylesheetsDirectly(asset('admin/modules/menu/menu-nestable.css'))
            ->addJavascripts('jquery-nestable')
            ->addJavascriptsDirectly(asset('admin/modules/menu/edit-menu.js'));

        $this->setPageTitle('Create menu');
        $this->breadcrumbs->addLink('Create menu');

        $this->dis['currentId'] = 0;

        $this->dis['object'] = $this->repository->getModel();
        $oldInputs = old();
        if ($oldInputs) {
            foreach ($oldInputs as $key => $row) {
                if($key === 'menu_structure') {
                    $this->dis['menuStructure'] = $row;
                } else {
                    $this->dis['object']->$key = $row;
                }
            }
        }

        return do_filter('menus.create.get', $this)->viewAdmin('create');
    }

    public function postCreate(CreateMenuRequest $request)
    {
        $data = [
            'menu_structure' => $request->get('menu_structure'),
            'deleted_nodes' => $request->get('deleted_nodes'),
            'status' => $request->get('status'),
            'title' => $request->get('title'),
            'slug' => ($request->get('slug') ? str_slug($request->get('slug')) : str_slug($request->get('title'))),
            'updated_by' => $this->loggedInUser->id,
            'created_by' => $this->loggedInUser->id,
        ];

        $result = $this->repository->createMenu($data);

        $msgType = $result['error'] ? 'danger' : 'success';

        $this->flashMessagesHelper
            ->addMessages($result['messages'], $msgType)
            ->showMessagesOnSession();

        if($result['error']) {
            return redirect()->back()->withInput();
        }

        do_action('menus.after-create.post', $result['data']->id, $result, $this);

        if ($request->has('_continue_edit')) {
            return redirect()->to(route('admin::menus.edit.get', ['id' => $result['data']->id]));
        }

        return redirect()->to(route('admin::menus.index.get'));
    }

    /**
     * Go to edit menu page
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function getEdit($id)
    {
        $id = do_filter('menus.before-edit.get', $id);

        $item = $this->repository->getMenu($id);
        if (!$item) {
            $this->flashMessagesHelper
                ->addMessages('This menu not exists', 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $this->assets
            ->addStylesheets('jquery-nestable')
            ->addStylesheetsDirectly(asset('admin/modules/menu/menu-nestable.css'))
            ->addJavascripts('jquery-nestable')
            ->addJavascriptsDirectly(asset('admin/modules/menu/edit-menu.js'));

        $this->setPageTitle('Edit menu', $item->title);
        $this->breadcrumbs->addLink('Edit menu');

        $this->dis['menuStructure'] = json_encode($item->all_menu_nodes);

        $this->dis['object'] = $item;
        $this->dis['currentId'] = $id;

        return do_filter('menus.edit.get', $this, $id)->viewAdmin('edit');
    }

    public function postEdit(UpdateMenuRequest $request, $id)
    {
        $id = do_filter('menus.before-edit.post', $id);

        $item = $this->repository->getMenu($id);
        if (!$item) {
            $this->flashMessagesHelper
                ->addMessages('This menu not exists', 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $data = [
            'menu_structure' => $request->get('menu_structure'),
            'deleted_nodes' => $request->get('deleted_nodes'),
            'status' => $request->get('status'),
            'title' => $request->get('title'),
            'slug' => ($request->get('slug') ? str_slug($request->get('slug')) : str_slug($request->get('title'))),
            'updated_by' => $this->loggedInUser->id,
        ];

        $result = $this->repository->updateMenu($id, $data);

        $msgType = $result['error'] ? 'danger' : 'success';

        $this->flashMessagesHelper
            ->addMessages($result['messages'], $msgType)
            ->showMessagesOnSession();

        if($result['error']) {
            return redirect()->back();
        }

        do_action('menus.after-edit.post', $id, $result, $this);

        if ($request->has('_continue_edit')) {
            return redirect()->back();
        }

        return redirect()->to(route('admin::menus.index.get'));
    }

    /**
     * Delete menu
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDelete($id)
    {
        $id = do_filter('menus.before-delete.delete', $id);

        $result = $this->repository->delete($id);

        do_action('menus.after-delete.delete', $id, $result);

        return response()->json($result, $result['response_code']);
    }
}
