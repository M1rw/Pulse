<?php
/**
 * API Routes.
 * 
 * Separate from web routes. Every response is JSON.
 * No HTML leaking into the API, ever.
 */

use App\Core\Router;

/** @var Router $router */

$router->get('/api/stats',    [App\Http\Controllers\DashboardController::class, 'apiStats']);
$router->get('/api/activity', [App\Http\Controllers\DashboardController::class, 'apiActivity']);
$router->get('/api/search',   [App\Http\Controllers\DashboardController::class, 'apiSearch']);

// admin API
$router->post('/api/admin/toggle-featured', [App\Http\Controllers\AdminController::class, 'toggleFeatured']);
$router->get('/api/admin/activity-stats',   [App\Http\Controllers\AdminController::class, 'apiActivityStats']);
