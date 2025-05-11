<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Authetication
$routes->group('/', function($routes) {
    $routes->get('', 'AuthController::index');
    $routes->get('login', 'AuthController::index');
    $routes->post('login/process', 'AuthController::process');
    $routes->get('logout', 'AuthController::logout');
    $routes->post('forgot-password', 'AuthController::forgotPassword');
});

$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->group('dashboard', function($routes) {
        $routes->get('operator', 'DashboardController::operator', ['filter' => 'role:Operator']);
        $routes->get('admin', 'DashboardController::admin', ['filter' => 'role:Admin']);
        $routes->get('super-admin', 'DashboardController::superAdmin', ['filter' => 'role:Super Admin']);
        $routes->get('detail/(:num)', 'DashboardController::detail/$1');
    });

    $routes->group('usulan', function($routes) {
        $routes->get('', 'UsulanController::index');
        $routes->post('tambah', 'UsulanController::tambah');
    });    

    

    $routes->group('manajemen', function($routes) {
        $routes->get('', 'ManajemenController::index');
        $routes->get('edit/(:num)', 'ManajemenController::edit/$1');
        $routes->post('update/(:num)', 'ManajemenController::update/$1'); 
        $routes->post('delete/(:num)', 'ManajemenController::delete/$1');
        $routes->post('tambah', 'ManajemenController::tambah');
        $routes->get('detail/(:num)', 'ManajemenController::detail/$1');
        $routes->get('cetak/pdf', 'ManajemenController::cetakPdf');
        $routes->get('cetak/excel', 'ManajemenController::cetakExcel');
        $routes->get('cetak/csv', 'ManajemenController::cetakCsv');
    });
    $routes->group('disposisi', function($routes) {
        $routes->get('(:num)', 'DisposisiController::index/$1');
        $routes->post('tambah', 'DisposisiController::tambah');
    });
    
    $routes->group('rekomendasi', function($routes) {
        $routes->get('(:num)', 'RekomendasiController::index/$1');
        $routes->post('tambah', 'RekomendasiController::tambah');
    });
});

