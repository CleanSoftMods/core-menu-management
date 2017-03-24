<?php namespace WebEd\Base\Menu\Providers;

use Illuminate\Support\ServiceProvider;
use WebEd\Base\Menu\Repositories\Contracts\MenuRepositoryContract;
use WebEd\Base\Menu\Repositories\MenuRepository;

class BootstrapModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        app()->booted(function () {
            $this->booted();
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }

    private function booted()
    {
        /**
         * Register to dashboard menu
         */
        \DashboardMenu::registerItem([
            'id' => 'webed-menus',
            'priority' => 20,
            'parent_id' => null,
            'heading' => null,
            'title' => trans('webed-menus::base.menus'),
            'font_icon' => 'fa fa-bars',
            'link' => route('admin::menus.index.get'),
            'css_class' => null,
            'permissions' => ['view-menus']
        ]);

        cms_settings()
            ->addSettingField('main_menu', [
                'group' => 'basic',
                'type' => 'select',
                'priority' => 3,
                'label' => trans('webed-menus::base.settings.main_menu.label'),
                'helper' => trans('webed-menus::base.settings.main_menu.helper'),
            ], function () {
                /**
                 * @var MenuRepository $menus
                 */
                $menus = app(MenuRepositoryContract::class);
                $menus = $menus->getWhere(['status' => 'activated']);

                $menusArr = [];

                foreach ($menus as $menu) {
                    $menusArr[$menu->slug] = $menu->title;
                }

                return [
                    'main_menu',
                    $menusArr,
                    get_settings('main_menu'),
                    ['class' => 'form-control']
                ];
            });
    }
}
