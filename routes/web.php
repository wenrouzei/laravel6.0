<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Route::get('/permission', function () {
//    $role = \Spatie\Permission\Models\Role::create(['name' => 'writer']);
//    $permission = \Spatie\Permission\Models\Permission::create(['name' => 'edit articles']);

    $role = Spatie\Permission\Models\Role::first();
    $permission = \Spatie\Permission\Models\Permission::first();
    $role->givePermissionTo($permission);
    $user = \App\User::first();
    $user->assignRole($role);
    $user->givePermissionTo($permission);
    dump($user->getDirectPermissions(), $user->getAllPermissions());
})->middleware('permission:edit articles');


Route::view('ws', 'ws');
