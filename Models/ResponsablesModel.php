<?php
class ResponsablesModel extends Query
{

    private $codigoResponsable, $nombreResponsable, $telefonoResponsale, $codigoUsuario;
    public function __construct()
    {
        parent::__construct();
    }

    public function getResponsables($start, $length, $search = "")
    {
        $searchQuery = "";
        $params = [];
        if (!empty($search)) {
            $searchQuery = "WHERE codigoResponsable LIKE :search OR nombreResponsable LIKE :search";
            $params = [':search' => '%' . $search . '%'];
        }

        // Consulta principal: obtener los datos paginados
        $sql = "SELECT codigoResponsable AS codigo, 
                nombreResponsable AS nombre,
                telefonoResponsable AS numero
                FROM responsables 
                $searchQuery
                ORDER BY fechaCreacion DESC
                LIMIT $start, $length";
            
        $data = $this->selectAll($sql, $params);

        // Consulta secundaria: obtener el total
        $sqlTotal = "SELECT COUNT(*) AS total FROM responsables $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        return [
            'responsables' => $data,
            'total' => $total
        ];
    }

    public function registrarResponsable(string $codigoResponsable, string $nombreResponsable, string $telefonoResponsale, string $codigoUsuario)
    {
        $this->codigoResponsable = $codigoResponsable;
        $this->nombreResponsable = $nombreResponsable;
        $this->telefonoResponsale = $telefonoResponsale;
        $this->codigoUsuario = $codigoUsuario;
        $verificar = "SELECT * FROM responsables WHERE codigoResponsabLe = ?";
        $existe = $this->select($verificar, [$this->codigoResponsable]);
        if (empty($existe)) {
            $sql = "INSERT INTO responsables(codigoResponsable, nombreResponsable, telefonoResponsable, codigoUsuario) VALUES (?,?,?,?)";
            $datos = array($this->codigoResponsable, $this->nombreResponsable, $this->telefonoResponsale, $this->codigoUsuario);
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

    public function editarResponsable(string $codigoResponsable)
    {
        $sql = "SELECT codigoResponsable AS codigo, 
                        nombreResponsable AS nombre,
                        telefonoResponsable AS telefono
                FROM responsables
                WHERE codigoResponsable = ?";
        $datos = array($codigoResponsable);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function modificarResponsable(string $nombreResponsable, string $telefonoResponsale, string $codigoUsuario, string $codigoResponsable)
    {
        $this->nombreResponsable = $nombreResponsable;
        $this->telefonoResponsale = $telefonoResponsale;
        $this->codigoUsuario = $codigoUsuario;
        $this->codigoResponsable = $codigoResponsable;
        $sql = "UPDATE responsables SET nombreResponsable = ?, telefonoResponsable = ?, codigoUsuario = ? WHERE codigoResponsable = ?";
        $datos = array($this->nombreResponsable, $this->telefonoResponsale, $this->codigoUsuario, $this->codigoResponsable);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }
}
