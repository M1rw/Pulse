<?php
/**
 * Web Routes.
 * 
 * Clean, explicit mappings for all web pages and admin panel.
 */

use App\Core\Router;

/** @var Router $router */

// ── public pages ─────────────────────────────────────────────
$router->get('/',               [App\Http\Controllers\DashboardController::class, 'index'])->name('home');
$router->get('/projects',        [App\Http\Controllers\DashboardController::class, 'projects'])->name('projects');
$router->get('/projects/{slug}', [App\Http\Controllers\DashboardController::class, 'showProject'])->name('project.show');
$router->get('/about',           [App\Http\Controllers\DashboardController::class, 'about'])->name('about');
$router->get('/contact',         [App\Http\Controllers\DashboardController::class, 'contact'])->name('contact');
$router->post('/contact',        [App\Http\Controllers\DashboardController::class, 'submitContact']);

// ── admin area ───────────────────────────────────────────────
$router->group(['prefix' => 'admin'], function (Router $r) {
    $r->get('/',                     [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin');
    $r->get('/projects',             [App\Http\Controllers\AdminController::class, 'projects'])->name('admin.projects');
    $r->get('/projects/new',         [App\Http\Controllers\AdminController::class, 'createProject'])->name('admin.projects.create');
    $r->post('/projects',            [App\Http\Controllers\AdminController::class, 'storeProject']);
    $r->get('/projects/{id}/edit',   [App\Http\Controllers\AdminController::class, 'editProject'])->name('admin.projects.edit');
    $r->post('/projects/{id}/update',[App\Http\Controllers\AdminController::class, 'updateProject']);
    $r->post('/projects/{id}/delete',[App\Http\Controllers\AdminController::class, 'deleteProject']);
    $r->get('/messages',             [App\Http\Controllers\AdminController::class, 'messages'])->name('admin.messages');
    $r->post('/messages/{id}/read',  [App\Http\Controllers\AdminController::class, 'markMessageRead']);
    $r->post('/messages/{id}/unread',[App\Http\Controllers\AdminController::class, 'markMessageUnread']);
    $r->post('/messages/{id}/delete',[App\Http\Controllers\AdminController::class, 'deleteMessage']);
});
