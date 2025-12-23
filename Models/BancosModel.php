<?php
class BancosModel extends Query
{
    private $codigoBanco, $nombreBanco, $codigoCuentaBancaria, $nombreCuentaBancaria, $saldoInicial, $ingresos,
        $salidas, $saldo, $codigoUsuario;
    public function __construct()
    {
        parent::__construct();
    }

    // cuentas
    public function getCuentaBancaria($start, $length, $search = "")
    {
        $searchQuery = "";
        $params = [];
        if (!empty($search)) {
            $searchQuery = "WHERE codigoCuentaBancaria LIKE :search OR nombreCuentaBancaria LIKE :search";
            $params = [':search' => '%' . $search . '%'];
        }

        // Consulta principal: obtener los datos paginados
        $sql = "SELECT codigoCuentaBancaria AS codigo, 
            nombreCuentaBancaria AS nombre 
            FROM cuentaBancaria
            $searchQuery
            ORDER BY fechaCreacion DESC 
            LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        // Consulta secundaria: obtener el total
        $sqlTotal = "SELECT COUNT(*) AS total FROM cuentaBancaria $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        return [
            'cuentaBancaria' => $data,
            'total' => $total
        ];
    }

    public function registrarCuenta(string $codigoCuentaBancaria, string $nombreCuentaBancaria,  string $codigoBanco, float $saldoInicial, float $ingresos, float $salidas, float $saldo, string $codigoUsuario)
    {

        $this->codigoCuentaBancaria = $codigoCuentaBancaria;
        $this->nombreCuentaBancaria = $nombreCuentaBancaria;
        $this->codigoBanco = $codigoBanco;
        $this->saldoInicial = $saldoInicial;
        $this->ingresos = $ingresos;
        $this->salidas = $salidas;
        $this->saldo = $saldo;
        $this->codigoUsuario = $codigoUsuario;
        $verificar = "SELECT * FROM cuentaBancaria WHERE codigoCuentaBancaria = ?";
        $existe = $this->select($verificar, [$this->codigoCuentaBancaria]);
        if (empty($existe)) {
            $sql = "INSERT INTO cuentaBancaria(codigoCuentaBancaria, nombreCuentaBancaria, codigoBanco, saldoInicial, ingresos, salidas, saldo, codigoUsuario) VALUES (?,?,?,?,?,?,?,?)";
            $datos = array(
                $this->codigoCuentaBancaria,
                $this->nombreCuentaBancaria,
                $this->codigoBanco,
                $this->saldoInicial,
                $this->ingresos,
                $this->salidas,
                $this->saldo,
                $this->codigoUsuario
            );
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

    public function modificarCuenta(string $nombreCuentaBancaria, float $saldoInicial, float $ingresos, float $salidas, float $saldo, string $codigoUsuario, string $codigoCuentaBancaria)
    {

        $this->nombreCuentaBancaria = $nombreCuentaBancaria;
        $this->saldoInicial = $saldoInicial;
        $this->ingresos = $ingresos;
        $this->salidas = $salidas;
        $this->saldo = $saldo;
        $this->codigoUsuario = $codigoUsuario;
        $this->codigoCuentaBancaria = $codigoCuentaBancaria;

        $sql = "UPDATE cuentaBancaria SET nombreCuentaBancaria = ?, saldoInicial = ?, ingresos = ?, salidas = ?, saldo = ?, codigoUsuario = ?  WHERE codigoCuentaBancaria = ?";
        $datos = array($this->nombreCuentaBancaria, $this->saldoInicial, $this->ingresos, $this->salidas, $this->saldo, $this->codigoUsuario, $this->codigoCuentaBancaria,);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function editarCuentaBancaria(string $codigoCuentaBancaria)
    {
        $sql = "SELECT  c.codigoCuentaBancaria AS cuenta,
                        c.nombreCuentaBancaria AS nombreCuenta,
                        c.codigoBanco AS codigo,
                        b.nombreBanco AS banco,
                        c.saldoInicial AS saldoIni,
                        c.ingresos,
                        c.salidas,
                        c.saldo
                        FROM cuentaBancaria c
                        LEFT JOIN bancos b ON c.codigoBanco = b.codigoBanco
                        WHERE codigoCuentaBancaria = ?";
        $datos = array($codigoCuentaBancaria);
        $data = $this->select($sql, $datos);
        return $data;
    }

    // codigo bancos
    public function getBancos($start, $length, $search = "")
    {
        $searchQuery = "";
        $params = [];
        if (!empty($search)) {
            $searchQuery = "WHERE codigoBanco LIKE :search OR nombreBanco LIKE :search";
            $params = [':search' => '%' . $search . '%'];
        }

        // Consulta principal: obtener los datos paginados
        $sql = "SELECT codigoBanco AS codigo, 
            nombreBanco AS nombre 
            FROM bancos
            $searchQuery
            ORDER BY fechaCreacion DESC 
            LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        // Consulta secundaria: obtener el total
        $sqlTotal = "SELECT COUNT(*) AS total FROM bancos $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        return [
            'banco' => $data,
            'total' => $total
        ];
    }

    public function registrarBanco(string $codigoBanco, string $nombreBanco)
    {

        $this->codigoBanco = $codigoBanco;
        $this->nombreBanco = $nombreBanco;
        $verificar = "SELECT * FROM bancos WHERE codigoBanco = ?";
        $existe = $this->select($verificar, [$this->codigoBanco]);
        if (empty($existe)) {
            $sql = "INSERT INTO bancos(codigoBanco, nombreBanco) VALUES (?,?)";
            $datos = array(
                $this->codigoBanco,
                $this->nombreBanco
            );
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

    public function modificarBanco(string $nombreBanco, string $codigoBanco,)
    {

        $this->nombreBanco = $nombreBanco;
        $this->codigoBanco = $codigoBanco;

        $sql = "UPDATE bancos SET nombreBanco = ? WHERE codigoBanco = ?";
        $datos = array($this->nombreBanco, $this->codigoBanco);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function editarBanco(string $codigoBanco)
    {
        $sql = "SELECT  codigoBanco AS codigo,
                        nombreBanco AS nombre
                        FROM bancos WHERE codigoBanco = ?";
        $datos = array($codigoBanco);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function getListado($start, $length, $search = "")
    {
        $searchQuery = "";
        $params = [];
        if (!empty($search)) {
            $searchQuery = "WHERE codigoBanco LIKE :search OR nombreBanco LIKE :search";
            $params = [':search' => '%' . $search . '%'];
        }

        // Consulta principal: obtener los datos paginados
        $sql = "SELECT codigoBanco AS codigo, 
            nombreBanco AS nombre 
            FROM bancos
            $searchQuery
            ORDER BY fechaCreacion DESC 
            LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        // Consulta secundaria: obtener el total
        $sqlTotal = "SELECT COUNT(*) AS total FROM bancos $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        return [
            'banco' => $data,
            'total' => $total
        ];
    }

    public function verListado(string $codigoBanco)
    {
        $sql = "SELECT
        b.codigoBanco,
		b.nombreBanco,
        IFNULL(cb.codigoCuentaBancaria, '---') AS codigoCuenta,
        IFNULL(cb.nombreCuentaBancaria, '---') AS nombreCuenta,
        IFNULL(cb.saldoInicial, '---') AS saldoI,
        IFNULL(cb.ingresos, '---') AS ing,
        IFNULL(cb.salidas, '---') AS sal,
        IFNULL(cb.saldo, '---') AS sald
        FROM bancos b
        LEFT JOIN cuentaBancaria cb ON b.codigoBanco = cb.codigoBanco
        WHERE b.codigoBanco = ?";
        $datos = array($codigoBanco);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    public function actualizarSaldoCuentas(float $saldo, string $codigoCuentaBancaria)
    {
        $sql = "UPDATE cuentaBancaria SET saldo = ? WHERE codigoCuentaBancaria = ?";
        $datos = array($saldo, $codigoCuentaBancaria);

        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function obtenerSaldoCuenta(string $codigoCuentaBancaria)
    {
        $sql = "SELECT saldoInicial, ingresos, saldo, salidas FROM cuentaBancaria WHERE codigoCuentaBancaria = ?";
        $datos = array($codigoCuentaBancaria);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }
}
