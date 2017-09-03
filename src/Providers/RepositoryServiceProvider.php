<?php namespace CleanSoft\Modules\Core\Menu\Providers;

use Illuminate\Support\ServiceProvider;
use CleanSoft\Modules\Core\Menu\Models\Menu;
use CleanSoft\Modules\Core\Menu\Models\MenuNode;
use CleanSoft\Modules\Core\Menu\Repositories\Contracts\MenuNodeRepositoryContract;
use CleanSoft\Modules\Core\Menu\Repositories\Contracts\MenuRepositoryContract;
use CleanSoft\Modules\Core\Menu\Repositories\MenuNodeRepository;
use CleanSoft\Modules\Core\Menu\Repositories\MenuNodeRepositoryCacheDecorator;
use CleanSoft\Modules\Core\Menu\Repositories\MenuRepository;
use CleanSoft\Modules\Core\Menu\Repositories\MenuRepositoryCacheDecorator;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(MenuRepositoryContract::class, function () {
            $repository = new MenuRepository(new Menu());

            if (config('webed-caching.repository.enabled')) {
                return new MenuRepositoryCacheDecorator($repository);
            }

            return $repository;
        });
        $this->app->bind(MenuNodeRepositoryContract::class, function () {
            $repository = new MenuNodeRepository(new MenuNode());

            if (config('webed-caching.repository.enabled')) {
                return new MenuNodeRepositoryCacheDecorator($repository);
            }

            return $repository;
        });
    }
}
