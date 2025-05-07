<?php

namespace Controllers;

use Classes\Correo;
use Model\Usuario;
use MVC\Router;

class AuthController {
    public static function login(Router $router) {
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarLogin();

            if (empty($alertas)) {
                // Verificar que el usuario existe.
                $usuario = Usuario::where('correo', $usuario->correo);

                if (!$usuario || !$usuario->confirmado) {
                    Usuario::setAlerta('error', '¡El usuario no existe o no está confirmado!');
                } else {
                    // Si el usuario existe.
                    if (password_verify($_POST['contrasena'], $usuario->contrasena)) {
                        // Iniciar la sesión.
                        session_start();    
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['apellido'] = $usuario->apellido;
                        $_SESSION['correo'] = $usuario->correo;
                        $_SESSION['rol'] = $usuario->rol ?? null;

                        // Redirección.
                        if ($usuario->admin) {
                            header('Location: /admin/dashboard');
                        } else {
                            header('Location: /finalizar-registro');
                        }
                    } else {
                        Usuario::setAlerta('error', '¡La contraseña ingresada es incorrecta!');
                    }
                }
            }
        }

        $alertas = Usuario::getAlertas();

        // Render a la vista.
        $router->render('auth/login', [
            'titulo'  => 'Iniciar sesión',
            'alertas'  => $alertas
        ]);
    }


    public static function logout() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();

            $_SESSION = [];
            header('Location: /');
        }
    }


    public static function registro(Router $router) {
        $alertas = [];
        $usuario = new Usuario;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $alertas->validarNuevaCuenta();

            if (empty($alertas)) {
                $existeUsuario = Usuario::where('correo', $usuario->correo);

                if ($existeUsuario) {
                    Usuario::setAlerta('error', '¡El usuario ya se encuentra registrado!');
                    $alertas = Usuario::getAlertas();
                } else {
                    // Hashear la contraseña.
                    $usuario->hashContrasena();

                    // Eliminar la contraseña2.
                    unset($usuario->contrasena2);

                    // Generar el token.
                    $usuario->crearToken();

                    // Crear el nuevo usuario.
                    $resultado = $usuario->guardar();

                    // Enviar el correo.
                    $correo = new Correo($usuario->nombre, $usuario->apellido, $usuario->correo, $usuario->token);
                    $correo->enviarConfirmacion();

                    if ($resultado) header('Location: /mensaje');
                }
            }
        }

        // Render a la vista.
        $router->render('auth/registro', [
            'titulo'  => 'Crear cuenta',
            'usuario' => $usuario,
            'alertas'  => $alertas
        ]);
    }


    public static function olvidar(Router $router) {
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarCorreo();

            if (empty($alertas)) {
                // Buscar el usuario.
                $usuario = Usuario::where('correo', $usuario->correo);

                if ($usuario && $usuario->confirmado) {
                    // Generar un nuevo token.
                    $usuario->crearToken();
                    unset($usuario->contrasena2);

                    // Actualizar el usuario.
                    $usuario->guardar();

                    // Enviar el correo.
                    $correo = new Correo($usuario->nombre, $usuario->apellido, $usuario->correo, $usuario->token);
                    $correo->enviarInstrucciones();

                    $alertas['exito'][] = '¡Se han enviado las instrucciones, por favor ponerse en contacto con un administrador (Iván)!';
                } else {
                    $alertas['error'][] = '¡El usuario no existe o no se encuentra confirmado!';
                }
            }
        }

        // Render a la vista.
        $router->render('auth/olvidar', [
            'titulo'  => 'Olvidar contraseña',
            'alertas'  => $alertas
        ]);
    }

    public static function restablecer(Router $router) {
        $alertas = [];
        $token = s($_GET['token']);
        $token_valido = true;

        if(!$token) header('Location: /');

        // Identificar el usuario con este token.
        $usuario = Usuario::where('token', $token);

        if (empty($usuario)) {
            Usuario::setAlerta('error', '¡Token no válido!');
            $token_valido = false;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Añadir la nueva contraseña.
            $usuario->sincronizar($_POST);

            // Validar la contraseña.
            $alertas = $usuario->validarContrasena();

            if (empty($alertas)) {
                // Hashear la nueva contraseña.
                $usuario->hashContrasena();

                // Eliminar el token.
                $usuario->token = null;

                // Guardar el usuario en la BD.
                $resultado = $usuario->guardar();

                // Redireccionar.
                if ($resultado) header('Location: /');
            }
        }

        $alertas = Usuario::getAlertas();

        // Render a la vista.
        $router->render('auth/restablecer', [
            'titulo'  => 'Restablecer contraseña',
            'alertas'  => $alertas,
            'token_valido' => $token_valido
        ]);
    }


    public static function mensaje(Router $router) {
        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta creada exitosamente'
        ]);
    }


    public static function confirmar() {
        $alertas = [];
        $token = s($_GET['token']);

        if(!$token) header('Location: /');

        // Encontrar al usuario con ese token.
        $usuario = Usuario::where('token', $token);

        if (empty($usuario)) {
            // Por si no se encontró un usuario con ese token.
            Usuario::setAlerta('error', '¡Token no válido, la cuenta no se pudo confirmar!');
        } else {
            // Confirmar la cuenta.
            $usuario->confirmado = 1;
            $usuario->token = '';
            unset($usuario->contrasena2);

            // Guardar en la BD.
            $usuario->guardar();

            Usuario::setAlerta('exito', '¡Cuenta comprobada correctamente!');
        }

        // Render a la vista.
        $router->render('auth/confirmar', [
            'titulo'  => 'Confirmacion de cuenta',
            'alertas'  => Usuario::getAlertas()
        ]);
    }
}
