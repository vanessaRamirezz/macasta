<?php
class TipoMovimientoModel extends Query
{

    private $codigo, $nombre, $codigoAplicacion, $efecto;
    public function __construct()
    {
        parent::__construct();
    }

    public function searchAplicacion($query = null)
    {
        $sql = "SELECT codigoAplicacion AS codigo, 
                    nombreAplicacion AS nombre 
                FROM aplicaciones";
        $params = [];

        if (!empty($query)) {
            $sql .= " WHERE nombreAplicacion LIKE ? ";
            $params = ["%$query%"];
        }

        $sql .= " LIMIT 5"; // Siempre limita a 5 resultados
        return $this->selectAll($sql, $params);
    }


    public function getTiposMovimientos($start, $length, $search = "")
    {
        $searchQuery = "";
        $params = [];
        if (!empty($search)) {
            $searchQuery = "WHERE m.codigoTipoMovimiento LIKE :search OR m.nombreMovimiento LIKE :search";
            $params = [':search' => '%' . $search . '%'];
        }

        $sql = "SELECT m.codigoTipoMovimiento AS codigo,
                        m.nombreMovimiento AS nombre,
                        a.nombreAplicacion AS aplicacion,
                        m.efecto AS efecto
                FROM tipoMovimiento m
                LEFT JOIN aplicaciones a ON m.codigoAplicacion = a.codigoAplicacion
                $searchQuery
                ORDER BY m.fechaCreacion DESC 
                LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        // Consulta secundaria: obtener el total
        $sqlTotal = "SELECT COUNT(*) AS total FROM tipoMovimiento m $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        return [
            'tipoMovimientos' => $data,
            'total' => $total
        ];
    }
    public function registrarTipoMovimiento(string $codigo, string $nombre, $codigoAplicacion, string $efecto)
    {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
        $this->codigoAplicacion = empty($codigoAplicacion) ? NULL : $codigoAplicacion;
        $this->efecto = $efecto;
        $verificar = "SELECT * FROM tipoMovimiento WHERE codigoTipoMovimiento = ?";
        $existe = $this->select($verificar, [$this->codigo]);
        if (empty($existe)) {
            $sql = "INSERT INTO tipoMovimiento(codigoTipoMovimiento, nombreMovimiento, codigoAplicacion, efecto) VALUES (?,?,?,?)";
            $datos = array($this->codigo, $this->nombre, $this->codigoAplicacion, $this->efecto);
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
    public function modificarTipoMovimiento(string $nombre, $codigoAplicacion, string $efecto, string $codigo,)
    {
        $this->nombre = $nombre;
        $this->codigoAplicacion = empty($codigoAplicacion) ? NULL : $codigoAplicacion;
        $this->efecto = $efecto;
        $this->codigo = $codigo;
        $sql = "UPDATE tipoMovimiento SET nombreMovimiento = ?, codigoAplicacion = ?, efecto = ? WHERE codigoTipoMovimiento = ?";
        $datos = array($this->nombre, $this->codigoAplicacion, $this->efecto, $this->codigo);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function editarTipoMovimiento(string $codigo)
    {
        $sql = "SELECT 
                m.codigoTipoMovimiento AS codigo,
                m.nombreMovimiento AS nombre,
                a.codigoAplicacion AS aplicacion,
                IFNULL(a.nombreAplicacion, '-----') AS nombreAplicacion, -- Asegúrate de que esta columna exista
                m.efecto AS efecto
        FROM tipoMovimiento m
        LEFT JOIN aplicaciones a ON m.codigoAplicacion = a.codigoAplicacion
        WHERE codigoTipoMovimiento = ?";

        $datos = array($codigo);
        $data = $this->select($sql, $datos);
        return $data;
    }
}
