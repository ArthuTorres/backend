<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EnderecoController;
use App\Http\Controllers\PrestadorServicoController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('ping', function () {
    return response("pong");
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});


Route::group([
    'middleware' => 'api',
    'prefix' => 'enderecos'
], function ($router) {
    Route::get('busca-cep/{cep}', [EnderecoController::class, 'buscarCep']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'usuarios'
], function ($router) {
    Route::post('signup', [UsuarioController::class, 'signup']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'clientes'
], function ($router) {
    Route::post('signup', [ClienteController::class, 'signup']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'prestadores-servico'
], function ($router) {
    Route::post('signup', [PrestadorServicoController::class, 'signup']);
});

Route::group(['middleware' => "api"], function ($router) {
    $generic_controller_routes = [
        "usuarios" => UsuarioController::class,
        "clientes" => ClienteController::class,
        "prestadores-servico" => PrestadorServicoController::class,
        "enderecos" => EnderecoController::class,
    ];

    foreach ($generic_controller_routes as $prefix => $controller) {
        Route::get($prefix . '/', [$controller, 'index']);
        Route::get($prefix . '/lookup', [$controller, 'lookup']);
        Route::get($prefix . '/{id}', [$controller, 'show']);
        Route::post($prefix . '/', [$controller, 'store']);
        Route::post($prefix . '/bulk', [$controller, 'bulk']);
        Route::post($prefix . '/{id}', [$controller, 'update']);
        Route::delete($prefix . '/{id}', [$controller, 'destroy']);
    }
});
