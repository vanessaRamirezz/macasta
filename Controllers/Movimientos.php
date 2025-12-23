<?php
class Movimientos extends Controller
{

    public function __construct()
    {
        session_start();
        parent::__construct();
    }

    public function index()
    {
        if (empty($_SESSION['codigoUsuario'])) {
            header("Location: " . base_url);
        }
        $this->views->getView($this, "index");
    }


    public function buscarTipoMovimiento()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->searchTipoMovimiento($query);
        $this->sendJsonResponse($data);
    }

    public function buscarTransaccionEmpleado()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->searchTipoTransaccionEmpleado($query);
        $this->sendJsonResponse($data);
    }

    public function buscarEmpleado()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->searchEmpleado($query);
        $this->sendJsonResponse($data);
    }

    public function buscarTipoDocumento()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->searchTipoDocumento($query);
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

    public function registrar()
    {
        // Obtener los datos del formulario
        $codigoTipoMovimiento = $_POST['selectTipoMovimiento'] ?? null;
        $numeroTransaccion = trim($_POST['numeroTransaccion']);
        $numeroDocumento = $_POST['numeroDocumento'] ?? null;
        $monto = isset($_POST['monto']) ? (float) str_replace(',', '', $_POST['monto']) : 0;
        $codigoProveedor = empty($_POST['selectProveedor']) ? null : $_POST['selectProveedor'];
        $codigoCliente = empty($_POST['selectCliente']) ? null : $_POST['selectCliente'];
        $codigoEmpleado = empty($_POST['selectCodigoEmpleado']) ? null : $_POST['selectCodigoEmpleado'];
        $metodoEmpleado = empty($_POST['selectTipoTransaccion']) ? null : $_POST['selectTipoTransaccion'];
        $codigoProyecto = empty($_POST['codigoProyecto']) ? null : $_POST['codigoProyecto'];
        $codigoTipoDocumento = $_POST['selectTipoDocumento'] ?? null;
        $codigoTipoPago = $_POST['selectTipoPago'] ?? null;
        $codigoBanco = empty($_POST['selectBanco']) ? null : $_POST['selectBanco'];
        $codigoCuentaBancaria = empty($_POST['selectCuentaBancaria']) ? null : $_POST['selectCuentaBancaria'];
        $fecha = trim($_POST['fecha']);
        $codigoUsuario = trim($_SESSION['codigoUsuario']);


        $nombre = $this->model->obtenerNombreTipoMovimiento($codigoTipoMovimiento);
        $nombreTipoMovimiento = $nombre ? $nombre['nombreMovimiento'] : null;

        // Validar que los campos obligatorios estén llenos
        if (strpos($numeroTransaccion, ' ') !== false) {
            $msg = "El campo 'Transacción' no debe contener espacios.";
        } else if (empty($codigoTipoMovimiento) || empty($numeroTransaccion) || empty($numeroDocumento) || empty($monto) || empty($codigoTipoDocumento) || empty($codigoTipoPago) || empty($fecha)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } elseif (stripos($nombreTipoMovimiento, 'proveedor') !== false && empty($codigoProveedor)) {
            $msg = "Debe seleccionar un proveedor para este tipo de movimiento.";
        } elseif (stripos($nombreTipoMovimiento, 'cliente') !== false && empty($codigoCliente)) {
            $msg = "Debe seleccionar un cliente para este tipo de movimiento.";
        } elseif (stripos($nombreTipoMovimiento, 'planilla') !== false && (empty($metodoEmpleado) || empty($codigoEmpleado))) {
            $msg = empty($metodoEmpleado) ? "Debe seleccionar una categoría de pago para este tipo de movimiento." : "Debe seleccionar un empleado para este tipo de movimiento.";
        } else {
            try {
                // Iniciar la transacción
                $this->model->iniciarTransaccion();

                // Registrar el movimiento
                $data = $this->model->registrarMovimiento(
                    $codigoTipoMovimiento,
                    $numeroTransaccion,
                    $numeroDocumento,
                    $monto,
                    $codigoProveedor,
                    $codigoCliente,
                    $codigoEmpleado,
                    $metodoEmpleado,
                    $codigoProyecto,
                    $codigoTipoDocumento,
                    $codigoTipoPago,
                    $codigoBanco,
                    $codigoCuentaBancaria,
                    $fecha,
                    $codigoUsuario
                );

                if ($data === "ok") {
                    // **Si hay un proveedor, actualizamos su saldo**
                    if (!is_null($codigoProveedor)) {
                        if (stripos($nombreTipoMovimiento, 'Pago a proveedor') !== false) {
                            if ($codigoTipoPago == '05' || $codigoTipoPago == '04') {

                                // si es por proveedor el monto de la transaccion se le resta al saldo actual
                                $saldoProveedor = $this->model->obtenerSaldoProveedor($codigoProveedor);
                                $saldoActual = (float) ($saldoProveedor['saldoProveedor'] ?? 0);
                                $nuevoSaldo = round($saldoActual - $monto, 2); // Asegurar 2 decimales
                                $actualizado = $this->model->actualizarSaldoProveedor($nuevoSaldo, $codigoProveedor);
                                if ($actualizado !== "ok") {
                                    throw new Exception("Error al actualizar saldo del proveedor");
                                }

                                // actualizar salida de la cuenta le sumamos el monto del movimiento
                                $salidaActualCuenta = $this->model->obtenerSalidasCuenta($codigoCuentaBancaria);
                                $nuevaSalida = $salidaActualCuenta['salidas'] + $monto;
                                $resultSalida = $this->model->actualizarSalidasCuentas($nuevaSalida, $codigoCuentaBancaria);
                                if ($resultSalida != "ok") {
                                    throw new Exception("Error al actualizar salida para la cuenta");
                                }

                                // actualizar saldo de la cuenta 
                                $datos = $this->model->obtenerSaldoCuenta($codigoCuentaBancaria);
                                $saldoInicialCuenta = (float) ($datos[0]['saldoInicial'] ?? 0);
                                $ingresoActualCuenta = (float) ($datos[0]['ingresos'] ?? 0);
                                $nuevoSaldoCuenta = $saldoInicialCuenta + $ingresoActualCuenta - $nuevaSalida;
                                $resultado = $this->model->actualizarSaldoCuentas($nuevoSaldoCuenta, $codigoCuentaBancaria);
                                if ($resultado != "ok") {
                                    throw new Exception("Error al actualizar saldo para la cuenta");
                                }
                            } else {
                                $saldoProveedor = $this->model->obtenerSaldoProveedor($codigoProveedor);
                                $saldoActual = isset($saldoProveedor['saldoProveedor']) ? (float) $saldoProveedor['saldoProveedor'] : 0;
                                $nuevoSaldo = round($saldoActual - $monto, 2); // Asegurar 2 decimales
                                $actualizado = $this->model->actualizarSaldoProveedor($nuevoSaldo, $codigoProveedor);
                                if ($actualizado !== "ok") {
                                    throw new Exception("Error al actualizar saldo del proveedor");
                                }
                            }
                        }
                    } else if (!is_null($codigoCliente)) {
                        if (stripos($nombreTipoMovimiento, 'Cobro a Cliente') !== false) {
                            if ($codigoTipoPago == '05' || $codigoTipoPago == '04') {
                                // actualizar saldo cliente
                                $saldoCliente = $this->model->obtenerSaldoCliente($codigoCliente);
                                $saldoActual = isset($saldoCliente['saldoCliente']) ? (float) $saldoCliente['saldoCliente'] : 0;
                                $nuevoSaldo = round($saldoActual + $monto, 2); // Asegurar 2 decimales
                                $actualizado = $this->model->actualizarSaldoCliente($nuevoSaldo, $codigoCliente);
                                if ($actualizado !== "ok") {
                                    throw new Exception("Error al actualizar saldo del cliente");
                                }

                                // actualizar ingreso de proyecto relazionado con cliente
                                $ingresosProyecto = $this->model->obtenerIngresoProyecto($codigoProyecto);
                                $ingresoActual = (float) ($datos[0]['ingresos'] ?? 0);
                                $nuevoIngreso = round($ingresoActual + $monto, 2); // Asegurar 2 decimales
                                $ingresoActualizado = $this->model->actualizarIngresoProyecto($nuevoIngreso, $codigoProyecto);
                                if ($ingresoActualizado !== "ok") {
                                    throw new Exception("Error al actualizar el ingreso del proyecto");
                                }

                                // actualizar ingreso de la cuenta le sumamos el monto del movimiento
                                $datos = $this->model->obtenerSaldoCuenta($codigoCuentaBancaria);
                                $ingresoActualCuentaB = (float) ($datos[0]['ingresos'] ?? 0);
                                $nuevoIngresoActual = $ingresoActualCuentaB + $monto;
                                $resultSalida = $this->model->actualizarIngresoCuenta($nuevoIngresoActual, $codigoCuentaBancaria);
                                if ($resultSalida != "ok") {
                                    throw new Exception("Error al actualizar ingreso para la cuenta");
                                }

                                $datos = $this->model->obtenerSaldoCuenta($codigoCuentaBancaria);
                                $saldoInicialCuenta = (float) ($datos[0]['saldoInicial'] ?? 0);
                                $salidaActualCuentaB = (float) ($datos[0]['salidas'] ?? 0);
                                //$ingresoAC = (float) ($datos[0]['ingresos'] ?? 0);
                                $nuevoSaldoCuenta = $saldoInicialCuenta + $nuevoIngresoActual  -  $salidaActualCuentaB;
                                //var_dump('saldoinicial= '.$saldoInicialCuenta, 'ingresos= '.$nuevoIngresoActual, $salidaActualCuentaB, $nuevoSaldoCuenta);
                                //exit;
                                $resultado = $this->model->actualizarSaldoCuentas($nuevoSaldoCuenta, $codigoCuentaBancaria);
                                if ($resultado != "ok") {
                                    throw new Exception("Error al actualizar saldo para la cuenta");
                                }
                            } else {
                                $saldoCliente = $this->model->obtenerSaldoCliente($codigoCliente);
                                $saldoActual = isset($saldoCliente['saldoCliente']) ? (float) $saldoCliente['saldoCliente'] : 0;
                                $nuevoSaldo = round($saldoActual + $monto, 2); // Asegurar 2 decimales
                                $actualizado = $this->model->actualizarSaldoCliente($nuevoSaldo, $codigoCliente);
                                if ($actualizado !== "ok") {
                                    throw new Exception("Error al actualizar saldo del cliente");
                                }

                                $ingresosProyecto = $this->model->obtenerIngresoProyecto($codigoProyecto);
                                $ingresoActual = isset($ingresosProyecto['ingresos']) ? (float) $ingresosProyecto['ingresos'] : 0;
                                $nuevoIngreso = round($ingresoActual + $monto, 2); // Asegurar 2 decimales
                                $ingresoActualizado = $this->model->actualizarIngresoProyecto($nuevoIngreso, $codigoProyecto);
                                if ($ingresoActualizado !== "ok") {
                                    throw new Exception("Error al actualizar el ingreso del proyecto");
                                }
                            }
                        }
                    } else    if (stripos($nombreTipoMovimiento, 'Pago Planilla') !== false) {
                        if ($codigoTipoPago == '05' || $codigoTipoPago == '04') {
                            // actualizar salida de la cuenta le sumamos el monto del movimiento
                            $salidaActualCuenta = $this->model->obtenerSalidasCuenta($codigoCuentaBancaria);
                            $nuevaSalida = $salidaActualCuenta['salidas'] + $monto;
                            $resultSalida = $this->model->actualizarSalidasCuentas($nuevaSalida, $codigoCuentaBancaria);
                            if ($resultSalida != "ok") {
                                throw new Exception("Error al actualizar salida para la cuenta");
                            }

                            // actualizar saldo de la cuenta 
                            $datos = $this->model->obtenerSaldoCuenta($codigoCuentaBancaria);
                            $saldoInicialCuenta = (float) ($datos[0]['saldoInicial'] ?? 0);
                            $ingresoActualCuenta = (float) ($datos[0]['ingresos'] ?? 0);
                            $nuevoSaldoCuenta = $saldoInicialCuenta + $ingresoActualCuenta - $nuevaSalida;
                            $resultado = $this->model->actualizarSaldoCuentas($nuevoSaldoCuenta, $codigoCuentaBancaria);
                            if ($resultado != "ok") {
                                throw new Exception("Error al actualizar saldo para la cuenta");
                            }
                        }
                    } else    if (stripos($nombreTipoMovimiento, 'Compra') !== false) {
                        if ($codigoTipoPago == 1 || $codigoTipoPago == 2) {
                            // actualizar salida de la cuenta le sumamos el monto del movimiento
                            $salidaActualCuenta = $this->model->obtenerSalidasCuenta($codigoCuentaBancaria);
                            $nuevaSalida = $salidaActualCuenta['salidas'] + $monto;
                            $resultSalida = $this->model->actualizarSalidasCuentas($nuevaSalida, $codigoCuentaBancaria);
                            if ($resultSalida != "ok") {
                                throw new Exception("Error al actualizar salida para la cuenta");
                            }

                            // actualizar saldo de la cuenta 
                            $datos = $this->model->obtenerSaldoCuenta($codigoCuentaBancaria);
                            $saldoInicialCuenta = (float) ($datos[0]['saldoInicial'] ?? 0);
                            $ingresoActualCuenta = (float) ($datos[0]['ingresos'] ?? 0);
                            $nuevoSaldoCuenta = $saldoInicialCuenta + $ingresoActualCuenta - $nuevaSalida;
                            $resultado = $this->model->actualizarSaldoCuentas($nuevoSaldoCuenta, $codigoCuentaBancaria);
                            if ($resultado != "ok") {
                                throw new Exception("Error al actualizar saldo para la cuenta");
                            }
                        }
                    }


                    // Confirmar la transacción
                    $this->model->confirmarTransaccion();
                    $msg = "si";
                } else if ($data === "existe") {
                    // Revertir la transacción si el código ya existe
                    $this->model->revertirTransaccion();
                    $msg = "El código de transacción ya existe";
                } else {
                    // Revertir la transacción si hubo otro error
                    $this->model->revertirTransaccion();
                    $msg = "Error al registrar";
                }
            } catch (Exception $e) {
                // Capturar cualquier error y hacer rollback
                $this->model->revertirTransaccion();
                $msg = "Error inesperado: " . $e->getMessage();
            }
        }
        // Enviar respuesta JSON
        $this->sendJsonResponse($msg);
    }

    public function listar()
    {
        $start = $_POST['start']; // Índice de inicio
        $length = $_POST['length']; // Número de registros por página
        $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : ""; // Búsqueda por defecto
        $customSearch = isset($_POST['searchValue']) ? $_POST['searchValue'] : ""; // Búsqueda personalizada

        // Si hay búsqueda personalizada, la aplicamos
        if (!empty($customSearch)) {
            $search = $customSearch;
        }

        $result = $this->model->historialMovimientos($start, $length, $search);
        $data = $result['mov'];
        $total = $result['total'];

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] =
                '<div class="text-center">
                        <button class="btn btn-editar" type="button" data-id="' . $data[$i]["transaccion"] . '"><i class="fas fa-file-pdf"></i></button>
                    <div/>';
        }

        // Responde con el formato esperado por DataTables
        $result = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data
        );

        $this->sendJsonResponse($result);
    }

    // public function listarRecibos()
    // {
    //     $start = $_POST['start']; // Índice de inicio
    //     $length = $_POST['length']; // Número de registros por página
    //     $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : ""; // Búsqueda por defecto
    //     $customSearch = isset($_POST['searchValue']) ? $_POST['searchValue'] : ""; // Búsqueda personalizada

    //     // Si hay búsqueda personalizada, la aplicamos
    //     if (!empty($customSearch)) {
    //         $search = $customSearch;
    //     }

    //     $result = $this->model->historialMovimientosPlanilla($start, $length, $search);
    //     $data = $result['recibos'];
    //     $total = $result['total'];

    //     for ($i = 0; $i < count($data); $i++) {
    //         $data[$i]['acciones'] =
    //             '<div class="text-center">
    //                     <button class="btn btn-editar" type="button" data-id="' . $data[$i]["transaccion"] . '"><i class="fas fa-file-pdf"></i></button>
    //                 <div/>';
    //     }

    //     // Responde con el formato esperado por DataTables
    //     $result = array(
    //         "draw" => intval($_POST['draw']),
    //         "recordsTotal" => $total,
    //         "recordsFiltered" => $total,
    //         "data" => $data
    //     );

    //     $this->sendJsonResponse($result);
    // }

    public function generarPdf($numeroTransaccion)
    {

        $movimientos = $this->model->getMovimientos($numeroTransaccion);
        $monto = $movimientos[0]['monto'];
        $empresa = $this->model->datosEmpresa();


        $date = new DateTime($movimientos[0]['fecha']);
        $meses = [
            'January' => 'enero',
            'February' => 'febrero',
            'March' => 'marzo',
            'April' => 'abril',
            'May' => 'mayo',
            'June' => 'junio',
            'July' => 'julio',
            'August' => 'agosto',
            'September' => 'septiembre',
            'October' => 'octubre',
            'November' => 'noviembre',
            'December' => 'diciembre'
        ];

        $dia = $date->format('j');
        $mes = $meses[$date->format('F')];
        $anio = $date->format('Y');

        // Crear el formateador para la parte entera del monto
        $fmt = new NumberFormatter("es_ES", NumberFormatter::SPELLOUT);

        // Separar la parte entera y la parte decimal
        $montoEntero = floor($monto);
        $montoDecimal = round(($monto - $montoEntero) * 100); // Los centavos

        // Convertir la parte entera a palabras
        $textEntero = strtoupper($fmt->format($montoEntero));

        // Mostrar los centavos como números en lugar de palabras
        $textDecimal = str_pad($montoDecimal, 2, "0", STR_PAD_LEFT); // Asegurarse de tener siempre 2 dígitos para los centavos

        // Construir la cadena final del monto en texto
        $text = $textEntero . " CON " . $textDecimal . "/100 DÓLARES";

        require('Libraries/tcpdf/tcpdf.php');

        ob_start(); // Inicia el buffer de salida
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);


        // Desactivar encabezado y pie de página
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setFooterMargin(15);

        $pdf->AddPage();

        if (!empty($movimientos[0]['codigoEm'])) {
            if ($movimientos[0]['tipoPagoEmpleado'] == 1) {
                $pdf->SetTitle('Pago ' . $movimientos[0]['empleado']);

                $pdf->Ln(30);

                // Logo
                $pdf->Image(base_url . 'Assets/img/logo.jpg', 10, 11, 78, 25);

                $pdf->Ln(15);
                $pdf->SetFont('', 'B', 14);
                $pdf->Cell(190, 8, 'RECIBO POR $' . number_format($movimientos[0]['monto'], 2), 0, 1, 'C');

                $pdf->Ln(20);

                $pdf->SetFont('', '', 14);
                $concepto = "Recibí de " . $empresa[0]['nombre'] . " la cantidad de " . $text . " en concepto de pago de planilla";

                // Ajuste para que el texto se muestre centrado
                $pdf->MultiCell(190, 6, $concepto, 0, 'L');

                $pdf->Ln(20);

                $pdf->Cell(60, 6, $empresa[0]['direccion'] . ', ' . $dia . " de " . $mes . " de " . $anio, 0, 1, 'L');

                $pdf->Ln(20);
                $pdf->Cell(30, 7, 'Recibí conforme: ', 0, 1);
                $pdf->Cell(40, 7, '', 0, 0);
                $pdf->Cell(50, 0, '', 'T', 0, 'L');

                $pdf->Ln(2);
                $pdf->Cell(40, 7, '', 0, 0);
                $pdf->Cell(50, 0, $movimientos[0]['empleado'], 0, 1, 'L');
            } else if ($movimientos[0]['tipoPagoEmpleado'] == 2) {


                $pdf->SetTitle('Anticipo ' . $movimientos[0]['empleado']);

                $pdf->Ln(30);

                // Logo
                $pdf->Image(base_url . 'Assets/img/logo.jpg', 10, 11, 78, 25);

                $pdf->Ln(15);
                $pdf->SetFont('', 'B', 14);
                $pdf->Cell(190, 8, 'RECIBO POR $' . number_format($movimientos[0]['monto'], 2), 0, 1, 'C');

                $pdf->Ln(20);

                $pdf->SetFont('', '', 14);
                // Texto del recibo
                $concepto = "Recibí de " . $empresa[0]['nombre'] . " la cantidad de " . $text . " en concepto de anticipo de pago de planilla.";
                //$concepto = $movimientos[0]['empleado'] . ", " . "por un monto de $ " . number_format($movimientos[0]['monto'], 2) . " recibí de " . $empresa . " la cantidad de " . $text . " en concepto de anticipo de pago de planilla.";

                // Ajuste para que el texto se muestre centrado
                $pdf->MultiCell(190, 6, $concepto, 0, 'L');

                $pdf->Ln(20);

                $pdf->Cell(60, 6, $empresa[0]['direccion'] . ', ' . $dia . " de " . $mes . " de " . $anio, 0, 1, 'L');

                $pdf->Ln(20);
                $pdf->Cell(30, 7, 'Recibí conforme: ', 0, 1);
                $pdf->Cell(40, 7, '', 0, 0);
                $pdf->Cell(50, 0, '', 'T', 0, 'L');

                $pdf->Ln(2);
                $pdf->Cell(40, 7, '', 0, 0);
                $pdf->Cell(50, 0, $movimientos[0]['empleado'], 0, 1, 'L');
            }
        } else {
            $pdf->SetTitle('Datos Movimiento');

            // Logo
            $pdf->Image(base_url . 'Assets/img/logo.jpg', 10, 6, 78, 25);

            // Espacio antes de la sección de datos
            $pdf->Ln(25);

            // Encabezado del documento
            $pdf->SetFont('', 'B', 14);
            $pdf->Cell(186, 8, 'Movimiento Financiero ' . $movimientos[0]['nombreMovimiento'], 0, 1, 'C');
            $pdf->Ln(5);

            // Detalles del movimiento
            $pdf->SetFont('', 'B', 12);
            $pdf->Cell(50, 6, 'Fecha:', 0, 0, 'L');
            $pdf->SetFont('', '', 12);
            $pdf->Cell(60, 6, $movimientos[0]['fecha'], 0, 1, 'L');

            $pdf->SetFont('', 'B', 12);
            $pdf->Cell(50, 6, 'Transacción:', 0, 0, 'L');
            $pdf->SetFont('', '', 12);
            $pdf->Cell(60, 6, $numeroTransaccion, 0, 1, 'L');

            // $pdf->SetFont('Times', 'B', 12);
            // $pdf->Cell(50, 6, 'Movimiento:', 0, 0, 'L');
            // $pdf->SetFont('Times', '', 12);
            // $pdf->Cell(60, 6, $movimientos[0]['nombreMovimiento'], 0, 1, 'L');

            $pdf->SetFont('', 'B', 12);
            $pdf->Cell(50, 6, 'Número Documento:', 0, 0, 'L');
            $pdf->SetFont('', '', 12);
            $pdf->Cell(60, 6, $movimientos[0]['numeroDocumento'], 0, 1, 'L');

            // Validar si es proveedor
            if (!empty($movimientos[0]['codigoProveedor'])) {
                $pdf->SetFont('', 'B', 12);
                $pdf->Cell(50, 6, 'Proveedor:', 0, 0, 'L');
                $pdf->SetFont('', '', 12);
                $pdf->Cell(30, 6, $movimientos[0]['codigoProveedor'], 0, 0, 'L');
                $pdf->Cell(60, 6, $movimientos[0]['nombreProveedor'], 0, 1, 'L');
            }

            // Validar si es cliente
            if (!empty($movimientos[0]['codigoCliente'])) {
                $pdf->SetFont('', 'B', 12);
                $pdf->Cell(50, 6, 'Cliente:', 0, 0, 'L');
                $pdf->SetFont('', '', 12);
                $pdf->Cell(30, 6, $movimientos[0]['codigoCliente'], 0, 0, 'L');
                $pdf->Cell(60, 6, $movimientos[0]['nombreCliente'], 0, 1, 'L');
            }

            if (!empty($movimientos[0]['codigoProyecto'])) {
                $pdf->SetFont('', 'B', 12);
                $pdf->Cell(50, 6, 'Proyecto:', 0, 0, 'L');
                $pdf->SetFont('', '', 12);
                $pdf->Cell(30, 6, $movimientos[0]['codigoProyecto'], 0, 0, 'L');
                $pdf->Cell(60, 6, $movimientos[0]['nombreProyecto'], 0, 1, 'L');
            }


            $pdf->SetFont('', 'B', 12);
            $pdf->Cell(50, 6, 'Monto:', 0, 0, 'L');
            $pdf->SetFont('', '', 12);
            $pdf->Cell(60, 6, number_format($movimientos[0]['monto'], 2), 0, 1, 'L');

            $pdf->SetFont('', 'B', 12);
            $pdf->Cell(50, 6, 'Tipo Pago:', 0, 0, 'L');
            $pdf->SetFont('', '', 12);
            $pdf->Cell(60, 6, $movimientos[0]['pago'], 0, 1, 'L');

            if (!empty($movimientos[0]['codigoBanco'])) {
                $pdf->SetFont('', 'B', 12);
                $pdf->Cell(50, 6, 'Banco:', 0, 0, 'L');
                $pdf->SetFont('', '', 12);
                $pdf->Cell(30, 6, $movimientos[0]['codigoBanco'], 0, 0, 'L');
                $pdf->Cell(60, 6, $movimientos[0]['nombreBanco'], 0, 1, 'L');
            }

            if (!empty($movimientos[0]['codigoCuentaBancaria'])) {
                $pdf->SetFont('', 'B', 12);
                $pdf->Cell(50, 6, 'Cuenta Bancaria:', 0, 0, 'L');
                $pdf->SetFont('', '', 12);
                $pdf->Cell(60, 6, $movimientos[0]['nombreCuenta'], 0, 1, 'L');
            }
        }


        if (ob_get_length()) {
            ob_end_clean(); // Limpia solo si hay contenido en el buffer
        }
        // Generar salida del PDF
        $pdf->Output();
    }


    public function salir()
    {
        session_destroy();
        header("location: " . base_url);
    }
}
?>
<?php
// Habilitar el reporte de todos los errores
error_reporting(E_ALL);
ini_set('display_errors', '1');



