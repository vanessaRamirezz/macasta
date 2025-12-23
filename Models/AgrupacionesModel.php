<?php
class AgrupacionesModel extends Query
{

    private $nombre, $codigoAgrupacion, $codigoUsuario;
    public function __construct()
    {
        parent::__construct();
    }

    public function getAgrupaciones($start, $length, $search = "")
    {
        $searchQuery = "";
        $params = [];
        if (!empty($search)) {
            $searchQuery = "WHERE codigoAgrupacion LIKE :search OR nombreAgrupacion LIKE :search";
            $params = [':search' => '%' . $search . '%'];
        }

        $sql = "SELECT codigoAgrupacion AS codigo, nombreAgrupacion AS nombre
                FROM agrupaciones
                $searchQuery
                ORDER BY fechaCreacion DESC
                LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        $sqlTotal = "SELECT COUNT(*) AS total FROM agrupaciones $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;
        // Devuelve las agrupaciones y el total
        return [
            'agrupaciones' => $data,
            'total' => $total
        ];
    }

    public function registrarAgrupacion(string $codigoAgrupacion, string $nombre, string $codigoUsuario)
    {
        $this->codigoAgrupacion = $codigoAgrupacion;
        $this->nombre = $nombre;
        $this->codigoUsuario = $codigoUsuario;
        $verificar = "SELECT * FROM agrupaciones WHERE codigoAgrupacion = ?";
        $existe = $this->select($verificar, [$this->codigoAgrupacion]);
        if (empty($existe)) {
            $sql = "INSERT INTO agrupaciones(codigoAgrupacion, nombreAgrupacion, codigoUsuario) VALUES (?,?,?)";
            $datos = array($this->codigoAgrupacion, $this->nombre, $this->codigoUsuario);
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

    public function modificarAgrupacion(string $nombre, string $codigoUsuario, string $codigoAgrupacion)
    {
        $this->nombre = $nombre;
        $this->codigoUsuario = $codigoUsuario;
        $this->codigoAgrupacion = $codigoAgrupacion;
        $sql = "UPDATE agrupaciones SET nombreAgrupacion = ?, codigoUsuario = ? WHERE codigoAgrupacion = ?";
        $datos = array($this->nombre, $this->codigoUsuario, $this->codigoAgrupacion);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }
    
    public function editarAgrupacion(string $codigoAgrupacion)
    {
        $sql = "SELECT codigoAgrupacion AS codigo, nombreAgrupacion AS nombre FROM agrupaciones WHERE codigoAgrupacion = ?";
        $datos = array($codigoAgrupacion);
        $data = $this->select($sql, $datos);
        return $data;
    }
}
