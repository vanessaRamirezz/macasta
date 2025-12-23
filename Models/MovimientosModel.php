<?php
class MovimientosModel extends Query
{
    private $codigoTipoMovimiento, $numeroTransaccion, $numeroDocumento, $monto,
        $codigoProveedor, $codigoCliente, $codigoProyecto, $codigoTipoDocumento, $tipoPago, $codigoBanco,
        $codigoCuentaBancaria, $fecha, $codigoUsuario, $codigoEmpleado, $metodoEmpleado;
    public function __construct()
    {
        parent::__construct();
    }

    public function searchTipoMovimiento($query)
    {
        $sql = "SELECT codigoTipoMovimiento AS codigo,
        nombreMovimiento AS nombre
        FROM tipoMovimiento
        WHERE nombreMovimiento LIKE ?
        ORDER BY fechaCreacion DESC
        LIMIT 5 ";
        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    public function searchTipoTransaccionEmpleado($query)
    {
        $sql = "SELECT id AS codigo,
        tipo AS nombre
        FROM transaccionEmpleado
        WHERE tipo LIKE ?
        LIMIT 5 ";
        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    public function searchEmpleado($query)
    {
        $sql = "SELECT codigoEmpleado AS codigo,
        nombreEmpleado AS nombre
        FROM empleados
        WHERE nombreEmpleado LIKE ?
        LIMIT 5 ";
        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    public function searchTipoDocumento($query)
    {
        $sql = "SELECT codigoTipoDocumento AS codigo,
        nombreTipoDocumento AS nombre
        FROM tiposDocumentos
        WHERE nombreTipoDocumento LIKE ?
        ORDER BY fechaCreacion ASC
        LIMIT 3 ";
        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    public function searchTipoPago($query)
    {
        $sql = "SELECT id AS codigo,
        nombrePago AS nombre
        FROM tipoPago
        WHERE nombrePago LIKE ?
        LIMIT 5 ";
        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    public function searchBanco($query)
    {
        $sql = "SELECT codigoBanco AS codigo,
        nombreBanco AS nombre
        FROM bancos
        WHERE nombreBanco LIKE ?
        ORDER BY fechaCreacion DESC
        LIMIT 5 ";
        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    public function obtenerCuenta($codigoBanco)
    {
        $sql = "SELECT codigoCuentaBancaria AS codigo,
                nombreCuentaBancaria AS nombre
                FROM cuentaBancaria
                WHERE codigoBanco = ? 
                ORDER BY fechaCreacion DESC
                LIMIT 5";
        $params = [$codigoBanco];
        return $this->selectAll($sql, $params);
    }

    public function registrarMovimiento(
        string $codigoTipoMovimiento,
        string $numeroTransaccion,
        string $numeroDocumento,
        float $monto,
        $codigoProveedor,
        $codigoCliente,
        $codigoEmpleado,
        $metodoEmpleado,
        $codigoProyecto,
        string $codigoTipoDocumento,
        string $tipoPago,
        $codigoBanco,
        $codigoCuentaBancaria,
        string $fecha,
        string $codigoUsuario
    ) {

        $this->codigoTipoMovimiento = $codigoTipoMovimiento;
        $this->numeroTransaccion = $numeroTransaccion;
        $this->numeroDocumento = $numeroDocumento;
        $this->monto = $monto;
        $this->codigoProveedor = empty($codigoProveedor) ? NULL : $codigoProveedor;
        $this->codigoCliente = empty($codigoCliente) ? NULL : $codigoCliente;
        $this->codigoEmpleado = empty($codigoEmpleado) ? NULL : $codigoEmpleado;
        $this->metodoEmpleado = empty($metodoEmpleado) ? NULL : $metodoEmpleado;
        $this->codigoProyecto = empty($codigoProyecto) ? NULL : $codigoProyecto;
        $this->codigoTipoDocumento = $codigoTipoDocumento;
        $this->tipoPago = $tipoPago;
        $this->codigoBanco = $codigoBanco;
        $this->codigoCuentaBancaria = $codigoCuentaBancaria;
        $this->fecha = $fecha;
        $this->codigoUsuario = $codigoUsuario;
        $verificar = "SELECT * FROM movimientos WHERE numeroTransaccion = ?";
        $existe = $this->select($verificar, [$this->numeroTransaccion]);
        if (empty($existe)) {
            $sql = "INSERT INTO movimientos(codigoTipoMovimiento, numeroTransaccion, numeroDocumento, monto, codigoProveedor, codigoCliente, codigoEmpleado, id, codigoProyecto, codigoTipoDocumento, id_tipoPago, codigoBanco, codigoCuentaBancaria, fecha, codigoUsuario) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $datos = array(
                $this->codigoTipoMovimiento,
                $this->numeroTransaccion,
                $this->numeroDocumento,
                $this->monto,
                $this->codigoProveedor,
                $this->codigoCliente,
                $this->codigoEmpleado,
                $this->metodoEmpleado,
                $this->codigoProyecto,
                $this->codigoTipoDocumento,
                $this->tipoPago,
                $this->codigoBanco,
                $this->codigoCuentaBancaria,
                $this->fecha,
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

    public function obtenerSaldoProveedor(string $codigoProveedor)
    {
        $sql = "SELECT saldoProveedor FROM proveedores WHERE codigoProveedor = ?";
        $datos = array($codigoProveedor);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function actualizarSaldoProveedor(float $saldo, string $codigoProveedor)
    {
        $sql = "UPDATE proveedores SET saldoProveedor = ? WHERE codigoProveedor = ?";
        $datos = array($saldo, $codigoProveedor);

        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function obtenerSaldoCliente(string $codigoCliente)
    {
        $sql = "SELECT saldoCliente FROM clientes WHERE codigoCliente = ?";
        $datos = array($codigoCliente);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function actualizarSaldoCliente(float $saldo, string $codigoCliente)
    {
        $sql = "UPDATE clientes SET saldoCliente = ? WHERE codigoCliente = ?";
        $datos = array($saldo, $codigoCliente);

        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function getMovimientos($numeroTransaccion)
    {
        $sql = "SELECT 
                    m.codigoTipoMovimiento, 
                    tm.nombreMovimiento AS nombreMovimiento, 
                    m.numeroTransaccion, 
                    m.numeroDocumento, 
                    m.monto, 
                    m.codigoProveedor, 
                    pr.nombreProveedor AS nombreProveedor, 
                    m.codigoCliente, 
                    c.nombreCliente AS nombreCliente,
                    m.codigoEmpleado AS codigoEm,
                    ep.nombreEmpleado AS empleado,
                    m.id AS tipoPagoEmpleado,
                    m.codigoProyecto, 
                    py.nombreProyecto AS nombreProyecto, 
                    m.codigoTipoDocumento, 
                    td.nombreTipoDocumento AS nombreTipoDocumento,
                    tp.id,
                    tp.nombrePago AS pago, 
                    m.codigoBanco, 
                    bc.nombreBanco AS nombreBanco, 
                    b.codigoCuentaBancaria, 
                    b.nombreCuentaBancaria AS nombreCuenta, 
                    m.fecha,
                    m.fecha_hora
                FROM movimientos m
                LEFT JOIN proveedores pr ON m.codigoProveedor = pr.codigoProveedor
                LEFT JOIN clientes c ON m.codigoCliente = c.codigoCliente
                LEFT JOIN tipoMovimiento tm ON m.codigoTipoMovimiento = tm.codigoTipoMovimiento
                LEFT JOIN tiposDocumentos td ON m.codigoTipoDocumento = td.codigoTipoDocumento
                LEFT JOIN cuentaBancaria b ON m.codigoCuentaBancaria = b.codigoCuentaBancaria
                LEFT JOIN proyectos py ON m.codigoProyecto = py.codigoProyecto
                LEFT JOIN bancos bc ON m.codigoBanco = bc.codigoBanco
                LEFT JOIN tipoPago tp ON m.id_tipoPago = tp.id
                LEFT JOIN empleados ep ON m.codigoEmpleado = ep.codigoEmpleado
                WHERE m.numeroTransaccion = ?";

        $datos = array($numeroTransaccion);
        return $this->selectAll($sql, $datos);
    }

    public function historialMovimientos($start, $length, $search = "")
    {
        $searchQuery = "";
        $params = [];

        if (!empty($search)) {
            $searchQuery = "WHERE fecha = :search";  // Comparación exacta con DATE
            $params = [':search' => $search];
        }

        $sql = "SELECT m.numeroTransaccion AS transaccion,
                    m.codigoTipoMovimiento AS codigo,
                    t.nombreMovimiento AS movimiento,
                    m.codigoProveedor AS cod,
                    pr.nombreProveedor AS proveedor,
                    m.codigoCliente AS codG,
                    c.nombreCliente AS cliente,
                    ep.nombreEmpleado AS empleado,
                    m.codigoProyecto AS codP,
                    py.nombreProyecto AS proyecto,
                    m.fecha AS fecha
                FROM movimientos m
                LEFT JOIN proveedores pr ON m.codigoProveedor = pr.codigoProveedor
                LEFT JOIN clientes c ON m.codigoCliente = c.codigoCliente
                LEFT JOIN tipoMovimiento t ON m.codigoTipoMovimiento = t.codigoTipoMovimiento 
                LEFT JOIN proyectos py ON m.codigoProyecto = py.codigoProyecto
                LEFT JOIN empleados ep ON m.codigoEmpleado = ep.codigoEmpleado
                $searchQuery
                ORDER BY m.fecha_hora DESC
                LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        $sqlTotal = "SELECT COUNT(*) AS total FROM movimientos en $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        return [
            'mov' => $data,
            'total' => $total
        ];
    }

    public function obtenerNombreTipoMovimiento($codigoTipoMovimiento)
    {
        $sql = "SELECT nombreMovimiento FROM tipoMovimiento WHERE codigoTipoMovimiento = ?";
        $datos = array($codigoTipoMovimiento);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function obtenerIngresoProyecto(string $codigoProyecto)
    {
        $sql = "SELECT ingresos FROM proyectos WHERE codigoProyecto = ?";
        $datos = array($codigoProyecto);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function actualizarIngresoProyecto(float $ingreso, string $codigoProyecto)
    {
        $sql = "UPDATE proyectos SET ingresos = ? WHERE codigoProyecto = ?";
        $datos = array($ingreso, $codigoProyecto);

        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function datosEmpresa()
    {
        $sql = "SELECT nombre, direccion FROM empresa";
        $data = $this->selectAll($sql);
        return $data;
    }

    public function obtenerSalidasCuenta(string $codigoCuentaBancaria)
    {
        $sql = "SELECT salidas FROM cuentaBancaria WHERE codigoCuentaBancaria = ?";
        $datos = array($codigoCuentaBancaria);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function actualizarSalidasCuentas(float $salidas, string $codigoCuentaBancaria)
    {
        $sql = "UPDATE cuentaBancaria SET salidas = ? WHERE codigoCuentaBancaria = ?";
        $datos = array($salidas, $codigoCuentaBancaria);

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

    public function actualizarIngresoCuenta(float $ingreso, string $codigoCuentaBancaria)
    {
        $sql = "UPDATE cuentaBancaria SET ingresos = ? WHERE codigoCuentaBancaria = ?";
        $datos = array($ingreso, $codigoCuentaBancaria);

        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }
    // public function historialMovimientosPlanilla($start, $length, $search = "")
    // {
    //     $searchQuery = "";
    //     $params = [];

    //     if (!empty($search)) {
    //         $searchQuery = "AND m.fecha = :search";
    //         $params = [':search' => $search];
    //     }

    //     $sql = "SELECT m.numeroTransaccion AS transaccion,
    //             m.codigoTipoMovimiento AS codigo,
    //             t.nombreMovimiento AS movimiento,
    //             e.nombreEmpleado AS empleado,
    //             m.codigoProyecto AS codP,
    //             py.nombreProyecto AS proyecto,
    //             m.fecha AS fecha
    //         FROM movimientos m
    //         LEFT JOIN tipoMovimiento t ON m.codigoTipoMovimiento = t.codigoTipoMovimiento 
    //         LEFT JOIN proyectos py ON m.codigoProyecto = py.codigoProyecto
    //         LEFT JOIN empleados e ON m.codigoEmpleado = e.codigoEmpleado
    //         WHERE m.codigoTipoMovimiento = 'PP'
    //         $searchQuery
    //         ORDER BY m.fecha_hora DESC
    //         LIMIT $start, $length";

    //     $data = $this->selectAll($sql, $params);

    //     // Corrección aquí
    //     $sqlTotal = "SELECT COUNT(*) AS total FROM movimientos m 
    //             WHERE m.codigoTipoMovimiento = 'PP' 
    //             $searchQuery";
    //     $total = $this->select($sqlTotal, $params)['total'] ?? 0;

    //     return [
    //         'recibos' => $data,
    //         'total' => $total
    //     ];
    // }
}
