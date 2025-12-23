<?php
class ProveedoresModel extends Query
{

    private $codigo, $nombre, $telefono, $contacto, $limiteCredito, $saldo, $codigoUsuario;
    public function __construct()
    {
        parent::__construct();
    }

    public function getProveedores($start, $length, $search = "")
    {
        $searchQuery = "";
        $params = [];
        if (!empty($search)) {
            $searchQuery = "WHERE codigoProveedor LIKE :search OR nombreProveedor LIKE :search";
            $params = [':search' => '%' . $search . '%'];
        }

        $sql = "SELECT codigoProveedor AS codigo,
                        nombreProveedor AS nombre,
                        numeroTelefonoProveedor AS telefono,
                        contactoProveedor AS contacto,
                        limiteCreditoProveedor AS creditoLimite,
                        saldoProveedor AS saldo
                FROM proveedores
                $searchQuery
                ORDER BY fechaCreacion DESC
                LIMIT $start,$length";
        $data = $this->selectAll($sql, $params);

        $sqlTotal = "SELECT COUNT(*) AS total FROM proveedores $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;
        // Devuelve las agrupaciones y el total
        return [
            'proveedores' => $data,
            'total' => $total
        ];
    }

    public function registrarProveedor(string $codigo, string $nombre, string $telefono, string $contacto, float $limiteCredito, float $saldo, string $codigoUsuario)
    {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
        $this->telefono = $telefono;
        $this->contacto = $contacto;
        $this->limiteCredito = $limiteCredito;
        $this->saldo = $saldo;
        $this->codigoUsuario = $codigoUsuario;
        $verificar = "SELECT * FROM proveedores WHERE codigoProveedor = ?";
        $existe = $this->select($verificar, [$this->codigo]);
        if (empty($existe)) {
            $sql = "INSERT INTO proveedores(codigoProveedor, nombreProveedor, numeroTelefonoProveedor, contactoProveedor, limiteCreditoProveedor, saldoProveedor, codigoUsuario) VALUES (?,?,?,?,?,?,?)";
            $datos = array($this->codigo, $this->nombre, $this->telefono, $this->contacto, $this->limiteCredito, $this->saldo, $this->codigoUsuario);
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

    public function modificarProveedor(string $nombre, string $telefono, string $contacto, float $limiteCredito, float $saldo, string $codigoUsuario, string $codigo)
    {
        $this->nombre = $nombre;
        $this->telefono = $telefono;
        $this->contacto = $contacto;
        $this->limiteCredito = $limiteCredito;
        $this->saldo = $saldo;
        $this->codigoUsuario = $codigoUsuario;
        $this->codigo = $codigo;
        $sql = "UPDATE proveedores SET nombreProveedor = ?, numeroTelefonoProveedor = ?, contactoProveedor = ?, limiteCreditoProveedor = ?, saldoProveedor = ?, codigoUsuario = ? WHERE codigoProveedor = ?";
        $datos = array($this->nombre, $this->telefono, $this->contacto, $this->limiteCredito, $this->saldo, $this->codigoUsuario, $this->codigo);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function editarProveedor(string $codigoProveedor)
    {
        $sql = "SELECT codigoProveedor AS codigo, nombreProveedor AS nombre, numeroTelefonoProveedor AS telefono, contactoProveedor AS contacto, limiteCreditoProveedor AS creditoLimite, saldoProveedor AS saldo FROM proveedores WHERE codigoProveedor = ?";
        $datos = array($codigoProveedor);
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
