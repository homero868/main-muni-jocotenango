<?php

namespace Model;

class ActiveRecord {
    // Base de datos.
    protected static $bd;
    protected static $tabla = '';
    protected static $columnasBD = [];

    // Alertas y mensajes.
    protected static $alertas = [];

    // Definir la conexión a la BD (includes/conexion.php).
    public static function setBD($basedatos) {
        self::$bd = $basedatos;
    }

    // Setear un tipo de alerta.
    public static function setAlerta($tipo, $mensaje) {
        static::$alertas[$tipo][] = $mensaje;
    }

    // Ordenar las alertas.
    public static function getAlertas() {
        return static::$alertas;
    }

    // Validación que se hereda de los modelos.
    public function validar() {
        static::$alertas = [];

        return static::$alertas;
    }

    // Consulta SQL para crear un objeto en memoria (Active Record).
    public static function consultarSQL($query) {
        // Consultar la Base de datos.
        $resultado = self::$bd->query($query);

        // Iterar los resultados.
        $array = [];

        while ($registro = $resultado->fetch_assoc()) {
            $array[] = static::crearObjeto($registro);
        }

        // Liberar la memoria.
        $resultado->free();

        // Retornar los resultados.
        return $array;
    }

    // Creación del objeto en memoria que es igual al de la BD.
    protected static function crearObjeto($registro) {
        $objeto = new static;

        foreach ($registro as $llave => $valor) {
            if (property_exists($objeto, $llave)) {
                $objeto->$llave = $valor;
            }
        }

        return $objeto;
    }

    // Identificar y unir los atributos de la BD.
    public function atributos() {
        $atributos = [];

        foreach (static::$columnasBD as $columna) {
            if ($columna === 'id') continue;
            $atributos[$columna] = $this->$columna;
        }

        return $atributos;
    }

    // Sanitizar los datos antes de guardarlos en la BD.
    public function sanitizarAtributos() {
        $atributos = $this->atributos();
        $sanitizado = [];
    
        foreach ($atributos as $llave => $valor) {
            // Solo aplicar trim a valores que son cadenas y no nulos.
            if (is_string($valor) && $valor !== null) {
                $valor = trim($valor);
            }

            $sanitizado[$llave] = self::$bd->escape_string($valor);
        }

        return $sanitizado;
    }

    // Sincroniza BD con objetos en memoria.
    public function sincronizar($args=[]) { 
        foreach($args as $llave => $valor) {
            if (property_exists($this, $llave) && !is_null($valor)) $this->$llave = $valor;
        }
    }

    // Registros - CRUD.
    public function guardar() {
        $resultado = '';

        if (!is_null($this->id)) {
            // Actualizar.
            $resultado = $this->actualizar();
        } else {
            // Creando un nuevo registro.
            $resultado = $this->crear();
        }

        return $resultado;
    }

    // Obtener todos los registros.
    public static function all() {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id DESC";
        $resultado = self::consultarSQL($query);

        return $resultado;
    }

    // Busca un registro por su ID.
    public static function find($id) {
        $query = "SELECT * FROM " . static::$tabla  ." WHERE id = ${id}";
        $resultado = self::consultarSQL($query);

        return array_shift($resultado) ;
    }

    // Obtener registros con cierta cantidad.
    public static function get($limite) {
        $query = "SELECT * FROM " . static::$tabla . " LIMIT ${limite} ORDER BY id DESC" ;
        $resultado = self::consultarSQL($query);

        return array_shift($resultado) ;
    }

    // Busqueda where con columna.
    public static function where($columna, $valor) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE ${columna} = '${valor}'";
        $resultado = self::consultarSQL($query);

        return array_shift($resultado) ;
    }

    // Crear un nuevo registro.
    public function crear() {
        // Sanitizar los datos.
        $atributos = $this->sanitizarAtributos();

        // Insertar en la Base de datos.
        $query = " INSERT INTO " . static::$tabla . " ( ";
        $query .= join(', ', array_keys($atributos));
        $query .= " ) VALUES (' "; 
        $query .= join("', '", array_values($atributos));
        $query .= " ') ";

        // Resultado de la consulta.
        $resultado = self::$bd->query($query);

        return [
            'resultado' =>  $resultado,
            'id' => self::$bd->insert_id
        ];
    }

    // Actualizar el registro.
    public function actualizar() {
        // Sanitizar los datos.
        $atributos = $this->sanitizarAtributos();

        // Iterar para ir agregando cada campo de la BD.
        $valores = [];

        foreach ($atributos as $llave => $valor) {
            $valores[] = "{$llave}='{$valor}'";
        }

        // Consulta SQL.
        $query = "UPDATE " . static::$tabla ." SET ";
        $query .=  join(', ', $valores );
        $query .= " WHERE id = '" . self::$bd->escape_string($this->id) . "' ";
        $query .= " LIMIT 1 "; 

        // Actualizar BD.
        $resultado = self::$bd->query($query);

        return $resultado;
    }

    // Eliminar un registro por su ID.
    public function eliminar() {
        $query = "DELETE FROM "  . static::$tabla . " WHERE id = " . self::$bd->escape_string($this->id) . " LIMIT 1";
        $resultado = self::$bd->query($query);

        return $resultado;
    }
}
