<?php

class SujetoExcluidoModel extends Query
{
    private $numeroControl, $version, $tipoDte, $codigoGeneracion, $tipoModelo,
        $tipoOperacion, $tipoContingencia, $motivoContingencia, $fechaEmision,
        $horaEmision, $receptor, $totalDescu, $subTotal, $ivaRete1, $reteRenta,
        $totalPagar, $condicionOperacion, $totalCompra, $descu, $observaciones,
        $tipoMoneda, $selloRecepcion, $ambiente, $documentoFirmado, $estado,
        $fechaProcesamiento, $tipoMovimiento, $codigoProyecto, $eventoContingenciaId;

    public function __construct()
    {
        parent::__construct();
    }

    // buscar clientes
    public function searchCliente($query, $tipo = 'todos')
    {
        $condicionExtra = '';
        $params = ["%$query%"];

        // Agregar condición para clientes con NRC si aplica
        if ($tipo === 'conNrc') {
            $condicionExtra = " AND nrc IS NOT NULL AND nrc != ''";
        }

        $sql = "SELECT codigoCliente AS codigo,
                   nombreCliente AS nombre
            FROM clientes
            WHERE nombreCliente LIKE ? $condicionExtra
            ORDER BY fechaCreacion DESC
            LIMIT 5";

        return $this->selectAll($sql, $params);
    }

    // obtener tipo de movimiento
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

    // obtener proyectos
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

    // obtener tipo de operacion
    public function getCodigoOperacion($query)
    {
        $sql = "SELECT codigo,
                    nombre
                FROM condicionOperacion
                WHERE nombre LIKE ?
                AND codigo != 2 AND codigo != 3";

        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    //seleccionar tipo de pago
    public function searchTipoPago($query)
    {
        $sql = "SELECT id AS codigo,
        nombrePago AS nombre
        FROM tipoPago
        WHERE nombrePago LIKE ?
        AND id != '02' AND id != '03' AND id != '08' AND id != '09' AND id != '11' AND id != '12' AND id != '13' AND id != '14' AND id != '99'
        LIMIT 13 ";
        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    // buscar banco
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

    // obtener cuentas bancarias
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

    // seleccionar tipo de item
    public function getTipoItem($query)
    {
        $sql = "SELECT idTipoItem AS codigo,
                    valorItem AS nombre
                FROM tipoItem
                WHERE valorItem LIKE ? 
                AND idTipoItem != 4";

        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    // registrar los productos al detalle temporal
    public function registrarDetalle(string $idProducto, int $cantidad, string $costo, string $precio, string $total, string $idUsuario,  string $unidadMedida, $descuentoItem, $tipoItem, $precioSinIva)
    {
        // Contar cuántos ítems lleva ese usuario
        $sqlCount = "SELECT COUNT(*) AS total FROM detalleTemporal WHERE id_Usuario = ?";
        $row = $this->select($sqlCount, [$idUsuario]);
        $nuevoItem = $row['total'] + 1;

        // Insertar con el nuevo número de ítem
        $sql = "INSERT INTO detalleTemporal(item, codigoProducto, cantidad, costoProducto, precioVenta, total, id_Usuario, unidadMedida, descuentoItem, tipoDeItem, precioSinIva)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)";

        $datos = array($nuevoItem, $idProducto, $cantidad, $costo, $precio, $total, $idUsuario, $unidadMedida, $descuentoItem, $tipoItem, $precioSinIva);
        $data = $this->guardar($sql, $datos);

        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }

        return $res;
    }

    // obtener el detalle en vista al agregar productos
    public function getDetalle(string $id)
    {
        $sql = "SELECT d.id,
                        d.item,
                        i.valorItem,
                        d.cantidad,
                        m.nombre,
                        p.nombreProducto,
                        d.precioSinIva,
                        d.descuentoItem,
                        d.total,
                        d.tipoDeItem,
                        d.codigoProducto,
                        d.unidadMedida
        FROM detalleTemporal d
        LEFT JOIN productos p ON d.codigoProducto = p.codigoProducto
        LEFT JOIN medidas m ON d.unidadMedida = m.id
        LEFT JOIN tipoItem i ON d.tipoDeItem = i.idTipoItem
        WHERE d.id_Usuario = ?";
        $datos = array($id);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    // obtener id de detalle 
    public function obtenerDetalle($id)
    {
        $sql = "SELECT * FROM detalleTemporal WHERE id = ?";
        return $this->select($sql, [$id]);
    }

    // eliminar el producto seleccionado antes
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

    // registrar formas de pago en temporal
    public function registrarDetalleFormasPago(string $codigoId, $montoPago, $referencia, $plazo, $periodo, string $idUsuario, $condicionOperacion, $codigoBanco, $codigoCuentaBancaria)
    {
        // Insertar con el nuevo número de ítem
        $sql = "INSERT INTO detallesPagosDteTemporal(codigoId,montoPago,referencia,plazo,periodo,idUsuario,condicionOperacion,codigoBanco,codigoCuentaBancaria)
            VALUES (?,?,?,?,?,?,?,?,?)";

        $datos = array($codigoId, $montoPago, $referencia, $plazo, $periodo, $idUsuario, $condicionOperacion, $codigoBanco, $codigoCuentaBancaria);
        $data = $this->guardar($sql, $datos);

        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }

        return $res;
    }

    //listar formas de pago en vista
    public function getDetallePagos(string $id)
    {
        $sql = "SELECT d.id,
                        d.codigoId,
                        d.montoPago,
                        d.referencia,
                        d.plazo,
                        d.periodo,
                        d.condicionOperacion,
                        t.nombrePago,
                        c.nombre,
                        d.referencia
        FROM detallesPagosDteTemporal d
        LEFT JOIN tipoPago t ON d.codigoId = t.id
        LEFT JOIN condicionOperacion c ON d.condicionOperacion = c.codigo
        -- LEFT JOIN tipoItem i ON d.tipoDeItem = i.idTipoItem
        WHERE d.idUsuario = ?";
        $datos = array($id);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    // modelos para formas de pago listar su detalle y eliminar

    public function eliminarPagosD(int $id)
    {
        $sql = "DELETE FROM detallesPagosDteTemporal WHERE id = ?";
        $datos = array($id);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            // Si se eliminaron registros, reiniciar el AUTO_INCREMENT
            $sqlReset = "ALTER TABLE detallesPagosDteTemporal AUTO_INCREMENT = 1";
            $this->guardar($sqlReset, array());
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function vaciarDetalleProductos($idUsuario)
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

    public function vaciarDetallePagos($idUsuario)
    {
        $sql = "DELETE FROM detallesPagosDteTemporal WHERE idUsuario = ?";
        $datos = array($idUsuario);
        $data = $this->guardar($sql, $datos);

        if ($data == 1) {
            // Si se eliminaron registros, reiniciar el AUTO_INCREMENT
            $sqlReset = "ALTER TABLE detallesPagosDteTemporal AUTO_INCREMENT = 1";
            $this->guardar($sqlReset, array());

            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }

    // obtener si hay productos seleccionados
    public function comprobarProductos(string $id_Usuario)
    {
        $sql = "SELECT SUM(total) AS probar FROM detalleTemporal WHERE id_Usuario = ?";
        $datos = array($id_Usuario);
        $data = $this->select($sql, $datos);
        return $data;
    }

    // obtener si hay pagos seleccionados
    public function comprobarPagos(string $id_Usuario)
    {
        $sql = "SELECT SUM(montoPago) AS probar FROM detallesPagosDteTemporal WHERE idUsuario = ?";
        $datos = array($id_Usuario);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function getExistencia(string $codigoProducto, string $codigoProyecto)
    {
        $sql = "SELECT cantidadProducto FROM existencias WHERE codigoProducto = ? AND codigoProyecto = ?";
        $datos = array($codigoProducto, $codigoProyecto);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }


    //registrar el dte
    public function registrarDTE(
        string $numeroControl,
        int $version,
        string $tipoDte,
        string $codigoGeneracion,
        int $tipoModelo,
        int $tipoOperacion,
        $tipoContingencia,
        $motivoContingencia,
        string $fechaEmision,
        string $horaEmision,
        string $receptor,
        float $totalDescu,
        float $subTotal,
        float $ivaRete1,
        float $reteRenta,
        $totalPagar,
        int $condicionOperacion,
        float $totalCompra,
        float $descu,
        $observaciones,
        string $tipoMoneda,
        $selloRecepcion,
        $ambiente,
        $documentoFirmado,
        string $estado,
        $fechaProcesamiento,
        $tipoMovimiento,
        $codigoProyecto,
        $eventoContingenciaId
    ) {
        // Asignar valores a las propiedades del objeto
        $this->numeroControl = $numeroControl;
        $this->version = $version;
        $this->tipoDte = $tipoDte;
        $this->codigoGeneracion = $codigoGeneracion;
        $this->tipoModelo = $tipoModelo;
        $this->tipoOperacion = $tipoOperacion;
        $this->tipoContingencia = $tipoContingencia;
        $this->motivoContingencia = $motivoContingencia;
        $this->fechaEmision = $fechaEmision;
        $this->horaEmision = $horaEmision;
        $this->receptor = $receptor;
        $this->totalDescu = $totalDescu;
        $this->subTotal = $subTotal;
        $this->ivaRete1 = $ivaRete1;
        $this->reteRenta = $reteRenta;
        $this->totalPagar = $totalPagar;
        $this->condicionOperacion = $condicionOperacion;
        $this->totalCompra = $totalCompra;
        $this->descu = $descu;
        $this->observaciones = $observaciones;
        $this->tipoMoneda = $tipoMoneda;
        $this->selloRecepcion = $selloRecepcion;
        $this->ambiente = $ambiente;
        $this->documentoFirmado = $documentoFirmado;
        $this->estado = $estado;
        $this->fechaProcesamiento = $fechaProcesamiento;
        $this->tipoMovimiento = $tipoMovimiento;
        $this->codigoProyecto = $codigoProyecto;
        $this->eventoContingenciaId = $eventoContingenciaId;

        // Verificar si el DTE ya existe
        $verificar = "SELECT * FROM dte_encabezado WHERE numeroControl = ?";
        $existe = $this->select($verificar, [$this->numeroControl]);

        if (empty($existe)) {
            // Si no existe, insertar nuevo DTE
            $sql = "INSERT INTO dte_encabezado 
                (numeroControl, versionDte, tipoDte, codigoGeneracion, tipoModelo, tipoOperacion, 
                tipoContingencia, motivoContingencia, fechaEmision, horaEmision, receptor, totalDescu,
                subTotal, ivaRete1, reteRenta, totalPagar, condicionOperacion, totalCompra,
                descu, observacionesResumen, tipoMoneda, selloRecepcion, ambiente, documentoFirmado,
                estado, fhProcesamiento, tipoMovimiento, idProyecto, eventoContingenciaId) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

            $datos = array(
                $this->numeroControl,
                $this->version,
                $this->tipoDte,
                $this->codigoGeneracion,
                $this->tipoModelo,
                $this->tipoOperacion,
                $this->tipoContingencia,
                $this->motivoContingencia,
                $this->fechaEmision,
                $this->horaEmision,
                $this->receptor,
                $this->totalDescu,
                $this->subTotal,
                $this->ivaRete1,
                $this->reteRenta,
                $this->totalPagar,
                $this->condicionOperacion,
                $this->totalCompra,
                $this->descu,
                $this->observaciones,
                $this->tipoMoneda,
                $this->selloRecepcion,
                $this->ambiente,
                $this->documentoFirmado,
                $this->estado,
                $this->fechaProcesamiento,
                $this->tipoMovimiento,
                $this->codigoProyecto,
                $this->eventoContingenciaId,
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

    public function registrarDTEcuerpo(
        $numeroControl,
        $numItem,
        $tipoItem,
        $cantidad,
        $codigo,
        $uniMedida,
        $precioUni,
        $montoDescu,
        $compra

    ) {
        $sql = "INSERT INTO dte_cuerpo (
                    idNumeroControl, numItem, tipoItem,
                    cantidad, codigo, uniMedida, precioUni, montoDescu, compra
                ) VALUES (?,?,?,?,?,?,?,?,?)";

        $datos = array(
            $numeroControl,
            $numItem,
            $tipoItem,
            $cantidad,
            $codigo,
            $uniMedida,
            $precioUni,
            $montoDescu,
            $compra
        );
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }

        return $res;
    }

    public function registrarPagosDte(
        $numeroControl,
        $codigoId,
        $montoPago,
        $referencia,
        $plazo,
        $periodo,
        $idUsuario,
        $condicionOperacion,
        $codigoBanco,
        $codigoCuentaBancaria
    ) {

        $sql = "INSERT INTO detallesPagos (
                    numeroControlId, codigoId, montoPago,
                    referencia, plazo, periodo, idUsuario, condicionOperacion, codigoBanco,
                    codigoCuentaBancaria
                ) VALUES (?,?,?,?,?,?,?,?,?,?)";

        $datos = array(
            $numeroControl,
            $codigoId,
            $montoPago,
            $referencia,
            $plazo,
            $periodo,
            $idUsuario,
            $condicionOperacion,
            $codigoBanco,
            $codigoCuentaBancaria
        );
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }

        return $res;
    }

    public function getDetallePagosPararegistro($idUsuario)
    {
        $sql = "SELECT *
            FROM detallesPagosDteTemporal
            WHERE idUsuario = ?";
        $datos =  array($idUsuario);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    public function actualizarEstadoDte(string $selloRecepcion, string $estado, $fechaProcesamiento, $observaciones, string $numeroControl,)
    {
        $sql = "UPDATE dte_encabezado SET selloRecepcion = ?, estado = ?, fhProcesamiento = ?, observaciones = ? WHERE numeroControl = ?";
        $datos = array($selloRecepcion, $estado, $fechaProcesamiento, $observaciones, $numeroControl);
        return $this->guardar($sql, $datos);
    }

    
}
