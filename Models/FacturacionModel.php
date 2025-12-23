<?php
class FacturacionModel extends Query
{
    private $version, $numeroControl,
        $tipoDte,
        $codigoGeneracion,
        $tipoModelo,
        $tipoOperacion,
        $tipoContingencia,
        $motivoContingencia,
        $fechaEmision,
        $horaEmision,
        $receptor,
        $totalNoSujeta,
        $totalExenta,
        $totalGravada,
        $subTotalVentas,
        $descuNoSujeta,
        $descuExenta,
        $descuGravada,
        $porcentajeDescuento,
        $totalDescu,
        $tributosCodigo,
        $tributosValor,
        $subTotal,
        $ivaPerci1,
        $ivaRete1,
        $reteRenta,
        $montoTotalOperacion,
        $totalNoGravado,
        $totalPagar,
        $totalIva,
        $saldoFavor,
        $condicionOperacion,
        $pagoCodigo,
        $pagoMontoPago,
        $referencia,
        $plazo,
        $periodo,
        $numPagoElectronico,
        $selloRecepcion,
        $ambiente,
        $documentoFirmado,
        $estado,
        $fechaProcesamiento,
        $tipoMovimiento,
        $idProyecto,
        $eventoContingencia,
        $descripcionNotaC,
        $codigoBanco,
        $codigoCuentaBancaria;

    public function __construct()
    {
        parent::__construct();
    }

    public function searchTipoDocumentoFE()
    {
        // Modificar la consulta para filtrar por el código '01'
        $sql = "SELECT codigoTipoDocumento,
                    nombreTipoDocumento
                FROM tiposDocumentos
                WHERE nombreTipoDocumento IN ('Nota de remisión', 'Documento contable de liquidación')
                ORDER BY fechaCreacion ASC";
        return $this->selectAll($sql);
    }

    public function searchTipoDocumentoCFE()
    {
        // Modificar la consulta para filtrar por el código '01'
        $sql = "SELECT codigoTipoDocumento,
                    nombreTipoDocumento
                FROM tiposDocumentos
                WHERE nombreTipoDocumento IN ('Nota de remisión', 'Comprobante de liquidación', 'Documento contable de liquidación')
                ORDER BY fechaCreacion ASC";
        return $this->selectAll($sql);
    }

    public function searchTipoDocumentoNC()
    {
        // Modificar la consulta para filtrar por el código '01'
        $sql = "SELECT codigoTipoDocumento,
                    nombreTipoDocumento
                FROM tiposDocumentos
                WHERE nombreTipoDocumento IN ('Comprobante de crédito fiscal')
                ORDER BY fechaCreacion ASC";
        return $this->selectAll($sql);
    }

    public function getClienteNrc($codigoCliente)
    {
        $sql = "SELECT nrc FROM clientes
                WHERE codigoCliente = ?";
        $datos = array($codigoCliente);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function getTipoDocumentoRelacionado()
    {
        $sql = "SELECT * FROM tiposDocumentos
                WHERE codigoTipoDocumento IN ('04', '09');";
        $data = $this->select($sql);
        return $data;
    }

    public function getTipoDocumento($codigoTipoDocumento)
    {
        $sql = "SELECT codigoTipoDocumento,
                        nombreTipoDocumento 
                FROM tiposDocumentos 
                WHERE codigoTipoDocumento = ?";
        $datos = array($codigoTipoDocumento);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function getProducto($id)
    {
        $sql = "SELECT * FROM productos WHERE codigoProducto = ? ";
        $datos = array($id);
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

    public function actualizarExistencias(int $cantidad, string $codigoProyecto, $codigoProducto)
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

    public function consultarDetalle($codigoProducto, $idUsuario)
    {
        $sql = "SELECT * FROM detalleTemporal WHERE codigoProducto = ? AND id_Usuario = ?";
        $datos = array($codigoProducto, $idUsuario);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function obtenerUltimoIdDetalle($idUsuario)
    {
        $sql = "SELECT MAX(id) AS max_id FROM detalleTemporal WHERE id_Usuario = ?";
        return $this->select($sql, [$idUsuario]);
    }


    public function registrarDetalle(string $idProducto, $cantidad, string $costo, string $precio, string $total, string $idUsuario, $documentoRelacionado, string $unidadMedida, $descripcionNo, $descripcionNota)
    {
        // Contar cuántos ítems lleva ese usuario
        $sqlCount = "SELECT COUNT(*) AS total FROM detalleTemporal WHERE id_Usuario = ?";
        $row = $this->select($sqlCount, [$idUsuario]);
        $nuevoItem = $row['total'] + 1;

        // Insertar con el nuevo número de ítem
        $sql = "INSERT INTO detalleTemporal(item, codigoProducto, cantidad, costoProducto, precioVenta, total, id_Usuario, documentoRelacionado, unidadMedida, descripcionN, descripcionNota)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)";

        $datos = array($nuevoItem, $idProducto, $cantidad, $costo, $precio, $total, $idUsuario, $documentoRelacionado, $unidadMedida, $descripcionNo, $descripcionNota);
        $data = $this->guardar($sql, $datos);

        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }

        return $res;
    }


    public function actualizarDetalle($totalCantidad, float $costoProducto, float $precioVenta, string $totalActualizar, $documentoRelacionado, string $unidadMedida, string $idProducto, string $idUsuario)
    {
        $sql = "UPDATE detalleTemporal SET cantidad = ?, costoProducto = ?, precioVenta = ?, total = ?, documentoRelacionado = ?, unidadMedida = ? WHERE codigoProducto = ? AND id_Usuario = ?";
        $datos = array($totalCantidad, $costoProducto, $precioVenta, $totalActualizar, $documentoRelacionado, $unidadMedida, $idProducto, $idUsuario);
        $data = $this->guardar($sql, $datos);
        return ($data == 1) ? "modificado" : "error";
    }

    public function limpiarDocumentoRelacionado(string $numeroDoc, string $idUsuario)
    {
        $sql = "UPDATE detalleTemporal SET documentoRelacionado = NULL 
            WHERE documentoRelacionado = ? AND id_Usuario = ?";
        $datos = array($numeroDoc, $idUsuario);
        return $this->guardar($sql, $datos);
    }


    public function getDetalle(string $id)
    {
        $sql = "SELECT d.*, p.nombreProducto, m.nombre
        FROM detalleTemporal d
        LEFT JOIN productos p ON d.codigoProducto = p.codigoProducto
        LEFT JOIN medidas m ON d.unidadMedida = m.id
        WHERE d.id_Usuario = ?";
        $datos = array($id);
        $data = $this->selectAll($sql, $datos);
        return $data;
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


    public function getTipoGeneracion()
    {
        return [
            "tipoGeneracion" => [
                '01' => [
                    'nombre' => 'Físico'
                ],
                '02' => [
                    'nombre' => 'Electronico'
                ]
            ]
        ];
    }
    // modelo para los correlativos
   public function generarCorrelativos($codigoTipoDocumento, $ambiente)
    {
        $anio = date('Y'); // ahora es '2025', no '25'

        try {
            $sql = "SELECT ultimo_correlativo FROM correlativosDte 
                WHERE anio = ? AND tipoDocumento = ? AND ambiente = ? FOR UPDATE";

            $params = [$anio, $codigoTipoDocumento, $ambiente];
            $row = $this->select($sql, $params);

            if ($row) {
                $nuevo = $row['ultimo_correlativo'] + 1;
                $sqlUpdate = "UPDATE correlativosDte 
                        SET ultimo_correlativo = ? 
                        WHERE anio = ? AND tipoDocumento = ? AND ambiente = ?";
                $this->guardar($sqlUpdate, [$nuevo, $anio, $codigoTipoDocumento, $ambiente]);
            } else {
                $nuevo = 1;
                $sqlInsert = "INSERT INTO correlativosDte (anio, tipoDocumento, ambiente, ultimo_correlativo)
                        VALUES (?, ?, ?, ?)";
                $this->guardar($sqlInsert, [$anio, $codigoTipoDocumento, $ambiente, $nuevo]);
            }

            return str_pad($nuevo, 15, "0", STR_PAD_LEFT);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getEmpresa()
    {
        $sql = "SELECT * FROM empresa";
        $data = $this->select($sql);
        return $data;
    }

    public function getDocAsociado()
    {
        return [
            "codDocAsociados" => [
                1 => [
                    'nombre' => 'Emisor'
                ],
                2 => [
                    'nombre' => 'Receptor'
                ],
                3 => [
                    'nombre' => 'Médico'
                ],
            ]
        ];
    }

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

    public function getUnidadMedida($query)
    {
        $sql = "SELECT id AS codigo,
                    nombre
                FROM medidas
                WHERE nombre LIKE ? 
                AND id != 99
                LIMIT 40";

        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }


    public function getTipoServicioMedico()
    {
        return [
            "tipoServicioMedico" => [
                1 => [
                    'nombre' => 'Cirujia'
                ],
                2 => [
                    'nombre' => 'Operación'
                ],
                3 => [
                    'nombre' => 'Tratamiento Medico'
                ],
                4 => [
                    'nombre' => 'Cirujia instituto salvadoreño de Bienestar Magisterial'
                ],
                5 => [
                    'nombre' => 'Operación Instituto Salvadoreño de Bienestar Magisterial'
                ],
                6 => [
                    'nombre' => 'Tratamiento médico Isntituto Salvadoreño de Bienestar Magisterial'
                ],
            ]
        ];
    }

    public function getCliente($codigoCliente)
    {
        $sql = "SELECT c.* ,
        a.valor
            FROM clientes c
                LEFT JOIN actividadesEconomicas a ON c.codigoActividadEconomica = a.codigo
                WHERE codigoCliente = ?";
        $datos = array($codigoCliente);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function comprobar(string $id_Usuario)
    {
        $sql = "SELECT SUM(total) AS probar FROM detalleTemporal WHERE id_Usuario = ?";
        $datos = array($id_Usuario);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function getNombreModeloFacturacion($codigo)
    {
        $sql = "SELECT valor FROM modeloFacturacion WHERE codigo = ?";
        $datos = array($codigo);
        $data = $this->select($sql, $datos);
        return $data['valor'];
    }


    public function getTipoModeloTransmision()
    {
        return [
            "modeloTransmision" => [
                1 => [
                    'nombre' => 'Transmision Normal'
                ],
                2 => [
                    'nombre' => 'Transmision por contingencia'
                ],
            ]
        ];
    }

    public function getNombreCondicionOperacion($codigo)
    {
        $sql = "SELECT nombre FROM condicionOperacion WHERE codigo = ?";
        $params = [$codigo];
        $resultado = $this->select($sql, $params);

        return $resultado ? $resultado['nombre'] : 'Desconocido';
    }

    public function datosFactura()
    {
        return [
            // SECCION 1 IDENTIFICACION
            'ambiente' => "01", //modo prueba // modo produccion(01)

            //datos para generar el numero de control definidos
            'codigoCasaMatriz' => "M001",
            'codigoPuntoVenta' => "P001",

            'tipoMoneda' => "USD",


            //SECCION 3 DATOS DE EMISOR
            // datos emisor
            // 'nit' => "12345678901234", //requerido
            // 'nrc' => "2603130", //requerido se puede enviar null si no tiene nrc activo
            // 'codActividadEmisor' => "00000", // solamente prueba buscar en pagina 26 catalogos //requerido
            // 'descActividad' => "Nombre Actividad Economica", // solamente prueba buscar en pagina 26 catalogos //requerido
            // 'nombreComercial' => 'este campo es opcional',
            // 'tipoEstablecimiento' => "02", // casa matriz //requerido
            // 'departamento' => "02", //santa ana //requerido
            // 'municipio' => "14", // campo relleno falta colocar municipio correcto // requerido
            // 'complemento' => "direccion de casa matriz, sucursal, agencia donde se realiza la operacion", //requerido
            // 'telefono' => "00000000", //requerido
            // 'correo' => "prueba@gmail.com", //requerido
            'codEstableMH' => 'M001', //opcional
            'codEstable' => null, //opcional
            'codPuntoVentaMH' => 'P001', //opcional
            'codPuntoVenta' => null, //opcional
        ];
    }


    // Modelos de evento contingencia
    public function searchModelofacturacion()
    {
        $sql = "SELECT codigo, valor AS nombre 
                FROM modeloFacturacion 
                WHERE codigo = 2 
                LIMIT 1";

        return $this->select($sql); // select() devuelve un array asociativo
    }


    public function searchTipoTransmision()
    {
        // Modificar la consulta para filtrar por el código '01'
        $sql = "SELECT codigo,
                    valor AS nombre
                FROM tipoTransmision
                WHERE codigo = 2
                LIMIT 1";

        return $this->select($sql);
    }

    public function searchTipoContingencia($query)
    {
        // Modificar la consulta para filtrar por el código '01'
        $sql = "SELECT codigo,
                    valor AS nombre
                FROM tipoContingencia
                WHERE valor LIKE ? 
                LIMIT 10";

        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    public function registrarContingencia(int $modeloFacturacion, int $tipoTransmision, int $tipoContingencia, $motivoContingencia, $fechaInicio, $fechaFin, int $estado)
    {
        $res = "error"; // Valor por defecto
        $verificar = "SELECT * FROM historialEventoContingencia WHERE estadoContingenciaId = 1";
        $contingenciaActiva = $this->select($verificar);

        if (empty($contingenciaActiva)) {
            $sql = "INSERT INTO historialEventoContingencia(modeloFacturacion, tipoTransmision, tipoContingencia, motivoContingencia, fechaInicio, fechaFin, estadoContingenciaId)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $datos = array($modeloFacturacion, $tipoTransmision, $tipoContingencia, $motivoContingencia, $fechaInicio, $fechaFin, $estado);
            $data = $this->guardar($sql, $datos);
            $res = ($data == 1) ? "ok" : "error";
        } else {
            $res = "activa";
        }

        return $res;
    }

    public function modificarContingencia($fechaFin, int $estado)
    {
        $sql = "UPDATE historialEventoContingencia SET fechaFin = ?, estadoContingenciaId = ? WHERE estadoContingenciaId = 1";
        $datos = array($fechaFin, $estado);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }


        return $res;
    }

    public function editarContingencia()
    {
        $sql = "SELECT
                    tipoContingencia AS tipoContin,
                    t.valor AS valor,
                    IFNULL(motivoContingencia, '---') AS motivo
                FROM historialEventoContingencia h
                INNER JOIN tipoContingencia t ON h.tipoContingencia = t.codigo
                WHERE estadoContingenciaId = 1
                ORDER BY id DESC LIMIT 1"; // por si hay más de uno
        $data = $this->select($sql); // asegúrate de que este método retorna UNA sola fila
        return $data;
    }

    public function obtenerEstadoContingencia()
    {
        // Modificar la consulta para filtrar por el código '01'
        $sql = "SELECT
                estadoContingenciaId
                FROM historialEventoContingencia where estadoContingenciaId = 1";

        return $this->select($sql);
    }

    public function datosContingencia()
    {
        $sql = "SELECT
                    *
                FROM historialEventoContingencia 
                WHERE estadoContingenciaId = 1";
        $data = $this->select($sql); // asegúrate de que este método retorna UNA sola fila
        return $data;
    }

    public function obtenerIdContingenciaActiva(int $estado)
    {
        $sql = "SELECT id FROM historialEventoContingencia WHERE estadoContingenciaId  = ?";
        $datos = array($estado);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function getNombreDepartamento($codigo)
    {
        $sql = "SELECT valor FROM departamentos WHERE codigo_departamento  = ?";
        $datos = array($codigo);
        $data = $this->select($sql, $datos);
        return $data['valor'];
    }

    public function getNombreMunicipio($codigoMunicipio, $codigoDepartamento)
    {
        $sql = "SELECT valor FROM municipios WHERE codigo = ? AND codigoDepartamento  = ?";
        $datos = array($codigoMunicipio, $codigoDepartamento);
        $data = $this->select($sql, $datos);
        return $data['valor'];
    }


    public function registrarDocRelacionados($numeroDocumento, $tipoDocumento, $tipoGeneracion, $fechaEmision, $numeroDeControl)
    {
        $sql = "INSERT INTO historialDocumentosRelacionadosDTE(numeroDocumento, tipoDocumento, tipoGeneracion, fechaEmision, numeroDeControl)
        VALUES (?,?,?,?,?)";
        $datos = array($numeroDocumento, $tipoDocumento, $tipoGeneracion, $fechaEmision, $numeroDeControl);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }

        return $res;
    }

    public function registrarDocAsociados($codigoAsociado, $descDocumento, $detalleDocumento, $nombre, $nit, $docIdentificacion, $tipoServicio, $numeroControl)
    {
        $sql = "INSERT INTO historialDocAsociados(codigoAsociado, descDocumento, detalleDocumento, nombre, nit, docIdentificacion, tipoServicio, numeroDeControl)
        VALUES (?,?,?,?,?,?,?,?)";
        $datos = array($codigoAsociado, $descDocumento, $detalleDocumento, $nombre, $nit, $docIdentificacion, $tipoServicio, $numeroControl);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }

        return $res;
    }

    public function registrarVentaTerceros($nit, $nombre, $numeroControl)
    {
        $sql = "INSERT INTO historialVentaTerceros(nit, nombre, codigoDeControl)
        VALUES (?,?,?)";
        $datos = array($nit, $nombre, $numeroControl);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }

        return $res;
    }

    // Registrar DTEs
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
        float $totalNoSujeta,
        float $totalExenta,
        float $totalGravada,
        float $subTotalVentas,
        float $descuNoSujeta,
        float $descuExenta,
        float $descuGravada,
        $porcentajeDescuento,
        float $totalDescu,
        $tributosCodigo,
        $tributosValor,
        float $subTotal,
        $ivaPerci1,
        float $ivaRete1,
        float $reteRenta,
        float $montoTotalOperacion,
        $totalNoGravado,
        $totalPagar,
        $totalIva,
        $saldoFavor,
        int $condicionOperacion,
        $pagoCodigo,
        $pagoMontoPago,
        $referencia,
        $plazo,
        $periodo,
        $numPagoElectronico,
        $selloRecepcion,
        string $ambiente,
        string $documentoFirmado,
        $estado,
        $fechaProcesamiento,
        string $tipoMovimiento,
        string $idProyecto,
        $eventoContingencia,
        $codigoBanco,
        $codigoCuentaBancaria
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
        $this->totalNoSujeta = $totalNoSujeta;
        $this->totalExenta = $totalExenta;
        $this->totalGravada = $totalGravada;
        $this->subTotalVentas = $subTotalVentas;
        $this->descuNoSujeta = $descuNoSujeta;
        $this->descuExenta = $descuExenta;
        $this->descuGravada = $descuGravada;
        $this->porcentajeDescuento = $porcentajeDescuento;
        $this->totalDescu = $totalDescu;
        $this->tributosCodigo = $tributosCodigo;
        $this->tributosValor = $tributosValor;
        $this->subTotal = $subTotal;
        $this->ivaPerci1 = $ivaPerci1;
        $this->ivaRete1 = $ivaRete1;
        $this->reteRenta = $reteRenta;
        $this->montoTotalOperacion = $montoTotalOperacion;
        $this->totalNoGravado = $totalNoGravado;
        $this->totalPagar = $totalPagar;
        $this->totalIva = $totalIva;
        $this->saldoFavor = $saldoFavor;
        $this->condicionOperacion = $condicionOperacion;
        $this->pagoCodigo = $pagoCodigo;
        $this->pagoMontoPago = $pagoMontoPago;
        $this->referencia = $referencia;
        $this->plazo = $plazo;
        $this->periodo = $periodo;
        $this->numPagoElectronico = $numPagoElectronico;
        $this->selloRecepcion = $selloRecepcion;
        $this->ambiente = $ambiente;
        $this->documentoFirmado = $documentoFirmado;
        $this->estado = $estado;
        $this->fechaProcesamiento = $fechaProcesamiento;
        $this->tipoMovimiento = $tipoMovimiento;
        $this->idProyecto = $idProyecto;
        $this->eventoContingencia = $eventoContingencia;
        $this->codigoBanco = $codigoBanco;
        $this->codigoCuentaBancaria = $codigoCuentaBancaria;

        // Verificar si el DTE ya existe
        $verificar = "SELECT * FROM dte_encabezado WHERE numeroControl = ?";
        $existe = $this->select($verificar, [$this->numeroControl]);

        if (empty($existe)) {
            // Si no existe, insertar nuevo DTE
            $sql = "INSERT INTO dte_encabezado 
                (numeroControl, versionDte, tipoDte, codigoGeneracion, tipoModelo, tipoOperacion, 
                tipoContingencia, motivoContingencia, fechaEmision, horaEmision, 
                receptor, totalNoSujeta, totalExenta, totalGravada, 
                subTotalVentas, descuNoSujeta, descuExenta, descuGravada, porcentajeDescuento, 
                totalDescu, tributosCodigo, tributosValor, subTotal, ivaPerci1, ivaRete1, reteRenta, montoTotalOperacion, 
                totalNoGravado, totalPagar, totalIva, saldoFavor, condicionOperacion, pagoCodigo, pagoMontoPago, 
                referencia, plazo, periodo, numPagoElectronico, selloRecepcion, ambiente, documentoFirmado, estado, fhProcesamiento, tipoMovimiento, idProyecto, eventoContingenciaId, codigoBanco, codigoCuentaBancaria) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

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
                $this->totalNoSujeta,
                $this->totalExenta,
                $this->totalGravada,
                $this->subTotalVentas,
                $this->descuNoSujeta,
                $this->descuExenta,
                $this->descuGravada,
                $this->porcentajeDescuento,
                $this->totalDescu,
                $this->tributosCodigo,
                $this->tributosValor,
                $this->subTotal,
                $this->ivaPerci1,
                $this->ivaRete1,
                $this->reteRenta,
                $this->montoTotalOperacion,
                $this->totalNoGravado,
                $this->totalPagar,
                $this->totalIva,
                $this->saldoFavor,
                $this->condicionOperacion,
                $this->pagoCodigo,
                $this->pagoMontoPago,
                $this->referencia,
                $this->plazo,
                $this->periodo,
                $this->numPagoElectronico,
                $this->selloRecepcion,
                $this->ambiente,
                $this->documentoFirmado,
                $this->estado,
                $this->fechaProcesamiento,
                $this->tipoMovimiento,
                $this->idProyecto,
                $this->eventoContingencia,
                $this->codigoBanco,
                $this->codigoCuentaBancaria,
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
        $idNumeroDocumento,
        $cantidad,
        $codigo,
        $codTributo,
        $uniMedida,
        $descripcion,
        $precioUni,
        $montoDescu,
        $ventaNoSuj,
        $ventaExenta,
        $ventaGrabada,
        $tributos,
        $psv,
        $noGrabado,
        $ivaItem,
        $descripcionNotaC
    ) {
        $sql = "INSERT INTO dte_cuerpo (
                    idNumeroControl, numItem, tipoItem, idNumeroDocumento,
                    cantidad, codigo, codTributo, uniMedida, descripcion,
                    precioUni, montoDescu, ventaNoSuj, ventaExenta,
                    ventaGrabada, tributos, psv, noGrabado, ivaItem, descripcionNotaC
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)";

        $datos = array(
            $numeroControl,
            $numItem,
            $tipoItem,
            $idNumeroDocumento,
            $cantidad,
            $codigo,
            $codTributo,
            $uniMedida,
            $descripcion,
            $precioUni,
            $montoDescu,
            $ventaNoSuj,
            $ventaExenta,
            $ventaGrabada,
            $tributos,
            $psv,
            $noGrabado,
            $ivaItem,
            $descripcionNotaC
        );
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }

        return $res;
    }

    // registrar web token 24 horas
    public function getWebToken()
    {
        $sql = "SELECT * FROM webToken WHERE fecha_expira > NOW() ORDER BY token DESC LIMIT 1";
        return $this->select($sql);
    }

    public function guardarToken(
        $token,
        $fecha_obtenido,
        $fecha_expira,
    ) {
        $sql = "INSERT INTO webToken ( token, fecha_obtenido, fecha_expira) VALUES (?, ?, ?)";

        $datos = array(
            $token,
            $fecha_obtenido,
            $fecha_expira
        );
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }

        return $res;
    }

    public function actualizarToken($token, $fecha_obtenido, $fecha_expira)
    {
        $sql = "UPDATE webToken SET token = ?, fecha_obtenido = ?, fecha_expira = ?";
        $datos = [$token, $fecha_obtenido, $fecha_expira];
        return $this->guardar($sql, $datos); // Usa el mismo método que insert
    }

    public function getDTEdte_encabezado($numeroDeControl)
    {
        $sql = "SELECT selloRecepcion FROM dte_encabezado WHERE numeroControl = ?";
        $datos = array($numeroDeControl);
        $data = $this->select($sql, $datos);
        return $data['selloRecepcion'];
    }

    public function actualizarEstadoDte(string $selloRecepcion, string $estado, $fechaProcesamiento, $observaciones, string $numeroControl,)
    {
        $sql = "UPDATE dte_encabezado SET selloRecepcion = ?, estado = ?, fhProcesamiento = ?, observaciones = ? WHERE numeroControl = ?";
        $datos = array($selloRecepcion, $estado, $fechaProcesamiento, $observaciones, $numeroControl);
        return $this->guardar($sql, $datos);
    }

    public function getDte($codigoGeneracion)
    {
        $sql = "SELECT * FROM dte_encabezado WHERE codigoGeneracion = ?";
        $datos = array($codigoGeneracion);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    public function getDtePorNumeroControl($numeroControl)
    {
        $sql = "SELECT * FROM dte_encabezado WHERE numeroControl = ?";
        $datos = array($numeroControl);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    public function getIdCliente(string $nombreCliente)
    {
        $sql = "SELECT codigoCliente FROM clientes WHERE nombreCliente = ?";
        $datos = array($nombreCliente);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function obtenerSaldoCuenta( $codigoCuentaBancaria)
    {
        $sql = "SELECT saldoInicial, ingresos, saldo, salidas FROM cuentaBancaria WHERE codigoCuentaBancaria = ?";
        $datos = array($codigoCuentaBancaria);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    public function actualizarIngresoCuenta( $ingreso,  $codigoCuentaBancaria)
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

    public function actualizarSaldoCuentas( $saldo,  $codigoCuentaBancaria)
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

    public function obtenerSalidasCuenta( $codigoCuentaBancaria)
    {
        $sql = "SELECT salidas FROM cuentaBancaria WHERE codigoCuentaBancaria = ?";
        $datos = array($codigoCuentaBancaria);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function actualizarSalidasCuentas( $salidas,  $codigoCuentaBancaria)
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
}