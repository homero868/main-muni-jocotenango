<?php

namespace Model;

class Usuario extends ActiveRecord {
    protected static $tabla = 'usuarios';
    protected static $columnasBD = ['id', 'nombre', 'apellido', 'correo', 'contrasena', 'confirmado', 'token', 'rol'];

    public $id;
    public $nombre;
    public $apellido;
    public $correo;
    public $contrasena;
    public $contrasena2;
    public $confirmado;
    public $token;
    public $rol;

    public $contrasena_actual;
    public $contrasena_nuevo;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido = $args['apellido'] ?? '';
        $this->correo = $args['correo'] ?? '';
        $this->contrasena = $args['contrasena'] ?? '';
        $this->contrasena2 = $args['contrasena2'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
        $this->token = $args['token'] ?? '';
        $this->rol = $args['rol'] ?? '';
    }

    // Validar el login de Usuarios.
    public function validarLogin() {
        if ((!$this->correo))  {
            self::$alertas['error'][] = '¡El correo electrónico del usuario es obligatorio!';
        } else if (!filter_var($this->correo, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = '¡Formato de correo electrónico no válido!';
        }

        if (!$this->contrasena) {
            self::$alertas['error'][] = '¡La contraseña del usuario es obligatoria!';
        } else if (strlen($this->contrasena) < 8) {
            self::$alertas['error'][] = '¡La contraseña debe contener como mínimo 8 caracteres!';
        }

        return self::$alertas;
    }

    // Validación para cuentas nuevas.
    public function validarNuevaCuenta() {
        if (!$this->nombre) {
            self::$alertas['error'][] = '¡El nombre del usuario es obligatorio!';
        } else if (!preg_match('/[a-zA-ZÑñáéíóúÁÉÍÓÚ]{3,60}/', $this->nombre)) {
            self::$alertas['error'][] = '¡Formato no válido para el nombre!';
        }

        if (!$this->apellido) {
            self::$alertas['error'][] = '¡El apellido del usuario es obligatorio!';
        } else if (!preg_match('/[a-zA-ZÑñáéíóúÁÉÍÓÚ]{3,60}/', $this->apellido)) {
            self::$alertas['error'][] = '¡Formato no válido para el apellido!';
        }

        if ((!$this->correo))  {
            self::$alertas['error'][] = '¡El correo electrónico del usuario es obligatorio!';
        } else if (!filter_var($this->correo, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = '¡Formato de correo electrónico no válido!';
        }

        if (!$this->contrasena) {
            self::$alertas['error'][] = '¡La contraseña del usuario es obligatoria!';
        } else if (strlen($this->contrasena) < 8) {
            self::$alertas['error'][] = '¡La contraseña debe contener como mínimo 8 caracteres!';
        }
        
        if ($this->contrasena !== $this->contrasena2) {
            self::$alertas['error'][] = '!Las contraseñas no coinciden!';
        }

        return self::$alertas;
    }

    // Valida un correo.
    public function validarCorreo() {
        if ((!$this->correo))  {
            self::$alertas['error'][] = '¡El correo electrónico del usuario es obligatorio!';
        } else if (!filter_var($this->correo, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = '¡Formato de correo electrónico no válido!';
        }

        return self::$alertas;
    }

    public function validarContrasena() {
        if (!$this->contrasena) {
            self::$alertas['error'][] = '¡La contraseña del usuario es obligatoria!';
        } else if (strlen($this->contrasena) < 8) {
            self::$alertas['error'][] = '¡La contraseña debe contener como mínimo 8 caracteres!';
        }

        return self::$alertas;
    }

    public function nuevaContrasena() : array {
        if (!$this->contrasena_actual) {
            self::$alertas['error'][] = '¡La contraseña del usuario es obligatoria!';
        }
        
        if (!$this->contrasena_nueva) {
            self::$alertas['error'][] = '¡La nueva contraseña del usuario es obligatoria!';
        } else if (strlen($this->contrasena_nueva) < 8) {
            self::$alertas['error'][] = '¡La nueva contraseña debe contener como mínimo 8 caracteres!';
        }

        return self::$alertas;
    }

    // Comprobar que la contraseña ingresada es la correcta.
    public function comprobarContrasena() : bool {
        return password_verify($this->contrasena_actual, $this->contrasena);
    }

    // Hashea la contraseña.
    public function hashContrasena() : void {
        $this->contrasena = password_hash($this->contrasena, PASSWORD_BCRYPT);
    }

    // Generar un Token
    public function crearToken() : void {
        // Para generar un token más grande: $this->token = md5(uniqid());
        $this->token = uniqid();
    }
}
