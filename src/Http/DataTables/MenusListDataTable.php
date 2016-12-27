<?php namespace WebEd\Base\Menu\Http\DataTables;

use WebEd\Base\Core\Http\DataTables\AbstractDataTables;
use WebEd\Base\Menu\Repositories\Contracts\MenuRepositoryContract;
use WebEd\Base\Menu\Repositories\MenuRepository;

class MenusListDataTable extends AbstractDataTables
{
    protected $repository;

    /**
     * MenusListDataTable constructor.
     * @param MenuRepository $repository
     */
    public function __construct(MenuRepositoryContract $repository)
    {
        $this->repository = $repository;

        $this->repository->select('id', 'created_at', 'title', 'slug', 'status');

        parent::__construct();
    }

    /**
     * @return string
     */
    public function run()
    {
        $this->setAjaxUrl(route('admin::menus.index.post'), 'POST');

        $this
            ->addHeading('title', 'Title', '25%')
            ->addHeading('slug', 'Alias', '25%')
            ->addHeading('status', 'Status', '15%')
            ->addHeading('created_at', 'Created at', '15%')
            ->addHeading('actions', 'Actions', '20%');

        $this->setColumns([
            ['data' => 'title', 'name' => 'title'],
            ['data' => 'slug', 'name' => 'alias'],
            ['data' => 'status', 'name' => 'status'],
            ['data' => 'created_at', 'name' => 'created_at', 'searchable' => false],
            ['data' => 'actions', 'name' => 'actions', 'searchable' => false, 'orderable' => false],
        ]);

        return $this->view();
    }

    /**
     * @return $this
     */
    protected function fetch()
    {
        $this->fetch = datatable()->of($this->repository)
            ->editColumn('status', function ($item) {
                return \Html::label($item->status, $item->status);
            })
            ->addColumn('actions', function ($item) {
                /*Edit link*/
                $activeLink = route('admin::menus.update-status.post', ['id' => $item->id, 'status' => 'activated']);
                $disableLink = route('admin::menus.update-status.post', ['id' => $item->id, 'status' => 'disabled']);
                $deleteLink = route('admin::menus.delete.delete', ['id' => $item->id]);

                /*Buttons*/
                $editBtn = link_to(route('admin::menus.edit.get', ['id' => $item->id]), 'Edit', ['class' => 'btn btn-sm btn-outline green']);

                $activeBtn = ($item->status != 'activated') ? \Form::button('Active', [
                    'title' => 'Active this item',
                    'data-ajax' => $activeLink,
                    'data-method' => 'POST',
                    'data-toggle' => 'confirmation',
                    'class' => 'btn btn-outline blue btn-sm ajax-link',
                    'type' => 'button',
                ]) : '';

                $disableBtn = ($item->status != 'disabled') ? \Form::button('Disable', [
                    'title' => 'Disable this item',
                    'data-ajax' => $disableLink,
                    'data-method' => 'POST',
                    'data-toggle' => 'confirmation',
                    'class' => 'btn btn-outline yellow-lemon btn-sm ajax-link',
                    'type' => 'button',
                ]) : '';

                $deleteBtn = \Form::button('Delete', [
                    'title' => 'Delete this item',
                    'data-ajax' => $deleteLink,
                    'data-method' => 'DELETE',
                    'data-toggle' => 'confirmation',
                    'class' => 'btn btn-outline red-sunglo btn-sm ajax-link',
                    'type' => 'button',
                ]);

                return $editBtn . $activeBtn . $disableBtn . $deleteBtn;
            });

        return $this;
    }
}
