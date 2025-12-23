<?php
class AplicacionesModel extends Query
{

    private $codigo, $nombre, $telefono, $contacto, $limiteCredito, $saldo, $codigoUsuario;
    public function __construct()
    {
        parent::__construct();
    }

    public function getAplicaciones($start, $length, $search = "")
    {
        $searchQuery = "";
        $params = [];
        if (!empty($search)) {
            $searchQuery = "WHERE codigoAplicacion LIKE :search OR nombreAplicacion LIKE :search";
            $params = [':search' => '%' . $search . '%'];
        }

        // Consulta principal: obtener los datos paginados
        $sql = "SELECT codigoAplicacion AS codigo, 
                nombreAplicacion AS nombre
                FROM aplicaciones 
                $searchQuery
                ORDER BY fechaCreacion DESC
                LIMIT $start, $length";
            
        $data = $this->selectAll($sql, $params);

        // Consulta secundaria: obtener el total
        $sqlTotal = "SELECT COUNT(*) AS total FROM aplicaciones $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        return [
            'aplicaciones' => $data,
            'total' => $total
        ];
    }

    public function registrarAplicacion(string $codigo, string $nombre, string $codigoUsuario)
    {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
        $this->codigoUsuario = $codigoUsuario;
        $verificar = "SELECT * FROM aplicaciones WHERE codigoAplicacion = ?";
        $existe = $this->select($verificar, [$this->codigo]);
        if (empty($existe)) {
            $sql = "INSERT INTO aplicaciones(codigoAplicacion, nombreAplicacion, codigoUsuario) VALUES (?,?,?)";
            $datos = array($this->codigo, $this->nombre,$this->codigoUsuario);
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

    public function modificarAplicacion( string $codigo, string $nombre, string $codigoUsuario)
    {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
        $this->codigoUsuario = $codigoUsuario;
        $sql = "UPDATE aplicaciones SET nombreAplicacion = ?, codigoUsuario = ? WHERE codigoAplicacion = ?";
        $datos = array($this->codigo, $this->nombre, $this->codigoUsuario);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function editarAplicacion(string $codigo)
    {
        $sql = "SELECT codigoAplicacion AS codigo,
                        nombreAplicacion AS nombre
                        FROM aplicaciones WHERE codigoAplicacion = ?";
        $datos = array($codigo);
        $data = $this->select($sql, $datos);
        return $data;
    }

    // public function accionUsuario(int $estadoUsuario, string $codigoUsuario){
    //     $this->codigo = $codigoUsuario;
    //     $this->estado = $estadoUsuario;
    //     $sql  = "UPDATE usuarios SET estado = ? WHERE codigoUsuario = ?";
    //     $datos = array($this->estado, $this->codigo);
    //     $data = $this->guardar($sql, $datos);
    //     return $data;

    // }
}
