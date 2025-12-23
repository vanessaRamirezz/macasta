<?php
require 'vendor/autoload.php';
require_once './services/DTEProcessor.php';

use Ramsey\Uuid\Uuid;
use Dompdf\Dompdf;

class Facturacion extends Controller
{

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        parent::__construct();
    }

    public function index()
    {
        if (empty($_SESSION['codigoUsuario'])) {
            header("Location: " . base_url);
        }

        unset($_SESSION['documentosRelacionados']);

        $estado = $this->model->obtenerEstadoContingencia();

        // Si hay un resultado, hay contingencia activa
        $contingenciaActiva = !empty($estado);

        // Enviar a la vista
        $this->views->getView($this, "index", ["contingenciaActiva" => $contingenciaActiva]);

        // $this->views->getView($this, "index");
    }

    public function tipoDocumento()
    {
        // $query = $_GET['q'] ?? '';

        // $clienteId = $_POST['id'];
        // $cliente = $this->model->getClienteNrc($clienteId);

        // // Determina qué tipo de documento corresponde
        // if (isset($cliente['nrc']) && trim($cliente['nrc']) !== '') {
        //     $data = $this->model->searchTipoDocumentoFE();
        // } else {
        //     $data = $this->model->searchTipoDocumentoCFE();
        // }

        // $this->sendJsonResponse($data);
    }

    public function tipoOperacion()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->getCodigoOperacion($query);
        $this->sendJsonResponse($data);
    }

    public function seleccionar()
    {
        $idProducto = isset($_POST['codigoProducto']) ? $_POST['codigoProducto'] : null;
        $codigoCliente = isset($_POST['codigoClienteDetalle']) ? $_POST['codigoClienteDetalle'] : null;
        $cantidad = isset($_POST['cantidadProducto']) ?  $_POST['cantidadProducto'] : 0;
        $precioCosto = isset($_POST['precioCosto']) ? (float) str_replace(',', '', $_POST['precioCosto']) : 0;
        $precioVenta = isset($_POST['precioVenta']) ? (float) str_replace(',', '', $_POST['precioVenta']) : 0;
        $documentoRelacionado = isset($_POST['documentoRelacionado']) ? $_POST['documentoRelacionado'] : null;
        $unidadDeMedida = isset($_POST['unidadMedida']) ? $_POST['unidadMedida'] : null;
        $documentosRelacionadosDisponibles = $_SESSION['documentosRelacionados'] ?? [];

        $tipoDte = $_POST['tipoDte'] ?? null;
        // var_dump($tipoDte);exit;


        $cliente = $this->model->getClienteNrc($codigoCliente);

        $descripcionNo = trim($_POST['descripcionNo']);
        $montoNo = trim($_POST['montoNo']);
        $idUsuario = $_SESSION['codigoUsuario'];

        $descripcionNota = trim($_POST['descripcionNota']);


        if (!empty($descripcionNo) && !empty($montoNo)) {
            $idProductoN = ''; // ID del producto
            $cantidadN = 1; // Cantidad fija en 1
            $precioCostoN = 0.00; // Costo siempre será 0
            $precioVentaN = 0.00; // Usar montoNo como precioVenta
            $totalN = round($precioVentaN * $cantidadN, 2); // Total = cantidad * precioVenta
            $unidadDeMedidaN = 99; // Unidad de medida fija en 99
            $descripcionN = $descripcionNo; // Usar descripcionNo como descripción

            // Registrar detalle
            $data = $this->model->registrarDetalle($idProductoN, $cantidadN, $precioCostoN, $precioVentaN, $montoNo, $idUsuario, $documentoRelacionado, $unidadDeMedidaN, $descripcionN, $descripcionNota);
            if ($data !== "ok") {
                $msg = "Error al registrar detalle no afecto";
            } else {
                $msg = "ok";
            }

            // IMPORTANTE: detener aquí el flujo
            $this->sendJsonResponse($msg);
            // return;
        }


        if (empty($idProducto) || $cantidad <= 0) {
            $msg = "Debe seleccionar un producto y una cantidad válida";
        } else if (empty($unidadDeMedida)) {
            $msg = "Debe Ingresar unidad de medida al producto";
        } else if (count($documentosRelacionadosDisponibles) > 0 && empty($documentoRelacionado) && !in_array($tipoDte, ['01', '03'])) {
            $msg = "Debe seleccionar el documento relacionado en este producto";
        } else {
            try {
                // Iniciar la transacción
                $this->model->iniciarTransaccion();

                $datos = $this->model->getProducto($idProducto);
                if (!$datos) {
                    throw new Exception("Producto no encontrado");
                }

                $idUsuario = $_SESSION['codigoUsuario'];
                $comprobar = $this->model->consultarDetalle($idProducto, $idUsuario);
                $ultimoId = $this->model->obtenerUltimoIdDetalle($idUsuario);

                if (!empty($codigoCliente)) {
                    // comprueba si ya ay producto resgitrado en el detalle lo actualiza nada mas
                    if (empty($comprobar)) {

                        if (!empty($cliente) && isset($cliente['nrc'])) {
                            // Calcular precio sin IVA (precio / 1.13)
                            $precioSinIva = round($precioVenta / 1.13, 2);

                            // Calcular el total sin IVA (antes de redondear)
                            $total = $precioSinIva * $cantidad;

                            // Redondear solo el total final a 2 decimales
                            $total = round($total, 2);

                            if (!empty($ultimoId) && $ultimoId['max_id'] >= 2000) {
                                throw new Exception("No puede seguir agregando más productos. Límite alcanzado.");
                            } else {
                                // Registrar detalle con el precio sin IVA y total redondeado
                                $data = $this->model->registrarDetalle($idProducto, $cantidad, $precioCosto, $precioSinIva, $total, $idUsuario, $documentoRelacionado, $unidadDeMedida, $descripcionNo, $descripcionNota);
                            }
                        } else {
                            if (!empty($ultimoId) && $ultimoId['max_id'] >= 2000) {
                                throw new Exception("No puede seguir agregando más productos. Límite alcanzado.");
                            } else {
                                // Para cliente final, usar precio con IVA normal
                                $total = round($precioVenta * $cantidad, 2);
                                $data = $this->model->registrarDetalle($idProducto, $cantidad, $precioCosto, $precioVenta, $total, $idUsuario, $documentoRelacionado, $unidadDeMedida, $descripcionNo, $descripcionNota);
                            }
                        }


                        if ($data !== "ok") {
                            throw new Exception("Error al registrar detalle");
                        }

                        $msg = "ok";
                    } else {
                        if (!empty($cliente) && isset($cliente['nrc'])) {
                            $totalCantidad = $comprobar['cantidad'] + $cantidad;
                            // Calcular precio sin IVA incluido (precio / 1.13)
                            $precioSinIva = $precioVenta / 1.13;


                            $totalActualizar = round($totalCantidad * $precioSinIva, 2);

                            $data = $this->model->actualizarDetalle($totalCantidad, $precioCosto, $precioSinIva, $totalActualizar, $documentoRelacionado, $unidadDeMedida, $idProducto, $idUsuario);
                        } else {
                            $totalCantidad = $comprobar['cantidad'] + $cantidad;
                            $totalActualizar = round($totalCantidad * $precioVenta, 2);
                            $data = $this->model->actualizarDetalle($totalCantidad, $precioCosto, $precioVenta, $totalActualizar, $documentoRelacionado, $unidadDeMedida, $idProducto, $idUsuario);
                            if ($data !== "modificado") {
                                throw new Exception("Error al modificar detalle");
                            }
                        }


                        $msg = "modificado";
                    }
                } else {
                    throw new Exception("Debe selecionar un cliente para registrar productos");
                }

                // Confirmar la transacción
                $this->model->confirmarTransaccion();
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                $this->model->revertirTransaccion();
                $msg = "Error inesperado: " . $e->getMessage();
            }
        }
        $this->sendJsonResponse($msg);
    }

    public function listarDetalle()
    {
        $idUsuario = $_SESSION['codigoUsuario'];
        $codigoCliente = $_POST['selectCliente'];
        $cliente = $this->model->getClienteNrc($codigoCliente);

        $subTotal = 0;
        $ivaRetenido = 0;
        $total = 0;

        $detalle = $this->model->getDetalle($idUsuario);

        foreach ($detalle as $row) {
            $subTotal += $row['total']; // Sumar el total de cada producto
        }

        if (!empty($cliente) && isset($cliente['nrc'])) {
            // Calcular el IVA sobre el subtotal completo y luego redondearlo
            $ivaRetenido = round($subTotal * 0.13, 2);
            $total = round($subTotal + $ivaRetenido, 2);
        } else {
            $total = $subTotal;
        }

        $data['detalle'] = $detalle;
        $data['subTotal'] = $subTotal;
        $data['ivaRetenido'] = $ivaRetenido;
        $data['total'] = $total;

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function vaciarDetalleFacturacion()
    {
        $idUsuario = $_SESSION['codigoUsuario'];

        // Vaciar la tabla de detalles para el usuario
        $vaciar = $this->model->vaciarDetalleCancelar($idUsuario);
        if ($vaciar == 'ok') {
            echo json_encode('ok');
        } else {
            echo json_encode('Error al vaciar los detalles de compra');
        }
    }

    public function validarNRCCliente()
    {
        $clienteId = $_POST['id'];
        $cliente = $this->model->getClienteNrc($clienteId);
        $codigoTipoN = '05';
        $tipoNotaCredito = $this->model->getTipoDocumento($codigoTipoN);
        $dataN = $this->model->searchTipoDocumentoNC();

        // Determina qué tipo de documento corresponde
        if (isset($cliente['nrc']) && trim($cliente['nrc']) !== '') {
            $codigoTipo = '03';
            $tipoDocumento = $this->model->getTipoDocumento($codigoTipo);
            $data = $this->model->searchTipoDocumentoCFE();
        } else {
            $codigoTipo = '01';
            $tipoDocumento = $this->model->getTipoDocumento($codigoTipo);
            $data = $this->model->searchTipoDocumentoFE();
        }

        // Enviar ambos resultados correctamente
        if ($tipoDocumento) {
            $this->sendJsonResponse([
                'tipo_documento' => $tipoDocumento,
                'dataTipoRelacionado' => $data,
                'nota_credito' => $tipoNotaCredito,
                'relacionadoN' => $dataN
            ]);
        } else {
            $this->sendJsonResponse(['error' => 'Tipo de documento no encontrado']);
        }
    }


    // CONTROLADORES PARA DOCUMENTOS RELACIONADOS INICO
    public function tipoGeneracion()
    {
        $tipos = $this->model->getTipoGeneracion();

        // Extraer los elementos dentro de 'tipoGeneracion'
        $resultados = [];
        foreach ($tipos['tipoGeneracion'] as $codigo => $tipo) {
            $resultados[] = [
                'id' => $codigo,     // El ID será el código de cada tipo
                'text' => $tipo['nombre']  // El texto será el nombre
            ];
        }

        // Enviar la respuesta JSON
        $this->sendJsonResponse($resultados);
    }

    public function docRelacionados()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $tipoDocumento = trim($data['tipoDocumento'] ?? '');
        $tipoGeneracion = trim($data['tipoGeneracion'] ?? '');
        $numeroDoc = trim($data['numeroDoc'] ?? '');
        $fechaEmision = trim($data['fechaEmision'] ?? '');

        if (empty($tipoDocumento) || empty($tipoGeneracion) || empty($numeroDoc) || empty($fechaEmision)) {
            $msg = 'Todos los campos son obligatorios.';
            $this->sendJsonResponse(['success' => false, 'message' => $msg]);
        }

        // Validar que numeroDoc tenga solo números o un patrón específico
        if ((int)$tipoGeneracion === 2) {

            $expresionRegular = "/^[A-F0-9]{8}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{12}$/";
            if (!preg_match($expresionRegular, $numeroDoc)) {
                $msg = 'El codigo de generacion no cumple con el formato requerido.';
                $this->sendJsonResponse(['success' => false, 'message' => $msg]);
            }
        } else if ((int) $tipoGeneracion === 1) {

            $expresionValidar = "/^[A-Z0-9\-]{1,20}$/";
            if (!preg_match($expresionValidar, $numeroDoc)) {
                $msg = 'El numero de correlativo no cumple con el formato requerido';
                $this->sendJsonResponse(['success' => false, 'message' => $msg]);
            }
        }

        $documento = [
            // 'tipoDocumento' => $tipoDocumento,
            // 'tipoGeneracion' => $tipoGeneracion,
            'numeroDoc' => $numeroDoc,
            // 'fechaEmision' => $fechaEmision,
        ];

        // Guardarlo en sesión
        $_SESSION['documentosRelacionados'][] = $documento;

        $this->sendJsonResponse(['success' => true]);
    }

    public function obtenerDocumentosRelacionados()
    {

        $documentos = $_SESSION['documentosRelacionados'] ?? [];

        // Convertir al formato que Select2 espera: [{ id, text }]
        $resultado = [];
        foreach ($documentos as $doc) {
            $texto = "{$doc['numeroDoc']}";

            $resultado[] = [
                'id' => $doc['numeroDoc'],
                'text' => $texto
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($resultado);
    }

    public function eliminarDocumentoRelacionado()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $numeroDoc = trim($data['numeroDoc'] ?? '');

        if (!$numeroDoc) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Número de documento no válido']);
        }

        // Verifica si hay sesión
        if (!isset($_SESSION['documentosRelacionados'])) {
            $_SESSION['documentosRelacionados'] = [];
        }

        // Elimina de sesión
        $_SESSION['documentosRelacionados'] = array_filter(
            $_SESSION['documentosRelacionados'],
            fn($doc) => $doc['numeroDoc'] !== $numeroDoc
        );

        // Limpia en la tabla detalleTemporal
        $idUsuario = $_SESSION['codigoUsuario'];

        $resultado = $this->model->limpiarDocumentoRelacionado($numeroDoc, $idUsuario);

        if ($resultado) {
            $this->sendJsonResponse(['success' => true]);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'No se pudo actualizar detalleTemporal']);
        }
    }
    // CONTROLADORES PARA DOCUMENTOS RELACIONADOS FIN

    // CONTROLADOR PARA DOC ASOCIADOS INICIO
    public function docAsociados()
    {
        $tipos = $this->model->getDocAsociado();


        // Extraer los elementos dentro de 'tipoGeneracion'
        $resultados = [];
        foreach ($tipos['codDocAsociados'] as $codigo => $tipo) {
            $resultados[] = [
                'id' => $codigo,     // El ID será el código de cada tipo
                'text' => $tipo['nombre']  // El texto será el nombre
            ];
        }
        // Enviar la respuesta JSON
        $this->sendJsonResponse($resultados);
    }

    public function tipoServicioMedico()
    {
        $tipos = $this->model->getTipoServicioMedico();


        // Extraer los elementos dentro de 'tipoGeneracion'
        $resultados = [];
        foreach ($tipos['tipoServicioMedico'] as $codigo => $tipo) {
            $resultados[] = [
                'id' => $codigo,     // El ID será el código de cada tipo
                'text' => $tipo['nombre']  // El texto será el nombre
            ];
        }
        // Enviar la respuesta JSON
        $this->sendJsonResponse($resultados);
    }

    public function documentosASOC()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $identificacion = trim($data['identificacionDoc'] ?? '');
        $descripcion = trim($data['descripcionDoc'] ?? '');

        if (empty($identificacion) || empty($descripcion)) {
            $msg = 'Todos los campos son obligatorios.';
            $this->sendJsonResponse(['success' => false, 'message' => $msg]);
        }

        if (strlen($identificacion) > 100) {
            $msg = 'La identificacion excede del limite requerido';
            $this->sendJsonResponse(['success' => false, 'message' => $msg]);
        }

        if (strlen($descripcion) > 300) {
            $msg = 'La descripción excede del limite requerido';
            $this->sendJsonResponse(['success' => false, 'message' => $msg]);
        }

        $this->sendJsonResponse(['success' => true]);
    }

    public function documentosMedico()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $codigo = trim($data['codigo'] ?? '');
        $tipoServicio = trim($data['tipoServicio'] ?? '');
        $nombreMedico = trim($data['nombreMedico'] ?? '');
        $tipoDocumento = trim($data['tipoDocumentoAS'] ?? '');
        $nitMedico = trim($data['nitMedico'] ?? '');

        if (empty($nombreMedico)) {
            $msg = 'Nombre es obligatorio.';
            $this->sendJsonResponse(['success' => false, 'message' => $msg]);
        } else if (empty($tipoServicio)) {
            $msg = 'Tipo Servicio es obligatorio.';
            $this->sendJsonResponse(['success' => false, 'message' => $msg]);
        }

        if (!empty($nitMedico)) {
            $expresionRegular = "/^([0-9]{14}|[0-9]{9})$/";
            if (!preg_match($expresionRegular, $nitMedico)) {
                $msg = 'El Nit no cumple con el formato requerido.';
                $this->sendJsonResponse(['success' => false, 'message' => $msg]);
            }
        }

        if (empty($tipoDocumento) && empty($nitMedico)) {
            $msg = 'Ingrese numero de NIT o documento que identifique al médico en caso no tenga NIT ';
            $this->sendJsonResponse(['success' => false, 'message' => $msg]);
        }

        if (!empty($tipoDocumento) && !empty($nitMedico)) {
            $msg = 'No puede ingresar NIT y Documento no domiciliado debe ser uno de los dos campos';
            $this->sendJsonResponse(['success' => false, 'message' => $msg]);
        }


        $this->sendJsonResponse(['success' => true]);
    }
    // CONTROLADOR PARA DOC ASOCIADOS FIN

    // CONTROLADOR PARA VENTA TERCEROS INICIO
    public function ventaTercero()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $nit = trim($data['nitTercero'] ?? '');
        $nombreTercero = trim($data['nombreTercero'] ?? '');

        if (empty($nit) || empty($nombreTercero)) {
            $msg = 'Todos los campos son obligatorios.';
            $this->sendJsonResponse(['success' => false, 'message' => $msg]);
        }

        if (!empty($nit)) {
            $expresionRegular = "/^([0-9]{14}|[0-9]{9})$/";
            if (!preg_match($expresionRegular, $nit)) {
                $msg = 'El Nit no cumple con el formato requerido.';
                $this->sendJsonResponse(['success' => false, 'message' => $msg]);
            }
        }
        $this->sendJsonResponse(['success' => true]);
    }
    // CONTROLADOR PARA VENTA TERCEROS FIN

    public function unidadDeMedida()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->getUnidadMedida($query);
        $this->sendJsonResponse($data);
    }

        public function numeroDeControl($tipoDocumento)
    {
        $facturaDatos = $this->model->datosFactura();
        $ambiente = $facturaDatos['ambiente'];
        $codigoCasaMatriz = str_pad($facturaDatos['codigoCasaMatriz'], 4, "0", STR_PAD_LEFT);  // 4 dígitos
        $codigoPuntoVenta = str_pad($facturaDatos['codigoPuntoVenta'], 4, "0", STR_PAD_LEFT);  // 4 dígitos
        // $correlativo = str_pad($facturaDatos['correlativo'], 15, "0", STR_PAD_LEFT);  // 15 dígitos
        $correlativo = $this->model->generarCorrelativos($tipoDocumento, $ambiente);

        // Generar el número de control
        $numeroControl = "DTE-" . $tipoDocumento . '-' . $codigoCasaMatriz . $codigoPuntoVenta . "-" . $correlativo;

        // Definir el patrón regex
        $pattern = "/^DTE-[0-9]{2}-[A-Z0-9]{8}-[0-9]{15}$/";

        // Validar el número de control
        if (!preg_match($pattern, $numeroControl)) {
            throw new Exception("Error: El número de control no cumple con el formato requerido.");
        }

        return $numeroControl;
    }

    public function codigoGeneracion()
    {

        $uuid = strtoupper(Uuid::uuid4()->toString());
        $expresionRegular = "/^[A-F0-9]{8}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{12}$/";

        if (!preg_match($expresionRegular, $uuid)) {
            throw new Exception("Error: El codigo de generacion no cumple con el formato requerido.");
        }
        return $uuid;
    }

    public function validarFechaEmi($fechaEmision)
    {

        $fechaEmiObj = DateTime::createFromFormat('Y-m-d', $fechaEmision);
        $fechaEmiObj->setTime(0, 0, 0); // Normaliza la hora

        $fechaActual = new DateTime(); // Fecha del sistema
        $fechaActual->setTime(0, 0, 0); // Normaliza la hora también


        // 1. Verificamos si hoy es el último día del mes
        $ultimoDiaMes = new DateTime($fechaActual->format('Y-m-t'));
        $ultimoDiaMes->setTime(0, 0, 0); // Normaliza también

        $esUltimoDiaDelMes = $fechaActual->format('Y-m-d') === $ultimoDiaMes->format('Y-m-d');

        // 2. Fecha de hace 2 días
        $haceDosDias = clone $fechaActual;
        $haceDosDias->modify('-2 days');

        if ($fechaEmiObj < $haceDosDias) {
            $msg = 'No puede generar DTE con más de 2 días de anterioridad';
            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
            return;
        } elseif ($fechaEmiObj > $fechaActual) {
            $msg = 'No puede generar DTE con fecha futura';
            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
            return;
        } elseif ($esUltimoDiaDelMes) {
            // El caso exacto que te falla
            if ($fechaEmiObj->format('Y-m-d') === $fechaActual->format('Y-m-d')) {
                return $fechaEmiObj;
            } else {
                $msg = 'No puede generar DTE con fecha distinta al último día del mes';
                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                return;
            }
        } else {
            $ayer = clone $fechaActual;
            $ayer->modify('-1 day');

            if ($fechaEmiObj >= $ayer) {
                return $fechaEmiObj;
            } else {
                $msg = 'No puede generar DTE con más de un día de anterioridad';
                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                return;
            }
        }
    }

    public function validarHoraEmi($horaEmi, $fechaEmi)
    {
        // Expresión regular con delimitadores `/` para validar el formato de la hora (hh:mm:ss)
        $pattern = "/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/";

        // Validar la hora con preg_match()
        if (!preg_match($pattern, $horaEmi)) {
            throw new Exception("Error: la hora no tiene el formato requerido.");
        }

        // Convertir la fecha de emisión a un objeto DateTime para validaciones adicionales
        $fecha = DateTime::createFromFormat('Y-m-d', $fechaEmi);
        if (!$fecha) {
            throw new Exception("Error: la fecha no tiene el formato correcto.");
        }

        // Obtener el último día del mes de la fecha proporcionada
        $ultimoDiaMes = new DateTime($fechaEmi);
        $ultimoDiaMes->modify('last day of this month');

        // Verificar si la fecha de emisión es el último día del mes
        if ($fechaEmi == $ultimoDiaMes->format('Y-m-d')) {
            // Permitir una holgura de 30 minutos para el último día del mes
            $horaLimite = new DateTime('now');
            $horaLimite->modify('+30 minutes');

            // Convertir la hora de emisión en un objeto DateTime
            $horaEmiObject = DateTime::createFromFormat('H:i:s', $horaEmi);

            // Verificar que la hora de emisión no supere la hora límite (hora actual + 30 minutos)
            if ($horaEmiObject > $horaLimite) {
                throw new Exception("Error: la hora de emisión no puede ser mayor a la hora límite.");
            }
        }

        // Si pasa todas las validaciones, devolver la hora de emisión
        return $horaEmi;
    }


    public function validarNit($nit)
    {
        // Si el NIT viene vacío o null, permitir continuar
        if ($nit === null || $nit === "") {
            return null; // se puede omitir
        }

        // Validar que el NIT tenga 9 o 14 dígitos
        $pattern = "/^([0-9]{9}|[0-9]{14})$/";

        if (!preg_match($pattern, $nit)) {
            $this->sendJsonResponse("El NIT no tiene el formato requerido.");
            exit; // detener ejecución después de enviar la respuesta
        }

        return $nit; // NIT válido
    }



    public function validarNrc($nrc)
    {
        // Si no se envía NRC, permitir continuar
        if ($nrc === null || $nrc === "") {
            return null; // se puede omitir
        }

        // NRC: entre 1 y 8 dígitos numéricos
        $pattern = "/^[0-9]{1,8}$/";

        if (!preg_match($pattern, $nrc)) {
            throw new Exception("Error: el NRC no tiene el formato requerido.");
        }

        return $nrc;
    }


    public function validarCodActividad($codActividad)
    {
        // Si no se envía nada, permitir continuar
        if ($codActividad === null || $codActividad === "") {
            return null; // se puede omitir
        }

        // Debe tener de 2 a 6 dígitos numéricos
        $pattern = "/^[0-9]{2,6}$/";

        if (!preg_match($pattern, $codActividad)) {
            throw new Exception("Error: el codActividad no tiene el formato requerido.");
        }

        return $codActividad;
    }


    public function validarDepartamento()
    {
        // validar departamento emisor
        $empresa = $this->model->getEmpresa();
        $departamento = $empresa['departamento'];

        // Expresión regular con delimitadores `/`
        $pattern = "/^0[1-9]|1[0-4]$/";

        // Validar la hora con preg_match()
        if (!preg_match($pattern, $departamento)) {
            throw new Exception("Error: el Departamento no tiene el formato requerido.");
        }

        return $departamento;
    }

    public function validarMunicipio()
    {
        $empresa = $this->model->getEmpresa();
        $municipio = $empresa['municipio'];

        // Expresión regular con delimitadores `/`
        $pattern = "/^[0-9]{2}$/";

        // Validar la hora con preg_match()
        if (!preg_match($pattern, $municipio)) {
            throw new Exception("Error: el municipio no tiene el formato requerido.");
        }

        return $municipio;
    }

    public function firmarJson($datosGenerados, $nit, $empresaId)
{
    if (!$datosGenerados || !$empresaId || !$nit) {
        return false;
    }

    $url = 'https://servidor-ws-xzj1.onrender.com/firmar'; // URL de Render

    $payload = json_encode([
        'empresaId' => $empresaId,
        'nit' => $nit,
        'dte' => $datosGenerados
    ], JSON_UNESCAPED_UNICODE);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true);

    // La respuesta del servidor es {"estado": "OK", "resultado": jsonFirmado}
    if ($statusCode === 200 && isset($decoded['estado']) && $decoded['estado'] === 'OK') {
        return $decoded['resultado']; // Aquí recibís el JSON firmado
    }

    return false;
}



    public function registrarCuerpoDocumento($cuerpoDocumento, $identificacion, $descripcionNotasPorItem = [])
    {
        foreach ($cuerpoDocumento as $row) {
            $descripcionNota = $descripcionNotasPorItem[$row['numItem']] ?? null;
            if ($row['tipoItem'] == 3) {
                $descricionProducto = $row['descripcion'];
            } else {
                $descricionProducto = null;
            }

            $resCuerpo = $this->model->registrarDTEcuerpo(
                $identificacion->numeroControl,
                $row['numItem'],
                $row['tipoItem'],
                $row['numeroDocumento'],
                $row['cantidad'],
                $row['codigo'],
                $row['codTributo'],
                $row['uniMedida'],
                $descricionProducto,
                $row['precioUni'],
                $row['montoDescu'],
                $row['ventaNoSuj'],
                $row['ventaExenta'],
                $row['ventaGravada'],
                is_array($row['tributos']) ? json_encode($row['tributos']) : null,
                $row['psv'] ?? null,
                $row['noGravado'] ?? null,
                $row['ivaItem'] ?? null,
                $descripcionNota ?? null
            );

            if ($resCuerpo !== "ok") {
                throw new Exception("Error al registrar el cuerpo del documento");
            }
        }
    }

    public function registrarDocumentosRelacionados($documentoRelacionado, $numeroControl)
    {
        if (!empty($documentoRelacionado) && is_array($documentoRelacionado)) {
            foreach ($documentoRelacionado as $row) {
                $numeroDocu = $row['numeroDocumento'];
                $tipoDoc = $row['tipoDocumento'];
                $tipoGene = $row['tipoGeneracion'];
                $fecha = $row['fechaEmision'];

                $resRelacionados = $this->model->registrarDocRelacionados(
                    $numeroDocu,
                    $tipoDoc,
                    $tipoGene,
                    $fecha,
                    $numeroControl
                );
                if ($resRelacionados !== "ok") {
                    throw new Exception("Error al registrar documento relacionado");
                }
            }
        }
    }

    public function registrarDocumentosAsociados($otrosDocumentosAsociados, $numeroControl)
    {
        if (!empty($otrosDocumentosAsociados) && is_array($otrosDocumentosAsociados)) {
            foreach ($otrosDocumentosAsociados as $row) {
                $numeroDocAsociado = $row['codDocAsociado'] ?? null;
                $desDocumentoAsociado = $row['descDocumento'] ?? null;
                $detalleAsociado = $row['detalleDocumento'] ?? null;

                $nombreMedico = null;
                $nitMedico = null;
                $identificacionmedico = null;
                $tipoServicioMedico = null;

                if (isset($row['medico']) && is_array($row['medico'])) {
                    $nombreMedico = $row['medico']['nombre'] ?? null;
                    $nitMedico = $row['medico']['nit'] ?? null;
                    $identificacionmedico = $row['medico']['docIdentificacion'] ?? null;
                    $tipoServicioMedico = $row['medico']['tipoServicio'] ?? null;
                }

                $resAsociados = $this->model->registrarDocAsociados(
                    $numeroDocAsociado,
                    $desDocumentoAsociado,
                    $detalleAsociado,
                    $nombreMedico,
                    $nitMedico,
                    $identificacionmedico,
                    $tipoServicioMedico,
                    $numeroControl
                );

                if ($resAsociados !== "ok") {
                    throw new Exception("Error al registrar documento asociado");
                }
            }
        }
    }

    public function registrarVentaTerceros($ventaTercero, $numeroControl)
    {
        if (!empty($ventaTercero)) {
            $resTercero = $this->model->registrarVentaTerceros(
                $ventaTercero->nit,
                $ventaTercero->nombre,
                $numeroControl
            );
            if ($resTercero !== "ok") {
                throw new Exception("Error al registrar venta a Terceros");
            }
        }
    }

        public function obtenerYGuardarToken()
    {
        // 1. Verificar si ya hay un token válido en la base de datos
        $tokenExistente = $this->model->getWebToken();

        if (!empty($tokenExistente) && isset($tokenExistente['token'])) {
            return ['token' => $tokenExistente['token']];
        }

        // 2. Configuración para solicitar nuevo token
        //$url = 'https://apitest.dtes.mh.gob.sv/seguridad/auth';
        $url = 'https://api.dtes.mh.gob.sv/seguridad/auth';
        $user = '02101205171019';
        //$pwd = 'MACASTA1*';
        $pwd = 'Macasta251*';
        $maxIntentos = 3;
        $intentos = 0;

        while ($intentos < $maxIntentos) {
            $intentos++;

            $postFields = http_build_query([
                'user' => $user,
                'pwd' => $pwd
            ]);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'User-Agent: MiSistemaFacturacion',
                    'Accept: application/json'
                ],
                CURLOPT_TIMEOUT => 8,
                CURLOPT_CONNECTTIMEOUT => 5
            ]);

            $response = curl_exec($ch);

            // 3. Verificar si hubo error de conexión
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                curl_close($ch);

                if ($intentos >= $maxIntentos) {
                    return [
                        'status' => 'error',
                        'message' => "No se pudo conectar a Hacienda después de $intentos intentos automaticos realizados.",
                        'detalle' => $error_msg
                    ];
                }

                sleep(8);
                continue;
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $decoded = json_decode($response, true);

            // 4. Verificar respuesta de Hacienda
            if ($httpCode === 200 && isset($decoded['status']) && $decoded['status'] === 'OK') {
                $token = $decoded['body']['token'];
                $fecha_obtenido = date('Y-m-d H:i:s');
                $fecha_expira = date('Y-m-d H:i:s', strtotime('+24 hours'));

                $resultado = $this->model->guardarToken($token, $fecha_obtenido, $fecha_expira);

                if ($resultado === "ok") {
                    return ['token' => $token];
                } else {
                    return [
                        'status' => 'error',
                        'message' => 'El token se obtuvo pero no se guardó en la base de datos'
                    ];
                }
            } else {
                // 5. Error en respuesta
                if ($intentos >= $maxIntentos) {
                    return [
                        'status' => 'error',
                        'message' => 'Error al obtener el token después de varios intentos.',
                        'detalle' => isset($decoded['message']) ? $decoded['message'] : $response
                    ];
                }

                sleep(8);
            }
        }

        // 6. Fallback en caso de que todo falle (por seguridad)
        return [
            'status' => 'error',
            'message' => 'Error no identificado al intentar obtener el token'
        ];
    }


    // public function obtenerTokenValido()
    // {


    //     $nuevoToken = $this->obtenerYGuardarToken();

    //     if (isset($nuevoToken['status']) && $nuevoToken['status'] === 'error') {
    //         return $nuevoToken; // <-- Devuelve el error tal cual
    //     }

    //     return $nuevoToken;
    // }


    public function generar()
    {
        date_default_timezone_set('America/El_Salvador');

	$Ivarenta = isset($_POST['retenido']) ? floatval($_POST['retenido']) : 0.00;
        $idUsuario = $_SESSION['codigoUsuario'];
        $facturaDatos = $this->model->datosFactura();
        $razonSocial = $this->model->getEmpresa();
        $nit = $razonSocial['nit'];
        $nrcE = $razonSocial['nrc'];
        $codActividad = $razonSocial['codActividad'];
        $codigoGeneracion = $this->codigoGeneracion();
        $fechaEmi = $_POST['fechaEmi'];
        $horaEmi = $_POST['horaEmi'];
        $nit = $this->validarNit($nit);
        $nrc = $this->validarNrc($nrcE);

        $codActividad = $this->validarCodActividad($codActividad);
        $departamento = $this->validarDepartamento();
        $municipio = $this->validarMunicipio();

        $comprobar = $this->model->comprobar($idUsuario);
        $condicionOperacion = isset($_POST['condicion']) ? $_POST['condicion'] : null;
        $selectTipoPago = isset($_POST['selectTipoPago']) ? $_POST['selectTipoPago'] : null;
        $codigoBanco = empty($_POST['selectBanco']) ? null : $_POST['selectBanco'];
        $codigoCuentaBancaria = empty($_POST['selectCuentaBancaria']) ? null : $_POST['selectCuentaBancaria'];
        // var_dump($condicionOperacion);exit;
        $codigoMedioPago = isset($_POST['selectTipoPago']) ? $_POST['selectTipoPago'] : null;
        $tipoDte = isset($_POST['selectTipoDocumento']) ? $_POST['selectTipoDocumento'] : null;

        // $tipoMovimiento = $_POST['selectTipoMovimiento'];
        // $codigoProyecto = $_POST['codigoProyecto'];
        $tipoMovimiento = isset($_POST['selectTipoMovimiento']) ? $_POST['selectTipoMovimiento'] : null;
        $codigoProyecto = isset($_POST['codigoProyecto']) ? $_POST['codigoProyecto'] : null;

        // Actualizar existencias
        $detalle = $this->model->getDetalle($idUsuario);

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


        if (empty($tipoDte)) {
            $msg = 'Debe seleccionar un cliente para continuar con la operación';
            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
        } else if (empty($tipoMovimiento) || empty($codigoProyecto)) {
            $msg = "Seleccione tipo de Movimiento y Proyecto";
            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
        } else if (empty($condicionOperacion) || empty($selectTipoPago)) {
            $msg = "Seleccione tipo de pago y medio de pago";
            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
        } else if (empty($fechaEmi)) {
            $msg = 'Debe seleccionar fecha';
            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
        } else if (empty($comprobar['probar'])) {
            $msg = "No ha seleccionado productos";
            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
        } else {
            $this->model->iniciarTransaccion();
            try {
                $codigoCliente = isset($_POST['selectCliente']) ? $_POST['selectCliente'] : null;
                $datosCliente = $this->model->getCliente($codigoCliente);


                // DATOS COMPROBANTE DE CREDITO FISCAL ELECTRONICO
                $nitCRF = $datosCliente['nit'];
                $nitReceptorCRF = $this->validarNit($nitCRF);
                $codActividadCRF = $datosCliente['codigoActividadEconomica'];
                $codActividadReceptorCRF = $this->validarCodActividad($codActividadCRF);
                $numeroControl = $this->numeroDeControl($tipoDte);
                $fechaEmisionObj = $this->validarFechaEmi($fechaEmi);
                $fechaEmision = $fechaEmisionObj->format('Y-m-d');
                $horaEmision = $this->validarHoraEmi($horaEmi, $fechaEmi);

                // DOCUMENTOS RELACIONADOS INICIO
                $tipoDocumento = $_POST['documentosRelacionados']['tipoDocumento'] ?? [];
                $tipoGeneracion = $_POST['documentosRelacionados']['tipoGeneracion'] ?? [];
                $numeroDoc = $_POST['documentosRelacionados']['numeroDoc'] ?? [];
                $fechaEmisionDoc = $_POST['documentosRelacionados']['fechaEmision'] ?? [];

                $documentoRelacionado = null;

                if ($tipoDte === '05') {
                    if (empty($tipoDocumento) || empty($tipoGeneracion) || empty($numeroDoc) || empty($fechaEmisionDoc)) {
                        $msg = "Es obligatorio ingresar documentos relacionados para este tipo de DTE";
                        $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                        return;
                    }
                }

                // Verificamos si los campos del formulario tienen datos
                if (!empty($tipoDocumento) && !empty($tipoGeneracion) && !empty($numeroDoc) && !empty($fechaEmisionDoc)) {

                    // Paso 1: Validar que todos los tipos de documentos sean iguales
                    $primerTipo = $tipoDocumento[0];
                    $mismosTipos = array_reduce($tipoDocumento, function ($carry, $item) use ($primerTipo) {
                        return $carry && $item === $primerTipo;
                    }, true);

                    if (!$mismosTipos) {
                        $msg = 'Todos los documentos relacionados deben tener el mismo tipo de documento.';
                        $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                    }

                    // Paso 2: Verificar que no haya más de 50 documentos
                    if (count($tipoDocumento) > 50) {
                        $msg = 'No se pueden relacionar más de 50 documentos.';
                        $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                    }

                    // Paso 3 extra: Si el tipo de documento es NR, validar que esté en el mismo periodo o 3 días después
                    if ($primerTipo === '04') {
                        foreach ($fechaEmisionDoc as $i => $fechaNRString) {
                            $fechaEmisionNR = DateTime::createFromFormat('Y-m-d', $fechaNRString);

                            if (!$fechaEmisionNR) {
                                $msg = "El documento #" . ($i + 1) . " tiene una fecha inválida o con formato incorrecto (esperado: YYYY-MM-DD).";
                                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                                return;
                            }

                            $fechaEmisionNR->setTime(0, 0, 0);

                            $fechaEmisionObj->setTime(0, 0, 0);


                            $finPeriodo = clone $fechaEmisionNR;
                            $finPeriodo->modify('last day of this month');

                            $fechaLimite = clone $finPeriodo;
                            $fechaLimite->modify('+3 days');

                            // Validar la fecha del DTE contra la NR
                            if ($fechaEmisionObj < $fechaEmisionNR || $fechaEmisionObj > $fechaLimite) {
                                $msg = "La Nota de remision #" . ($i + 1) . " tiene una fecha fuera del rango permitido. Debe estar en el mismo período tributario o dentro de los 3 días siguientes al momento de generar este mismo DTE";
                                $this->sendJsonResponse(['status' => 'error', 'message' => $msg,]);
                                return;
                            }
                        }
                    } else if ($primerTipo === '09') {
                        foreach ($fechaEmisionDoc as $i => $fechaDCLString) {
                            $fechaEmisionDCL = DateTime::createFromFormat('Y-m-d', $fechaDCLString);

                            if (!$fechaEmisionDCL) {
                                $msg = "El documento #" . ($i + 1) . " tiene una fecha inválida o con formato incorrecto (esperado: YYYY-MM-DD).";
                                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                                return;
                            }

			    $fechaEmisionDCL->setTime(0, 0, 0);

                            $fechaEmisionObj->setTime(0, 0, 0);
                            
                            $finPeriodo = clone $fechaEmisionDCL;
                            $finPeriodo->modify('last day of this month');

                            $fechaLimite = clone $finPeriodo;

                            // Validar la fecha del DTE contra la NR
                            if ($fechaEmisionObj < $fechaEmisionDCL || $fechaEmisionObj > $fechaLimite) {
                                $msg = "El documento DCL #" . ($i + 1) . " tiene una fecha fuera del rango permitido. Debe estar en el mismo período tributario de este mismo DTE";
                                $this->sendJsonResponse(['status' => 'error', 'message' => $msg,]);
                                return;
                            }
                        }
                    } else if ($primerTipo === '03') {
                        foreach ($fechaEmisionDoc as $i => $fechaNRString) {
                            $fechaEmisionNR = DateTime::createFromFormat('Y-m-d', $fechaNRString);


                            if (!$fechaEmisionNR) {
                                $msg = "El documento #" . ($i + 1) . " tiene una fecha inválida o con formato incorrecto (esperado: YYYY-MM-DD).";
                                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                                return;
                            }
                            $fechaEmisionNR->setTime(0, 0, 0);

                            $fechaEmisionObj->setTime(0, 0, 0);

                            $fechaLimite = clone $fechaEmisionNR;
                            $fechaLimite->modify('+3 months');

                            // Validar que la fecha del nuevo DTE esté entre la fecha del CCF y 3 meses después
                            if ($fechaEmisionObj < $fechaEmisionNR || $fechaEmisionObj > $fechaLimite) {
                                $msg = "La NCE relacionada al CCF #" . ($i + 1) . " tiene una fecha fuera del rango permitido. Debe generarse dentro de los 3 meses posteriores a la fecha de emisión del CCF.";
                                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                                return;
                            }
                        }
                    } else if ($primerTipo === '07') { // 07 = CR relacionado
                        foreach ($fechaEmisionDoc as $i => $fechaNRString) {
                            $fechaEmisionNR = DateTime::createFromFormat('Y-m-d', $fechaNRString);


                            if (!$fechaEmisionNR) {
                                $msg = "El documento #" . ($i + 1) . " tiene una fecha inválida o con formato incorrecto (esperado: YYYY-MM-DD).";
                                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                                return;
                            }

                            // Validar que esté en el mismo mes y año
                            $mismoMes = $fechaEmisionNR->format('Y-m') === $fechaEmisionObj->format('Y-m');

                            if (!$mismoMes) {
                                $msg = "La NCE relacionada al CR #" . ($i + 1) . "debe generarse dentro del mismo período tributario (mismo mes) que la fecha del CR.";
                                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                                return;
                            }
                        }
                    }



                    // Paso 3: Construir el arreglo de documentos relacionados
                    $documentoRelacionado = [];

                    for ($i = 0; $i < count($tipoDocumento); $i++) {
                        $documentoRelacionado[] = [
                            'tipoDocumento' => $tipoDocumento[$i],
                            'tipoGeneracion' => (int) $tipoGeneracion[$i],
                            'numeroDocumento' => $numeroDoc[$i],
                            'fechaEmision' => $fechaEmisionDoc[$i],
                        ];
                    }
                } else {
                    // Si no se seleccionaron documentos, se asigna null
                    $documentoRelacionado = null;
                }

                // DOCUMENTOS RELACIONADOS FIN

                //VALIDACION MUNICIPIO INICIO // cuando departamento es 02 solo permite ciertos municipios
                if ($departamento === '02') {
                    $pattern = "/^(14|15|16|17)$/";

                    if (!preg_match($pattern, $municipio)) {
                        throw new Exception("Error: el municipio no existe en el departamento");
                    }
                }
                // VALIDACION MUNICIPIO FIN


                // OTROS DOCUMENTOS ASOCIADOS INICIO
                $codigoDocAsociado = $_POST['documentoAsociado']['codigo'] ?? [];
                $identificacionDocAsociado = $_POST['documentoAsociado']['identificacionDoc'] ?? [];
                $descripcionDocAsociado = $_POST['documentoAsociado']['descripcionDoc'] ?? [];

                $nombreMedico = $_POST['documentoMedico']['nombreMedico'] ?? [];
                $nitMedico = $_POST['documentoMedico']['nitMedico'] ?? [];
                $tipoDocumentoAS = $_POST['documentoMedico']['tipoDocumentoAS'] ?? [];
                $tipoServicio = $_POST['documentoMedico']['tipoServicio'] ?? [];

                $otrosDocumentosAsociados = null;
                if (!empty($codigoDocAsociado)) {

                    if (count($codigoDocAsociado) > 10) {
                        $this->sendJsonResponse(['status' => 'error', 'message' => 'No se pueden relacionar más de 10 documentos asociados.']);
                    }

                    $otrosDocumentosAsociados = [];

                    $indexMedico = 0;

                    for ($i = 0; $i < count($codigoDocAsociado); $i++) {
                        $codigo = (int)$codigoDocAsociado[$i];

                        if ($codigo === 3) {
                            $medico = [
                                'nombre' => $nombreMedico[$indexMedico] ?? null,
                                'nit' => isset($nitMedico[$indexMedico]) && trim($nitMedico[$indexMedico]) !== '' ? trim($nitMedico[$indexMedico]) : null,
                                'docIdentificacion' => isset($tipoDocumentoAS[$indexMedico]) && trim($tipoDocumentoAS[$indexMedico]) !== '' ? trim($tipoDocumentoAS[$indexMedico]) : null,
                                'tipoServicio' => isset($tipoServicio[$indexMedico]) ? (int)$tipoServicio[$indexMedico] : null
                            ];


                            $otrosDocumentosAsociados[] = [
                                'codDocAsociado' => $codigo,
                                'descDocumento' => null,
                                'detalleDocumento' => null,
                                'medico' => $medico
                            ];

                            $indexMedico++;
                        } else {
                            // Otros documentos
                            if (empty($identificacionDocAsociado[$i]) || empty($descripcionDocAsociado[$i])) {
                                $this->sendJsonResponse(['status' => 'error', 'message' => 'Faltan identificación o descripción para documento asociado.']);
                            }

                            $otrosDocumentosAsociados[] = [
                                'codDocAsociado' => $codigo,
                                'descDocumento' => $identificacionDocAsociado[$i],
                                'detalleDocumento' => $descripcionDocAsociado[$i],
                                'medico' => null
                            ];
                        }
                    }
                } else {
                    $otrosDocumentosAsociados = null;
                }
                // OTROS DOCUMENTOS ASOCIADOS FIN

                // VENTA A CUENTA DE TERCERO INICIO
                $nitTerceros = $_POST['ventaTercero']['nitTercero'] ?? [];
                $nombreTerceros = $_POST['ventaTercero']['nombreTercero'] ?? [];

                $cantidadTerceros = max(count($nitTerceros), count($nombreTerceros));

                if ($cantidadTerceros > 1) {
                    $this->sendJsonResponse(['status' => 'error', 'message' => 'Solo puede registrar un Tercero']);
                    exit;
                }

                $ventaTercero = null;

                if ($cantidadTerceros === 1 && (!empty($nitTerceros[0]) || !empty($nombreTerceros[0]))) {
                    $ventaTercero = (object)[
                        'nit' => $nitTerceros[0],
                        'nombre' => $nombreTerceros[0],
                    ];
                }

                // VENTA A CUENTAS DE TERCERO FIN

                $tributosResumen = null;
                if ($tipoDte === '01') {
                    $version = 1;

                    $receptor = (object)[
                        'tipoDocumento' => $datosCliente['tipoIdentificacion'],
                        'numDocumento' => $datosCliente['numeroIdentificacion'],
                        'nrc' => $datosCliente['nrc'],
                        'nombre' => $datosCliente['nombreCliente'],
                        'codActividad' => $datosCliente['codigoActividadEconomica'],
                        'descActividad' => $datosCliente['valor'],
                        'direccion' => [
                            'departamento' => $datosCliente['departamento'],
                            'municipio' => $datosCliente['municipio'],
                            'complemento' => $datosCliente['complemento'],
                        ],
                        'telefono' => $datosCliente['numeroTelefonoCliente'], //opcional
                        'correo' => $datosCliente['correo']
                    ];


                    //CUERPO DEL DOCUMENTO INICIO
                    $cuerpoDocumento = [];



                    $totalVGravada = 0.00;
                    $noAfectoTotalSuma = 0.00;
                    $ivaItemFinalSuma = 0.00;
                    foreach ($detalle as $row) {
                        $id = $row['item'];
                        $idProducto = $row['codigoProducto'];
                        $unidadMed = $row['unidadMedida'];
                        $cantidad = $row['cantidad'];
                        $costo = $row['costoProducto'];
                        $precio = $row['precioVenta'];
                        $totalP = $precio * $cantidad;
                        $documentoRelacionadoItem = $row['documentoRelacionado'];
                        $descripcion = $row['nombreProducto'];
                        $noafecto = $row['descripcionN'];
                        $totalAfecto = $row['total'];
                        $ivaItem = $totalP / 1.13 * 0.13;
                        $ivaItemFinal = round($ivaItem, 2);
                        $tipoItem = 1;

                        /// USO DE CARGOS QUE NO AFECTAN LA BASE IMPONIBLE
                        if (!empty($noafecto)) {
                            $idProducto = null;
                            $tipoItem = 3;
                            $precio = 0.00;

                            $descripcion = $noafecto;
                            $noAfectoTotal = round($totalAfecto * $cantidad, 3);
                            $totalP = 0.00;
                            $ivaItemFinal = 0;
                        } else {
                            $noAfectoTotal = 0.00;
                        }



                        $cuerpoDocumento[] = [
                            'numItem' => $id,
                            'tipoItem' => $tipoItem,
                            'numeroDocumento' => $documentoRelacionadoItem ?? null,
                            'cantidad' => floatval(str_replace(',', '.', $cantidad)),
                            'codigo' => $idProducto,
                            'codTributo' => null,
                            'uniMedida' => $unidadMed,
                            'descripcion' => $descripcion,
                            'precioUni' => round($precio, 2),

                            //valores pendienres si se aplicara ?
                            'montoDescu' => 0.00,
                            'ventaNoSuj' => 0.00, // resultado de precio*cantidad-Descuento,bonififcaion,rebajas por item
                            'ventaExenta' => 0.00,
                            'ventaGravada' => round($totalP, 2),
                            'tributos' => null, // en FE sera null porque ya llevan iva incluido los items
                            //valores pendienres si se aplicara ?

                            'psv' => 0.00,
                            'noGravado' => $noAfectoTotal,
                            'ivaItem' =>  $ivaItemFinal
                        ];

                        $totalVGravada += $totalP;
                        $noAfectoTotalSuma += $noAfectoTotal;
                        $ivaItemFinalSuma += $ivaItemFinal;
                    }
                    //CUERPO DEL DOCUMENTO FIN

                    //RESUMEN DEL CODUMENTO INICIO
                    $sumaIva = round($ivaItemFinalSuma, 2);
                    $totalNoSuj = 0.00;
                    $totalExenta = 0.00;
                    $totalGravada = round($totalVGravada, 2);
                    $subTotalVentas = $totalNoSuj + $totalExenta + $totalGravada;
                    $descuNoSuj = 0.00;
                    $descuExenta = 0.00;
                    $descuGravada = 0.00;
                    $porcentajeDescuento = 0.00;
                    $totalDescu = 0.00;
                    // $tributosResumen = [
                    //     (object)[
                    //         'codigo' => '20',
                    //         'descripcion' => 'Impuesto al Valor Agregado 13%',
                    //         'valor' => $sumaIva,
                    //     ]
                    // ];
                    $subTotal = $totalNoSuj + $totalExenta + $totalGravada;
                    if (empty($Ivarenta)) {
                        $ivaRete1 = 0.00;
                    } else {
                        $ivaRete1 = round($Ivarenta, 2);
                    }
                    $reteRenta = 0.00;
                    $montoTotalOperacion = $subTotal;
                    $totalNoGravado = round($noAfectoTotalSuma, 2);
                    $totalPagar = round($montoTotalOperacion - $ivaRete1 - $reteRenta + $totalNoGravado, 2);
                    /// Total en dolares
                    $fmt = new NumberFormatter("es_ES", NumberFormatter::SPELLOUT);
                    $montoEntero = floor($totalPagar);
                    $montoDecimal = round(($totalPagar - $montoEntero) * 100);
                    $textEntero = strtoupper($fmt->format($montoEntero));
                    $textDecimal = str_pad($montoDecimal, 2, "0", STR_PAD_LEFT);
                    $text = $textEntero . " CON " . $textDecimal . "/100 USD";
                    /// Total en dolares
                    $totalIva = round($ivaItemFinalSuma - $descuGravada, 2);
                    $saldoFavor = 0.00;

                    if ((int) $condicionOperacion === 1 || (int) $condicionOperacion === 3) {
                        if (empty($codigoMedioPago)) {
                            $msg = "Debe seleccionar medio de pago";
                            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                        }
                    }
                    // pendiente si se usa tipos de pagos 
                    // $montoTotalPagos = array_sum(array_map(fn($p) => $p->montoPago, $pagos));
                    // if (round($montoTotalPagos, 2) !== $totalPagar) {
                    //     $this->sendJsonResponse(['status' => 'error', 'message' => 'Suma de pagos no coincide con el total a pagar']);
                    // }

                    $pagos = [(object)[
                        'codigo' => $codigoMedioPago,
                        'montoPago' => $totalPagar,
                        'referencia' => null,
                        'plazo' => null,
                        'periodo' => null,
                    ]];

                    $resumen = (object)[
                        'totalNoSuj' => $totalNoSuj,
                        'totalExenta' => $totalExenta,
                        'totalGravada' => $totalGravada,
                        'subTotalVentas' => $subTotalVentas,
                        'descuNoSuj' => $descuNoSuj,
                        'descuExenta' => $descuExenta,
                        'descuGravada' => $descuGravada,
                        'porcentajeDescuento' => $porcentajeDescuento,
                        'totalDescu' => $totalDescu,
                        'tributos' => null,
                        'subTotal' => $subTotal,
                        'ivaRete1' => $ivaRete1,
                        'reteRenta' => $reteRenta,
                        'montoTotalOperacion' => $montoTotalOperacion,
                        'totalNoGravado' => $totalNoGravado,
                        'totalPagar' => $totalPagar,
                        'totalLetras' => $text,
                        'totalIva' => $totalIva,
                        'saldoFavor' => $saldoFavor,
                        'condicionOperacion' => (int) $condicionOperacion,
                        'pagos' => $pagos,
                        'numPagoElectronico' => null,
                    ];

                    //RESUMEN DEL CODUMENTO FINAL
                } else if ($tipoDte === '03') {
                    $nrcReceptorCRF = $datosCliente['nrc'];
                    $nrcReceptorCCRF = $this->validarNrc($nrcReceptorCRF);

                    $version = 3;
                    $receptor = (object)[
                        'nit' => $nitReceptorCRF,
                        'nrc' => $nrcReceptorCCRF,
                        'nombre' => $datosCliente['nombreCliente'],
                        'codActividad' => $codActividadReceptorCRF,
                        'descActividad' => $datosCliente['valor'],
                        'nombreComercial' => $datosCliente['nombreComercial'],
                        'direccion' => [
                            'departamento' => $datosCliente['departamento'],
                            'municipio' => $datosCliente['municipio'],
                            'complemento' => $datosCliente['complemento'],
                        ],
                        'telefono' => $datosCliente['numeroTelefonoCliente'], //opcional
                        'correo' => $datosCliente['correo']
                    ];

                    //CUERPO DEL DOCUMENTO INICIO
                    $cuerpoDocumento = [];


                    // $detalle = $this->model->getDetalle($idUsuario);
                    $totalVGravada = 0.00;
                    $noAfectoTotalSuma = 0.00;
                    $ivaItemFinalSuma = 0.00;
                    foreach ($detalle as $row) {
                        $id = $row['item'];
                        $idProducto = $row['codigoProducto'];
                        $unidadMed = $row['unidadMedida'];
                        $cantidad = $row['cantidad'];
                        $costo = $row['costoProducto'];
                        $precio = $row['precioVenta'];
                        $totalP = $precio * $cantidad;
                        $documentoRelacionadoItem = $row['documentoRelacionado'];
                        $descripcion = $row['nombreProducto'];
                        $noafecto = $row['descripcionN'];
                        $totalAfecto = $row['total'];
                        $ivaItem = $totalP * 0.13;
                        $ivaItemFinal = $ivaItem;

                        $tipoItem = 1;

                        /// USO DE CARGOS QUE NO AFECTAN LA BASE IMPONIBLE
                        if (!empty($noafecto)) {
                            $idProducto = null;
                            $tipoItem = 3;
                            $precio = 0.00;

                            $descripcion = $noafecto;
                            $noAfectoTotal = round($totalAfecto * $cantidad, 3);
                            $totalP = 0.00;
                            $tributos = null;
                            $tributosResumen = null;
                        } else {
                            $noAfectoTotal = 0.00;
                            $tributos = ["20"];
                        }



                        $cuerpoDocumento[] = [
                            'numItem' => $id,
                            'tipoItem' => $tipoItem,
                            'numeroDocumento' => $documentoRelacionadoItem,
                            'codigo' => $idProducto,
                            'codTributo' => null,
                            'descripcion' => $descripcion,
                            'cantidad' => floatval(str_replace(',', '.', $cantidad)),
                            'uniMedida' => $unidadMed,
                            'precioUni' => round($precio, 2),

                            //valores pendienres si se aplicara ?
                            'montoDescu' => 0.00,
                            'ventaNoSuj' => 0.00, // resultado de precio*cantidad-Descuento,bonififcaion,rebajas por item
                            'ventaExenta' => 0.00,
                            'ventaGravada' => $totalP,
                            'tributos' => $tributos, // en FE sera null porque ya llevan iva incluido los items
                            //valores pendienres si se aplicara ?

                            'psv' => 0.00,
                            'noGravado' => $noAfectoTotal,
                        ];

                        $totalVGravada += $totalP;
                        $noAfectoTotalSuma += $noAfectoTotal;
                        $ivaItemFinalSuma += $ivaItemFinal;
                    }

                    //CUERPO DEL DOCUMENTO FIN

                    $sumaIva = round($ivaItemFinalSuma, 2);
                    $iva = 0;
                    //RESUMEN DEL CODUMENTO INICIO
                    $totalNoSuj = 0.00;
                    $totalExenta = 0.00;
                    $totalGravada = round($totalVGravada, 2);
                    $subTotalVentas = $totalNoSuj + $totalExenta + $totalGravada;
                    $descuNoSuj = 0.00;
                    $descuExenta = 0.00;
                    $descuGravada = 0.00;
                    $porcentajeDescuento = 0.00;
                    $totalDescu = 0.00;
                    $subTotal = $totalNoSuj + $totalExenta + $totalGravada;
                    // $iva = $_POST['iva'];
                    $tributosResumen = [
                        (object)[
                            'codigo' => '20',
                            'descripcion' => 'Impuesto al Valor Agregado 13%',
                            'valor' => $sumaIva,
                        ]
                    ];

                    $ivaPerci1 = 0.00;
                    if (empty($Ivarenta)) {
                        $ivaRete1 = 0.00;
                    } else {
                        $ivaRete1 = round($Ivarenta, 2);
                    }
                    $reteRenta = 0.00;
                    $montoTotalOperacion = round($subTotal + $sumaIva, 2);
                    $totalNoGravado = $noAfectoTotalSuma;
                    $totalPagar = $montoTotalOperacion + $ivaPerci1 - $ivaRete1 - $reteRenta + $totalNoGravado;
                    /// Total en dolares
                    $fmt = new NumberFormatter("es_ES", NumberFormatter::SPELLOUT);
                    $montoEntero = floor($totalPagar);
                    $montoDecimal = round(($totalPagar - $montoEntero) * 100);
                    $textEntero = strtoupper($fmt->format($montoEntero));
                    $textDecimal = str_pad($montoDecimal, 2, "0", STR_PAD_LEFT);
                    $text = $textEntero . " CON " . $textDecimal . "/100 USD";
                    /// Total en dolares

                    $saldoFavor = 0.00;

                    if ((int) $condicionOperacion === 1 || (int) $condicionOperacion === 3) {
                        if (empty($codigoMedioPago)) {
                            $msg = "Debe seleccionar medio de pago";
                            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                        }
                    }
                    $pagos = [(object)[
                        'codigo' => $codigoMedioPago,
                        'montoPago' => $totalPagar,
                        'referencia' => null,
                        'plazo' => null,
                        'periodo' => null,
                    ]];


                    $resumen = (object)[
                        'totalNoSuj' => $totalNoSuj,
                        'totalExenta' => $totalExenta,
                        'totalGravada' => $totalGravada,
                        'subTotalVentas' => $subTotalVentas,
                        'descuNoSuj' => $descuNoSuj,
                        'descuExenta' => $descuExenta,
                        'descuGravada' => $descuGravada,
                        'porcentajeDescuento' => $porcentajeDescuento,
                        'totalDescu' => $totalDescu,
                        'tributos' => $tributosResumen,
                        'subTotal' => $subTotal,
                        'ivaPerci1' => $ivaPerci1,
                        'ivaRete1' => $ivaRete1,
                        'reteRenta' => $reteRenta,
                        'montoTotalOperacion' => $montoTotalOperacion,
                        'totalNoGravado' => $totalNoGravado,
                        'totalPagar' => $totalPagar,
                        'totalLetras' => $text,
                        'saldoFavor' => $saldoFavor,
                        'condicionOperacion' => (int) $condicionOperacion,
                        'pagos' => $pagos,
                        'numPagoElectronico' => null,
                    ];

                    //RESUMEN DEL CODUMENTO FINAL
                } else if ($tipoDte === '05') {
                    $nrcReceptorCRF = $datosCliente['nrc'];
                    $nrcReceptorCCRF = $this->validarNrc($nrcReceptorCRF);
                    $version = 3;

                    $receptor = (object)[
                        'nit' => $nitReceptorCRF,
                        'nrc' => $nrcReceptorCCRF,
                        'nombre' => $datosCliente['nombreCliente'],
                        'codActividad' => $codActividadReceptorCRF,
                        'descActividad' => $datosCliente['valor'],
                        'nombreComercial' => $datosCliente['nombreComercial'],
                        'direccion' => [
                            'departamento' => $datosCliente['departamento'],
                            'municipio' => $datosCliente['municipio'],
                            'complemento' => $datosCliente['complemento'],
                        ],
                        'telefono' => $datosCliente['numeroTelefonoCliente'], //opcional
                        'correo' => $datosCliente['correo']
                    ];

                    $cuerpoDocumento = [];


                    // $detalle = $this->model->getDetalle($idUsuario);
                    $totalVGravada = 0.00;
                    $ivaItemFinalSuma = 0.00;
                    foreach ($detalle as $row) {
                        $id = $row['item'];
                        $idProducto = $row['codigoProducto'];
                        $unidadMed = $row['unidadMedida'];
                        $cantidad = $row['cantidad'];
                        $costo = $row['costoProducto'];
                        $precio = $row['precioVenta'];
                        $totalP = $precio * $cantidad;
                        $documentoRelacionadoItem = $row['documentoRelacionado'];
                        $descripcionProducto = $row['nombreProducto'];
                        $noafecto = $row['descripcionN'];
                        $totalAfecto = $row['total'];
                        $descripcionNota = $row['descripcionNota'];
                        $ivaItem = $totalP * 0.13;
                        $ivaItemFinal = $ivaItem;

                        $tipoItem = 1;

                        $noAfectoTotal = 0.00;
                        $tributos = ["20"];

                        $descripcionNotasPorItem[$id] = $descripcionNota;

                        $descripcion = $descripcionNota . ' - ' . $descripcionProducto;
                        $cuerpoDocumento[] = [
                            'numItem' => $id,
                            'tipoItem' => $tipoItem,
                            'numeroDocumento' => $documentoRelacionadoItem,
                            'cantidad' => $cantidad,
                            'codigo' => $idProducto,
                            'codTributo' => null,
                            'uniMedida' => $unidadMed,
                            'descripcion' => $descripcion,
                            'precioUni' => round($precio, 2),

                            //valores pendienres si se aplicara ?
                            'montoDescu' => 0.00,
                            'ventaNoSuj' => 0.00, // resultado de precio*cantidad-Descuento,bonififcaion,rebajas por item
                            'ventaExenta' => 0.00,
                            'ventaGravada' => $totalP,
                            'tributos' => $tributos, // en FE sera null porque ya llevan iva incluido los items
                            //valores pendienres si se aplicara ?
                        ];

                        $totalVGravada += $totalP;
                        $ivaItemFinalSuma += $ivaItemFinal;

                        $sumaIva = round($ivaItemFinalSuma, 2);
                        $iva = 0;
                        //RESUMEN DEL CODUMENTO INICIO
                        $totalNoSuj = 0.00;
                        $totalExenta = 0.00;
                        $totalGravada = round($totalVGravada, 2);
                        $subTotalVentas = $totalNoSuj + $totalExenta + $totalGravada;
                        $descuNoSuj = 0.00;
                        $descuExenta = 0.00;
                        $descuGravada = 0.00;
                        $porcentajeDescuento = 0.00;
                        $totalDescu = 0.00;
                        $subTotal = $totalNoSuj + $totalExenta + $totalGravada;
                        // $iva = $_POST['iva'];
                        $tributosResumen = [
                            (object)[
                                'codigo' => '20',
                                'descripcion' => 'Impuesto al Valor Agregado 13%',
                                'valor' => $sumaIva,
                            ]
                        ];

                        $ivaPerci1 = 0.00;
                        if (empty($Ivarenta)) {
                            $ivaRete1 = 0.00;
                        } else {
                            $ivaRete1 = round($Ivarenta, 2);
                        }
                        $reteRenta = 0.00;
                        $montoTotalOperacion = round($subTotal + $sumaIva + $ivaPerci1 - $ivaRete1 - $reteRenta, 2);
                        $totalPagar = $montoTotalOperacion - $ivaRete1 - $reteRenta;
                        /// Total en dolares
                        $fmt = new NumberFormatter("es_ES", NumberFormatter::SPELLOUT);
                        $montoEntero = floor($totalPagar);
                        $montoDecimal = round(($totalPagar - $montoEntero) * 100);
                        $textEntero = strtoupper($fmt->format($montoEntero));
                        $textDecimal = str_pad($montoDecimal, 2, "0", STR_PAD_LEFT);
                        $text = $textEntero . " CON " . $textDecimal . "/100 USD";
                        /// Total en dolares

                        $saldoFavor = 0.00;

                        if ((int) $condicionOperacion === 1 || (int) $condicionOperacion === 3) {
                            if (empty($codigoMedioPago)) {
                                $msg = "Debe seleccionar medio de pago";
                                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                            }
                        }
                        $pagos = [(object)[
                            'codigo' => $codigoMedioPago,
                            'montoPago' => $montoTotalOperacion ,
                            'referencia' => null,
                            'plazo' => null,
                            'periodo' => null,
                        ]];


                        $resumen = (object)[
                            'totalNoSuj' => $totalNoSuj,
                            'totalExenta' => $totalExenta,
                            'totalGravada' => $totalGravada,
                            'subTotalVentas' => $subTotalVentas,
                            'descuNoSuj' => $descuNoSuj,
                            'descuExenta' => $descuExenta,
                            'descuGravada' => $descuGravada,
                            'totalDescu' => $totalDescu,
                            'tributos' => $tributosResumen,
                            'subTotal' => $subTotal,
                            'ivaPerci1' => $ivaPerci1,
                            'ivaRete1' => $ivaRete1,
                            'reteRenta' => $reteRenta,
                            'montoTotalOperacion' => $montoTotalOperacion,
                            'totalLetras' => $text,
                            'condicionOperacion' => (int) $condicionOperacion,
                        ];
                    }
                }


                // EXTENCION INICIO
                // $extension = [
                //     'nombEntrega' => null,
                //     'docuEntrega' => null,
                //     'nombRecibe' => null,
                //     'docuRecibe' => null,
                //     'observaciones' => null,
                //     'placaVehiculo' => null,
                // ];
                $extension = null;
                // EXTENCION FIN


                // APENDICE INICIO
                // $apendice = [
                //     'campo' => null,
                //     'etiqueta' => null,
                //     'valor' => null,
                // ];
                $apendice = null;
                // APENDICE FIN

                // EVENTO DE CONTINGENCIA INICIO
                $estadoContingencia = $this->model->obtenerEstadoContingencia();
                $datosContingencia = $this->model->datosContingencia();
                $eventoActivo = $estadoContingencia['estadoContingenciaId'] ?? null;

                if ($eventoActivo == null) {
                    $identificacion = (object)[
                        'version' => $version,
                        'ambiente' => $facturaDatos['ambiente'],
                        'tipoDte' => $tipoDte,
                        'numeroControl' => $numeroControl,
                        'codigoGeneracion' => $codigoGeneracion,
                        'tipoModelo' => 1,
                        'tipoOperacion' => 1,
                        'tipoContingencia' => null,
                        'motivoContin' => null,
                        'fecEmi' => $fechaEmision,
                        'horEmi' => $horaEmision,
                        'tipoMoneda' => $facturaDatos['tipoMoneda'],
                    ];
                } else if ($eventoActivo == 1) {
                    $identificacion = (object)[
                        'version' => $version,
                        'ambiente' => $facturaDatos['ambiente'],
                        'tipoDte' => $tipoDte,
                        'numeroControl' => $numeroControl,
                        'codigoGeneracion' => $codigoGeneracion,
                        'tipoModelo' => $datosContingencia['modeloFacturacion'],
                        'tipoOperacion' => $datosContingencia['tipoTransmision'],
                        'tipoContingencia' => $datosContingencia['tipoContingencia'],
                        'motivoContin' => $datosContingencia['motivoContingencia'] ?? null,
                        'fecEmi' => $fechaEmision,
                        'horEmi' => $horaEmision,
                        'tipoMoneda' => $facturaDatos['tipoMoneda']
                    ];
                }
                // EVENTO DE CONTINGENCIA FIN
                // Si todo está bien, construimos los datos
                if ($tipoDte === '05') {
                    $datosGenerados = [
                        "identificacion" => $identificacion,
                        "documentoRelacionado" => $documentoRelacionado,
                        "emisor" => [
                            'nit' => $nit,
                            'nrc' => $nrc,
                            'nombre' => $razonSocial['nombre'],
                            'codActividad' => $codActividad,
                            'descActividad' => $razonSocial['descActividad'],
                            'nombreComercial' => $razonSocial['nombre'],
                            'tipoEstablecimiento' => $razonSocial['tipoEstablecimiento'],
                            "direccion" => [
                                'departamento' => $departamento,
                                'municipio' => $municipio,
                                'complemento' => $razonSocial['direccion'],
                            ],
                            'telefono' => $razonSocial['telefono'],
                            'correo' => $razonSocial['correo']
                        ],
                        "receptor" => $receptor,
                        'ventaTercero' => $ventaTercero,
                        'cuerpoDocumento' => $cuerpoDocumento,
                        'resumen' => $resumen,
                        'extension' => $extension,
                        'apendice' => $apendice
                    ];
                } else if ($tipoDte === '01' || $tipoDte === '03') {
                    $datosGenerados = [
                        "identificacion" => $identificacion,
                        "documentoRelacionado" => $documentoRelacionado,
                        "emisor" => [
                            'nit' => $nit,
                            'nrc' => $nrc,
                            'nombre' => $razonSocial['nombre'],
                            'codActividad' => $codActividad,
                            'descActividad' => $razonSocial['descActividad'],
                            'nombreComercial' => $razonSocial['nombre'],
                            'tipoEstablecimiento' => $razonSocial['tipoEstablecimiento'],
                            "direccion" => [
                                'departamento' => $departamento,
                                'municipio' => $municipio,
                                'complemento' => $razonSocial['direccion'],
                            ],
                            'telefono' => $razonSocial['telefono'],
                            'correo' => $razonSocial['correo'],
                            'codEstableMH' => $facturaDatos['codEstableMH'],
                            'codEstable' => $facturaDatos['codEstable'],
                            'codPuntoVentaMH' => $facturaDatos['codPuntoVentaMH'],
                            'codPuntoVenta' => $facturaDatos['codPuntoVenta'],
                        ],
                        "receptor" => $receptor,
                        'otrosDocumentos' => $otrosDocumentosAsociados,
                        'ventaTercero' => $ventaTercero,
                        'cuerpoDocumento' => $cuerpoDocumento,
                        'resumen' => $resumen,
                        'extension' => $extension,
                        'apendice' => $apendice
                    ];
                }


			//var_dump($datosGenerados);
                	//exit;
                 $token = $this->obtenerYGuardarToken();

                if (!isset($token['token'])) {
                    // Mostrar o devolver el mensaje de error
                    $this->sendJsonResponse(['status' => 'error', 'message' => $token['message']]);
                    return;
                }



                $empresaId = 'MacastA01';
$jsonFirmado = $this->firmarJson($datosGenerados, $nit, $empresaId);

if (!$jsonFirmado) {
    throw new Exception("Error al firmar el JSON o generarlo");
}



                // Llamar a la función `registrarDTE` con los datos extraídos del JSON
                $estado = 'PENDIENTE';
                $fechaProcesamiento = null;
                $datosReceptor = $receptor->nombre;
                $idCliente = $this->model->getIdCliente($datosReceptor);

                if ($eventoActivo == 1) {
                    $idContingencia = $this->model->obtenerIdContingenciaActiva($eventoActivo);
                    $registrar = $idContingencia['id'];
                } else {
                    $registrar = null;
                }


                $res = $this->model->registrarDTE(
                    $identificacion->numeroControl,
                    $identificacion->version,
                    $identificacion->tipoDte,
                    $identificacion->codigoGeneracion,
                    $identificacion->tipoModelo,
                    $identificacion->tipoOperacion,
                    $identificacion->tipoContingencia,
                    $identificacion->motivoContin,
                    $identificacion->fecEmi,
                    $identificacion->horEmi,
                    $idCliente['codigoCliente'],
                    $resumen->totalNoSuj,
                    $resumen->totalExenta,
                    $resumen->totalGravada,
                    $resumen->subTotalVentas,
                    $resumen->descuNoSuj,
                    $resumen->descuExenta,
                    $resumen->descuGravada,
                    $resumen->porcentajeDescuento ?? null,
                    $resumen->totalDescu,
                    $resumen->tributos[0]->codigo ?? null,
                    $resumen->tributos[0]->valor ?? null,
                    $resumen->subTotal,
                    $resumen->ivaPerci1 ?? null,
                    $resumen->ivaRete1,
                    $resumen->reteRenta,
                    $resumen->montoTotalOperacion,
                    $resumen->totalNoGravado ?? null,
                    $resumen->totalPagar ?? null,
                    $resumen->totalIva ?? null,
                    $resumen->saldoFavor ?? null,
                    $resumen->condicionOperacion,
                    $resumen->pagos[0]->codigo ?? null,       // Código de forma de pago
                    $resumen->pagos[0]->montoPago ?? null,    // Monto de pago
                    $resumen->pagos[0]->referencia ?? null,   // Referencia
                    $resumen->pagos[0]->plazo ?? null,        // Plazo
                    $resumen->pagos[0]->periodo ?? null,      // Periodo
                    $resumen->numPagoElectronico ?? null,
                    $celloRecepcion ?? null,
                    $identificacion->ambiente,
                    $jsonFirmado['body'],
                    $estado,
                    $fechaProcesamiento,
                    $tipoMovimiento,
                    $codigoProyecto,
                    $registrar,
                    $codigoBanco ?? null,
                    $codigoCuentaBancaria ?? null
                );

                if ($res !== "ok") {
                    throw new Exception("Error al registrar el encabezado del DTE");
                }

                if (!empty($descripcionNotasPorItem)) {
                    $this->registrarCuerpoDocumento($cuerpoDocumento, $identificacion, $descripcionNotasPorItem);
                } else {
                    $this->registrarCuerpoDocumento($cuerpoDocumento, $identificacion);
                }
                $this->registrarDocumentosRelacionados($documentoRelacionado, $identificacion->numeroControl);
                $this->registrarDocumentosAsociados($otrosDocumentosAsociados, $identificacion->numeroControl);
                $this->registrarVentaTerceros($ventaTercero, $identificacion->numeroControl);

                // Reintentos de emisión
                $maxIntentos = 3;
                $intentos = 0;
                $exito = false;
                $codigoGeneracion = $datosGenerados['identificacion']->codigoGeneracion;
                $ambiente = $datosGenerados['identificacion']->ambiente;
                $version = $datosGenerados['identificacion']->version;
                $tipoDte = $datosGenerados['identificacion']->tipoDte;

                                if ($eventoActivo == 1) {
                    $this->model->confirmarTransaccion();
                    foreach ($cuerpoDocumento as $item) {
                        $codigoProducto = $item['codigo'];
                        $cantidadDescontar = $item['cantidad'];

                        // Validar código de producto antes de actualizar existencia
                        if (empty($codigoProducto)) {
                            // Si no hay código de producto, significa que es un ítem que no afecta inventario, así que saltamos
                            continue;
                        }

                        // Obtener existencia actual del producto
                        $cantidadActual = $this->model->getExistencia($codigoProducto, $codigoProyecto);
                        $cantidadProductoA = $cantidadActual[0]['cantidadProducto'] ?? 0;

                        // Calcular nueva existencia
                        if ($tipoDte === '05') {
                            // Devolución: aumentar existencias
                            $existencia = $cantidadProductoA + $cantidadDescontar;
                        } else {
                            // Venta: disminuir existencias
                            $existencia = $cantidadProductoA - $cantidadDescontar;
                        }

                        // Actualizar existencia
                        $resultExistencia = $this->model->actualizarExistencias($existencia, $codigoProyecto, $codigoProducto);
                        if ($resultExistencia != "ok") {
                            throw new Exception("Error al actualizar las existencias del producto con código: $codigoProducto");
                        }
                    }
                    $datosCuentas = $this->model->obtenerSaldoCuenta($codigoCuentaBancaria);
                    // Solo ejecutar si el tipo DTE requiere afectar cuentas bancarias
                    if (in_array($tipoDte, ['01', '03', '05']) && is_array($datosCuentas) && count($datosCuentas) > 0) {

                        if ($tipoDte === '01' || $tipoDte === '03') {
                            // actualizar ingreso de la cuenta seleccionada
                            $ingresoActualCuentaB = (float) ($datosCuentas[0]['ingresos'] ?? 0);
                            $nuevoIngresoJsonActual = $ingresoActualCuentaB + $resumen->totalPagar;
                            $resultSalida = $this->model->actualizarIngresoCuenta($nuevoIngresoJsonActual, $codigoCuentaBancaria);
                            if ($resultSalida != "ok") {
                                throw new Exception("Error al actualizar ingreso para la cuenta");
                            }

                            // actualizar saldo de la cuenta seleccionada
                            $saldoInicialCuenta = (float) ($datosCuentas[0]['saldoInicial'] ?? 0);
                            $salidaActualCuentaB = (float) ($datosCuentas[0]['salidas'] ?? 0);
                            $nuevoSaldoCuenta = $saldoInicialCuenta + $nuevoIngresoJsonActual - $salidaActualCuentaB;
                            $resultado = $this->model->actualizarSaldoCuentas($nuevoSaldoCuenta, $codigoCuentaBancaria);
                            if ($resultado != "ok") {
                                throw new Exception("Error al actualizar saldo para la cuenta");
                            }
                        } else if ($tipoDte === '05') {
                            // actualizamos la salida de las cuentas
                            $salidaActualCuenta = $this->model->obtenerSalidasCuenta($codigoCuentaBancaria);
                            $nuevaSalida = $salidaActualCuenta['salidas'] + $resumen->montoTotalOperacion;
                            $resultSalida = $this->model->actualizarSalidasCuentas($nuevaSalida, $codigoCuentaBancaria);
                            if ($resultSalida != "ok") {
                                throw new Exception("Error al actualizar salida para la cuenta");
                            }

                            // se actualiza el saldo de las cuentas
                            $saldoInicialCuenta = (float) ($datosCuentas[0]['saldoInicial'] ?? 0);
                            $ingresoActualCuenta = (float) ($datosCuentas[0]['ingresos'] ?? 0);
                            $nuevoSaldoCuenta = $saldoInicialCuenta + $ingresoActualCuenta - $nuevaSalida;
                            $resultado = $this->model->actualizarSaldoCuentas($nuevoSaldoCuenta, $codigoCuentaBancaria);
                            if ($resultado != "ok") {
                                throw new Exception("Error al actualizar saldo para la cuenta");
                            }
                        }
                    } else {
                        // Si no se requiere actualización de cuentas, puedes ignorar
                        // o incluso registrar en el log que no se aplicó ningún cambio.
                    }

                    $this->enviarFacturaPorCorreo($receptor->correo, $identificacion->numeroControl, $tipoDte);
                    $this->sendJsonResponse([
                        'status' => 'contingencia',
                        'message' => 'DTE generado en contingencia. Firmado y almacenado. Esperando envío por lote.',
                        'jsonFirmado' => $jsonFirmado,
                        'data' => $datosGenerados
                    ]);
                    return;
                }

                while ($intentos < $maxIntentos && !$exito) {
                    $intentos++;

                    $postData = json_encode([
                        'ambiente' => $ambiente,
                        'idEnvio' => $intentos,
                        'version' => $version,
                        'tipoDte' => $tipoDte,
                        'documento' => $jsonFirmado['body'],
                        'codigoGeneracion' => $codigoGeneracion,
                    ], JSON_UNESCAPED_UNICODE);

                    $ch = curl_init('https://api.dtes.mh.gob.sv/fesv/recepciondte');
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
                        if ($intentos >= $maxIntentos) {
                            throw new Exception("Error de conexión con Hacienda en el intento $intentos: $errorCurl ACTIVE Y FACTURE EN CONTINGENCIA");
                        }
                        sleep(8);
                        continue;
                    }

                    curl_close($ch);
                    $decoded = json_decode($response, true);
                    if ($decoded === null) {
                        if ($intentos >= $maxIntentos) {
                            throw new Exception("No se pudo interpretar respuesta JSON de Hacienda en intento $intentos.");
                        }
                        sleep(8);
                        continue;
                    }

                    if ($decoded && isset($decoded['estado']) && $decoded['estado'] === 'PROCESADO' && !empty($decoded['selloRecibido'])) {
                        $sello = $decoded['selloRecibido'];
                        $estadoo = $decoded['estado'];
                        $fechaProcesamiento = $decoded['fhProcesamiento'];
                        $observaciones = is_array($decoded['observaciones']) ? json_encode($decoded['observaciones'], JSON_UNESCAPED_UNICODE) : $decoded['observaciones'];
                        $datosGenerados['selloRecibido'] = $sello;
                        $this->model->actualizarEstadoDte($sello, $estadoo, $fechaProcesamiento, $observaciones, $identificacion->numeroControl);
                        $this->model->confirmarTransaccion();
                        foreach ($cuerpoDocumento as $item) {
                            $codigoProducto = $item['codigo'];
                            $cantidadDescontar = $item['cantidad'];

                            // Validar código de producto antes de actualizar existencia
                            if (empty($codigoProducto)) {
                                // Si no hay código de producto, significa que es un ítem que no afecta inventario, así que saltamos
                                continue;
                            }

                            // Obtener existencia actual del producto
                            $cantidadActual = $this->model->getExistencia($codigoProducto, $codigoProyecto);
                            $cantidadProductoA = $cantidadActual[0]['cantidadProducto'] ?? 0;

                            // Calcular nueva existencia
                            if ($tipoDte === '05') {
                                // Devolución: aumentar existencias
                                $existencia = $cantidadProductoA + $cantidadDescontar;
                            } else {
                                // Venta: disminuir existencias
                                $existencia = $cantidadProductoA - $cantidadDescontar;
                            }

                            // Actualizar existencia
                            $resultExistencia = $this->model->actualizarExistencias($existencia, $codigoProyecto, $codigoProducto);
                            if ($resultExistencia != "ok") {
                                throw new Exception("Error al actualizar las existencias del producto con código: $codigoProducto");
                            }
                        }

                        $datosCuentas = $this->model->obtenerSaldoCuenta($codigoCuentaBancaria);
                        // Solo ejecutar si el tipo DTE requiere afectar cuentas bancarias
                        if (in_array($tipoDte, ['01', '03', '05']) && is_array($datosCuentas) && count($datosCuentas) > 0) {

                            if ($tipoDte === '01' || $tipoDte === '03') {
                                // actualizar ingreso de la cuenta seleccionada
                                $ingresoActualCuentaB = (float) ($datosCuentas[0]['ingresos'] ?? 0);
                                $nuevoIngresoJsonActual = $ingresoActualCuentaB + $resumen->totalPagar;
                                $resultSalida = $this->model->actualizarIngresoCuenta($nuevoIngresoJsonActual, $codigoCuentaBancaria);
                                if ($resultSalida != "ok") {
                                    throw new Exception("Error al actualizar ingreso para la cuenta");
                                }

                                // actualizar saldo de la cuenta seleccionada
                                $saldoInicialCuenta = (float) ($datosCuentas[0]['saldoInicial'] ?? 0);
                                $salidaActualCuentaB = (float) ($datosCuentas[0]['salidas'] ?? 0);
                                $nuevoSaldoCuenta = $saldoInicialCuenta + $nuevoIngresoJsonActual - $salidaActualCuentaB;
                                $resultado = $this->model->actualizarSaldoCuentas($nuevoSaldoCuenta, $codigoCuentaBancaria);
                                if ($resultado != "ok") {
                                    throw new Exception("Error al actualizar saldo para la cuenta");
                                }
                            } else if ($tipoDte === '05') {
                                // actualizamos la salida de las cuentas
                                $salidaActualCuenta = $this->model->obtenerSalidasCuenta($codigoCuentaBancaria);
                                $nuevaSalida = $salidaActualCuenta['salidas'] + $resumen->montoTotalOperacion;
                                $resultSalida = $this->model->actualizarSalidasCuentas($nuevaSalida, $codigoCuentaBancaria);
                                if ($resultSalida != "ok") {
                                    throw new Exception("Error al actualizar salida para la cuenta");
                                }

                                // se actualiza el saldo de las cuentas
                                $saldoInicialCuenta = (float) ($datosCuentas[0]['saldoInicial'] ?? 0);
                                $ingresoActualCuenta = (float) ($datosCuentas[0]['ingresos'] ?? 0);
                                $nuevoSaldoCuenta = $saldoInicialCuenta + $ingresoActualCuenta - $nuevaSalida;
                                $resultado = $this->model->actualizarSaldoCuentas($nuevoSaldoCuenta, $codigoCuentaBancaria);
                                if ($resultado != "ok") {
                                    throw new Exception("Error al actualizar saldo para la cuenta");
                                }
                            }
                        } else {
                            // Si no se requiere actualización de cuentas, puedes ignorar
                            // o incluso registrar en el log que no se aplicó ningún cambio.
                        }


			$this->enviarFacturaPorCorreo($receptor->correo, $identificacion->numeroControl, $tipoDte);
                        $this->sendJsonResponse([
                            'status' => 'success',
                            'data' => $datosGenerados,
                            'jsonFirmado' => $jsonFirmado,
                            'token' => $token,
                            'emision' => $decoded
                        ]);
                        exit;
                    } else {
                        $estadoConsulta = $this->consultarEstadoDTE($codigoGeneracion, $tipoDte, $nit, $token['token']);

                        if (isset($estadoConsulta['estado']) && $estadoConsulta['estado'] === 'PROCESADO' && !empty($estadoConsulta['selloRecibido'])) {
                            $sello = $estadoConsulta['selloRecibido'];
                            $estadoo = $estadoConsulta['estado'];
                            $fechaProcesamiento = $estadoConsulta['fhProcesamiento'];
                            $observaciones = is_array($decoded['observaciones']) ? json_encode($decoded['observaciones'], JSON_UNESCAPED_UNICODE) : $decoded['observaciones'];
                            $datosGenerados['selloRecibido'] = $sello;
                            $this->model->actualizarEstadoDte($sello, $estadoo, $fechaProcesamiento, $observaciones, $identificacion->numeroControl);
                            $this->model->confirmarTransaccion();
                            foreach ($cuerpoDocumento as $item) {
                                $codigoProducto = $item['codigo'];
                                $cantidadDescontar = $item['cantidad'];

                                // Validar código de producto antes de actualizar existencia
                                if (empty($codigoProducto)) {
                                    // Si no hay código de producto, significa que es un ítem que no afecta inventario, así que saltamos
                                    continue;
                                }

                                // Obtener existencia actual del producto
                                $cantidadActual = $this->model->getExistencia($codigoProducto, $codigoProyecto);
                                $cantidadProductoA = $cantidadActual[0]['cantidadProducto'] ?? 0;

                                // Calcular nueva existencia
                                if ($tipoDte === '05') {
                                // Devolución: aumentar existencias
                                $existencia = $cantidadProductoA + $cantidadDescontar;
                            } else {
                                // Venta: disminuir existencias
                                $existencia = $cantidadProductoA - $cantidadDescontar;
                            }

                                // Actualizar existencia
                                $resultExistencia = $this->model->actualizarExistencias($existencia, $codigoProyecto, $codigoProducto);
                                if ($resultExistencia != "ok") {
                                    throw new Exception("Error al actualizar las existencias del producto con código: $codigoProducto");
                                }
                            }

                            $datosCuentas = $this->model->obtenerSaldoCuenta($codigoCuentaBancaria);
                            // Solo ejecutar si el tipo DTE requiere afectar cuentas bancarias
                            if (in_array($tipoDte, ['01', '03', '05']) && is_array($datosCuentas) && count($datosCuentas) > 0) {

                                if ($tipoDte === '01' || $tipoDte === '03') {
                                    // actualizar ingreso de la cuenta seleccionada
                                    $ingresoActualCuentaB = (float) ($datosCuentas[0]['ingresos'] ?? 0);
                                    $nuevoIngresoJsonActual = $ingresoActualCuentaB + $resumen->totalPagar;
                                    $resultSalida = $this->model->actualizarIngresoCuenta($nuevoIngresoJsonActual, $codigoCuentaBancaria);
                                    if ($resultSalida != "ok") {
                                        throw new Exception("Error al actualizar ingreso para la cuenta");
                                    }

                                    // actualizar saldo de la cuenta seleccionada
                                    $saldoInicialCuenta = (float) ($datosCuentas[0]['saldoInicial'] ?? 0);
                                    $salidaActualCuentaB = (float) ($datosCuentas[0]['salidas'] ?? 0);
                                    $nuevoSaldoCuenta = $saldoInicialCuenta + $nuevoIngresoJsonActual - $salidaActualCuentaB;
                                    $resultado = $this->model->actualizarSaldoCuentas($nuevoSaldoCuenta, $codigoCuentaBancaria);
                                    if ($resultado != "ok") {
                                        throw new Exception("Error al actualizar saldo para la cuenta");
                                    }
                                } else if ($tipoDte === '05') {
                                    // actualizamos la salida de las cuentas
                                    $salidaActualCuenta = $this->model->obtenerSalidasCuenta($codigoCuentaBancaria);
                                    $nuevaSalida = $salidaActualCuenta['salidas'] + $resumen->montoTotalOperacion;
                                    $resultSalida = $this->model->actualizarSalidasCuentas($nuevaSalida, $codigoCuentaBancaria);
                                    if ($resultSalida != "ok") {
                                        throw new Exception("Error al actualizar salida para la cuenta");
                                    }

                                    // se actualiza el saldo de las cuentas
                                    $saldoInicialCuenta = (float) ($datosCuentas[0]['saldoInicial'] ?? 0);
                                    $ingresoActualCuenta = (float) ($datosCuentas[0]['ingresos'] ?? 0);
                                    $nuevoSaldoCuenta = $saldoInicialCuenta + $ingresoActualCuenta - $nuevaSalida;
                                    $resultado = $this->model->actualizarSaldoCuentas($nuevoSaldoCuenta, $codigoCuentaBancaria);
                                    if ($resultado != "ok") {
                                        throw new Exception("Error al actualizar saldo para la cuenta");
                                    }
                                }
                            } else {
                                // Si no se requiere actualización de cuentas, puedes ignorar
                                // o incluso registrar en el log que no se aplicó ningún cambio.
                            }


				$this->enviarFacturaPorCorreo($receptor->correo, $identificacion->numeroControl, $tipoDte);
                            $this->sendJsonResponse([
                                'status' => 'success',
                                'data' => $datosGenerados,
                                'jsonFirmado' => $jsonFirmado,
                                'token' => $token,
                                'emision' => $estadoConsulta
                            ]);
                            exit;
                        }

                        if ($intentos >= $maxIntentos) {
                            $errores = "Error en envío: " . ($decoded['descripcionMsg'] ?? 'Respuesta desconocida');
                            if (isset($decoded['observaciones'])) {
                                foreach ($decoded['observaciones'] as $obs) {
                                    $errores .= "\n- Observación: $obs";
                                }
                            }
                            $this->sendJsonResponse(['status' => 'error', 'message' => $response]);
                            throw new Exception($errores);
                        } else {
                            sleep(8); // pausa antes del reintento
                        }
                    }
                }
            } catch (Exception $e) {
                $this->model->revertirTransaccion();
                $msg = $e->getMessage();
                $this->sendJsonResponse([
                    'status' => 'error',
                    'message' => $msg,
                    // 'data' => $datosGenerados, // incluir aunque sea parcial
                ]);
            }
        }
    }


    private function consultarEstadoDTE($codigoGeneracion, $tipoDte, $nit, $token)
    {
        //$url = "https://apitest.dtes.mh.gob.sv/fesv/recepcion/consultadte/";
        $url = "https://api.dtes.mh.gob.sv/fesv/recepcion/consultadte/";

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


    public function vistaDebugJSON()
    {
        if (empty($_POST['jsonDebug'])) {
            exit('No se enviaron datos para el debug.');
        }

        $json = json_decode($_POST['jsonDebug'], true);
        if (!$json) {
            exit('JSON inválido');
        }

        header('Content-Type: application/json');
        header('Content-Disposition: inline; filename="debug_dte.json"'); // o attachment para descargar
        echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }


    public function vistaPreviaPDF()
    {
        if (empty($_POST['data'])) {
            exit('No se enviaron datos');
        }

        $datos = json_decode($_POST['data'], true);
        if (!$datos) {
            exit('Datos inválidos');
        }

        $tipoDte = $datos['identificacion']['tipoDte'] ?? null;

        if (!$tipoDte) {
            exit('No se proporcionó el tipo de DTE');
        }

        // Según el tipo de documento, llama a la función correspondiente
        switch ($tipoDte) {
            case '01': // Factura electrónica
                $this->vistaFacturaPDF($datos);
                break;
            case '03': // Comprobante de crédito fiscal electrónico
                $this->vistaComprobantePDF($datos);
                break;
            case '05': // Nota de credito
                $this->vistaNotaDeCreditoPDF($datos);
                break;
            default:
                exit('Tipo de DTE no soportado para vista previa');
        }
    }

    public function vistaFacturaPDF($datos)
    {
        require('Libraries/tcpdf/tcpdf.php');

        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterMargin(15);

        $ident = $datos['identificacion'];
        $numeroControl = $ident['numeroControl'];
        $dte = $this->model->getDtePorNumeroControl($numeroControl);
        $dte_encabezado = $this->model->getDTEdte_encabezado($numeroControl);
        $pdf->AddPage();
        $pdf->SetTitle('FACTURA_N_' . $dte[0]['numeroControl'] . '.pdf', 'I');

        // Accede a los datos del array
        $receptor = $datos['receptor'];
        $tercero = $datos['ventaTercero'];
        $docRelacionado = $datos['documentoRelacionado'];
        $docAsociado = $datos['otrosDocumentos'];
        $cuerpoDocumento = $datos['cuerpoDocumento'];
        $resumen = $datos['resumen'];

        $codigoModelo = $ident['tipoModelo'];
        $nombreModelo = $this->model->getNombreModeloFacturacion($codigoModelo);
        $empresa = $this->model->getEmpresa();
        $codigoDepartamento = $empresa['departamento'];
        $codigoMunicipio = $empresa['municipio'];
        $complemento = $empresa['direccion'];
        $nombreDepartamento = $this->model->getNombreDepartamento($codigoDepartamento);
        $nombreMunicipio = $this->model->getNombreMunicipio($codigoMunicipio, $codigoDepartamento);


        // Dibujar borde redondeado
        $pdf->SetLineWidth(0.5);

        $pdf->Image(base_url . 'Assets/img/logo.jpg', 15, 0, 35); // Logo

        $this->centrarCelda($pdf, 180, 3, 'DOCUMENTO TRIBUTARIO ELECTRÓNICO', 8, 'B');
        $this->centrarCelda($pdf, 180, 9, 'FACTURA', 8, 'B');

        $this->escribirEtiqueta($pdf, 10, 20, 'Código de Generación:', $ident['codigoGeneracion'], 30);
        $this->escribirEtiqueta($pdf, 14, 23, 'Número de Control:', $ident['numeroControl'], 26);


        if (!empty($dte_encabezado)) {

            $this->escribirEtiqueta($pdf, 14, 26, 'Sello de Recepción:', $dte_encabezado, 26);
        } else {
            $this->escribirEtiqueta($pdf, 14, 26, 'Sello de Recepción:', '', 26);
        }

        $qrContenido = 'https://admin.factura.gob.sv/consultaPublica?ambiente=' . $ident['ambiente'] . '&codGen=' . $ident['codigoGeneracion'] . '&fechaEmi=' . $ident['fecEmi'] . '';

        $style = [
            'border' => 0,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false,
            'module_width' => 1,
            'module_height' => 1
        ];

        // Posición y tamaño similar al Image anterior
        $pdf->write2DBarcode($qrContenido, 'QRCODE,H', 100, 15, 25, 25, $style, 'N');

        $this->escribirEtiqueta($pdf, 135, 20, 'Modelo de Facturación:', $nombreModelo, 32);
        $modeloTransmision = $this->model->getTipoModeloTransmision();
        $codigoModelo = $ident['tipoModelo'];
        $nombreTransmision = isset($modeloTransmision['modeloTransmision'][$codigoModelo]['nombre'])
            ? $modeloTransmision['modeloTransmision'][$codigoModelo]['nombre']
            : 'Desconocido';
        $this->escribirEtiqueta($pdf, 138, 23, 'Tipo de Transmisión:', $nombreTransmision, 29);
        $this->escribirEtiqueta($pdf, 130, 26, 'Fecha y hora de generación:', $ident['fecEmi'] . ' ' . $ident['horEmi'], 37);

        // Rectángulo para EMISOR
        $this->escribirEtiqueta($pdf, 50, 40, 'EMISOR', '');
        $pdf->RoundedRect(10, 45, 90, 45, 3, '1111');
        // $this->escribirEtiqueta($pdf, 12, 47, 'Nombre o razón social:', $emisor['nombre'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 51, 'NIT:', $empresa['nit'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 55, 'NRC:', $empresa['nrc'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 59, 'Actividad económica:', $empresa['descActividad'], 30, 50);
        $direccion = [
            $nombreDepartamento,
            $nombreMunicipio,
            $complemento,
        ];

        $direccionFormateada = implode(', ', array_filter($direccion));

        $this->escribirEtiqueta($pdf, 12, 63, 'Dirección:', $direccionFormateada, 30, 50);

        // $this->escribirEtiqueta($pdf, 12, 63, 'Direccion:', $emisor['direccion']['complemento'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 71, 'Número de teléfono:', $empresa['telefono'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 75, 'Correo electrónico:', $empresa['correo'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 79, 'Nombre Comercial:', $empresa['nombre'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 83, 'Tipo de establecimiento:', 'Casa matriz', 30, 50);

        // Rectángulo para RECEPTOR
        $this->escribirEtiqueta($pdf, 150, 40, 'RECEPTOR', '');
        $pdf->RoundedRect(111, 45, 90, 45, 3, '1111');

        $this->escribirEtiqueta($pdf, 113, 51, 'Nombre o razón social:', $receptor['nombre'], 30, 50);
        if ($receptor['tipoDocumento'] == '02') {
            $this->escribirEtiqueta($pdf, 113, 55, 'Carnet de Residente:', $receptor['numDocumento'], 30, 50);
        } else if ($receptor['tipoDocumento'] == '03') {
            $this->escribirEtiqueta($pdf, 113, 55, 'Pasaporte:', $receptor['numDocumento'], 30, 50);
        } else if ($receptor['tipoDocumento'] == '13') {
            $this->escribirEtiqueta($pdf, 113, 55, 'DUI:', $receptor['numDocumento'], 30, 50);
        } else if ($receptor['tipoDocumento'] == '36') {
            $this->escribirEtiqueta($pdf, 113, 55, 'NIT:', $receptor['numDocumento'], 30, 50);
        } else if ($receptor['tipoDocumento'] == '37') {
            $this->escribirEtiqueta($pdf, 113, 55, 'Identificación:', $receptor['numDocumento'], 30, 50);
        }

        if (!empty($receptor['telefono'])) {
            $this->escribirEtiqueta($pdf, 113, 59, 'Telefono:', $receptor['telefono'], 30, 50);
        }

        // Rectángulo para CUENTA DE TERCEROS
        if (!empty($tercero['nit'])) {
            $this->escribirEtiqueta($pdf, 89.5, 95, 'VENTA A CUENTA DE TERCEROS', '', 45);
            $pdf->RoundedRect(10, 98, 195, 5, 1, '1111');
            if (!empty($tercero)) {
                $this->escribirEtiqueta($pdf, 12, 99, 'NIT:', $tercero['nit'], 8);
                $this->escribirEtiqueta($pdf, 65, 99, 'Nombre, denominacion o razón social:', $tercero['nombre'], 50);
            } else {
                $this->escribirEtiqueta($pdf, 65, 99, 'Nombre, denominacion o razón social:', '', 50);
                $this->escribirEtiqueta($pdf, 12, 99, 'NIT:', '', 8);
            }
        }


        // === DOCUMENTOS RELACIONADOS ===
        $yInicioDocRel = 105;
        $docRelacionado = $docRelacionado ?? []; // asegura que sea un array
        if (!empty($docRelacionado)) {

            // Título
            $this->escribirEtiqueta($pdf, 89.5, $yInicioDocRel, 'DOCUMENTOS RELACIONADOS', '', 45);

            // Altura dinámica
            $altoFila = 5;
            $cantidadDocsRelacionados = count($docRelacionado);

            $altoRectRelacionados = max(1, $cantidadDocsRelacionados) * $altoFila + 6; // mínimo una fila para encabezados
            $yRectDocRel = $yInicioDocRel + 5;
            $pdf->RoundedRect(10, $yRectDocRel, 195, $altoRectRelacionados, 1, '1111');

            // Encabezados
            $yEncabezado = $yRectDocRel + 1;
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->SetXY(12, $yEncabezado);
            $pdf->MultiCell(50, $altoFila, 'Tipo de Documento', 0, 'L', false, 0);
            $pdf->MultiCell(60, $altoFila, 'N° de documento', 0, 'L', false, 0);
            $pdf->MultiCell(60, $altoFila, 'Fecha del Documento', 0, 'L', false, 1);

            // Datos si hay
            $pdf->SetFont('helvetica', '', 7);
            $yDatos = $yEncabezado + $altoFila;
            foreach ($docRelacionado as $doc) {
                $pdf->SetXY(12, $yDatos);

                // Obtener el nombre del tipo de documento desde el código
                $tipoDocData = $this->model->getTipoDocumento($doc['tipoDocumento']);
                $tipoDocumento = $tipoDocData ? $tipoDocData['nombreTipoDocumento'] : 'Desconocido';

                $pdf->MultiCell(50, $altoFila, $tipoDocumento, 0, 'L', false, 0);
                $pdf->MultiCell(60, $altoFila, $doc['numeroDocumento'], 0, 'L', false, 0);
                $pdf->MultiCell(60, $altoFila, $doc['fechaEmision'], 0, 'L', false, 1);

                $yDatos += $altoFila;
            }
        } else {
            $altoFila = 5;
            $docRelacionado = $docRelacionado ?? [];
            $cantidadDocsRelacionados = count($docRelacionado);
            $altoRectRelacionados = max(0, $cantidadDocsRelacionados) * $altoFila + 0; // mínimo una fila para encabezados
            $yRectDocRel = $yInicioDocRel + 0;
        }



        // === OTROS DOCUMENTOS ASOCIADOS ===
        $yInicioAsociado = $yRectDocRel + $altoRectRelacionados + 5;
        $docAsociado = $docAsociado ?? [];
        if (!empty($docAsociado)) {

            // Título
            $this->escribirEtiqueta($pdf, 89.5, $yInicioAsociado, 'OTROS DOCUMENTOS ASOCIADOS', '', 45);

            // Altura dinámica
            $cantidadDocsAsociados = count($docAsociado);
            $altoRectAsociados = $cantidadDocsAsociados * $altoFila + 6;
            $yRectAsociado = $yInicioAsociado + 5;
            $pdf->RoundedRect(10, $yRectAsociado, 195, $altoRectAsociados, 1, '1111');

            // Encabezados
            $yEncabezado = $yRectAsociado + 1;
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->SetXY(12, $yEncabezado);
            $pdf->MultiCell(60, $altoFila, 'Identificacion documento', 0, 'L', false, 0);
            $pdf->MultiCell(60, $altoFila, 'Descripción', 0, 'L', false, 0);

            // Datos
            if (!empty($docAsociado)) {
                $pdf->SetFont('helvetica', '', 7);
                $yDatos = $yEncabezado + $altoFila;

                $tiposServicio = $this->model->getTipoServicioMedico();

                foreach ($docAsociado as $doc) {
                    if (!empty($doc['medico'])) {
                        $medico = $doc['medico'];
                        $pdf->SetXY(12, $yDatos);

                        $nitMedico = !empty($medico['nit']) ? $medico['nit'] : (!empty($medico['docIdentificacion']) ? $medico['docIdentificacion'] : '');

                        $codigoTipo = isset($medico['tipoServicio']) ? $medico['tipoServicio'] : null;
                        $tipoServicioNombre = isset($tiposServicio['tipoServicioMedico'][$codigoTipo]['nombre'])
                            ? $tiposServicio['tipoServicioMedico'][$codigoTipo]['nombre']
                            : 'Desconocido';

                        $pdf->MultiCell(60, $altoFila, $nitMedico, 0, 'L', false, 0);
                        $pdf->MultiCell(60, $altoFila, $tipoServicioNombre, 0, 'L', false, 1);
                        $yDatos += $altoFila;
                    } else {
                        $pdf->SetXY(12, $yDatos);
                        $pdf->MultiCell(60, $altoFila, $doc['descDocumento'] ?? '', 0, 'L', false, 0);
                        $pdf->MultiCell(60, $altoFila, $doc['detalleDocumento'] ?? '', 0, 'L', false, 1);
                        $yDatos += $altoFila;
                    }
                }
            } else if (!empty($docAsociado['medico'])) {
                $pdf->SetFont('helvetica', '', 7);
                $yDatos = $yEncabezado + $altoFila;

                $tiposServicio = $this->model->getTipoServicioMedico();

                $medico = $docAsociado['medico'];
                $pdf->SetXY(12, $yDatos);

                $nitMedico = !empty($medico['nit']) ? $medico['nit'] : (!empty($medico['docIdentificacion']) ? $medico['docIdentificacion'] : '');

                $codigoTipo = isset($medico['tipoServicio']) ? $medico['tipoServicio'] : null;
                $tipoServicioNombre = isset($tiposServicio['tipoServicioMedico'][$codigoTipo]['nombre'])
                    ? $tiposServicio['tipoServicioMedico'][$codigoTipo]['nombre']
                    : 'Desconocido';

                $pdf->MultiCell(60, $altoFila, $nitMedico, 0, 'L', false, 0);
                $pdf->MultiCell(60, $altoFila, $tipoServicioNombre, 0, 'L', false, 1);
                $yDatos += $altoFila;
            }
        } else {
            $altoFila = 0;
            $docAsociado = $docAsociado ?? [];
            $cantidadDocsAsociados = count($docAsociado);
            $altoRectAsociados = $cantidadDocsAsociados * $altoFila + 0;
            $yRectAsociado = $yInicioAsociado + 0;
        }





        // CUERPO DE FACTURA
        // Definición de anchos
        $anchos = [
            'numItem'        => 13,
            'cantidad'       => 15,
            // 'uniMedida'      => 15,
            'descripcion'    => 60,
            'precioUni'      => 20,
            'noGravado'      => 20,
            'montoDescu'     => 20,
            'ventaNoSuj'     => 15,
            'ventaExenta'    => 15,
            'ventaGravada'   => 17
        ];

        // Encabezados legibles
        $encabezados = [
            'numItem'        => 'N°',
            'cantidad'       => 'Cantidad',
            // 'uniMedida'      => 'Unidad',
            'descripcion'    => 'Descripción',
            'precioUni'      => 'Precio Unitario',
            'noGravado'      => 'Otros montos no afectos.',
            'montoDescu'     => 'Descuento por Item',
            'ventaNoSuj'     => 'Ventas No Sujetas',
            'ventaExenta'    => 'Ventas Exentas',
            'ventaGravada'   => 'Ventas Gravadas'
        ];

        // Estilos de tabla
        $pdf->SetLineWidth(0.05); // Ultra delgado
        $pdf->SetFillColor(230, 230, 230); // Fondo gris claro para encabezado

        $altoEncabezado = 10;
        $yInicial = $yRectAsociado + $altoRectAsociados + 5;
        $x = 10;

        // Encabezado
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetXY($x, $yInicial);
        foreach ($encabezados as $key => $titulo) {
            $pdf->MultiCell($anchos[$key], $altoEncabezado, $titulo, 1, 'C', true, 0, '', '', true, 0, false, true, $altoEncabezado, 'M');
        }
        $pdf->Ln();
        $yInicial += $altoEncabezado;

        $altoFilaMinimo = 8;
        $pdf->SetFont('helvetica', '', 7);

        foreach ($cuerpoDocumento as $prod) {
            $alturas = [];

            // CALCULAR ALTURA NECESARIA PARA CADA CELDA (especialmente descripción)
            foreach (array_keys($anchos) as $key) {
                $valor = $prod[$key];

                if ($key === 'numItem') {
                    $valor = number_format($valor, 0);        // Solo numItem va entero
                } else if ($key === 'uniMedida') {
                    $valor = number_format($valor, 0);        // Solo numItem va entero
                } elseif ($key === 'cantidad') {
                    $valor = number_format($valor, 2);        // Cantidad con 2 decimales
                } elseif (is_numeric($valor)) {
                    $valor = number_format($valor, 2);        // El resto con 2 decimales
                }

                $align = 'C';
                if ($key === 'descripcion') $align = 'L';
                if (in_array($key, ['precioUni', 'noGravado', 'montoDescu', 'ventaNoSuj', 'ventaExenta', 'ventaGravada'])) $align = 'R';

                // Obtener la altura real del contenido
                $altura = $pdf->getStringHeight($anchos[$key], $valor);
                $alturas[] = $altura;
            }

            $altoFila = max($alturas); // la fila debe tener la altura del contenido más alto
            $altoFila = max($altoFilaMinimo, $altoFila); // garantizar un mínimo

            // VERIFICAR SI CABE EN LA PÁGINA ACTUAL
            $espacioDisponible = $pdf->getPageHeight() - $pdf->getBreakMargin();
            if ($yInicial + $altoFila > $espacioDisponible) {
                $pdf->AddPage();
                $yInicial = 10;

                // Repetir encabezado
                $pdf->SetFont('helvetica', 'B', 7);
                $x = 10;
                $pdf->SetXY($x, $yInicial);
                foreach ($encabezados as $key => $titulo) {
                    $pdf->MultiCell($anchos[$key], $altoEncabezado, $titulo, 1, 'C', true, 0, '', '', true, 0, false, true, $altoEncabezado, 'M');
                }
                $pdf->Ln();
                $yInicial += $altoEncabezado;
                $pdf->SetFont('helvetica', '', 7);
            }

            // IMPRIMIR LA FILA
            $x = 10;
            $pdf->SetXY($x, $yInicial);
            foreach (array_keys($anchos) as $i => $key) {
                $valor = $prod[$key];

                // // Si es la unidad de medida, convertir el ID a nombre
                // if ($key === 'uniMedida') {
                //     $valor = $this->model->getNombreUnidadMedida($valor);
                // }

                // Formatear valores numéricos
                if ($key === 'numItem' && is_numeric($valor)) {
                    $valor = number_format($valor, 0);
                } elseif ($key === 'cantidad' && is_numeric($valor)) {
                    $valor = number_format($valor, 2);
                } elseif (is_numeric($valor)) {
                    $valor = number_format($valor, 2);
                }

                // Alineación
                $align = 'C';
                if ($key === 'descripcion') $align = 'L';
                if (in_array($key, ['precioUni', 'noGravado', 'montoDescu', 'ventaNoSuj', 'ventaExenta', 'ventaGravada'])) $align = 'R';

                $pdf->MultiCell($anchos[$key], $altoFila, $valor, 0, $align, false, 0, '', '', true, 0, true, true, $altoFila, 'M');
            }


            $pdf->Ln();
            $yInicial += $altoFila;
        }

        // Espacio después del cuerpo
        $yResumen = $yInicial + 5;

        // Verificar si cabe el resumen en esta página (56 de alto)
        if ($yResumen + 56 > $espacioDisponible) {
            $pdf->AddPage();
            $yResumen = 20;
        }

        // Dibujar el rectángulo del resumen
        $altoResumen = 60; // 10 líneas de altura 5 + margen
        $pdf->RoundedRect(125, $yResumen, 80, $altoResumen, 1, '1111');

        $yEtiqueta = $yResumen + 3;
        $altoFila = 5;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Sumatoria de ventas:', number_format($resumen['subTotalVentas'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas no sujetas:', number_format($resumen['descuNoSuj'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas exentas:', number_format($resumen['descuExenta'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas gravadas:', number_format($resumen['descuGravada'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Nombre del Tributo', 'Valor del tributo', 50, 30); // Asumimos que esto es solo etiqueta fija
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Sub-Total:', number_format($resumen['subTotal'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'IVA Retenido:', number_format($resumen['ivaRete1'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Retención de Renta:', number_format($resumen['reteRenta'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto Total de la Operación:', number_format($resumen['montoTotalOperacion'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Total Otros Montos No Afectos de la Operación:', number_format($resumen['totalNoGravado'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Total a Pagar:', number_format($resumen['totalPagar'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        // Ahora el bloque de “Valor en Letras / Condición de la Operación” al lado izquierdo
        $yOperacion = $yResumen; // para que empiece alineado con el resumen

        // Verificar si cabe en la página
        if ($yOperacion + 10 > $espacioDisponible) {
            $pdf->AddPage();
            $yOperacion = 10;
        }

        // Dibujar rectángulo a la izquierda
        $pdf->RoundedRect(10, $yOperacion, 110, 20, 1, '1111');
        $yEtiqueta = $yResumen + 3;
        $altoFila = 5;

        // Escribir etiquetas
        $this->escribirEtiqueta($pdf, 12, $yEtiqueta, 'Valor en Letras:', $resumen['totalLetras'], 20, 70);
        $yEtiqueta += $altoFila;
        $nombreCondicion = $this->model->getNombreCondicionOperacion($resumen['condicionOperacion']);
        $this->escribirEtiqueta($pdf, 12, $yEtiqueta, 'Condición de la Operación:', $nombreCondicion, 20, 20);

        $pdf->Output('FACTURA_N_' . $dte[0]['numeroControl'] . '.pdf', 'I');
    }

    public function vistaComprobantePDF($datos)
    {
        require('Libraries/tcpdf/tcpdf.php');

        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterMargin(15);

        $ident = $datos['identificacion'];
        $numeroControl = $ident['numeroControl'];
        $dte = $this->model->getDtePorNumeroControl($numeroControl);
        $dte_encabezado = $this->model->getDTEdte_encabezado($numeroControl);
        $pdf->AddPage();
        $pdf->SetTitle('COMPROBANTE DE CREDITO FISCAL_N_' . $dte[0]['numeroControl'] . '.pdf', 'I');

        // Accede a los datos del array
        $receptor = $datos['receptor'];
        $tercero = $datos['ventaTercero'];
        $docRelacionado = $datos['documentoRelacionado'];
        $docAsociado = $datos['otrosDocumentos'];
        $cuerpoDocumento = $datos['cuerpoDocumento'];
        $resumen = $datos['resumen'];

        $codigoModelo = $ident['tipoModelo'];
        $codigoDepartamentoR = $receptor['direccion']['departamento'];
        $codigoMunicipioR = $receptor['direccion']['municipio'];
        $nombreDepartamentoReceptor = $this->model->getNombreDepartamento($codigoDepartamentoR);
        $nombreMunicipioReceptor = $this->model->getNombreMunicipio($codigoMunicipioR, $codigoDepartamentoR);
        $complementoR = $receptor['direccion']['complemento'];
        $nombreModelo = $this->model->getNombreModeloFacturacion($codigoModelo);
        $empresa = $this->model->getEmpresa();
        $codigoDepartamento = $empresa['departamento'];
        $codigoMunicipio = $empresa['municipio'];
        $complemento = $empresa['direccion'];
        $nombreDepartamento = $this->model->getNombreDepartamento($codigoDepartamento);
        $nombreMunicipio = $this->model->getNombreMunicipio($codigoMunicipio, $codigoDepartamento);

        // Dibujar borde redondeado
        $pdf->SetLineWidth(0.5);

        $pdf->Image(base_url . 'Assets/img/logo.jpg', 15, 0, 35); // Logo

        $this->centrarCelda($pdf, 180, 3, 'DOCUMENTO TRIBUTARIO ELECTRÓNICO', 8, 'B');
        $this->centrarCelda($pdf, 180, 9, 'COMPROBANTE DE CRÉDITO FISCAL', 8, 'B');

        $this->escribirEtiqueta($pdf, 10, 20, 'Código de Generación:', $ident['codigoGeneracion'], 30);
        $this->escribirEtiqueta($pdf, 14, 23, 'Número de Control:', $ident['numeroControl'], 26);

        if (!empty($dte_encabezado)) {
            $this->escribirEtiqueta($pdf, 14, 26, 'Sello de Recepción:', $dte_encabezado, 26);
        } else {
            $this->escribirEtiqueta($pdf, 14, 26, 'Sello de Recepción:', '', 26);
        }

        // codigo QR
        $qrContenido = 'https://admin.factura.gob.sv/consultaPublica?ambiente=' . $ident['ambiente'] . '&codGen=' . $ident['codigoGeneracion'] . '&fechaEmi=' . $ident['fecEmi'] . '';
        $style = [
            'border' => 0,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false,
            'module_width' => 1,
            'module_height' => 1
        ];

        // Posición y tamaño similar al Image anterior
        $pdf->write2DBarcode($qrContenido, 'QRCODE,H', 100, 15, 25, 25, $style, 'N');

        $this->escribirEtiqueta($pdf, 135, 20, 'Modelo de Facturación:', $nombreModelo, 32);
        $modeloTransmision = $this->model->getTipoModeloTransmision();
        $codigoModelo = $ident['tipoModelo'];
        $nombreTransmision = isset($modeloTransmision['modeloTransmision'][$codigoModelo]['nombre'])
            ? $modeloTransmision['modeloTransmision'][$codigoModelo]['nombre']
            : 'Desconocido';
        $this->escribirEtiqueta($pdf, 138, 23, 'Tipo de Transmisión:', $nombreTransmision, 29);
        $this->escribirEtiqueta($pdf, 130, 26, 'Fecha y hora de generación:', $ident['fecEmi'] . ' ' . $ident['horEmi'], 37);

        // Rectángulo para EMISOR
        $this->escribirEtiqueta($pdf, 50, 40, 'EMISOR', '');
        $pdf->RoundedRect(10, 45, 90, 45, 3, '1111');
        $this->escribirEtiqueta($pdf, 12, 47, 'Nombre o razón social:', $empresa['nombre'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 51, 'NIT:', $empresa['nit'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 55, 'NRC:', $empresa['nrc'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 59, 'Actividad económica:', $empresa['descActividad'], 30, 50);
        $direccion = [
            $nombreDepartamento,
            $nombreMunicipio,
            $complemento
        ];

        $direccionFormateada = implode(', ', array_filter($direccion));
        $this->escribirEtiqueta($pdf, 12, 63, 'Direccion:', $direccionFormateada, 30, 50);
        $this->escribirEtiqueta($pdf, 12, 71, 'Número de teléfono:', $empresa['telefono'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 75, 'Correo electrónico:', $empresa['correo'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 79, 'Nombre Comercial:', $empresa['nombre'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 83, 'Tipo de establecimiento:', 'Casa matriz', 30, 50);

        // Rectángulo para RECEPTOR
        $this->escribirEtiqueta($pdf, 150, 40, 'RECEPTOR', '');
        $pdf->RoundedRect(111, 45, 90, 45, 3, '1111');
        if (!empty($receptor['nombre'])) {
            $this->escribirEtiqueta($pdf, 113, 47, 'Nombre o razón social:', $receptor['nombre'], 30, 50);
        }
        if (!empty($receptor['nit'])) {
            $this->escribirEtiqueta($pdf, 113, 51, 'NIT:', $receptor['nit'], 30, 50);
        }
        $this->escribirEtiqueta($pdf, 113, 55, 'NRC:', $receptor['nrc'], 30, 50);
        if (!empty($receptor['descActividad'])) {
            $this->escribirEtiqueta($pdf, 113, 59, 'Actividad económica:', $receptor['descActividad'], 30, 50);
        }
        $direccion = [
            $nombreDepartamentoReceptor,
            $nombreMunicipioReceptor,
            $complementoR,
        ];

        $direccionFormateada = implode(', ', array_filter($direccion));

        $this->escribirEtiqueta($pdf, 113, 67, 'Dirección:', $direccionFormateada, 30, 50);
        $this->escribirEtiqueta($pdf, 113, 75, 'Correo electrónico:', $receptor['correo'], 30, 50);
        if (!empty($receptor['nombreComercial'])) {
            $this->escribirEtiqueta($pdf, 113, 83, 'Nombre Comercial:', $receptor['nombreComercial'], 30, 50);
        }
        // if (!empty($receptor['telefono'])) {
        //     $this->escribirEtiqueta($pdf, 113, 83, 'Telefono:', $receptor['telefono'], 30, 50);
        // }

        // === OTROS DOCUMENTOS ASOCIADOS ===
        // Nuevo Y dinámico para este bloque
        // $yInicioAsociado = $yRectDocRel + $altoRectRelacionados + 5;
        $yInicioAsociado = 95;
        $docAsociado = $docAsociado ?? [];
        if (!empty($docAsociado)) {

            // // Título
            $this->escribirEtiqueta($pdf, 89.5, $yInicioAsociado, 'OTROS DOCUMENTOS ASOCIADOS', '', 45);
            $altoFila = 5;
            // Altura dinámica
            $cantidadDocsAsociados = count($docAsociado);
            $altoRectAsociados = $cantidadDocsAsociados * $altoFila + 6;
            $yRectAsociado = $yInicioAsociado + 5;
            $pdf->RoundedRect(10, $yRectAsociado, 195, $altoRectAsociados, 1, '1111');

            // Encabezados
            $yEncabezado = $yRectAsociado + 1;
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->SetXY(12, $yEncabezado);
            $pdf->MultiCell(60, $altoFila, 'Identificacion documento', 0, 'L', false, 0);
            $pdf->MultiCell(60, $altoFila, 'Descripción', 0, 'L', false, 0);
            // Datos
            if (!empty($docAsociado)) {
                $pdf->SetFont('helvetica', '', 7);
                $yDatos = $yEncabezado + $altoFila;

                $tiposServicio = $this->model->getTipoServicioMedico();

                foreach ($docAsociado as $doc) {
                    if (!empty($doc['medico'])) {
                        $medico = $doc['medico'];
                        $pdf->SetXY(12, $yDatos);

                        $nitMedico = !empty($medico['nit']) ? $medico['nit'] : (!empty($medico['docIdentificacion']) ? $medico['docIdentificacion'] : '');

                        $codigoTipo = isset($medico['tipoServicio']) ? $medico['tipoServicio'] : null;
                        $tipoServicioNombre = isset($tiposServicio['tipoServicioMedico'][$codigoTipo]['nombre'])
                            ? $tiposServicio['tipoServicioMedico'][$codigoTipo]['nombre']
                            : 'Desconocido';

                        $pdf->MultiCell(60, $altoFila, $nitMedico, 0, 'L', false, 0);
                        $pdf->MultiCell(60, $altoFila, $tipoServicioNombre, 0, 'L', false, 1);
                        $yDatos += $altoFila;
                    } else {
                        $pdf->SetXY(12, $yDatos);
                        $pdf->MultiCell(60, $altoFila, $doc['descDocumento'] ?? '', 0, 'L', false, 0);
                        $pdf->MultiCell(60, $altoFila, $doc['detalleDocumento'] ?? '', 0, 'L', false, 1);
                        $yDatos += $altoFila;
                    }
                }
            } else if (!empty($docAsociado['medico'])) {
                $pdf->SetFont('helvetica', '', 7);
                $yDatos = $yEncabezado + $altoFila;

                $tiposServicio = $this->model->getTipoServicioMedico();

                $medico = $docAsociado['medico'];
                $pdf->SetXY(12, $yDatos);

                $nitMedico = !empty($medico['nit']) ? $medico['nit'] : (!empty($medico['docIdentificacion']) ? $medico['docIdentificacion'] : '');

                $codigoTipo = isset($medico['tipoServicio']) ? $medico['tipoServicio'] : null;
                $tipoServicioNombre = isset($tiposServicio['tipoServicioMedico'][$codigoTipo]['nombre'])
                    ? $tiposServicio['tipoServicioMedico'][$codigoTipo]['nombre']
                    : 'Desconocido';

                $pdf->MultiCell(60, $altoFila, $nitMedico, 0, 'L', false, 0);
                $pdf->MultiCell(60, $altoFila, $tipoServicioNombre, 0, 'L', false, 1);
                $yDatos += $altoFila;
            }
        } else {

            $yInicioAsociado = 90;
            $altoFila = 0;
            $docAsociado = $docAsociado ?? [];
            $cantidadDocsAsociados = count($docAsociado);
            $altoRectAsociados = $cantidadDocsAsociados * $altoFila + 0;
            $yRectAsociado = $yInicioAsociado + 0;
        }


        // Rectángulo para CUENTA DE TERCEROS
        $yInicioTercero = $yRectAsociado + $altoRectAsociados + 5;
        if (!empty($tercero['nit'])) {

            $this->escribirEtiqueta($pdf, 89.5, $yInicioTercero, 'VENTA A CUENTA DE TERCEROS', '', 45);
            // $cantidadDocsTercero = count($tercero);
            $altoFila = 5;
            $altoRectTercero =  $altoFila + 2;
            $yRectTercero = $yInicioTercero + 5;
            $pdf->RoundedRect(10, $yRectTercero, 195, $altoRectTercero, 1, '1111');
            $yDatosTercero = $yRectTercero + 1;
            $pdf->SetFont('helvetica', '', 7);
            if (!empty($tercero)) {
                $this->escribirEtiqueta($pdf, 12, $yDatosTercero, 'NIT:', $tercero['nit'], 8);
                $this->escribirEtiqueta($pdf, 65, $yDatosTercero, 'Nombre, denominación o razón social:', $tercero['nombre'], 50);
            } else {
                $this->escribirEtiqueta($pdf, 12, $yDatosTercero, 'NIT:', '', 8);
                $this->escribirEtiqueta($pdf, 65, $yDatosTercero, 'Nombre, denominación o razón social:', '', 50);
            }
        } else {
            $altoFila = 0;
            $altoRectTercero =  $altoFila + 0;
            $yRectTercero = $yInicioTercero + 0;
        }


        // // === DOCUMENTOS RELACIONADOS ===
        // Título
        $yInicioDocRel = $yRectTercero + $altoRectTercero + 5;
        $docRelacionado = $docRelacionado ?? []; // asegura que sea un array
        if (!empty($docRelacionado)) {
            $this->escribirEtiqueta($pdf, 89.5, $yInicioDocRel, 'DOCUMENTOS RELACIONADOS', '', 45);

            // Altura dinámica
            $altoFila = 5;
            $cantidadDocsRelacionados = count($docRelacionado);

            $altoRectRelacionados = max(1, $cantidadDocsRelacionados) * $altoFila + 6; // mínimo una fila para encabezados
            $yRectDocRel = $yInicioDocRel + 5;
            $pdf->RoundedRect(10, $yRectDocRel, 195, $altoRectRelacionados, 1, '1111');

            // Encabezados
            $yEncabezado = $yRectDocRel + 1;
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->SetXY(12, $yEncabezado);
            $pdf->MultiCell(50, $altoFila, 'Tipo de Documento', 0, 'L', false, 0);
            $pdf->MultiCell(60, $altoFila, 'N° de documento', 0, 'L', false, 0);
            $pdf->MultiCell(60, $altoFila, 'Fecha del Documento', 0, 'L', false, 1);
            // Datos si hay
            $pdf->SetFont('helvetica', '', 7);
            $yDatos = $yEncabezado + $altoFila;
            foreach ($docRelacionado as $doc) {
                $pdf->SetXY(12, $yDatos);

                // Obtener el nombre del tipo de documento desde el código
                $tipoDocData = $this->model->getTipoDocumento($doc['tipoDocumento']);
                $tipoDocumento = $tipoDocData ? $tipoDocData['nombreTipoDocumento'] : 'Desconocido';

                $pdf->MultiCell(50, $altoFila, $tipoDocumento, 0, 'L', false, 0);
                $pdf->MultiCell(60, $altoFila, $doc['numeroDocumento'], 0, 'L', false, 0);
                $pdf->MultiCell(60, $altoFila, $doc['fechaEmision'], 0, 'L', false, 1);

                $yDatos += $altoFila;
            }
        } else {
            $altoFila = 0;
            $docRelacionado = $docRelacionado ?? [];
            $cantidadDocsRelacionados = count($docRelacionado);
            $altoRectRelacionados = max(0, $cantidadDocsRelacionados) * $altoFila + 0;
            $yRectDocRel = $yInicioDocRel + 0;
        }


        // CUERPO DE CREDITO FISCAL
        // Definición de anchos
        $anchos = [
            'numItem'        => 13,
            'cantidad'       => 15,
            // 'uniMedida'      => 15,
            'descripcion'    => 60,
            'precioUni'      => 20,
            'montoDescu'     => 20,
            'noGravado'      => 20,
            'ventaNoSuj'     => 15,
            'ventaExenta'    => 15,
            'ventaGravada'   => 17
        ];

        // Encabezados legibles
        $encabezados = [
            'numItem'        => 'N°',
            'cantidad'       => 'Cantidad',
            // 'uniMedida'      => 'Unidad',
            'descripcion'    => 'Descripción',
            'precioUni'      => 'Precio Unitario',
            'montoDescu'     => 'Descuento por Item',
            'noGravado'      => 'Otros montos no afectos.',
            'ventaNoSuj'     => 'Ventas No Sujetas',
            'ventaExenta'    => 'Ventas Exentas',
            'ventaGravada'   => 'Ventas Gravadas'
        ];

        // Estilos de tabla
        $pdf->SetLineWidth(0.05); // Ultra delgado
        $pdf->SetFillColor(230, 230, 230); // Fondo gris claro para encabezado

        $altoEncabezado = 10;
        $yInicial = $yRectDocRel + $altoRectRelacionados + 5;
        $x = 10;

        // Encabezado
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetXY($x, $yInicial);
        foreach ($encabezados as $key => $titulo) {
            $pdf->MultiCell($anchos[$key], $altoEncabezado, $titulo, 1, 'C', true, 0, '', '', true, 0, false, true, $altoEncabezado, 'M');
        }
        $pdf->Ln();
        $yInicial += $altoEncabezado;

        $altoFilaMinimo = 8;
        $pdf->SetFont('helvetica', '', 7);

        foreach ($cuerpoDocumento as $prod) {
            $alturas = [];

            // CALCULAR ALTURA NECESARIA PARA CADA CELDA (especialmente descripción)
            foreach (array_keys($anchos) as $key) {
                $valor = $prod[$key];

                if ($key === 'numItem') {
                    $valor = number_format($valor, 0);        // Solo numItem va entero
                } else if ($key === 'uniMedida') {
                    $valor = number_format($valor, 0);        // Solo numItem va entero
                } elseif ($key === 'cantidad') {
                    $valor = number_format($valor, 2);        // Cantidad con 2 decimales
                } elseif (is_numeric($valor)) {
                    $valor = number_format($valor, 2);        // El resto con 2 decimales
                }

                $align = 'C';
                if ($key === 'descripcion') $align = 'L';
                if (in_array($key, ['precioUni', 'noGravado', 'montoDescu', 'ventaNoSuj', 'ventaExenta', 'ventaGravada'])) $align = 'R';

                // Obtener la altura real del contenido
                $altura = $pdf->getStringHeight($anchos[$key], $valor);
                $alturas[] = $altura;
            }

            $altoFila = max($alturas); // la fila debe tener la altura del contenido más alto
            $altoFila = max($altoFilaMinimo, $altoFila); // garantizar un mínimo

            // VERIFICAR SI CABE EN LA PÁGINA ACTUAL
            $espacioDisponible = $pdf->getPageHeight() - $pdf->getBreakMargin();
            if ($yInicial + $altoFila > $espacioDisponible) {
                $pdf->AddPage();
                $yInicial = 10;

                // Repetir encabezado
                $pdf->SetFont('helvetica', 'B', 7);
                $x = 10;
                $pdf->SetXY($x, $yInicial);
                foreach ($encabezados as $key => $titulo) {
                    $pdf->MultiCell($anchos[$key], $altoEncabezado, $titulo, 1, 'C', true, 0, '', '', true, 0, false, true, $altoEncabezado, 'M');
                }
                $pdf->Ln();
                $yInicial += $altoEncabezado;
                $pdf->SetFont('helvetica', '', 7);
            }

            // IMPRIMIR LA FILA
            $x = 10;
            $pdf->SetXY($x, $yInicial);
            foreach (array_keys($anchos) as $i => $key) {
                $valor = $prod[$key];

                // // Si es la unidad de medida, convertir el ID a nombre
                // if ($key === 'uniMedida') {
                //     $valor = $this->model->getNombreUnidadMedida($valor);
                // }

                // Formatear valores numéricos
                if ($key === 'numItem' && is_numeric($valor)) {
                    $valor = number_format($valor, 0);
                } elseif ($key === 'cantidad' && is_numeric($valor)) {
                    $valor = number_format($valor, 2);
                } elseif (is_numeric($valor)) {
                    $valor = number_format($valor, 2);
                }

                // Alineación
                $align = 'C';
                if ($key === 'descripcion') $align = 'L';
                if (in_array($key, ['precioUni', 'noGravado', 'montoDescu', 'ventaNoSuj', 'ventaExenta', 'ventaGravada'])) $align = 'R';

                $pdf->MultiCell($anchos[$key], $altoFila, $valor, 0, $align, false, 0, '', '', true, 0, true, true, $altoFila, 'M');
            }


            $pdf->Ln();
            $yInicial += $altoFila;
        }

        // Espacio después del cuerpo
        $yResumen = $yInicial + 5;

        // Verificar si cabe el resumen en esta página (56 de alto)
        if ($yResumen + 56 > $espacioDisponible) {
            $pdf->AddPage();
            $yResumen = 20;
        }

        // Dibujar el rectángulo del resumen
        $altoResumen = 65; // 10 líneas de altura 5 + margen
        $pdf->RoundedRect(125, $yResumen, 80, $altoResumen, 1, '1111');

        $yEtiqueta = $yResumen + 3;
        $altoFila = 5;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Suma Total de Operaciones:', number_format($resumen['subTotalVentas'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas no sujetas:', number_format($resumen['descuNoSuj'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas exentas:', number_format($resumen['descuExenta'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas gravadas:', number_format($resumen['descuGravada'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'IVA 13%', number_format($resumen['tributos'][0]['valor'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Sub-Total:', number_format($resumen['subTotal'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'IVA Percibido:', number_format($resumen['ivaPerci1'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'IVA Retenido:', number_format($resumen['ivaRete1'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Retención de Renta:', number_format($resumen['reteRenta'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto Total de la Operación:', number_format($resumen['montoTotalOperacion'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Total Otros Montos No Afectos de la Operación:', number_format($resumen['totalNoGravado'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Total a Pagar:', number_format($resumen['totalPagar'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        // Ahora el bloque de “Valor en Letras / Condición de la Operación” al lado izquierdo
        $yOperacion = $yResumen; // para que empiece alineado con el resumen

        // Verificar si cabe en la página
        if ($yOperacion + 10 > $espacioDisponible) {
            $pdf->AddPage();
            $yOperacion = 10;
        }

        // Dibujar rectángulo a la izquierda
        $pdf->RoundedRect(10, $yOperacion, 110, 20, 1, '1111');
        $yEtiqueta = $yResumen + 3;
        $altoFila = 5;

        $this->escribirEtiqueta($pdf, 12, $yEtiqueta, 'Valor en Letras:', $resumen['totalLetras'], 20, 70);
        $yEtiqueta += $altoFila;
        $nombreCondicion = $this->model->getNombreCondicionOperacion($resumen['condicionOperacion']);
        $this->escribirEtiqueta($pdf, 12, $yEtiqueta, 'Condición de la Operación:', $nombreCondicion, 20, 20);

        $pdf->Output('COMPROBANTE DE CREDITO FISCAL_N_' . $dte[0]['numeroControl'] . '.pdf', 'I');
    }

    public function vistaNotaDeCreditoPDF($datos)
    {
        require('Libraries/tcpdf/tcpdf.php');

        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterMargin(15);

        $ident = $datos['identificacion'];
        $numeroControl = $ident['numeroControl'];
        $dte = $this->model->getDtePorNumeroControl($numeroControl);
        $dte_encabezado = $this->model->getDTEdte_encabezado($numeroControl);
        $pdf->AddPage();
        $pdf->SetTitle('NOTA DE CREDITO_N_' . $dte[0]['numeroControl'] . '.pdf', 'I');

        // Accede a los datos del array
        $receptor = $datos['receptor'];
        $tercero = $datos['ventaTercero'];
        $docRelacionado = $datos['documentoRelacionado'];
        $cuerpoDocumento = $datos['cuerpoDocumento'];
        $resumen = $datos['resumen'];

        $codigoModelo = $ident['tipoModelo'];
        $codigoDepartamentoR = $receptor['direccion']['departamento'];
        $codigoMunicipioR = $receptor['direccion']['municipio'];
        $nombreDepartamentoReceptor = $this->model->getNombreDepartamento($codigoDepartamentoR);
        $nombreMunicipioReceptor = $this->model->getNombreMunicipio($codigoMunicipioR, $codigoDepartamentoR);
        $complementoR = $receptor['direccion']['complemento'];
        $nombreModelo = $this->model->getNombreModeloFacturacion($codigoModelo);
        $empresa = $this->model->getEmpresa();
        $codigoDepartamento = $empresa['departamento'];
        $codigoMunicipio = $empresa['municipio'];
        $complemento = $empresa['direccion'];
        $nombreDepartamento = $this->model->getNombreDepartamento($codigoDepartamento);
        $nombreMunicipio = $this->model->getNombreMunicipio($codigoMunicipio, $codigoDepartamento);

        // Dibujar borde redondeado
        $pdf->SetLineWidth(0.5);

        $pdf->Image(base_url . 'Assets/img/logo.jpg', 15, 0, 35); // Logo

        $this->centrarCelda($pdf, 180, 3, 'DOCUMENTO TRIBUTARIO ELECTRÓNICO', 8, 'B');
        $this->centrarCelda($pdf, 180, 9, 'NOTA DE CRÉDITO', 8, 'B');

        $this->escribirEtiqueta($pdf, 10, 20, 'Código de Generación:', $ident['codigoGeneracion'], 30);
        $this->escribirEtiqueta($pdf, 14, 23, 'Número de Control:', $ident['numeroControl'], 26);

        if (!empty($dte_encabezado)) {
            $this->escribirEtiqueta($pdf, 14, 26, 'Sello de Recepción:', $dte_encabezado, 26);
        } else {
            $this->escribirEtiqueta($pdf, 14, 26, 'Sello de Recepción:', '', 26);
        }

        // codigo QR
        $qrContenido = 'https://admin.factura.gob.sv/consultaPublica?ambiente=' . $ident['ambiente'] . '&codGen=' . $ident['codigoGeneracion'] . '&fechaEmi=' . $ident['fecEmi'] . '';
        $style = [
            'border' => 0,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false,
            'module_width' => 1,
            'module_height' => 1
        ];

        // Posición y tamaño similar al Image anterior
        $pdf->write2DBarcode($qrContenido, 'QRCODE,H', 100, 15, 25, 25, $style, 'N');

        $this->escribirEtiqueta($pdf, 135, 20, 'Modelo de Facturación:', $nombreModelo, 32);
        $modeloTransmision = $this->model->getTipoModeloTransmision();
        $codigoModelo = $ident['tipoModelo'];
        $nombreTransmision = isset($modeloTransmision['modeloTransmision'][$codigoModelo]['nombre'])
            ? $modeloTransmision['modeloTransmision'][$codigoModelo]['nombre']
            : 'Desconocido';
        $this->escribirEtiqueta($pdf, 138, 23, 'Tipo de Transmisión:', $nombreTransmision, 29);
        $this->escribirEtiqueta($pdf, 130, 26, 'Fecha y hora de generación:', $ident['fecEmi'] . ' ' . $ident['horEmi'], 37);

        // Rectángulo para EMISOR
        $this->escribirEtiqueta($pdf, 50, 40, 'EMISOR', '');
        $pdf->RoundedRect(10, 45, 90, 45, 3, '1111');
        $this->escribirEtiqueta($pdf, 12, 47, 'Nombre o razón social:', $empresa['nombre'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 51, 'NIT:', $empresa['nit'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 55, 'NRC:', $empresa['nrc'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 59, 'Actividad económica:', $empresa['descActividad'], 30, 50);
        $direccion = [
            $nombreDepartamento,
            $nombreMunicipio,
            $complemento
        ];

        $direccionFormateada = implode(', ', array_filter($direccion));
        $this->escribirEtiqueta($pdf, 12, 63, 'Direccion:', $direccionFormateada, 30, 50);
        $this->escribirEtiqueta($pdf, 12, 71, 'Número de teléfono:', $empresa['telefono'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 75, 'Correo electrónico:', $empresa['correo'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 79, 'Nombre Comercial:', $empresa['nombre'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 83, 'Tipo de establecimiento:', 'Casa matriz', 30, 50);

        // Rectángulo para RECEPTOR
        $this->escribirEtiqueta($pdf, 150, 40, 'RECEPTOR', '');
        $pdf->RoundedRect(111, 45, 90, 45, 3, '1111');
        if (!empty($receptor['nombre'])) {
            $this->escribirEtiqueta($pdf, 113, 47, 'Nombre o razón social:', $receptor['nombre'], 30, 50);
        }
        if (!empty($receptor['nit'])) {
            $this->escribirEtiqueta($pdf, 113, 51, 'NIT:', $receptor['nit'], 30, 50);
        }
        $this->escribirEtiqueta($pdf, 113, 55, 'NRC:', $receptor['nrc'], 30, 50);
        if (!empty($receptor['descActividad'])) {
            $this->escribirEtiqueta($pdf, 113, 59, 'Actividad económica:', $receptor['descActividad'], 30, 50);
        }
        $direccion = [
            $nombreDepartamentoReceptor,
            $nombreMunicipioReceptor,
            $complementoR,
        ];

        $direccionFormateada = implode(', ', array_filter($direccion));

        $this->escribirEtiqueta($pdf, 113, 67, 'Dirección:', $direccionFormateada, 30, 50);
        $this->escribirEtiqueta($pdf, 113, 75, 'Correo electrónico:', $receptor['correo'], 30, 50);
        if (!empty($receptor['nombreComercial'])) {
            $this->escribirEtiqueta($pdf, 113, 83, 'Nombre Comercial:', $receptor['nombreComercial'], 30, 50);
        }

        $yInicioAsociado = 90;
        $altoFila = 0;
        $docAsociado = $docAsociado ?? [];
        $cantidadDocsAsociados = count($docAsociado);
        $altoRectAsociados = $cantidadDocsAsociados * $altoFila + 0;
        $yRectAsociado = $yInicioAsociado + 0;

        // Rectángulo para CUENTA DE TERCEROS
        $yInicioTercero = $yRectAsociado + $altoRectAsociados + 5;
        if (!empty($tercero['nit'])) {

            $this->escribirEtiqueta($pdf, 89.5, $yInicioTercero, 'VENTA A CUENTA DE TERCEROS', '', 45);
            // $cantidadDocsTercero = count($tercero);
            $altoFila = 5;
            $altoRectTercero =  $altoFila + 2;
            $yRectTercero = $yInicioTercero + 5;
            $pdf->RoundedRect(10, $yRectTercero, 195, $altoRectTercero, 1, '1111');
            $yDatosTercero = $yRectTercero + 1;
            $pdf->SetFont('helvetica', '', 7);
            if (!empty($tercero)) {
                $this->escribirEtiqueta($pdf, 12, $yDatosTercero, 'NIT:', $tercero['nit'], 8);
                $this->escribirEtiqueta($pdf, 65, $yDatosTercero, 'Nombre, denominación o razón social:', $tercero['nombre'], 50);
            } else {
                $this->escribirEtiqueta($pdf, 12, $yDatosTercero, 'NIT:', '', 8);
                $this->escribirEtiqueta($pdf, 65, $yDatosTercero, 'Nombre, denominación o razón social:', '', 50);
            }
        } else {
            $altoFila = 0;
            $altoRectTercero =  $altoFila + 0;
            $yRectTercero = $yInicioTercero + 0;
        }


        // // === DOCUMENTOS RELACIONADOS ===
        // Título
        $yInicioDocRel = $yRectTercero + $altoRectTercero + 5;
        $docRelacionado = $docRelacionado ?? []; // asegura que sea un array
        if (!empty($docRelacionado)) {
            $this->escribirEtiqueta($pdf, 89.5, $yInicioDocRel, 'DOCUMENTOS RELACIONADOS', '', 45);

            // Altura dinámica
            $altoFila = 5;
            $cantidadDocsRelacionados = count($docRelacionado);

            $altoRectRelacionados = max(1, $cantidadDocsRelacionados) * $altoFila + 6; // mínimo una fila para encabezados
            $yRectDocRel = $yInicioDocRel + 5;
            $pdf->RoundedRect(10, $yRectDocRel, 195, $altoRectRelacionados, 1, '1111');

            // Encabezados
            $yEncabezado = $yRectDocRel + 1;
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->SetXY(12, $yEncabezado);
            $pdf->MultiCell(50, $altoFila, 'Tipo de Documento', 0, 'L', false, 0);
            $pdf->MultiCell(60, $altoFila, 'N° de documento', 0, 'L', false, 0);
            $pdf->MultiCell(60, $altoFila, 'Fecha del Documento', 0, 'L', false, 1);
            // Datos si hay
            $pdf->SetFont('helvetica', '', 7);
            $yDatos = $yEncabezado + $altoFila;
            foreach ($docRelacionado as $doc) {
                $pdf->SetXY(12, $yDatos);

                // Obtener el nombre del tipo de documento desde el código
                $tipoDocData = $this->model->getTipoDocumento($doc['tipoDocumento']);
                $tipoDocumento = $tipoDocData ? $tipoDocData['nombreTipoDocumento'] : 'Desconocido';

                $pdf->MultiCell(50, $altoFila, $tipoDocumento, 0, 'L', false, 0);
                $pdf->MultiCell(60, $altoFila, $doc['numeroDocumento'], 0, 'L', false, 0);
                $pdf->MultiCell(60, $altoFila, $doc['fechaEmision'], 0, 'L', false, 1);

                $yDatos += $altoFila;
            }
        } else {
            $altoFila = 0;
            $docRelacionado = $docRelacionado ?? [];
            $cantidadDocsRelacionados = count($docRelacionado);
            $altoRectRelacionados = max(0, $cantidadDocsRelacionados) * $altoFila + 0;
            $yRectDocRel = $yInicioDocRel + 0;
        }


        // CUERPO NOTA DE CREDITO
        // Definición de anchos
        $anchos = [
            'numItem'        => 13,
            'cantidad'       => 15,
            // 'uniMedida'      => 15,
            'descripcion'    => 80,
            'precioUni'      => 20,
            'montoDescu'     => 20,
            // 'noGravado'      => 20,
            'ventaNoSuj'     => 15,
            'ventaExenta'    => 15,
            'ventaGravada'   => 17
        ];

        // Encabezados legibles
        $encabezados = [
            'numItem'        => 'N°',
            'cantidad'       => 'Cantidad',
            // 'uniMedida'      => 'Unidad',
            'descripcion'    => 'Descripción',
            'precioUni'      => 'Precio Unitario',
            'montoDescu'     => 'Descuento por Item',
            // 'noGravado'      => 'Otros montos no afectos.',
            'ventaNoSuj'     => 'Ventas No Sujetas',
            'ventaExenta'    => 'Ventas Exentas',
            'ventaGravada'   => 'Ventas Gravadas'
        ];

        // Estilos de tabla
        $pdf->SetLineWidth(0.05); // Ultra delgado
        $pdf->SetFillColor(230, 230, 230); // Fondo gris claro para encabezado

        $altoEncabezado = 10;
        $yInicial = $yRectDocRel + $altoRectRelacionados + 5;
        $x = 10;

        // Encabezado
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetXY($x, $yInicial);
        foreach ($encabezados as $key => $titulo) {
            $pdf->MultiCell($anchos[$key], $altoEncabezado, $titulo, 1, 'C', true, 0, '', '', true, 0, false, true, $altoEncabezado, 'M');
        }
        $pdf->Ln();
        $yInicial += $altoEncabezado;

        $altoFilaMinimo = 8;
        $pdf->SetFont('helvetica', '', 7);

        foreach ($cuerpoDocumento as $prod) {
            $alturas = [];

            // CALCULAR ALTURA NECESARIA PARA CADA CELDA (especialmente descripción)
            foreach (array_keys($anchos) as $key) {
                $valor = $prod[$key];

               if ($key === 'numItem') {
                    $valor = number_format($valor, 0);        // Solo numItem va entero
                } elseif ($key === 'cantidad') {
                    $valor = number_format($valor, 2);        // Cantidad con 2 decimales
                } elseif (is_numeric($valor)) {
                    $valor = number_format($valor, 2);        // El resto con 2 decimales
                }
                $align = 'C';
                if ($key === 'descripcion') $align = 'L';
                if (in_array($key, ['precioUni', 'montoDescu', 'ventaNoSuj', 'ventaExenta', 'ventaGravada'])) $align = 'R';

                // Obtener la altura real del contenido
                $altura = $pdf->getStringHeight($anchos[$key], $valor);
                $alturas[] = $altura;
            }

            $altoFila = max($alturas); // la fila debe tener la altura del contenido más alto
            $altoFila = max($altoFilaMinimo, $altoFila); // garantizar un mínimo

            // VERIFICAR SI CABE EN LA PÁGINA ACTUAL
            $espacioDisponible = $pdf->getPageHeight() - $pdf->getBreakMargin();
            if ($yInicial + $altoFila > $espacioDisponible) {
                $pdf->AddPage();
                $yInicial = 10;

                // Repetir encabezado
                $pdf->SetFont('helvetica', 'B', 7);
                $x = 10;
                $pdf->SetXY($x, $yInicial);
                foreach ($encabezados as $key => $titulo) {
                    $pdf->MultiCell($anchos[$key], $altoEncabezado, $titulo, 1, 'C', true, 0, '', '', true, 0, false, true, $altoEncabezado, 'M');
                }
                $pdf->Ln();
                $yInicial += $altoEncabezado;
                $pdf->SetFont('helvetica', '', 7);
            }

            // IMPRIMIR LA FILA
            $x = 10;
            $pdf->SetXY($x, $yInicial);
            foreach (array_keys($anchos) as $i => $key) {
                $valor = $prod[$key];

                // // Si es la unidad de medida, convertir el ID a nombre
                // if ($key === 'uniMedida') {
                //     $valor = $this->model->getNombreUnidadMedida($valor);
                // }

                // Formatear valores numéricos
                if ($key === 'numItem' && is_numeric($valor)) {
                    $valor = number_format($valor, 0);
                } elseif ($key === 'cantidad' && is_numeric($valor)) {
                    $valor = number_format($valor, 2);
                } elseif (is_numeric($valor)) {
                    $valor = number_format($valor, 2);
                }

                // Alineación
                $align = 'C';
                if ($key === 'descripcion') $align = 'L';
                if (in_array($key, ['precioUni', 'noGravado', 'montoDescu', 'ventaNoSuj', 'ventaExenta', 'ventaGravada'])) $align = 'R';

                $pdf->MultiCell($anchos[$key], $altoFila, $valor, 0, $align, false, 0, '', '', true, 0, true, true, $altoFila, 'M');
            }


            $pdf->Ln();
            $yInicial += $altoFila;
        }

        // Espacio después del cuerpo
        $yResumen = $yInicial + 5;

        // Verificar si cabe el resumen en esta página (56 de alto)
        if ($yResumen + 56 > $espacioDisponible) {
            $pdf->AddPage();
            $yResumen = 20;
        }

        // Dibujar el rectángulo del resumen
        $altoResumen = 65; // 10 líneas de altura 5 + margen
        $pdf->RoundedRect(125, $yResumen, 80, $altoResumen, 1, '1111');

        $yEtiqueta = $yResumen + 3;
        $altoFila = 5;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Suma Total de Operaciones:', number_format($resumen['subTotalVentas'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas no sujetas:', number_format($resumen['descuNoSuj'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas exentas:', number_format($resumen['descuExenta'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas gravadas:', number_format($resumen['descuGravada'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'IVA 13%', number_format($resumen['tributos'][0]['valor'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Sub-Total:', number_format($resumen['subTotal'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'IVA Percibido:', number_format($resumen['ivaPerci1'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'IVA Retenido:', number_format($resumen['ivaRete1'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto Total de la Operación:', number_format($resumen['montoTotalOperacion'], 2), 50, 30);
        $yEtiqueta += $altoFila;


        // Ahora el bloque de “Valor en Letras / Condición de la Operación” al lado izquierdo
        $yOperacion = $yResumen; // para que empiece alineado con el resumen

        // Verificar si cabe en la página
        if ($yOperacion + 10 > $espacioDisponible) {
            $pdf->AddPage();
            $yOperacion = 10;
        }

        // Dibujar rectángulo a la izquierda
        $pdf->RoundedRect(10, $yOperacion, 110, 20, 1, '1111');
        $yEtiqueta = $yResumen + 3;
        $altoFila = 5;

        $this->escribirEtiqueta($pdf, 12, $yEtiqueta, 'Valor en Letras:', $resumen['totalLetras'], 20, 70);
        $yEtiqueta += $altoFila;
        $nombreCondicion = $this->model->getNombreCondicionOperacion($resumen['condicionOperacion']);
        $this->escribirEtiqueta($pdf, 12, $yEtiqueta, 'Condición de la Operación:', $nombreCondicion, 20, 20);

        $pdf->Output('NOTA DE CREDITO_N_' . $dte[0]['numeroControl'] . '.pdf', 'I');
    }

    private function centrarCelda($pdf, $anchoCelda, $y, $texto, $fontSize = 8, $estilo = '')
    {
        $pageWidth = 216;
        $x = ($pageWidth - $anchoCelda) / 2;

        $pdf->SetFont('helvetica', $estilo, $fontSize);
        $pdf->SetXY($x, $y);
        $pdf->Cell($anchoCelda, 8, $texto, 0, 1, 'C');
    }

    private function escribirEtiqueta($pdf, $x, $y, $label, $valor, $anchoLabel = 35, $anchoValor = 60, $alto = 5)
    {
        $pdf->SetXY($x, $y);
        $pdf->SetFont('helvetica', 'B', 6);
        $pdf->MultiCell($anchoLabel, $alto, $label, 0, 'L', false, 0); // label
        $pdf->SetFont('helvetica', '', 6);
        $pdf->MultiCell($anchoValor, $alto, $valor, 0, 'L', false, 1); // valor con salto de línea automático
    }


    /// controladores de contingencia
    public function modeloFacturacion()
    {
        $data = $this->model->searchModelofacturacion();
        $this->sendJsonResponse($data);
    }

    public function tipoTransmision()
    {
        $data = $this->model->searchTipoTransmision();
        $this->sendJsonResponse($data);
    }

    public function tipoContingencia()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->searchTipoContingencia($query);
        $this->sendJsonResponse($data);
    }

    public function registrarContingencia()
    {
        date_default_timezone_set('America/El_Salvador');

        $estadoContingencia = $this->model->obtenerEstadoContingencia();
        $eventoActivo = $estadoContingencia['estadoContingenciaId'] ?? null;
        $modelo = trim($_POST['modeloFacturacionCodigo']);
        $tipo = trim($_POST['tipoTransmisionCodigo']);
        $tipoContingencia = empty($_POST['tipoContingencia']) ? null : $_POST['tipoContingencia'];
        $motivoContingencia = empty($_POST['motivoContingencia']) ? null : $_POST['motivoContingencia'];
        $fechaInicio = date('Y-m-d H:i:s');
        $fechaFin = null;
        $estado = 1; // ACTIVO
        $msg = "Datos inválidos";

        if ($eventoActivo == 1) {
            $msg = "El evento se encuentra activo";
        } elseif (empty($modelo) || empty($tipo) || empty($tipoContingencia)) {
            $msg = "Debe seleccionar Tipo de contingencia";
        } elseif ($tipoContingencia == 5 && empty($motivoContingencia)) {
            $msg = "Escriba el motivo de la contingencia";
        } else {
            try {
                $this->model->iniciarTransaccion();

                $data = $this->model->registrarContingencia(
                    $modelo,
                    $tipo,
                    $tipoContingencia,
                    $motivoContingencia,
                    $fechaInicio,
                    $fechaFin,
                    $estado
                );

                if ($data === "ok") {
                    $this->model->confirmarTransaccion();
                    $msg = "si";
                } elseif ($data === "activa") {
                    $msg = "Ya está activo el evento de contingencia";
                } else {
                    $this->model->revertirTransaccion();
                    $msg = "Error al registrar la contingencia";
                }
            } catch (Exception $e) {
                $this->model->revertirTransaccion();
                $msg = "Error inesperado: " . $e->getMessage();
            }
        }

        $this->sendJsonResponse($msg);
    }

    public function editarContingenciaBtn()
    {
        $data = $this->model->editarContingencia();
        $this->sendJsonResponse($data);
    }

    public function modificarContingencia()
    {
        date_default_timezone_set('America/El_Salvador');

        $fechaFin = date('Y-m-d H:i:s');
        $estado = 0; // Desactiva la contingencia
        $msg = "Error al modificar contingencia";

        try {
            $this->model->iniciarTransaccion();

            $data = $this->model->modificarContingencia($fechaFin, $estado);

            if ($data === "modificado") {
                $this->model->confirmarTransaccion();
                $msg = "modificado";
            } else {
                $this->model->revertirTransaccion();
            }
        } catch (Exception $e) {
            $this->model->revertirTransaccion();
            $msg = "Error inesperado: " . $e->getMessage();
        }

        $this->sendJsonResponse($msg);
    }


    public function salir()
    {
        session_destroy();
        header("location: " . base_url);
    }
    
    public function enviarFacturaPorCorreo($emailCliente, $numeroControl, $tipoDte)
    {
        $listados = new Listados;

        // Generar los archivos y obtener las rutas relativas
        $pdfRelativo = '';
        $jsonRelativo = '';

        switch ($tipoDte) {
            case '01':
                $pdfRelativo = $listados->generarPdfFe($numeroControl, true);
                $jsonRelativo = $listados->generarPdfFeJSON($numeroControl, true);
                break;
            case '03':
                $pdfRelativo = $listados->generarPdfCcf($numeroControl, true);
                $jsonRelativo = $listados->generarPdfCcfJSON($numeroControl, true);
            case '05':
                $pdfRelativo = $listados->generarPdfNc($numeroControl, true);
                $jsonRelativo = $listados->generarPdfNcfJSON($numeroControl, true);
                break;
            default:
                # code...
                break;
        }

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

            error_log("Correo enviado correctamente para $numeroControl");
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $mail->ErrorInfo);
        }
    }
}