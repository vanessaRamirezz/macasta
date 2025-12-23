<?php
class EmpleadosModel extends Query
{
    private $codigoEmpleado, $nombreEmpleado, $telefonoEmpleado, $codigoUsuario;
    public function __construct()
    {
        parent::__construct();
    }

    public function getEmpleados($start, $length, $search = "")
    {
        $searchQuery = "";
        $params = [];
        if (!empty($search)) {
            $searchQuery = "WHERE codigoEmpleado LIKE :search OR nombreEmpleado LIKE :search";
            $params = [':search' => '%' . $search . '%'];
        }

        $sql = "SELECT codigoEmpleado AS codigo,
                        nombreEmpleado AS nombre,
                        telefonoEmpleado AS telefono
                FROM empleados
                $searchQuery
                ORDER BY fechaCreacion DESC
                LIMIT $start,$length";
        $data = $this->selectAll($sql, $params);

        $sqlTotal = "SELECT COUNT(*) AS total FROM empleados $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;
        // Devuelve las agrupaciones y el total
        return [
            'empleados' => $data,
            'total' => $total
        ];
    }

    public function registrarEmpleado(string $codigoEmpleado, string $nombreEmpleado, $telefonoEmpleado, string $codigoUsuario)
    {
        $this->codigoEmpleado = $codigoEmpleado;
        $this->nombreEmpleado = $nombreEmpleado;
        $this->telefonoEmpleado = $telefonoEmpleado;
        $this->codigoUsuario = $codigoUsuario;
        $verificar = "SELECT * FROM empleados WHERE codigoEmpleado = ?";
        $existe = $this->select($verificar, [$this->codigoEmpleado]);
        if (empty($existe)) {
            $sql = "INSERT INTO empleados(codigoEmpleado, nombreEmpleado, telefonoEmpleado, codigoUsuario) VALUES (?,?,?,?)";
            $datos = array($this->codigoEmpleado, $this->nombreEmpleado, $this->telefonoEmpleado, $this->codigoUsuario);
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

    public function modificarEmpleado(string $nombreEmpleado, $telefonoEmpleado, string $codigoUsuario, string $codigoEmpleado)
    {
        $this->nombreEmpleado = $nombreEmpleado;
        $this->telefonoEmpleado = $telefonoEmpleado;
        $this->codigoUsuario = $codigoUsuario;
        $this->codigoEmpleado = $codigoEmpleado;
        $sql = "UPDATE empleados SET nombreEmpleado = ?, telefonoEmpleado = ?, codigoUsuario = ? WHERE codigoEmpleado = ?";
        $datos = array($this->nombreEmpleado, $this->telefonoEmpleado, $this->codigoUsuario, $this->codigoEmpleado);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function editarEmpleado(string $codigoEmpleado)
    {
        $sql = "SELECT codigoEmpleado AS codigo,
                        nombreEmpleado AS nombre,
                        telefonoEmpleado AS telefono
                        FROM empleados WHERE codigoEmpleado = ?";
        $datos = array($codigoEmpleado);
        $data = $this->select($sql, $datos);
        return $data;
    }
    
}
