<?php
class Cotizacion extends Controller
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

    public function listarDetalle()
    {
        $idUsuario = $_SESSION['codigoUsuario'];
        $codigoCliente = $_POST['codigoCliente'];
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


    public function seleccionar()
    {
        $idProducto = isset($_POST['codigoProducto']) ? $_POST['codigoProducto'] : null;
        $codigoCliente = isset($_POST['codigoClienteDetalle']) ? $_POST['codigoClienteDetalle'] : null;
        $cantidad = isset($_POST['cantidadProducto']) ? (int) $_POST['cantidadProducto'] : 0;
        $precioCosto = isset($_POST['precioCosto']) ? (float) str_replace(',', '', $_POST['precioCosto']) : 0;
        $precioVenta = isset($_POST['precioVenta']) ? (float) str_replace(',', '', $_POST['precioVenta']) : 0;
        $cliente = $this->model->getClienteNrc($codigoCliente);

        if (empty($idProducto) || $cantidad <= 0) {
            $msg = "Debe seleccionar un producto y una cantidad válida";
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

                if (!empty($codigoCliente)) {
                    // comprueba si ya ay producto resgitrado en el detalle lo actualiza nada mas
                    if (empty($comprobar)) {

                        if (!empty($cliente) && isset($cliente['nrc'])) {
                            // Calcular precio sin IVA (precio / 1.13)
                            $precioSinIva = round($precioVenta / 1.13,2);

                            // Calcular el total sin IVA (antes de redondear)
                            $total = $cantidad * $precioSinIva;

                            // Redondear solo el total final a 2 decimales
                            $total = round($total, 2);

                            // Registrar detalle con el precio sin IVA y total redondeado
                            $data = $this->model->registrarDetalle($idProducto, $cantidad, $precioCosto, $precioSinIva, $total, $idUsuario);
                        } else {
                            // Para cliente final, usar precio con IVA normal
                            $total = round($precioVenta * $cantidad);
                            $data = $this->model->registrarDetalle($idProducto, $cantidad, $precioCosto, $precioVenta, $total, $idUsuario);
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

                            $data = $this->model->actualizarDetalle($totalCantidad, $precioCosto, $precioSinIva, $totalActualizar, $idProducto, $idUsuario);
                        } else {
                            $totalCantidad = $comprobar['cantidad'] + $cantidad;
                            $totalActualizar = $totalCantidad * $precioVenta;
                            $data = $this->model->actualizarDetalle($totalCantidad, $precioCosto, $precioVenta, $totalActualizar, $idProducto, $idUsuario);
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

    public function registrarCotizacion()
    {
        $idUsuario = $_SESSION['codigoUsuario'];
        $codigoCotizacion = trim($_POST['codigoCotizacion']);
        $codigoCliente = $_POST['codigoCliente'] ?? null;
        $codigoProyecto = $_POST['codigoProyecto'] ?? null;
        $comprobar = $this->model->comprobar($idUsuario);
        $cliente = $this->model->getClienteNrc($codigoCliente);


        if (strpos($codigoCotizacion, ' ') !== false) {
            $msg = "El campo 'Código Cotización' no debe contener espacios.";
        } else if (empty($codigoCotizacion) || empty($codigoCliente) || empty($codigoProyecto)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else if (empty($comprobar['probar'])) {
            $msg = "No ha seleccionado productos";
        } else {
            try {
                $this->model->iniciarTransaccion();

                $detalle = $this->model->getDetalle($idUsuario);
                $subTotal = 0;
                $iva = 0;
                $total = 0;

                // $detalle = $this->model->getDetalle($idUsuario);

                foreach ($detalle as $row) {
                    $subTotal += $row['total']; // Sumar el total de cada producto
                }

                if (!empty($cliente) && isset($cliente['nrc'])) {
                    // Si el cliente tiene NRC, calcular IVA
                    $iva = round($subTotal * 0.13, 2);
                    $total = $subTotal + $iva;
                } else {
                    // Si el cliente NO tiene NRC, solo usar el subtotal
                    $total = $subTotal;
                }
                $data = $this->model->registrarCotz($codigoCotizacion, $codigoCliente, $codigoProyecto, $subTotal, $iva, $total);

                if ($data === "ok") {

                    $detalle = $this->model->getDetalle($idUsuario);
                    foreach ($detalle as $row) {
                        $idProducto = $row['codigoProducto'];
                        $cantidad = $row['cantidad'];
                        $costo = $row['costoProducto'];
                        $precio = $row['precioVenta'];
                        $totalP = $cantidad * $precio;

                        $resultDetalle = $this->model->registrarDetalleCotz($codigoCotizacion, $codigoProyecto, $idProducto, $cantidad, $costo, $precio, $totalP);
                        if ($resultDetalle != "ok") {
                            throw new Exception("Error al registrar el detalle");
                        }
                    }
                    $this->model->confirmarTransaccion();
                    $this->model->vaciarDetalle($idUsuario);
                    $msg = ["status" => "si", "codigoCotizacion" => $codigoCotizacion];
                } else if ($data === "existe") {
                    // Revertir la transacción si el código ya existe
                    $this->model->revertirTransaccion();
                    $msg = "El codigo de cotización ya existe";
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

        $this->sendJsonResponse($msg);
    }
    public function cotizacionPdf($codigoCotizacion)
    {
        $cotizaciones = $this->model->getCotizacion($codigoCotizacion);
        require('Libraries/tcpdf/tcpdf.php');

        ob_start();

        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterMargin(15);
        $pdf->AddPage();
        $pdf->SetTitle('Cotización ' . $cotizaciones[0]['nombreCliente']);

        if (!empty($codigoCotizacion)) {
            // Determinar el tipo de cotización según el NRC
            if (!empty($cotizaciones[0]['nrc'])) {
                $encabezado = 'COTIZACIÓN CONTRIBUYENTE FISCAL';
                $totaEncabezados = 4;
                $widths = [25, 105, 30, 30];
                $headers = ['Cantidad', 'Descripción', 'Precio', 'Total'];
                $fecha = $cotizaciones[0]['fecha'];
                $codigo = $cotizaciones[0]['idCotizacion'];
                $proyecto = $cotizaciones[0]['nombreProyecto'];
                $nrc = $cotizaciones[0]['nrc'];
                $actividadEconomica = $cotizaciones[0]['valor'];
                $this->encabezadoPdf($pdf, $encabezado, $fecha, $codigo, $proyecto, $nrc, $actividadEconomica, $headers, $widths, $totaEncabezados);

                foreach ($cotizaciones as $row) {
                    $heights = []; // Almacena la altura de cada celda
                    $values = [$row['cantidad'], $row['nombreProducto'], $row['precioVenta'], $row['total']];

                    // Primera pasada: calcular la altura más grande de la fila
                    foreach ($values as $i => $value) {
                        $heights[] = $pdf->getStringHeight($widths[$i], $value);
                    }
                    // Determinar la altura máxima de la fila
                    $maxHeight = max($heights);
                    $paddingVertical = 3;
                    $adjustedHeight = $maxHeight + $paddingVertical;

                    // Verificar si hay suficiente espacio en la página antes de escribir la fila
                    if ($pdf->GetY() + $adjustedHeight > $pdf->getPageHeight() - 20) {
                        $pdf->AddPage();
                        $this->encabezadoPdf($pdf, $encabezado, $tipoCotizacion, $fecha, $codigo, $proyecto, $nrc, $actividadEconomica, $headers, $widths, $totaEncabezados);
                    }

                    // Segunda pasada: escribir las celdas con la misma altura
                    // Ajusta el padding agregando espacio adicional arriba y abajo
                    $paddingVertical = 3; // Ajusta según lo necesites
                    $adjustedHeight = $maxHeight + $paddingVertical;

                    $x = $pdf->GetX();
                    $y = $pdf->GetY();

                    foreach ($widths as $i => $width) {
                        // Definir la alineación por índice
                        $alignment = match ($i) {
                            0 => 'C', // Cantidad - Centrado
                            1 => 'L', // Descripción - Izquierda
                            2, 3 => 'R', // Precio y Total - Derecha
                            default => 'L'
                        };

                        $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, $alignment, false);
                        $x += $width;
                        $pdf->SetXY($x, $y);
                    }

                    $pdf->Ln($adjustedHeight);
                }

                $espacioNecesario = 60;
                if ($pdf->GetY() + $espacioNecesario > $pdf->getPageHeight() - 20) {
                    $pdf->AddPage();
                    $this->encabezadoPdf($pdf, $encabezado, $tipoCotizacion, $fecha, $codigo, $proyecto, $nrc, $actividadEconomica, $headers, $widths, $totaEncabezados);
                }

                ///Definir posición del rectángulo
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $width = 190;
                $height = 20;

                // Dibujar el rectángulo
                $pdf->Rect($x, $y, $width, $height);

                // Total con borde superior
                //         //PRECIOS SIN IVA
                $pdf->SetFont('', 'B', 10);
                $pdf->SetXY($x + 110, $y + 2); // Ajusta para el texto Total
                $pdf->Cell(40, 7, 'Sub-Total: $', 0, 0, 'R');
                $pdf->Cell(40, 7, $cotizaciones[0]['subTotal'], 0, 1, 'R');

                $pdf->SetFont('', 'B', 10);
                $pdf->SetXY($x + 110, $y + 7); // Ajusta para el texto Total
                $pdf->Cell(40, 7, 'IVA (13%): $', 0, 0, 'R');
                $pdf->Cell(40, 7, $cotizaciones[0]['iva'], 0, 1, 'R');

                $pdf->SetFont('', 'B', 10);
                $pdf->SetXY($x + 110, $y + 12); // Ajusta para el texto Total
                $pdf->Cell(40, 7, 'Total: $', 0, 0, 'R');
                $pdf->Cell(40, 7, $cotizaciones[0]['totales'], 0, 1, 'R');

                // Mensajes adicionales
                $pdf->SetFont('', '', 10);
                $pdf->SetXY($x, $y + 2); // Ajusta para el texto de valores expresados
                $pdf->Cell(60, 6, '* Valores expresados en dólares de los Estados Unidos', 0, 1, 'L');
                $pdf->SetXY($x, $y + 8); // Ajusta para la segunda línea
                $pdf->Cell(60, 6, '* Vigencia de 30 días', 0, 1, 'L');

                $pdf->Ln(25);

                // Verificar espacio para "Aprobado"
                if ($pdf->GetY() + 20 > $pdf->getPageHeight() - 20) {
                    $pdf->AddPage();
                    $this->encabezadoPdf($pdf, $encabezado, $tipoCotizacion, $fecha, $codigo, $proyecto, $nrc, $actividadEconomica, $headers, $widths, $totaEncabezados);
                }

                //         // Texto "Aprobado:"
                $pdf->Cell(30, 7, 'Aprobado: ', 0, 1);
                $pdf->Cell(25, 7, '', 0, 0);
                $pdf->Cell(50, 0, '', 'T', 0, 'L');

                // Nombre debajo de la línea
                $pdf->Ln(2);
                $pdf->Cell(30, 7, '', 0, 0);
                $pdf->Cell(50, 0, $cotizaciones[0]['nombreCliente'], 0, 1, 'L');
            } else {
                $encabezado = 'COTIZACIÓN CONSUMIDOR FINAL';
                $totaEncabezados = 4;
                $widths = [25, 105, 30, 30];
                $headers = ['Cantidad', 'Descripción', 'Precio', 'Total'];
                $fecha = $cotizaciones[0]['fecha'];
                $codigo = $cotizaciones[0]['idCotizacion'];
                $proyecto = $cotizaciones[0]['nombreProyecto'];
                $nrc = null;
                $actividadEconomica = null;
                $this->encabezadoPdf($pdf, $encabezado, $fecha, $codigo, $proyecto, $nrc, $actividadEconomica, $headers, $widths, $totaEncabezados);

                $total = 0.00;
                foreach ($cotizaciones as $row) {
                    $total += (float) $row['total'];
                    $heights = []; // Almacena la altura de cada celda
                    $values = [$row['cantidad'], $row['nombreProducto'], $row['precioVenta'], $row['total']];

                    // Primera pasada: calcular la altura más grande de la fila
                    foreach ($values as $i => $value) {
                        $heights[] = $pdf->getStringHeight($widths[$i], $value);
                    }
                    // Determinar la altura máxima de la fila
                    $maxHeight = max($heights);
                    $paddingVertical = 3;
                    $adjustedHeight = $maxHeight + $paddingVertical;

                    // Verificar si hay suficiente espacio en la página antes de escribir la fila
                    if ($pdf->GetY() + $adjustedHeight > $pdf->getPageHeight() - 20) {
                        $pdf->AddPage();
                        $this->encabezadoPdf($pdf, $encabezado, $tipoCotizacion, $fecha, $codigo, $proyecto, $nrc, $actividadEconomica, $headers, $widths, $totaEncabezados);
                    }

                    // Segunda pasada: escribir las celdas con la misma altura
                    // Ajusta el padding agregando espacio adicional arriba y abajo
                    $paddingVertical = 3; // Ajusta según lo necesites
                    $adjustedHeight = $maxHeight + $paddingVertical;

                    $x = $pdf->GetX();
                    $y = $pdf->GetY();

                    foreach ($widths as $i => $width) {
                        // Definir la alineación por índice
                        $alignment = match ($i) {
                            0 => 'C', // Cantidad - Centrado
                            1 => 'L', // Descripción - Izquierda
                            2, 3 => 'R', // Precio y Total - Derecha
                            default => 'L'
                        };

                        $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, $alignment, false);
                        $x += $width;
                        $pdf->SetXY($x, $y);
                    }

                    $pdf->Ln($adjustedHeight);
                }


                $espacioNecesario = 60;
                if ($pdf->GetY() + $espacioNecesario > $pdf->getPageHeight() - 20) {
                    $pdf->AddPage();
                    $this->encabezadoPdf($pdf, $encabezado, $tipoCotizacion, $fecha, $codigo, $proyecto, $nrc, $actividadEconomica, $headers, $widths, $totaEncabezados);
                }

                // Definir posición del rectángulo
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $width = 190;
                $height = 20;

                // Dibujar el rectángulo
                $pdf->Rect($x, $y, $width, $height);

                // Total con borde superior
                $pdf->SetFont('', 'B', 10);
                $pdf->SetXY($x + 110, $y + 2); // Ajusta para el texto Total
                $pdf->Cell(40, 7, 'Total: $', 0, 0, 'R');
                $pdf->Cell(40, 7, number_format($total, 2), 0, 1, 'R');

                // Mensajes adicionales
                $pdf->SetFont('', '', 10);
                $pdf->SetXY($x, $y + 2); // Ajusta para el texto de valores expresados
                $pdf->Cell(60, 6, '* Valores expresados en dólares de los Estados Unidos', 0, 1, 'L');
                $pdf->SetXY($x, $y + 8); // Ajusta para la segunda línea
                $pdf->Cell(60, 6, '* Vigencia de 30 días', 0, 1, 'L');


                $pdf->Ln(25);

                // Verificar espacio para "Aprobado"
                if ($pdf->GetY() + 20 > $pdf->getPageHeight() - 20) {
                    $pdf->AddPage();
                    $this->encabezadoPdf($pdf, $encabezado, $tipoCotizacion, $fecha, $codigo, $proyecto, $nrc, $actividadEconomica, $headers, $widths, $totaEncabezados);
                }

                // Texto "Aprobado:"
                $pdf->Cell(30, 7, 'Aprobado: ', 0, 1);
                $pdf->Cell(25, 7, '', 0, 0);
                $pdf->Cell(50, 0, '', 'T', 0, 'L');

                // Nombre debajo de la línea
                $pdf->Ln(2);
                $pdf->Cell(30, 7, '', 0, 0);
                $pdf->Cell(50, 0, $cotizaciones[0]['nombreCliente'], 0, 1, 'L');
            }
        }


        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdf->Output();
    }

    public function encabezadoPdf($pdf, $encabezado, $fecha, $codigo, $proyecto, $nrc, $actividadEconomica, $headers, $widths, $totaEncabezados)
    {
        // Logo
        $pdf->Image(base_url . 'Assets/img/logo.jpg', 10, 4, 78, 25);
        $pdf->Ln(20);

        // Título del reporte
        $pdf->SetFont('', 'B', 14);
        $pdf->Cell(190, 8, $encabezado, 0, 1, 'C');
        $pdf->Ln(2);

        //Datos de la cotización
        $pdf->SetFont('', '', 11);
        $pdf->Cell(40, 6, '    Fecha:', 0, 0, 'L');
        $pdf->Cell(60, 6, $fecha, 0, 1, 'L');

        $pdf->Cell(40, 6, '  Código:', 0, 0, 'L');
        $pdf->Cell(60, 6, $codigo, 0, 1, 'L');


        $pdf->Cell(40, 6, 'Proyecto:', 0, 0, 'L');
        $pdf->Cell(60, 6, $proyecto, 0, 1, 'L');

        if (!is_null($nrc)) { // Solo mostrar si $nrc tiene un valor
            $pdf->Cell(40, 6, '      NRC:', 0, 0, 'L');
            $pdf->Cell(60, 6, $nrc, 0, 1, 'L');
        }

        if (!is_null($actividadEconomica)) { // Solo mostrar si $giro tiene un valor
            $pdf->Cell(40, 6, 'Actividad Economica:', 0, 0, 'L');
            $pdf->Cell(60, 6, $actividadEconomica, 0, 1, 'L');
        }

        $pdf->Ln(3);


        $pdf->SetFillColor(230, 230, 230); // Color gris claro
        $pdf->SetFont('', '', 10);

        // Imprimir encabezado con MultiCell
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        foreach ($headers as $i => $header) {
            $alignment = match ($i) {
                0 => 'C',
                1 => 'C',
                2, 3 => 'R',
                default => 'L'
            };
            $pdf->MultiCell($widths[$i], $totaEncabezados, $header, 0, $alignment, true); // Agregar el 'true' para llenar el fondo
            $x += $widths[$i];
            $pdf->SetXY($x, $y);
        }

        $pdf->SetFont('', '', 10);
        $pdf->Ln();
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); // Línea divisoria
        $pdf->Ln(2);
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

        $result = $this->model->historialCotizaciones($start, $length, $search);
        $data = $result['cotizacionesE'];
        $total = $result['total'];

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] =
                '<div class="text-center">
                        <button class="btn btn-editar" type="button" data-id="' . $data[$i]["codigo"] . '"><i class="fas fa-file-pdf"></i></button>
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

    public function vaciarDetalleCotizacion()
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

    public function salir()
    {
        session_destroy();
        header("location: " . base_url);
    }
}


