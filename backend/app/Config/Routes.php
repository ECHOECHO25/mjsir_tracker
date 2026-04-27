<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    $routes->resource('manuscripts');
    $routes->post('manuscripts/(:segment)', 'Manuscripts::updatePost/$1');
    $routes->post('auth/login', 'Auth::login');
    $routes->resource('users');
    
    $routes->get('notifications', 'Notifications::getByUser');
    $routes->post('notifications/(:segment)/read', 'Notifications::markAsRead/$1');
    $routes->post('notifications', 'Notifications::createNotification');
    
    // CORS Preflight
    $routes->options('(:any)', function() {
        return response()->setStatusCode(200);
    });
});
