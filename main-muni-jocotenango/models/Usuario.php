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

    public function __construct($args = [])
    {
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

    // LAS VALIDACIONES LAS TENGO EN LA CASA.

}
