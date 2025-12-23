<?php
class ComprasModel extends Query
{
    private $numeroDocumento, $documentoFe,
        $fecha, $codigoProveedor,
        $codigoCliente, $codigoTipoMovimiento,
        $total, $codigoProyecto,
        $observacion, $idProducto, $cantidad,
        $costo, $precio, $totalP;

    public function __construct()
    {
        parent::__construct();
    }

    // buscar producto select
    public function searchProyecto($query)
    {
        $sql = "SELECT codigoProyecto AS codigo,
        nombreProyecto AS nombre
        FROM proyectos
        WHERE nombreProyecto LIKE ?
        ORDER BY fechaCreacion DESC
        LIMIT 5 ";
        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    // Obtener el detalle temporal a tabla compras
    public function getDetalle(string $id)
    {
        $sql = "SELECT d.*, p.nombreProducto
        FROM detalleTemporal d
        INNER JOIN productos p ON d.codigoProducto = p.codigoProducto
        WHERE d.id_Usuario = ?";
        $datos = array($id);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    // se calcula el total de los detalles extraidos 
    public function calcularCompra(string $id_Usuario)
    {
        $sql = "SELECT SUM(total) AS totalPagar FROM detalleTemporal WHERE id_Usuario = ?";
        $datos = array($id_Usuario);
        $data = $this->select($sql, $datos);
        return $data;
    }

    //Eliminar detalles de la tabla de seleccion compra
    public function EDetalle(int $id)
    {
        $sql = "DELETE FROM detalleTemporal WHERE id = ?";
        $datos = array($id);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            // Si se eliminaron registros, reiniciar el AUTO_INCREMENT
            $sqlReset = "ALTER TABLE detalleTemporal AUTO_INCREMENT = 1";
            $this->guardar($sqlReset, array());
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function obtenerDetalle($id)
    {
        $sql = "SELECT * FROM detalleTemporal WHERE id = ?";
        return $this->select($sql, [$id]);
    }


    public function reordenarItems($idUsuario)
    {
        $sql = "SELECT id FROM detalleTemporal WHERE id_Usuario = ? ORDER BY item ASC";
        $detalles = $this->selectAll($sql, [$idUsuario]);

        $item = 1;
        foreach ($detalles as $detalle) {
            $sqlUpdate = "UPDATE detalleTemporal SET item = ? WHERE id = ?";
            $this->guardar($sqlUpdate, [$item, $detalle['id']]);
            $item++;
        }
    }


    //Registar el encabezado de la compra
    public function registrarCompra(
        string $numeroDocumento,
        string $documentoFe,
        string $fecha,
        string $codigoProveedor,
        $codigoCliente,
        string $codigoTipoMovimiento,
        float $total,
        string $codigoProyecto,
        string $observacion
    ) {
        $this->numeroDocumento = $numeroDocumento;
        $this->documentoFe = $documentoFe;
        $this->fecha = $fecha;
        $this->codigoProveedor = $codigoProveedor;
        $this->codigoCliente = empty($codigoCliente) ? NULL : $codigoCliente;
        $this->codigoTipoMovimiento = $codigoTipoMovimiento;
        $this->total = $total;
        $this->codigoProyecto = $codigoProyecto;
        $this->observacion = $observacion;

        $verificar = "SELECT * FROM encabezadoMovimiento WHERE numeroDocumento = ?";
        $existe = $this->select($verificar, [$this->numeroDocumento]);

        if (empty($existe)) {
            $sql = "INSERT INTO encabezadoMovimiento (numeroDocumento, numeroDocumentoFe, fecha, codigoProveedor, codigoCliente, codigoTipoMovimiento, total, codigoProyecto, observacion)
                VALUES (?,?,?,?,?,?,?,?,?)";
            $datos = array(
                $this->numeroDocumento,
                $this->documentoFe,
                $this->fecha,
                $this->codigoProveedor,
                $this->codigoCliente,
                $this->codigoTipoMovimiento,
                $this->total,
                $this->codigoProyecto,
                $this->observacion
            );
            $data = $this->guardar($sql, $datos);
            if ($data == 1) {
                $res = "ok";
            } else {
                $res = "error";
            }
        } else {
            $res = "existe";
        }
        return $res;
    }

    //Registrar el detalle de la compra
    public function registrarDetalleCompra(string $numeroDocumento, string $documentoFe, string $idProducto, int $cantidad, float $costo, float $precio, float $totalP, string $codigoProyecto)
    {
        $this->numeroDocumento = $numeroDocumento;
        $this->documentoFe = $documentoFe;
        $this->idProducto = $idProducto;
        $this->cantidad = $cantidad;
        $this->costo = $costo;
        $this->precio = $precio;
        $this->totalP = $totalP;
        $this->codigoProyecto = $codigoProyecto;

        $sql = "INSERT INTO detalleMovimiento (numeroDocumento, numeroDocumentoFe, codigoProducto, cantidad, costoProducto, precioVenta, total, codigoProyecto)
                    VALUES (?,?,?,?,?,?,?,?)";
        $datos = array(
            $this->numeroDocumento,
            $this->documentoFe,
            $this->idProducto,
            $this->cantidad,
            $this->costo,
            $this->precio,
            $this->totalP,
            $this->codigoProyecto,
        );
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {

            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }

    //Obtener datos de factura para el pdf de compra 
    public function getEmpresa()
    {
        $sql = "SELECT * FROM empresa";
        $data = $this->select($sql);
        return $data;
    }

    // cada que se realiza la compra se vacian los datos de ese ususario en temporal detalle
    public function vaciarDetalle($idUsuario)
    {
        // Eliminar los registros del usuario
        $sql = "DELETE FROM detalleTemporal WHERE id_Usuario = ?";
        $datos = array($idUsuario);
        $data = $this->guardar($sql, $datos);

        if ($data == 1) {
            // Si se eliminaron registros, reiniciar el AUTO_INCREMENT
            $sqlReset = "ALTER TABLE detalleTemporal AUTO_INCREMENT = 1";
            $this->guardar($sqlReset, array());

            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }

    //Obtener existencias de productos para usar en actualizacion
    public function getExistencia(string $codigoProducto, string $codigoProyecto)
    {
        $sql = "SELECT cantidadProducto FROM existencias WHERE codigoProducto = ? AND codigoProyecto = ?";
        $datos = array($codigoProducto, $codigoProyecto);
        $data = $this->select($sql, $datos);
        return $data;
    }

    // Para actualizar existencias de productos
    public function actualizarExistencias(int $cantidad, string $codigoProyecto, string $codigoProducto)
    {
        // Verificar si el producto ya existe para ese proyecto
        $verificar = "SELECT * FROM existencias WHERE codigoProyecto = ? AND codigoProducto = ?";
        $existe = $this->select($verificar, [$codigoProyecto, $codigoProducto]);

        if (empty($existe)) {
            // Insertar si no existe
            $sql = "INSERT INTO existencias (codigoProducto, cantidadProducto, codigoProyecto) VALUES (?, ?, ?)";
            $datos = array($codigoProducto, $cantidad, $codigoProyecto);
        } else {
            // Actualizar cantidad si el proyecto existe
            $sql = "UPDATE existencias SET cantidadProducto = ? WHERE codigoProducto = ? AND codigoProyecto = ?";
            $datos = array($cantidad, $codigoProducto, $codigoProyecto);
        }

        $data = $this->guardar($sql, $datos);
        return ($data == 1) ? "ok" : "error";
    }



    // Obtener el saldo para utilizar en la actualizacion
    public function getSaldoProveedor(string $codigoProveedor)
    {
        $sql = "SELECT saldoProveedor FROM proveedores WHERE codigoProveedor = ?";
        $datos = array($codigoProveedor);
        return $this->select($sql, $datos);
    }


    // Actualizar saldo de proveedor cada que se registra una compra
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

    // Obtener el limite para advertencia
    public function getLimiteProveedor(string $codigoProveedor)
    {
        $sql = "SELECT limiteCreditoProveedor FROM proveedores WHERE codigoProveedor = ?";
        $datos = array($codigoProveedor);
        return $this->select($sql, $datos);
    }

    //Obtener existencias de productos para usar en actualizacion
    public function getSalidaProyecto(string $codigoProyecto)
    {
        $sql = "SELECT salidas FROM proyectos WHERE codigoProyecto = ? ";
        $datos = array($codigoProyecto);
        $data = $this->select($sql, $datos);
        return $data;
    }

    // Para actualizar existencias de productos
    public function actualizarSalidaProyecto(float $salidas, string $codigoProyecto)
    {
        $sql = "UPDATE proyectos SET salidas = ? WHERE codigoProyecto = ?";
        $datos = array($salidas, $codigoProyecto);

        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function vaciarDetalleCancelar($idUsuario)
    {
        $sql = "DELETE FROM detalleTemporal WHERE id_Usuario = ?";
        $datos = array($idUsuario);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            // Si se eliminaron registros, reiniciar el AUTO_INCREMENT
            $sqlReset = "ALTER TABLE detalleTemporal AUTO_INCREMENT = 1";
            $this->guardar($sqlReset, array());
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }

    // para generar pdf
    public function getProCompra($numeroDocumento)
    {
        $sql = "SELECT e.numeroDocumento, e.numeroDocumentoFe, e.fecha, e.codigoTipoMovimiento, t.nombreMovimiento, e.codigoProveedor, pr.nombreProveedor, e.codigoProyecto, py.nombreProyecto, e.observacion, d.codigoProducto, p.nombreProducto, d.cantidad, d.costoProducto, d.precioVenta, d.total
                FROM encabezadoMovimiento e
                INNER JOIN detalleMovimiento d ON e.numeroDocumento = d.numeroDocumento 
                INNER JOIN productos p ON d.codigoProducto = p.codigoProducto 
                INNER JOIN tipoMovimiento t ON e.codigoTipoMovimiento = t.codigoTipoMovimiento
                INNER JOIN proveedores pr ON e.codigoProveedor = pr.codigoProveedor
                INNER JOIN proyectos py ON e.codigoProyecto = py.codigoProyecto
                WHERE e.numeroDocumento = ?";
        $datos = array($numeroDocumento);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    public function historialCompras($start, $length, $search = "")
    {
        $searchQuery = "";
        $params = [];

        if (!empty($search)) {
            $searchQuery = "WHERE fecha = :search";
            $params = [':search' => $search];
        }

        $sql = "SELECT e.numeroDocumento AS codigo,
                    e.numeroDocumentoFe AS fe,
                    e.codigoProveedor AS cod,
                    p.nombreProveedor AS proveedor,
                    DATE(e.fecha) AS fecha
                FROM encabezadoMovimiento e
                INNER JOIN proveedores p ON e.codigoProveedor = p.codigoProveedor
                $searchQuery
                ORDER BY e.fecha_hora DESC
                LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        $sqlTotal = "SELECT COUNT(*) AS total FROM encabezadoMovimiento en $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        return [
            'encabezadoMov' => $data,
            'total' => $total
        ];
    }

    public function actualizarPrecioProducto(float $precioCosto, float $precioVenta, string $codigoProducto)
    {
        $sql = "UPDATE productos SET costoProducto = ?, precioVenta = ? WHERE codigoProducto = ?";
        $datos = array($precioCosto, $precioVenta, $codigoProducto);

        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }
}
