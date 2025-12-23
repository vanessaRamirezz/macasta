<?php
class UsuariosModel extends Query
{

    private $usuario, $codigo, $nombre, $clave, $nivelUsuario, $estado, $nombreCompleto, $tipoIdentificacion, $numeroIdentificacion;
    public function __construct()
    {
        parent::__construct();
    }

    public function getUsuario(string $usuario, ?string $clave, int $estado)
    {
        if ($clave !== null) {
            $sql = "SELECT * FROM usuarios WHERE nombreUsuario = ? AND claveUsuario = ? AND estado = ? ";
            $datos = array($usuario, $clave, $estado);
        } else {
            $sql = "SELECT * FROM usuarios WHERE nombreUsuario = ? AND estado = ? ";
            $datos = array($usuario, $estado);
        }

        $data = $this->select($sql, $datos);
        return $data;
    }



    public function getUsuarios($start, $length, $search)
    {
        $searchQuery = "";
        $params = [];
        if (!empty($search)) {
            $searchQuery = "WHERE codigoUsuario LIKE :search OR nombreUsuario LIKE :search";
            $params = [':search' => '%' . $search . '%'];
        }

        $sql = "SELECT codigoUsuario AS codigo,
                nombreUsuario AS nombre,
                nivelSeguridadUsuario AS nivel,
                estado 
                FROM usuarios
                $searchQuery
                ORDER BY fechaCreacion DESC
                LIMIT $start,$length";
        $data = $this->selectAll($sql, $params);

        $sqlTotal = "SELECT COUNT(*) AS total FROM usuarios $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;
        // Devuelve las agrupaciones y el total
        return [
            'usuarios' => $data,
            'total' => $total
        ];
    }

    public function registrarUsuario(string $codigo, string $nombre, string $clave, string $nivelUsuario, string $nombreCompleto, string $tipoIdentificacion, string $numeroIdentificacion)
    {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
        $this->clave = $clave;
        $this->nivelUsuario = $nivelUsuario;
        $this->nombreCompleto = $nombreCompleto;
        $this->tipoIdentificacion = $tipoIdentificacion;
        $this->numeroIdentificacion = $numeroIdentificacion;
        $verificar = "SELECT * FROM usuarios WHERE codigoUsuario = ?";
        $existe = $this->select($verificar, [$this->codigo]);
        if (empty($existe)) {
            $sql = "INSERT INTO usuarios(codigoUsuario, nombreUsuario, claveUsuario, nivelSeguridadUsuario,nombreCompleto,tipoIdentificacion,numeroIdentificacion) VALUES (?,?,?,?,?,?,?)";
            $datos = array($this->codigo, $this->nombre, $this->clave, $this->nivelUsuario, $this->nombreCompleto, $this->tipoIdentificacion, $this->numeroIdentificacion);
            $data = $this->guardar($sql, $datos);
            if ($data == 1) {
                $rest = "ok";
            } else {
                $rest = "error";
            }
        } else {
            $rest = "existe";
        }
        return $rest;
    }

    public function modificarUsuario(string $nombre, string $nivelUsuario, string $nombreCompleto, string $tipoIdentificacion, string $numeroIdentificacion, string $codigo)
    {
        $this->nombre = $nombre;
        $this->nivelUsuario = $nivelUsuario;
        $this->nombreCompleto = $nombreCompleto;
        $this->tipoIdentificacion = $tipoIdentificacion;
        $this->numeroIdentificacion = $numeroIdentificacion;
        $this->codigo = $codigo;
        $sql = "UPDATE usuarios SET nombreUsuario = ?, nivelSeguridadUsuario = ?, nombreCompleto = ?, tipoIdentificacion = ?, numeroIdentificacion = ?  WHERE codigoUsuario = ?";
        $datos = array($this->nombre, $this->nivelUsuario, $this->nombreCompleto, $this->tipoIdentificacion, $this->numeroIdentificacion, $this->codigo);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function editarUsuario(string $codigoUsuario)
    {
        $sql = "SELECT u.codigoUsuario AS codigo,
                        u.nombreUsuario AS nombre,
                        u.nivelSeguridadUsuario AS nivel,
                        u.nombreCompleto AS completo,
                        u.tipoIdentificacion AS tipo,
                        u.numeroIdentificacion AS numero,
                        d.valor AS nombreIdentificacion
                        FROM usuarios u
                        LEFT JOIN documentoIdentificacion d ON u.tipoIdentificacion = d.codigo
                        WHERE codigoUsuario = ?";
        $datos = array($codigoUsuario);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function accionUsuario(int $estadoUsuario, string $codigoUsuario)
    {
        $this->codigo = $codigoUsuario;
        $this->estado = $estadoUsuario;
        $sql  = "UPDATE usuarios SET estado = ? WHERE codigoUsuario = ?";
        $datos = array($this->estado, $this->codigo);
        $data = $this->guardar($sql, $datos);
        return $data;
    }
}