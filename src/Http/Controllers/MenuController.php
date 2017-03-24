<?php namespace WebEd\Base\Menu\Http\Controllers;

use WebEd\Base\Http\Controllers\BaseAdminController;
use WebEd\Base\Http\Requests\Request;
use WebEd\Base\Menu\Http\DataTables\MenusListDataTable;
use WebEd\Base\Menu\Http\Requests\CreateMenuRequest;
use WebEd\Base\Menu\Http\Requests\UpdateMenuRequest;
use WebEd\Base\Menu\Repositories\Contracts\MenuRepositoryContract;
use WebEd\Base\Menu\Repositories\MenuRepository;

class MenuController extends BaseAdminController
{
    protected $module = 'webed-menus';

    /**
     * @param MenuRepository $repository
     */
    public function __construct(MenuRepositoryContract $repository)
    {
        parent::__construct();

        $this->repository = $repository;

        $this->getDashboardMenu($this->module);

        $this->breadcrumbs->addLink(trans('webed-menus::base.menus'), 'admin::menus.index.get');
    }

    public function getIndex(MenusListDataTable $menusListDataTable)
    {
        $this->setPageTitle(trans('webed-menus::base.menus_management'));

        $this->dis['dataTable'] = $menusListDataTable->run();

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_MENUS, 'index.get')->viewAdmin('list');
    }

    public function postListing(MenusListDataTable $menusListDataTable)
    {
        return do_filter(BASE_FILTER_CONTROLLER, $menusListDataTable, WEBED_MENUS, 'index.post', $this);
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
            ->addStylesheetsDirectly('admin/modules/menu/menu-nestable.css')
            ->addJavascripts('jquery-nestable')
            ->addJavascriptsDirectly('admin/modules/menu/edit-menu.js');

        $this->setPageTitle(trans('webed-menus::base.create_menu'));
        $this->breadcrumbs->addLink(trans('webed-menus::base.create_menu'));

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_MENUS, 'create.get')->viewAdmin('create');
    }

    public function postCreate(CreateMenuRequest $request)
    {
        $data = $this->parseData($request);
        $data['created_by'] = $this->loggedInUser->id;

        $result = $this->repository->createMenu($data);

        $msgType = $result['error'] ? 'danger' : 'success';

        flash_messages()
            ->addMessages($result['messages'], $msgType)
            ->showMessagesOnSession();

        if ($result['error']) {
            return redirect()->back()->withInput();
        }

        do_action(BASE_ACTION_AFTER_CREATE, WEBED_MENUS, $result);

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
        $item = $this->repository->getMenu($id);
        if (!$item) {
            flash_messages()
                ->addMessages(trans('webed-menus::base.menu_not_exists'), 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $item = do_filter(BASE_FILTER_BEFORE_UPDATE, $item, WEBED_MENUS);

        $this->assets
            ->addStylesheets('jquery-nestable')
            ->addStylesheetsDirectly('admin/modules/menu/menu-nestable.css')
            ->addJavascripts('jquery-nestable')
            ->addJavascriptsDirectly('admin/modules/menu/edit-menu.js');

        $this->setPageTitle(trans('webed-menus::base.edit_menu'), $item->title);
        $this->breadcrumbs->addLink(trans('webed-menus::base.edit_menu'));

        $this->dis['menuStructure'] = json_encode($item->all_menu_nodes);

        $this->dis['object'] = $item;

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_MENUS, 'edit.get', $id)->viewAdmin('edit');
    }

    public function postEdit(UpdateMenuRequest $request, $id)
    {
        $item = $this->repository->find($id);
        if (!$item) {
            flash_messages()
                ->addMessages(trans('webed-menus::base.menu_not_exists'), 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $item = do_filter(BASE_FILTER_BEFORE_UPDATE, $item, WEBED_MENUS);

        $data = $this->parseData($request);

        $result = $this->repository->updateMenu($item, $data);

        $msgType = $result['error'] ? 'danger' : 'success';

        flash_messages()
            ->addMessages($result['messages'], $msgType)
            ->showMessagesOnSession();

        if ($result['error']) {
            return redirect()->back();
        }

        do_action(BASE_ACTION_AFTER_UPDATE, WEBED_MENUS, $result);

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
        $id = do_filter(BASE_FILTER_BEFORE_DELETE, $id, WEBED_MENUS);

        $result = $this->repository->delete($id);

        do_action(BASE_ACTION_AFTER_DELETE, WEBED_MENUS, $id, $result);

        return response()->json($result, $result['response_code']);
    }

    protected function parseData(Request $request)
    {
        return [
            'menu_structure' => $request->get('menu_structure'),
            'deleted_nodes' => $request->get('deleted_nodes'),
            'status' => $request->get('status'),
            'title' => $request->get('title'),
            'slug' => ($request->get('slug') ? str_slug($request->get('slug')) : str_slug($request->get('title'))),
            'updated_by' => $this->loggedInUser->id,
        ];
    }
}
