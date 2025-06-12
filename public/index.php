<?php

use App\Core\Request;
use App\Core\Router;
use App\Controllers\ItemController;

require_once __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

$request = new Request();
$router = new Router($request);
$router->get('/', [ItemController::class, 'index']);
$router->get('/items', [ItemController::class, 'index']);

$router->post('/items/add', [ItemController::class, 'add']);
$router->post('/items/delete', [ItemController::class, 'delete']);
$router->post('/items/toggle', [ItemController::class, 'toggle']);
$router->get('/items/edit/{id}', [ItemController::class, 'edit']);
$router->post('/items/update/{id}', [ItemController::class, 'update']);
$router->resolve();
