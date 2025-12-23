<?php

class SujetoExcluido extends Controller
{
    private $metodosmodel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        parent::__construct();
        $this->metodosmodel = new MetodosModel();
    }

    public function index()
    {
        if (empty($_SESSION['codigoUsuario'])) {
            header("Location: " . base_url);
        }
        $this->views->getView($this, "index");
    }

    public function buscarClientes()
    {
        $query = $_GET['q'] ?? '';
        $tipo = $_GET['tipo'] ?? 'todos'; // tipo puede ser "todos" o "conNrc"

        $data = $this->model->searchCliente($query, $tipo);
        $this->sendJsonResponse($data);
    }

    public function buscarTipoMovimiento()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->searchTipoMovimiento($query);
        $this->sendJsonResponse($data);
    }

    public function buscarProyecto()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->searchProyecto($query);
        $this->sendJsonResponse($data);
    }

    public function tipoOperacion()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->getCodigoOperacion($query);
        $this->sendJsonResponse($data);
    }

    public function buscarTipoPago()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->searchTipoPago($query);
        $this->sendJsonResponse($data);
    }

    public function buscarBanco()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->searchBanco($query);
        $this->sendJsonResponse($data);
    }

    public function obtenerCuentaBancaria()
    {
        $codigoBanco = $_GET['q'] ?? null; // Cambia $_POST por $_GET
        $data = $this->model->obtenerCuenta($codigoBanco);
        $this->sendJsonResponse($data);
    }

    // validar fecha dte no se puede otro dia diferente al actual
    public function validarFechaDte($fechaEmision)
    {
        // validar que no se permite genera este dte con un dia atras del actual
        $fechaActual = new DateTime();
        $fechaActual->setTime(0, 0, 0);

        $ayer = clone $fechaActual;
        $ayer->modify('-1 days');

        if ($fechaActual < $ayer) {
            $msg = 'No puede generar DTE con un dia atras del actual';
            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
            return;
        } else {
        }
    }

    // obtener tipo de item del producto
    public function tipoDeItem()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->getTipoItem($query);
        $this->sendJsonResponse($data);
    }

    // seleccionar productos desde el modal
    public function seleccionar()
    {
        $idProducto = isset($_POST['codigoProducto']) ? $_POST['codigoProducto'] : null;
        $cantidad = isset($_POST['cantidadProducto']) ? (int) $_POST['cantidadProducto'] : 0;
        $unidadDeMedida = isset($_POST['unidadMedida']) ? $_POST['unidadMedida'] : null;
        $tipoItem = isset($_POST['tipoItemP']) ? $_POST['tipoItemP'] : null;
        $precioCosto = isset($_POST['precioCosto']) ? (float) str_replace(',', '', $_POST['precioCosto']) : 0;
        $precioVenta = isset($_POST['precioVenta']) ? (float) str_replace(',', '', $_POST['precioVenta']) : 0;
        $descuentoItem = isset($_POST['descuentoItem']) ? (float) str_replace(',', '', $_POST['descuentoItem']) : 0;
        $idUsuario = $_SESSION['codigoUsuario'];

        if (empty($idProducto)) {
            $msg = "Debe seleccionar un producto";
        } else if ($cantidad <= 0) {
            $msg = "Debe colocar una cantidad del producto especifico";
        } else if (empty($unidadDeMedida)) {
            $msg = "Debe Ingresar unidad de medida al producto";
        } else if (empty($tipoItem)) {
            $msg = "Debe Ingresar el tipo de item al producto";
        } else {
            try {
                $this->model->iniciarTransaccion();

                $datos = $this->metodosmodel->getProducto($idProducto);
                if (!$datos) {
                    $msg = "Producto no encontrado";
                }

                $comprobar = $this->metodosmodel->consultarDetalle($idProducto, $idUsuario);
                $ultimoId = $this->metodosmodel->obtenerUltimoIdDetalle($idUsuario);

                if (empty($comprobar)) {
                    // para este dte los precios deben reflejarse sin iva incluido
                    // calcular precio sin iva (precioventa/1.13)
                    $precioSinIva = round($precioVenta / 1.13, 2);
                    $subtotalSinIva = $precioSinIva * $cantidad;
                    $descuentoTotal = $descuentoItem * $cantidad;

                    // calcular el total ya sin iva y agregando descuento por item si se agrego
                    if ($descuentoItem < 0) {
                        $msg = "El descuento no puede ser negativo";
                    } else if ($descuentoItem > $subtotalSinIva) {
                        $msg = "El descuento no puede ser mayor al subtotal sin IVA del ítem";
                    } else {
                        // Calcular total con descuento
                        $total = round($subtotalSinIva - $descuentoTotal, 2);

                        // solo permitir un maximo de 2000 items por dte
                        if (!empty($ultimoId) && $ultimoId['max_id'] >= 2000) {
                            $msg = "No puede seguir agregando más productos. Límite alcanzado.";
                        } else {
                            $data = $this->model->registrarDetalle($idProducto, $cantidad, $precioCosto, $precioVenta, $total, $idUsuario, $unidadDeMedida, $descuentoTotal, $tipoItem, $precioSinIva);
                        }

                        if ($data !== "ok") {
                            $msg = "Error al registrar detalle";
                        }
                        $msg = "ok";
                    }
                }

                $this->model->confirmarTransaccion();
            } catch (Exception $e) {
                $this->model->revertirTransaccion();
                $msg = "Error inesperado: " . $e->getMessage();
            }
        }
        // var_dump($idProducto, $cantidad, $unidadDeMedida, $tipoItem, $precioVenta, $descuentoItem, $idUsuario);
        // exit;
        $this->sendJsonResponse($msg);
    }

    //listar el detalle
    public function listarDetalle()
    {
        $idUsuario = $_SESSION['codigoUsuario'];

        $subTotal = 0;
        $totalDescuentoPorItem = 0;
        // $ivaRetenido = 0;
        // $total = 0;

        $detalle = $this->model->getDetalle($idUsuario);

        foreach ($detalle as $row) {
            $subTotal += $row['total'];
            $totalDescuentoPorItem += $row['descuentoItem'];
        }

        $data['detalle'] = $detalle;
        $data['subTotal'] = $subTotal;
        $data['totalDescuentoPorItem'] = $totalDescuentoPorItem;

        // if (!empty($cliente) && isset($cliente['nrc'])) {
        //     // Calcular el IVA sobre el subtotal completo y luego redondearlo
        //     $ivaRetenido = round($subTotal * 0.13, 2);
        //     $total = round($subTotal + $ivaRetenido, 2);
        // } else {
        //     $total = $subTotal;
        // }


        // $data['ivaRetenido'] = $ivaRetenido;
        // $data['total'] = $total;

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function eliminarDetalle($id)
    {
        $detalle = $this->model->obtenerDetalle($id); // Debes crear este método para obtener el id_Usuario
        $data = $this->model->EDetalle($id);

        if ($data == "ok") {
            $this->model->reordenarItems($detalle['id_Usuario']);
            $msg = "ok";
        } else {
            $msg = "Error al Eliminar";
        }

        $this->sendJsonResponse($msg);
    }

    public function seleccionarFp()
    {
        try {
            $idUsuario = $_SESSION['codigoUsuario'];
            $condicionOperacion = $_POST['condicion'] ?? null;
            $medioPago = $_POST['selectTipoPago'] ?? null;
            $montoPago = $_POST['totalModal'] ?? null;
            $npagosDividido = $_POST['montoPago'] ?? null;
            $referencia = $_POST['referencia'] ?? null;
            $banco = $_POST['selectBanco'] ?? null;
            $cuentaBanco = $_POST['selectCuentaBancaria'] ?? null;
            $plazo = null;
            $periodo = null;

            if (empty($condicionOperacion)) {
                return $this->sendJsonResponse(['status' => 'error', 'message' => 'No ha seleccionado Pago']);
            }

            if (empty($medioPago)) {
                return $this->sendJsonResponse(['status' => 'error', 'message' => 'No ha seleccionado medio de Pago']);
            }

            // Validar solo los medios de pago permitidos
            $mediosPermitidos = ['01', '04', '05'];
            if (!in_array($medioPago, $mediosPermitidos)) {
                return $this->sendJsonResponse(['status' => 'error', 'message' => 'Medio de pago no permitido']);
            }

            if ($medioPago === '05' && (empty($banco) || empty($cuentaBanco))) {
                return $this->sendJsonResponse(['status' => 'error', 'message' => 'Debe seleccionar un banco y una cuenta bancaria']);
            }

            $detalle = $this->model->getDetallePagos($idUsuario);
            $totalPagos = 0.00;
            foreach ($detalle as $row) {
                $totalPagos += $row['montoPago'];
            }

            if ($montoPago == $totalPagos) {
                return $this->sendJsonResponse(['status' => 'error', 'message' => 'Ya se cubrio el total a pagar']);
            }

            $monto = !empty($npagosDividido) ? $npagosDividido : $montoPago;

            if ($medioPago === '05') {
                $referencia = $cuentaBanco;
            }

            $data = $this->model->registrarDetalleFormasPago(
                $medioPago,
                $monto,
                $referencia,
                $plazo,
                $periodo,
                $idUsuario,
                $condicionOperacion,
                $banco,
                $cuentaBanco
            );

            if ($data !== "ok") {
                return $this->sendJsonResponse(['status' => 'error', 'message' => 'Error al agregar forma de pago']);
            }

            return $this->sendJsonResponse(['status' => 'success', 'res' => $data]);
        } catch (Exception $e) {
            return $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }


    public function listarDetallePagos()
    {
        $idUsuario = $_SESSION['codigoUsuario'];

        $detalle = $this->model->getDetallePagos($idUsuario);
        $totalPagos = 0.00;
        foreach ($detalle as $row) {
            $totalPagos += $row['montoPago'];
        }

        $data['detalle'] = $detalle;
        $data['totalPagos'] = $totalPagos;

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function eliminarDetallePagos($id)
    {
        $data = $this->model->eliminarPagosD($id);

        if ($data == "ok") {
            $msg = "ok";
        } else {
            $msg = "Error al Eliminar";
        }

        $this->sendJsonResponse($msg);
    }

    public function vaciarDetalleProductos()
    {
        $idUsuario = $_SESSION['codigoUsuario'];

        $vaciar = $this->model->vaciarDetalleProductos($idUsuario);
        if ($vaciar == 'ok') {
            echo json_encode('ok');
        } else {
            echo json_encode('Error al vaciar los detalles de productos');
        }
    }

    public function vaciarDetallePagos()
    {
        $idUsuario = $_SESSION['codigoUsuario'];

        $vaciar = $this->model->vaciarDetallePagos($idUsuario);
        if ($vaciar == 'ok') {
            echo json_encode('ok');
        } else {
            echo json_encode('Error al vaciar los detalles de pagos');
        }
    }

    public function generar()
    {
        try {
            $this->model->iniciarTransaccion();

            date_default_timezone_set('America/El_Salvador');

            //instancia de clase controlador metodos
            $metodos = new Metodos;

            $codigoCliente = isset($_POST['selectCliente']) ? $_POST['selectCliente'] : null;
            $tipoMovimiento = isset($_POST['selectTipoMovimiento']) ? $_POST['selectTipoMovimiento'] : null;
            $codigoProyecto = isset($_POST['codigoProyecto']) ? $_POST['codigoProyecto'] : null;
            $idUsuario = $_SESSION['codigoUsuario'];
            $comprobarProductos = $this->model->comprobarProductos($idUsuario);
            $comprobarPagos = $this->model->comprobarPagos($idUsuario);

            //validar si la cantidad en total es menor que los montos en tipos de pagos
            $total = isset($_POST['total']) ? floatval($_POST['total']) : 0.00;
            $totalPagos = isset($_POST['totalPagos']) ? floatval($_POST['totalPagos']) : 0.00;

            if (!$codigoCliente) {
                $msg = "No ha seleccionado cliente";
                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                return;
            } else if (!$tipoMovimiento) {
                $msg = "No ha seleccionado tipo de movimiento";
                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                return;
            } else if (!$codigoProyecto) {
                $msg = "No ha seleccionado proyecto";
                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                return;
            } else if (empty($comprobarProductos['probar'])) {
                $msg = "No ha agregado productos";
                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                return;
            } else if (empty($comprobarPagos['probar'])) {
                $msg = "No ha agregado la forma de pago";
                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                return;
            } else if ($totalPagos < $total) {
                $msg = "No ha cubierto e total a pagar en los pagos";
                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                return;
            }

            $detalle = $this->model->getDetalle($idUsuario);
            // validacion de si hay productos en existencia de ese proyecto seleccionado
            foreach ($detalle as $row) {
                $producto = $row['codigoProducto'];

                if ($producto === '') {
                    continue;
                }

                $nombrePr = $row['nombreProducto'];
                $cantidadSolicitada = $row['cantidad'];

                $productoCantidad = $this->model->getExistencia($producto, $codigoProyecto);
                $cantidadDisponible = $productoCantidad[0]['cantidadProducto'] ?? 0;

                // Validar si no hay existencias
                if ($cantidadDisponible <= 0) {
                    $msg = 'El producto con código: ' . $producto . ' y nombre: ' . $nombrePr . ' no tiene existencias en el proyecto seleccionado';
                    $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                    return;
                }

                // Validar si la cantidad solicitada es mayor a la disponible
                if ($cantidadSolicitada > $cantidadDisponible) {
                    $msg = 'El producto con código: ' . $producto . ' y nombre: ' . $nombrePr .
                        ' tiene solo ' . $cantidadDisponible . ' unidades disponibles, pero se solicitaron ' . $cantidadSolicitada;
                    $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                    return;
                }
            }

            //IDENTIFICACION
            $version = 1;
            $globales = $metodos->variablesGlobales();
            $ambiente = $globales['ambiente'];
            $tipoDte = '14';
            $numeroControl = $metodos->numeroDeControl($tipoDte);
            $codigoGeneracion = $metodos->codigoGeneracion();

            $row = $this->metodosmodel->obtenerCorrelativoTemporal($tipoDte, $ambiente);
            $numeroCorrelativo = $row['correlativo'] ?? null;

            if (!$numeroCorrelativo) {
                throw new Exception("No se encontró correlativo reservado para este documento");
            }


            //codigo de fechas para validaciones
            $fechaFormulario = $_POST['fechaEmi'];
            $fechaEmiHoy = $metodos->fecha_YYY_MM_DD($fechaFormulario);
            $fechaActual = new DateTime();
            $fechaActual->setTime(0, 0, 0);
            if ($fechaEmiHoy < $fechaActual) {
                $msg = 'No puede generar este DTE con un día atrás del actual';
                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                return;
            }

            //codigo de validacion de hora
            $horaFormulario = $_POST['horaEmi'];
            $horaEmiHoy = $metodos->hora_HH_MM_SS($horaFormulario);
            if ($metodos->esUltimoDiaDelMes($fechaEmiHoy)) {
                $fechaHoraEmision = $metodos->fechaHora_YYYY_MM_DD_HH_MM_SS($fechaEmiHoy, $horaEmiHoy);
                $horaActual = new DateTime(); // ahora mismo
                $horaLimite = clone $horaActual;
                $horaLimite->modify('+30 minutes');

                if ($fechaHoraEmision > $horaLimite) {
                    $msg = 'La hora de emisión excede el límite permitido (30 minutos posteriores a la hora actual).';
                    $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                    return;
                }
            }

            $tipoMoneda = $globales['tipoMoneda'];

            // validacion si esta activa la contingencia
            $estadoContingencia = $this->metodosmodel->obtenerEstadoContingencia();
            $eventoActivo = $estadoContingencia['estadoContingenciaId'] ?? null;
            $datosContingencia = $this->metodosmodel->datosContingencia();

            if ($eventoActivo == null) {
                // datos de identificaion segun contingencia desactivada
                $tipoModeloFacConting = 1; // modelo facturacion previa
                $tipoOperacionEstadoConting = 1; // trasnmision normal
                $tipoContingencia = null;
                $motivoContingencia = null;
            } else if ($eventoActivo == 1) {
                $tipoModeloFacConting = $datosContingencia['modeloFacturacion']; // modelo facturacion diferida
                $tipoOperacionEstadoConting = $datosContingencia['tipoTransmision']; // transmision por contingencia
                $tipoContingencia = $datosContingencia['tipoContingencia'];
                $motivoContingencia = $datosContingencia['motivoContingencia'];
            }

            $identificacion = (object) [
                "version" => $version,
                "ambiente" => $ambiente,
                "tipoDte" => $tipoDte,
                "numeroControl" => $numeroControl,
                "codigoGeneracion" => $codigoGeneracion,
                "tipoModelo" => $tipoModeloFacConting,
                "tipoOperacion" => $tipoOperacionEstadoConting,
                "tipoContingencia" => $tipoContingencia,
                "motivoContin" => $motivoContingencia,
                "fecEmi" => $fechaEmiHoy->format('Y-m-d'),
                "horEmi" => $horaEmiHoy,
                "tipoMoneda" => $tipoMoneda,
            ];

            // EMISOR
            $macasta = $this->metodosmodel->getEmpresa();

            $emisor = (object) [
                "nit" => $metodos->validarNit($macasta['nit']),
                "nrc" => $metodos->validarNrc($macasta['nrc']),
                "nombre" => $macasta['nombre'],
                "codActividad" => $metodos->validarCodActividad($macasta['codActividad']),
                "descActividad" => $macasta['descActividad'],
                "direccion" => [
                    "departamento" => $metodos->validarDepartamento($macasta['departamento']),
                    "municipio" => $metodos->validarMunicipio($macasta['municipio']),
                    "complemento" => $macasta['direccion'],
                ],
                "telefono" => $macasta['telefono'],
                "codEstableMH" => $globales['codEstableMH'],
                "codEstable" => $globales['codEstable'],
                "codPuntoVentaMH" => $globales['codPuntoVentaMH'],
                "codPuntoVenta" => $globales['codPuntoVenta'],
                "correo" => $macasta['correo'],
            ];

            // SUJETO EXCLUIDO
            $codigoCliente = isset($_POST['selectCliente']) ? $_POST['selectCliente'] : null;
            $datosCliente = $this->metodosmodel->getCliente($codigoCliente);

            // validar tipo de cliente
            if ($datosCliente['tipoIdentificacion'] == '36') {
                $numeroDocumento = $metodos->validarNit($datosCliente['numeroIdentificacion']);

                $sujetoExcluido = (object) [
                    "tipoDocumento" => $datosCliente['tipoIdentificacion'],
                    "numDocumento" => $numeroDocumento,
                    //"nrc" => $datosCliente['nrc'],
                    "nombre" => $datosCliente['nombreCliente'],
                    "codActividad" => $datosCliente['codigoActividadEconomica'],
                    "descActividad" => $datosCliente['valor'],
                    "direccion" => [
                        "departamento" => $datosCliente['departamento'],
                        "municipio" => $datosCliente['municipio'],
                        "complemento" => $datosCliente['complemento'],
                    ],
                    "telefono" => $datosCliente['numeroTelefonoCliente'],
                    "correo" => $datosCliente['correo']
                ];
            } else if (empty($datosCliente['tipoIdentificacion'])) {
                $sujetoExcluido = (object) [
                    "tipoDocumento" => '36',
                    "numDocumento" => $datosCliente['nit'],
                    //"nrc" => $datosCliente['nrc'],
                    "nombre" => $datosCliente['nombreCliente'],
                    "codActividad" => $datosCliente['codigoActividadEconomica'],
                    "descActividad" => $datosCliente['valor'],
                    "direccion" => [
                        "departamento" => $datosCliente['departamento'],
                        "municipio" => $datosCliente['municipio'],
                        "complemento" => $datosCliente['complemento'],
                    ],
                    "telefono" => $datosCliente['numeroTelefonoCliente'],
                    "correo" => $datosCliente['correo']
                ];
            } else if ($datosCliente['tipoIdentificacion'] == '13') {
                $numeroDocumento = $metodos->validarDui($datosCliente['numeroIdentificacion']);

                $sujetoExcluido = (object) [
                    "tipoDocumento" => $datosCliente['tipoIdentificacion'],
                    "numDocumento" => $numeroDocumento,
                    "nombre" => $datosCliente['nombreCliente'],
                    "codActividad" => $datosCliente['codigoActividadEconomica'],
                    "descActividad" => $datosCliente['valor'],
                    "direccion" => [
                        "departamento" => $datosCliente['departamento'],
                        "municipio" => $datosCliente['municipio'],
                        "complemento" => $datosCliente['complemento'],
                    ],
                    "telefono" => $datosCliente['numeroTelefonoCliente'],
                    "correo" => $datosCliente['correo']
                ];
            }


            // CUERPO DOCUMENTO
            $cuerpoDocumento = [];
            $totalCompra = 0.00;
            $descuentoTI = 0.00;
            foreach ($detalle as $row) {
                $numeroDeItem = $row['item'];
                $tipoDeItem = $row['tipoDeItem'];
                $cantidad = $row['cantidad'];
                $codigoProducto = $row['codigoProducto'];
                $unidadDeMedida = $row['unidadMedida'];
                $descripcion = $row['nombreProducto'];
                $precioUni = $row['precioSinIva'];
                $descuento = $row['descuentoItem'];
                $compra = $row['total'];

                $cuerpoDocumento[] = [
                    "numItem" => $numeroDeItem,
                    "tipoItem" => $tipoDeItem,
                    "cantidad" => $cantidad,
                    "codigo" => $codigoProducto,
                    "uniMedida" => $unidadDeMedida,
                    "descripcion" => $descripcion,
                    "precioUni" => (float)$precioUni,
                    "montoDescu" => (float)$descuento,
                    "compra" => (float)$compra
                ];

                $totalCompra += $compra;
                $descuentoTI +=  $descuento;
            }

            // RESUMEN
            $montoDescuGlobal = isset($_POST['montoDescuTotal']) ? floatval($_POST['montoDescuTotal']) : 0.00;
            $ivaRetenido = isset($_POST['ivaRetenido']) ? floatval($_POST['ivaRetenido']) : 0.00;
            $rentencionRenta = isset($_POST['rentaRetenida']) ? floatval($_POST['rentaRetenida']) : 0.00;
            $totalDescu = (float)$descuentoTI + (float)$montoDescuGlobal;
            $subTotal = (float)$totalCompra - (float)$montoDescuGlobal;
            $totalPagar = $subTotal - $ivaRetenido - $rentencionRenta;
            $text = $metodos->cantidadLetras($totalPagar);
            $observacion = !empty($_POST['observacion']) ? $_POST['observacion'] : null;
            // $condicionOperacion = isset($_POST['condicion']) ? $_POST['condicion'] : null;
            // $montoPago = isset($_POST['montoPago']) ? floatval($_POST['montoPago']) : 0.00;
            if ($montoDescuGlobal > $totalCompra) {
                $msg = "El descuento global no puede ser mayor al total de operaciones";
                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                return;
            }

            $detallePagos = $this->model->getDetallePagos($idUsuario);
            $pagos = [];
            foreach ($detallePagos as $row) {
                $pagos[] = [
                    "codigo" => $row['codigoId'],
                    'montoPago' => (float)$row['montoPago'],
                    'referencia' => $row['referencia'],
                    'plazo' => null,
                    'periodo' => null,
                ];
            }



            $resumen = (object)[
                "totalCompra" => (float)$totalCompra,
                "descu" => (float)$montoDescuGlobal,
                "totalDescu" => $totalDescu,
                "subTotal" => $subTotal,
                "ivaRete1" => $ivaRetenido,
                "reteRenta" => $rentencionRenta,
                "totalPagar" => $totalPagar,
                "totalLetras" => $text,
                "condicionOperacion" => (int)$detallePagos[0]['condicionOperacion'],
                "pagos" => $pagos,
                "observaciones" => $observacion
            ];

            //APENDICE
            $apendice = null;


            // ESTRUCTURA FINAL
            $estructuraFseeJson = [
                "identificacion" => $identificacion,
                "emisor" => $emisor,
                "sujetoExcluido" => $sujetoExcluido,
                "cuerpoDocumento" => $cuerpoDocumento,
                "resumen" => $resumen,
                "apendice" => $apendice
            ];
            // var_dump('');
            // echo json_encode($estructuraFseeJson, JSON_PRETTY_PRINT);
            // exit;
            $this->model->confirmarTransaccion();
            $this->sendJsonResponse([
                'status' => 'success',
                'dteJson' => $estructuraFseeJson,
                'nit' => $emisor->nit,
                'passwordPri' => PASSWORD_PRIVADA,
                'movimiento' => $tipoMovimiento,
                'proyecto' => $codigoProyecto
            ]);
        } catch (Exception $e) {
            $this->metodosmodel->liberarCorrelativo($tipoDte, $ambiente, $numeroCorrelativo);
            $this->model->revertirTransaccion();
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()]);
            return;
        }
    }

    public function emitirFirmado()
    {
        try {

$listados = new Listados;
            $this->model->iniciarTransaccion();

            $metodos = new Metodos;
            $data = json_decode(file_get_contents("php://input"), true);

            $firmado = $data['firmado'] ?? null;
            $dteJson = $data['dteJson'] ?? null;
            $tipoMovimiento = $data['tipoMovimiento'] ?? null;
            $codigoProyecto = $data['codigoProyecto'] ?? null;


            $ambiente = $dteJson['identificacion']['ambiente'] ?? '';
            $version = $dteJson['identificacion']['version'] ?? '';
            $tipoDte = $dteJson['identificacion']['tipoDte'] ?? '';
            $codigoGeneracion = $dteJson['identificacion']['codigoGeneracion'] ?? '';
            // Recuperar correlativo reservado desde la tabla temporal
            $row = $this->metodosmodel->obtenerCorrelativoTemporal($tipoDte, $ambiente);
            $numeroCorrelativo = $row['correlativo'] ?? null;

            if (!$numeroCorrelativo) {
                throw new Exception("No se encontró correlativo reservado para este documento");
            }


            //validar si contingencia esta activa
            $estado = 'PENDIENTE';
            $fechaProcesamiento = null;
            $sujetoExcluido = $dteJson['sujetoExcluido']['nombre'];
            $idCliente =  $this->metodosmodel->getIdCliente($sujetoExcluido);
            $estadoContingencia = $this->metodosmodel->obtenerEstadoContingencia();
            $eventoActivo = $estadoContingencia['estadoContingenciaId'] ?? null;
            if ($eventoActivo == 1) {
                $idContingencia = $this->metodosmodel->obtenerIdContingenciaActiva($eventoActivo);
                $registrar = $idContingencia['id'];
            } else {
                $registrar = null;
            }

            // registrar el dte si esta en contingencia para luego emitir por lote
            $this->registrarEncabezado($dteJson['identificacion'], $version, $tipoDte, $codigoGeneracion, $idCliente, $dteJson['resumen'], $selloRecepcion ?? null, $ambiente, $firmado, $estado, $fechaProcesamiento, $tipoMovimiento, $codigoProyecto, $registrar);
            $this->registrarCuerpo($dteJson['cuerpoDocumento'], $dteJson['identificacion']);
            $this->registrarPagos($dteJson['identificacion'], $dteJson['resumen']);

            if ($eventoActivo == 1) {
                $this->metodosmodel->confirmarCorrelativo($tipoDte, $ambiente, $numeroCorrelativo);
                $this->model->confirmarTransaccion();
                $this->actualizarExistencias($dteJson['cuerpoDocumento'], $codigoProyecto);
                $this->actualizarCuentasBancarias();
                $this->enviarFacturaTipo14DesdeSujeto($dteJson['sujetoExcluido']['correo'], $dteJson['identificacion']['numeroControl']);

                $this->sendJsonResponse([
                    'status' => 'success',
                    'emision' => 'DTE generado en contingencia. Firmado y almacenado. Esperando envío por lote.'
                ]);
                exit;
            }
            
            $token = $metodos->obtenerYGuardarToken();

            if (!isset($token['token'])) {
                // Mostrar o devolver el mensaje de error
                $this->sendJsonResponse(['status' => 'error', 'message' => $token['message']]);
                return;
            }

            $maximoIntentos = 3;
            $intentos = 1;
            $exito = false;

            while ($intentos < $maximoIntentos && !$exito) {
                $intentos++;

                $postData = json_encode([
                    'ambiente' => $ambiente,
                    'idEnvio' => $intentos,
                    'version' => $version,
                    'tipoDte' => $tipoDte,
                    'documento' => $firmado,
                    'codigoGeneracion' => $codigoGeneracion
                ], JSON_UNESCAPED_UNICODE);

                // $ch = curl_init('https://apitest.dtes.mh.gob.sv/fesv/recepciondte');
                $ch = curl_init(URL_RECEPCION_DTE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: ' . $token['token'],
                    'Content-Type: application/json',
                    'User-Agent: sistema-facturacion'
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 8);

                $response = curl_exec($ch);

                if ($response === false) {
                    $errorCurl = curl_error($ch);
                    curl_close($ch);
                    if ($intentos >= $maximoIntentos) {
                        throw new Exception("Error de conexión con Hacienda en el intento $intentos: $errorCurl ACTIVE Y FACTURE EN CONTINGENCIA");
                    }
                    sleep(8);
                    continue;
                }

                curl_close($ch);
                $decoded = json_decode($response, true);
                if ($decoded === null) {
                    if ($intentos >= $maximoIntentos) {
                        throw new Exception("No se pudo interpretar respuesta JSON de Hacienda en intento $intentos.");
                    }
                    sleep(8);
                    continue;
                }

                if ($decoded && isset($decoded['estado']) && $decoded['estado'] === 'PROCESADO' && !empty($decoded['selloRecibido'])) {
                    $sello = $decoded['selloRecibido'];
                    $estado = $decoded['estado'];
                    $fechaProcesamiento = $decoded['fhProcesamiento'];
                    $observaciones = is_array($decoded['observaciones']) ? json_encode($decoded['observaciones'], JSON_UNESCAPED_UNICODE) : $decoded['observaciones'];
                    $this->metodosmodel->confirmarCorrelativo($tipoDte, $ambiente, $numeroCorrelativo);
                    $this->model->actualizarEstadoDte($sello, $estado, $fechaProcesamiento, $observaciones, $dteJson['identificacion']['numeroControl']);
                    $this->model->confirmarTransaccion();
                    $this->actualizarExistencias($dteJson['cuerpoDocumento'], $codigoProyecto);
                    $this->actualizarCuentasBancarias();
                    $this->enviarFacturaTipo14DesdeSujeto($dteJson['sujetoExcluido']['correo'], $dteJson['identificacion']['numeroControl']);
                    $this->sendJsonResponse([
                        'status' => 'success',
                        'emision' => $decoded
                    ]);
                    exit;
                } else {

                    $estadoConsulta = $this->consultarEstadoDTE($codigoGeneracion, $tipoDte, $dteJson['emisor']['nit'], $token['token']);
                    if (isset($estadoConsulta['estado']) && $estadoConsulta['estado'] === 'PROCESADO' && !empty($estadoConsulta['selloRecibido'])) {
                        $sello = $decoded['selloRecibido'];
                        $estado = $decoded['estado'];
                        $fechaProcesamiento = $decoded['fhProcesamiento'];
                        $observaciones = is_array($decoded['observaciones']) ? json_encode($decoded['observaciones'], JSON_UNESCAPED_UNICODE) : $decoded['observaciones'];
                        $this->metodosmodel->confirmarCorrelativo($tipoDte, $ambiente, $numeroCorrelativo);
                        $this->model->actualizarEstadoDte($sello, $estado, $fechaProcesamiento, $observaciones, $dteJson['identificacion']['numeroControl']);
                        $this->model->confirmarTransaccion();
                        $this->actualizarExistencias($dteJson['cuerpoDocumento'], $codigoProyecto);
                        $this->actualizarCuentasBancarias();
                        $this->enviarFacturaTipo14DesdeSujeto($dteJson['sujetoExcluido']['correo'], $dteJson['identificacion']['numeroControl']);
                        $this->sendJsonResponse([
                            'status' => 'success',
                            'emision' => $decoded
                        ]);
                        exit;
                    }

                    if ($intentos >= $maximoIntentos) {
                        $errores = "Error en envío: " . ($decoded['descripcionMsg'] ?? 'Respuesta desconocida');
                        if (isset($decoded['observaciones'])) {
                            foreach ($decoded['observaciones'] as $obs) {
                                $errores .= "\n- Observación: $obs";
                            }
                        }
                        $this->metodosmodel->liberarCorrelativo($tipoDte, $ambiente, $numeroCorrelativo);

                        $this->model->revertirTransaccion();
                        $this->sendJsonResponse(['status' => 'error', 'message' => $response]);
                        throw new Exception($errores);
                    } else {
                        sleep(8); // pausa antes del reintento
                    }
                }
            }
        } catch (Throwable $e) {
            try {
                // Si quieres, aquí podrías liberar el correlativo antes de hacer rollback
                $this->metodosmodel->liberarCorrelativo($tipoDte, $ambiente, $numeroCorrelativo);


                $this->model->revertirTransaccion();
            } catch (Exception $inner) {
                error_log("Error revirtiendo transacción: " . $inner->getMessage());
            }
            $this->sendJsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function consultarEstadoDTE($codigoGeneracion, $tipoDte, $nit, $token)
    {
        // $url = "https://apitest.dtes.mh.gob.sv/fesv/recepcion/consultadte/";
        $url = URL_CONSULTA_DTE;

        $postData = json_encode([
            'nitEmisor' => $nit,
            'tdte' => $tipoDte,
            'codigoGeneracion' => $codigoGeneracion
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'User-Agent: sistema-facturacion'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $decoded = json_decode($response, true);
        return $decoded['estado'] ?? null;
    }

    public function registrarEncabezado($identificacion, $version, $tipoDte, $codigoGeneracion, $idCliente, $resumen, $selloRecepcion, $ambiente, $firmado, $estado, $fechaProcesamiento, $tipoMovimiento, $codigoProyecto, $registrar)
    {
        $resgistrarDte = $this->model->registrarDte(
            $identificacion['numeroControl'],
            $version,
            $tipoDte,
            $codigoGeneracion,
            $identificacion['tipoModelo'],
            $identificacion['tipoOperacion'],
            $identificacion['tipoContingencia'],
            $identificacion['motivoContin'],
            $identificacion['fecEmi'],
            $identificacion['horEmi'],
            $idCliente['codigoCliente'],
            $resumen['totalDescu'],
            $resumen['subTotal'],
            $resumen['ivaRete1'],
            $resumen['reteRenta'],
            $resumen['totalPagar'],
            $resumen['condicionOperacion'],
            $resumen['totalCompra'],
            $resumen['descu'],
            $resumen['observaciones'],
            $identificacion['tipoMoneda'],
            $selloRecepcion ?? null,
            $ambiente,
            $firmado,
            $estado,
            $fechaProcesamiento,
            $tipoMovimiento,
            $codigoProyecto,
            $registrar
        );

       if ($resgistrarDte !== "ok") {
    throw new Exception("Error al registrar el encabezado del DTE. Respuesta: " . $resgistrarDte);
}

    }

    public function registrarCuerpo($cuerpoDocumento, $identificacion)
    {
        foreach ($cuerpoDocumento as $row) {

            $registrarCuerpo = $this->model->registrarDTEcuerpo(
                $identificacion['numeroControl'],
                $row['numItem'],
                $row['tipoItem'],
                $row['cantidad'],
                $row['codigo'],
                $row['uniMedida'],
                $row['precioUni'],
                $row['montoDescu'],
                $row['compra'],
            );

            if ($registrarCuerpo !== "ok") {
                throw new Exception("Error al registrar el cuerpo del documento");
            }
        }
    }

    public function registrarPagos($identificacion, $resumen)
    {
        $idUsuario = $_SESSION['codigoUsuario'];
        $detallePagos = $this->model->getDetallePagosPararegistro($idUsuario);

        foreach ($detallePagos as $row) {
            $registrarPagos = $this->model->registrarPagosDte(
                $identificacion['numeroControl'],
                $row['codigoId'],
                $row['montoPago'],
                $row['referencia'],
                $row['plazo'],
                $row['periodo'],
                $idUsuario,
                $resumen['condicionOperacion'],
                $row['codigoBanco'],
                $row['codigoCuentaBancaria']
            );

            if ($registrarPagos !== "ok") {
                throw new Exception("Error al registrar el pago del documento");
            }
        }
    }

    public function actualizarExistencias($cuerpoDocumento, $codigoProyecto)
    {
        foreach ($cuerpoDocumento as $item) {
            $codigoProducto = $item['codigo'];
            $cantidadDescontar = $item['cantidad'];

            $cantidadActual = $this->metodosmodel->getExistencia($codigoProducto, $codigoProyecto);
            $cantidadProductoA = $cantidadActual[0]['cantidadProducto'] ?? 0;
            $existencia = $cantidadProductoA - $cantidadDescontar;

            $resultExistencia = $this->metodosmodel->actualizarExistenciasM($existencia, $codigoProyecto, $codigoProducto);
            if ($resultExistencia != "ok") {
                throw new Exception("Error al actualizar las existencias del producto con código: $codigoProducto");
            }
        }
    }

    public function actualizarCuentasBancarias()
    {
        $idUsuario = $_SESSION['codigoUsuario'];
        $detallePagos = $this->model->getDetallePagosPararegistro($idUsuario);

        // Agrupar pagos por cuenta bancaria
        $pagosPorCuenta = [];
        foreach ($detallePagos as $row) {
            $codigoCuentaBancaria = $row['codigoCuentaBancaria'] ?? null;
            if (!$codigoCuentaBancaria) {
                continue; // Ignorar pagos sin cuenta bancaria
            }

            $montoPago = (float) ($row['montoPago'] ?? 0);

            if (!isset($pagosPorCuenta[$codigoCuentaBancaria])) {
                $pagosPorCuenta[$codigoCuentaBancaria] = 0;
            }
            $pagosPorCuenta[$codigoCuentaBancaria] += $montoPago;
        }

        // Actualizar cada cuenta una sola vez
        foreach ($pagosPorCuenta as $codigoCuentaBancaria => $totalPago) {
            // Obtener datos actuales de la cuenta
            $datosCuentas = $this->metodosmodel->obtenerSaldoCuenta($codigoCuentaBancaria);
            $ingresoActualCuentaB = (float) ($datosCuentas[0]['ingresos'] ?? 0);
            $saldoInicialCuenta   = (float) ($datosCuentas[0]['saldoInicial'] ?? 0);
            $salidaActualCuentaB  = (float) ($datosCuentas[0]['salidas'] ?? 0);

            // Calcular nuevo ingreso y saldo
            $nuevoIngreso = $ingresoActualCuentaB + $totalPago;
            $nuevoSaldo   = $saldoInicialCuenta + $nuevoIngreso - $salidaActualCuentaB;

            // Actualizar ingreso
            $resultSalida = $this->metodosmodel->actualizarIngresoCuenta($nuevoIngreso, $codigoCuentaBancaria);
            if ($resultSalida != "ok") {
                throw new Exception("Error al actualizar ingreso para la cuenta $codigoCuentaBancaria");
            }

            // Actualizar saldo
            $resultado = $this->metodosmodel->actualizarSaldoCuentas($nuevoSaldo, $codigoCuentaBancaria);
            if ($resultado != "ok") {
                throw new Exception("Error al actualizar saldo para la cuenta $codigoCuentaBancaria");
            }
        }
    }

    public function liberarUltimoCorrelativo()
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            $tipoDte = $data['tipoDte'];
            $ambiente = $data['ambiente'];

            // Obtener correlativo reservado más reciente
            $row = $this->metodosmodel->obtenerCorrelativoTemporal($tipoDte, $ambiente);
            $numeroCorrelativo = $row['correlativo'] ?? null;

            if ($numeroCorrelativo) {
                $this->metodosmodel->liberarCorrelativo($tipoDte, $ambiente, $numeroCorrelativo);
                $this->sendJsonResponse(['status' => 'success', 'message' => 'Correlativo liberado']);
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'No hay correlativo reservado']);
            }
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    public function enviarFacturaTipo14DesdeSujeto($emailCliente, $numeroControl)
    {
        $listados = new Listados;

        // Generar los archivos y obtener las rutas relativas
        $pdfRelativo = $listados->generarPdfFse($numeroControl, true);
        $jsonRelativo = $listados->generarPdfFseJSON($numeroControl, true);

        // Convertir a rutas absolutas
        $pdfPath = realpath(__DIR__ . '/../' . $pdfRelativo);
        $jsonPath = realpath(__DIR__ . '/../' . $jsonRelativo);

        // Validar existencia de archivos
        if (!file_exists($pdfPath) || !file_exists($jsonPath)) {
            error_log("Archivos PDF o JSON no encontrados para $numeroControl");
            return;
        }

        require_once 'vendor/autoload.php';
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'dtemacasta@gmail.com';
            $mail->Password = 'eqtx oxya lngu yien';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('dtemacasta@gmail.com', 'MACASTA');
            $mail->addAddress($emailCliente);

            $mail->addAttachment($pdfPath, $numeroControl . '.pdf');
            $mail->addAttachment($jsonPath, $numeroControl . '.json');

            $mail->isHTML(true);
            $mail->Subject = $numeroControl;
            $mail->Body = "
            <p>Estimado cliente,</p>
            <p>Adjunto encontrará su DTE en formato <strong>PDF</strong> y <strong>JSON</strong>.</p>
            <p>Número de control: <strong>$numeroControl</strong></p>
            <p>Gracias por su preferencia.</p>
        ";

            $mail->send();

            // Opcional: eliminar archivos
            unlink($pdfPath);
            unlink($jsonPath);

            error_log("Correo tipo 14 enviado correctamente para $numeroControl");
        } catch (Exception $e) {
            error_log("Error al enviar correo tipo 14: " . $mail->ErrorInfo);
        }
    }
    
        public function obtenerContingencia()
    {
        $estado = $this->metodosmodel->obtenerEstadoContingencia(); // puede ser un array vacío o con datos

        $activo = !empty($estado); // Si hay al menos un registro, la contingencia está activa

        $this->sendJsonResponse(['activo' => $activo]);
    }
    
}

// // Habilitar el reporte de todos los errores
// error_reporting(E_ALL);
// ini_set('display_errors', '1');