<?php
class EstadosProyectosModel extends Query
{
    private $codigoEstado, $nombre, $codigoUsuario;
    public function __construct()
    {
        parent::__construct();
    }

    public function getEstadoProyectos($start, $length, $search = "")
    {
        $searchQuery = "";
        $params = [];
        if (!empty($search)) {
            $searchQuery = "WHERE codigoEstadoProyecto LIKE :search OR nombreEstadoProyecto LIKE :search";
            $params = [':search' => '%' . $search . '%'];
        }

        $sql = "SELECT codigoEstadoProyecto AS codigo, nombreEstadoProyecto AS estado
                FROM estadoProyecto
                $searchQuery
                ORDER BY fechaCreacion DESC
                LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        $sqlTotal = "SELECT COUNT(*) AS total FROM estadoProyecto $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;
        return [
            'estadoProyecto' => $data,
            'total' => $total
        ];
    }

    public function registrarEstadoProyecto(string $codigoEstado, string $nombre, string $codigoUsuario)
    {
        $this->codigoEstado = $codigoEstado;
        $this->nombre = $nombre;
        $this->codigoUsuario = $codigoUsuario;
        $verificar = "SELECT * FROM estadoProyecto WHERE codigoEstadoProyecto = ?";
        $existe = $this->select($verificar, [$this->codigoEstado]);
        if (empty($existe)) {
            $sql = "INSERT INTO estadoProyecto(codigoEstadoProyecto, nombreEstadoProyecto, codigoUsuario) VALUES (?,?,?)";
            $datos = array($this->codigoEstado, $this->nombre, $this->codigoUsuario);
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

    public function modificarEstadoProyecto(string $nombre, string $codigoUsuario, string $codigoEstado)
    {
        $this->nombre = $nombre;
        $this->codigoUsuario = $codigoUsuario;
        $this->codigoEstado = $codigoEstado;
        $sql = "UPDATE estadoProyecto SET nombreEstadoProyecto = ?, codigoUsuario = ? WHERE codigoEstadoProyecto = ?";
        $datos = array($this->nombre, $this->codigoUsuario, $this->codigoEstado);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function editarEstadoProyecto(string $codigoEstado)
    {
        $sql = "SELECT codigoEstadoProyecto AS codigo, nombreEstadoProyecto AS nombre FROM estadoProyecto WHERE codigoEstadoProyecto = ?";
        $datos = array($codigoEstado);
        $data = $this->select($sql, $datos);
        return $data;
    }
}
