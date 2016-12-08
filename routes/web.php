<?php use Illuminate\Routing\Router;

/**
 *
 * @var Router $router
 *
 */

$adminRoute = config('webed.admin_route');

$moduleRoute = 'menus';

/**
 * Admin routes
 */
$router->group(['prefix' => $adminRoute . '/' . $moduleRoute], function (Router $router) use ($adminRoute, $moduleRoute) {
    /**
     * Put some route here
     */
    $router->get('', 'MenuController@getIndex')
        ->name('admin::menus.index.get')
        ->middleware('has-permission:view-menus');

    $router->post('', 'MenuController@postListing')
        ->name('admin::menus.index.post')
        ->middleware('has-permission:view-menus');

    $router->post('update-status/{id}/{status}', 'MenuController@postUpdateStatus')
        ->name('admin::menus.update-status.post')
        ->middleware('has-permission:edit-menus');

    $router->get('create', 'MenuController@getCreate')
        ->name('admin::menus.create.get')
        ->middleware('has-permission:create-menus');

    $router->get('edit/{id}', 'MenuController@getEdit')
        ->name('admin::menus.edit.get')
        ->middleware('has-permission:edit-menus');

    $router->post('edit/{id}', 'MenuController@postEdit')
        ->name('admin::menus.edit.post')
        ->middleware('has-permission:edit-menus');

    $router->delete('/{id}', 'MenuController@deleteDelete')
        ->name('admin::menus.delete.delete')
        ->middleware('has-permission:delete-menus');
});
