<?php
class ReportesModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getRangodeFecha(string $desde, string $hasta, $codigoCliente = null)
    {
        $sql = "SELECT p.codigoProyecto,
                    p.nombreProyecto,
                    p.fechaInicio,
                    p.fechaFin,
                    c.nombreCliente,
                    p.valorCotizado,
                    p.ingresos,
                    p.salidas,
                    p.valorRentabilidad,
                    r.nombreResponsable,
                    IFNULL(e.nombreEstadoProyecto, '--') AS nombreEstadoProyecto
            FROM proyectos p
            LEFT JOIN clientes c ON p.codigoCliente = c.codigoCliente
            LEFT JOIN responsables r ON p.codigoResponsable = r.codigoResponsable
            LEFT JOIN estadoProyecto e ON p.codigoEstadoProyecto = e.codigoEstadoProyecto
            WHERE DATE(p.fechaCreacion) BETWEEN ? AND ?";

        $datos = array($desde, $hasta);

        // Agregar condición de cliente si se proporciona
        if (!empty($codigoCliente)) {
            $sql .= " AND p.codigoCliente = ?";
            $datos[] = $codigoCliente;
        }

        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    public function getRangodeFechaCompra(string $desde, string $hasta, $codigoProyecto = null)
    {
        $sql = "SELECT e.numeroDocumento,
                e.numeroDocumentoFe,
                t.nombreMovimiento, 
                py.nombreProyecto, 
                pr.nombreProveedor, 
                e.total,
                e.observacion,
                e.fecha
                FROM encabezadoMovimiento e
                INNER JOIN tipoMovimiento t ON e.codigoTipoMovimiento = t.codigoTipoMovimiento
                INNER JOIN proveedores pr ON e.codigoProveedor = pr.codigoProveedor
                INNER JOIN proyectos py ON e.codigoProyecto = py.codigoProyecto
            WHERE DATE(e.fecha) BETWEEN ? AND ?";

        $datos = array($desde, $hasta);

        // Agregar condición de cliente si se proporciona
        if (!empty($codigoProyecto)) {
            $sql .= " AND e.codigoProyecto = ?";
            $datos[] = $codigoProyecto;
        }

        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    public function getRangodeFechaProductos(string $desde, string $hasta, $codigoProveedor = null, $codigoProducto = null)
    {
        $sql = "SELECT p.nombreProducto,
                        p.costoProducto,
                        p.precioVenta,
                        pro.nombreProveedor
                FROM productos p
                LEFT JOIN proveedores pro ON p.codigoProveedor = pro.codigoProveedor
            WHERE DATE(p.fechaCreacion) BETWEEN ? AND ?";

        $datos = array($desde, $hasta);

        // Agregar condición de proveedor si se proporciona
        if (!empty($codigoProveedor)) {
            $sql .= " AND pro.codigoProveedor = ?";
            $datos[] = $codigoProveedor;
        }

        if (!empty($codigoProducto)) {
            $sql .= " AND p.codigoProducto = ?";
            $datos[] = $codigoProducto;
        }

        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    public function getRangodeFechaMovimiento(string $desde, string $hasta, $codigoCliente = null, $codigoProveedor = null, $tipoMovimiento = null, $codigoEmpleado = null)
    {
        $sql = "SELECT tm.nombreMovimiento,
                m.numeroTransaccion,
                te.tipo, 
                m.numeroDocumento, 
                m.monto, 
                IFNULL(p.nombreProveedor, '--') AS proveedor,
                IFNULL(c.nombreCliente, '--') AS cliente,
                IFNULL(emp.nombreEmpleado, '--') AS empleado,
                py.nombreProyecto,
                tpd.nombreTipoDocumento,
                IFNULL(tp.nombrePago, '--') AS pago,
                IFNULL(b.nombreBanco, '--') AS banco,
                IFNULL(m.codigoCuentaBancaria, '--') AS cuentaBancaria,
                m.fecha
                FROM movimientos m
                LEFT JOIN tipoMovimiento tm ON m.codigoTipoMovimiento = tm.codigoTipoMovimiento
                LEFT JOIN proveedores p ON m.codigoProveedor = p.codigoProveedor
                LEFT JOIN clientes c ON m.codigoCliente = c.codigoCliente
                LEFT JOIN proyectos py ON m.codigoProyecto = py.codigoProyecto
                LEFT JOIN tiposDocumentos tpd ON m.codigoTipoDocumento = tpd.codigoTipoDocumento
                LEFT JOIN tipoPago tp ON m.id_tipoPago = tp.id
                LEFT JOIN bancos b ON m.codigoBanco = b.codigoBanco
                LEFT JOIN empleados emp  ON m.codigoEmpleado = emp.codigoEmpleado
                LEFT JOIN transaccionEmpleado te ON m.id = te.id
            WHERE m.fecha BETWEEN ? AND ?";

        $datos = array($desde, $hasta);

        // Agregar condición de cliente si se proporciona
        if (!empty($codigoCliente)) {
            $sql .= " AND m.codigoCliente = ?";
            $datos[] = $codigoCliente;
        }

        // Agregar condición de proveedor si se proporciona
        if (!empty($codigoProveedor)) {
            $sql .= " AND m.codigoProveedor = ?";
            $datos[] = $codigoProveedor;
        }

        if (!empty($codigoEmpleado)) {
            $sql .= " AND m.codigoEmpleado = ?";
            $datos[] = $codigoEmpleado;
        }
        // Agregar condición de proveedor si se proporciona
        if (!empty($tipoMovimiento)) {
            $sql .= " AND m.codigoTipoMovimiento = ?";
            $datos[] = $tipoMovimiento;
        }



        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    public function getRangodeFechaProveedores(string $desde, string $hasta)
    {
        $sql = "SELECT nombreProveedor,
                        numeroTelefonoProveedor,
                        contactoProveedor,
                        limiteCreditoProveedor,
                        saldoProveedor
                FROM proveedores
            WHERE DATE(fechaCreacion) BETWEEN ? AND ?";

        $datos = array($desde, $hasta);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    public function getRangodeFechaClientes(string $desde, string $hasta)
    {
        $sql = "SELECT c.nombreCliente,
                        IFNULL(c.nrc, '---') AS nrc,
                        c.codigoActividadEconomica,
                        IFNULL(a.valor, '---') AS actividad,
                        IFNULL(c.numeroTelefonoCliente,'---') AS telefono,
                        IFNULL(c.contactoCliente, '---') AS contacto,
                        c.limiteCreditoCliente AS limite,
                        c.saldoCliente
                FROM clientes c
                LEFT JOIN actividadesEconomicas a ON c.codigoActividadEconomica = a.codigo
            WHERE DATE(fechaCreacion) BETWEEN ? AND ?";

        $datos = array($desde, $hasta);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    public function getRangodeFechaCotizaciones(string $desde, string $hasta,  $codigoCliente = null)
    {
        $sql = "SELECT co.idCotizacion,
                        c.nombreCliente,
                        p.nombreProyecto,
                        co.total,
                        DATE(co.fecha) AS fecha
                FROM cotizaciones co
                LEFT JOIN clientes c ON c.codigoCliente = co.codigoCliente
                LEFT JOIN proyectos p ON p.codigoProyecto = co.codigoProyecto
            WHERE DATE(co.fecha) BETWEEN ? AND ?";

        $datos = array($desde, $hasta);

        if (!empty($codigoCliente)) {
            $sql .= " AND co.codigoCliente = ?";
            $datos[] = $codigoCliente;
        }

        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    public function getExistencias($codigoProyecto = null)
    {
        $sql = "SELECT e.codigoProyecto,
                        py.nombreProyecto,
                        e.codigoProducto,
                        p.nombreProducto,
                        e.cantidadProducto,
                        p.costoProducto
                FROM existencias e
                LEFT JOIN productos p ON e.codigoProducto = p.codigoProducto
                LEFT JOIN proyectos py ON e.codigoProyecto = py.codigoProyecto ";

        // Si se pasa el código de proyecto, se agrega la cláusula WHERE
        if (!empty($codigoProyecto)) {
            $sql .= "WHERE e.codigoProyecto = ? ";
            $datos = array($codigoProyecto);
        } else {
            $datos = array();  // No se pasa códigoProyecto, entonces no se agrega WHERE
        }

        // Siempre se agrega ORDER BY
        $sql .= "ORDER BY py.nombreProyecto ASC";

        $data = $this->selectAll($sql, $datos);
        return $data;
    }
    
     public function seleccionarTipoDte()
    {
        $sql = "SELECT codigoTipoDocumento AS codigo,
        nombreTipoDocumento AS nombre
        FROM tiposDocumentos
        WHERE nombreTipoDocumento IN ('Factura','Comprobante de crédito fiscal','Nota de crédito','Facturas de sujeto excluido')
        ORDER BY fechaCreacion DESC
        LIMIT 5 ";
        return $this->selectAll($sql);
    }

    // public function getRangodeFechaFacturas(string $desde, string $hasta, $tipoDocumento = null)
    // {
    //     $sql = "SELECT e.numeroControl,
    //                     e.codigoGeneracion,
    //                     e.receptor,
    //                     IFNULL(e.selloRecepcion,'---') AS selloRecepcion,
    //                     e.totalCompra,
    //                     e.totalPagar,
    //                     d.nombreTipoDocumento,
    //                     c.nombreCliente,
    //                     e.montoTotalOperacion
    //         FROM dte_encabezado e
    //         LEFT JOIN tiposDocumentos d ON e.tipoDte = d.codigoTipoDocumento
    //         LEFT JOIN clientes c ON e.receptor = c.codigoCliente
    //         WHERE DATE(e.fechaEmision) BETWEEN ? AND ? AND ambiente = '00'";

    //     $datos = array($desde, $hasta);

    //     // Agregar condición de cliente si se proporciona
    //     if (!empty($tipoDocumento)) {
    //         $sql .= " AND e.tipoDte = ?";
    //         $datos[] = $tipoDocumento;
    //     }

    //     $data = $this->selectAll($sql, $datos);
    //     return $data;
    // }

    public function getRangodeFechaFacturas(string $desde, string $hasta, $tipoDocumento = null)
    {
        if (empty($tipoDocumento)) {
            // Consulta agrupada con total dinámico
            $sql = "SELECT e.tipoDte,
                   d.nombreTipoDocumento,
                   COUNT(*) AS totalFacturas,
                   SUM(
                        CASE 
                            WHEN e.tipoDte IN ('01', '03') THEN e.totalPagar
                            WHEN e.tipoDte = '05' THEN e.montoTotalOperacion
                            WHEN e.tipoDte = '14' THEN e.totalCompra
                            ELSE 0
                        END
                   ) AS total
            FROM dte_encabezado e
            LEFT JOIN tiposDocumentos d ON e.tipoDte = d.codigoTipoDocumento
            WHERE DATE(e.fechaEmision) BETWEEN ? AND ? AND ambiente = '01'
            GROUP BY e.tipoDte, d.nombreTipoDocumento";

            $datos = [$desde, $hasta];
            return $this->selectAll($sql, $datos);
        } else {
            // Consulta normal filtrada
            $sql = "SELECT e.numeroControl,
                        e.codigoGeneracion,
                        e.receptor,
                        IFNULL(e.selloRecepcion,'---') AS selloRecepcion,
                        e.totalCompra,
                        e.totalPagar,
                        d.nombreTipoDocumento,
                        c.nombreCliente,
                        e.montoTotalOperacion,
                        e.fechaEmision
                FROM dte_encabezado e
                LEFT JOIN tiposDocumentos d ON e.tipoDte = d.codigoTipoDocumento
                LEFT JOIN clientes c ON e.receptor = c.codigoCliente
                WHERE DATE(e.fechaEmision) BETWEEN ? AND ? AND ambiente = '01'
                AND e.tipoDte = ?";

            $datos = [$desde, $hasta, $tipoDocumento];
            return $this->selectAll($sql, $datos);
        }
    }
    
};
?>
<?php
// Habilitar el reporte de todos los errores
error_reporting(E_ALL);
ini_set('display_errors', '1');
?>