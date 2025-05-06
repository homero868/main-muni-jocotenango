<?php

require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\AuthController;

$router = new Router();

// Login
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// Creación de cuenta.
$router->get('/registro', [AuthController::class, 'registro']);
$router->post('/registro', [AuthController::class, 'registro']);

// Formulario de olvido de contraseña.
$router->get('/olvidar', [AuthController::class, 'olvidar']);
$router->post('/olvidar', [AuthController::class, 'olvidar']);

// Colocar la nueva contraseña.
$router->get('/restablecer', [AuthController::class, 'restablecer']);
$router->post('/restablecer', [AuthController::class, 'restablecer']);

// Confirmación de cuenta.
$router->get('/mensaje', [AuthController::class, 'mensaje']);
$router->get('/confirmar', [AuthController::class, 'confirmar']);

$router->comprobarRutas();
