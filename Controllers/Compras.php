<?php
class Compras extends Controller
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

    public function buscarProyecto()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->searchProyecto($query);
        $this->sendJsonResponse($data);
    }

    public function listarDetalle()
    {
        $idUsario = $_SESSION['codigoUsuario'];
        $data['detalle'] = $this->model->getDetalle($idUsario);
        $data['totalPagar'] = $this->model->calcularCompra($idUsario);
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


    public function registrar()
    {
        $idUsario = $_SESSION['codigoUsuario'];
        $numeroDocumento = trim($_POST['numeroDocumento']);
        $documentoFe = trim($_POST['numeroDocumentoFe']);
        $fecha = trim($_POST['fechaCompra']);
        $codigoCliente = empty($_POST['codigoCliente']) ? NULL : $_POST['codigoCliente'];
        $codigoProveedor = isset($_POST['codigoProveedor']) ? $_POST['codigoProveedor'] : null;
        $codigoTipoMovimiento = isset($_POST['codigoTipoMovimiento']) ? $_POST['codigoTipoMovimiento'] : null;
        $total = $this->model->calcularCompra($idUsario);
        $codigoProyecto = isset($_POST['codigoProyecto']) ? $_POST['codigoProyecto'] : null;
        $observacion = !empty($_POST['observacion']) ? $_POST['observacion'] : 'Ninguna';

        if (strpos($numeroDocumento, ' ') !== false) {
            $msg = "El campo 'Número Documento' no debe contener espacios.";
        } else if (empty($numeroDocumento) || empty($documentoFe) || empty($fecha) || empty($codigoProveedor) || empty($codigoTipoMovimiento) || empty($codigoProyecto)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else if (empty($total['totalPagar'])) {
            $msg = "No ha registrado productos";
        } else {
            try {
                // Iniciar la transacción
                $this->model->iniciarTransaccion();

                // Registrar compra
                $data = $this->model->registrarCompra($numeroDocumento, $documentoFe, $fecha, $codigoProveedor, $codigoCliente, $codigoTipoMovimiento, $total['totalPagar'], $codigoProyecto, $observacion);

                if ($data === "ok") {

                    // ACA CODIGO DE DETALLE DE LA COMPRA
                    $detalle = $this->model->getDetalle($idUsario);
                    foreach ($detalle as $row) {
                        $idProducto = $row['codigoProducto'];
                        $cantidad = $row['cantidad'];
                        $costo = $row['costoProducto'];
                        $precio = $row['precioVenta'];
                        $totalP = $cantidad * $costo;

                        // Registrar detalle de compra
                        $resultDetalle = $this->model->registrarDetalleCompra($numeroDocumento, $documentoFe, $idProducto, $cantidad, $costo, $precio, $totalP, $codigoProyecto);
                        if ($resultDetalle != "ok") {
                            throw new Exception("Error al registrar el detalle para producto");
                        }

                        // Actualizar existencias
                        $cantidadActual = $this->model->getExistencia($idProducto, $codigoProyecto);
                        if ($cantidadActual) {
                            $existencia = $cantidadActual['cantidadProducto'] + $cantidad;
                        } else {
                            $existencia = $cantidad;
                        }

                        $resultExistencia = $this->model->actualizarExistencias($existencia, $codigoProyecto, $idProducto);
                        if ($resultExistencia != "ok") {
                            throw new Exception("Error al actualizar las existencias del producto");
                        }

                        // Actualizar saldo proveedor
                        $saldoActualProveedor = $this->model->getSaldoProveedor($codigoProveedor);
                        $nuevoSaldo = $saldoActualProveedor['saldoProveedor'] + $totalP;
                        $resultSaldo = $this->model->actualizarSaldoProveedor($nuevoSaldo, $codigoProveedor);
                        if ($resultSaldo != "ok") {
                            throw new Exception("Error al actualizar saldo del proveedor");
                        }

                        // Actualizar salida proyecto
                        $salidaActualProyecto = $this->model->getSalidaProyecto($codigoProyecto);
                        $nuevaSalida = $salidaActualProyecto['salidas'] + $totalP;
                        $resultSalida = $this->model->actualizarSalidaProyecto($nuevaSalida, $codigoProyecto);
                        if ($resultSalida != "ok") {
                            throw new Exception("Error al actualizar salida para el proyecto");
                        }

                        $resultSaldo = $this->model->actualizarPrecioProducto($costo, $precio, $idProducto);
                        if ($resultSaldo !== "ok") {
                            throw new Exception("Error al actualizar costo y venta del producto");
                        }
                    }

                    // Confirmar la transacción de la compra
                    $this->model->confirmarTransaccion();

                    // Llamar a vaciarDetalle después de confirmar la transacción
                    $this->model->vaciarDetalle($idUsario);
                    $msg = "si";
                } else if ($data === "existe") {
                    // Revertir la transacción si el código ya existe
                    $this->model->revertirTransaccion();
                    $msg = "El numero de documento ya existe";
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

    public function generarPdf($numeroDocumento)
    {
        $productos = $this->model->getProCompra($numeroDocumento);
        //$empresa = $this->model->getEmpresa();
        require('Libraries/tcpdf/tcpdf.php'); // Asegúrate de usar la ruta correcta para TCPDF


        ob_start(); // Inicia el buffer de salida
        // Crear instancia de TCPDF
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

        // Desactivar encabezado y pie de página
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterMargin(15);

        $pdf->AddPage();
        $pdf->SetTitle('Datos Movimiento Compra');

        $encabezado = 'DATOS DE LA COMPRA';
        $totaEncabezados = 4;
        $widths = [105, 25, 30, 30];
        $headers = ['Nombre', 'Cantidad', 'Costo', 'Total'];
        $fecha = $productos[0]['fecha'];
        $documentoFe = $productos[0]['numeroDocumentoFe'];
        $movimiento = $productos[0]['nombreMovimiento'];
        $proveedor = $productos[0]['nombreProveedor'];
        $codigoProyecto = $productos[0]['codigoProyecto'];
        $nombreProyecto = $productos[0]['nombreProyecto'];
        $observacion = $productos[0]['observacion'];

        $this->encabezadoPdf($pdf, $encabezado, $fecha, $numeroDocumento, $documentoFe, $movimiento, $proveedor, $codigoProyecto, $nombreProyecto, $observacion, $headers, $widths, $totaEncabezados);

        $total = 0.00;
        foreach ($productos as $row) {
            $total += (float) $row['total'];
            $heights = []; // Almacena la altura de cada celda
            $values = [$row['nombreProducto'], $row['cantidad'], $row['costoProducto'], $row['total']];

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
                $this->encabezadoPdf($pdf, $encabezado, $fecha, $numeroDocumento, $documentoFe, $movimiento, $proveedor, $codigoProyecto, $nombreProyecto, $observacion, $headers, $widths, $totaEncabezados);
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
                    0 => 'L',
                    1 => 'C',
                    2, 3 => 'R',
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
            $this->encabezadoPdf($pdf, $encabezado, $fecha, $numeroDocumento, $documentoFe, $movimiento, $proveedor, $codigoProyecto, $nombreProyecto, $observacion, $headers, $widths, $totaEncabezados);
        }

        // Definir posición del rectángulo
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $width = 190;
        $height = 10;

        // Dibujar el rectángulo
        $pdf->Rect($x, $y, $width, $height);

        $pdf->SetFont('', 'B', 10);
        $pdf->SetXY($x + 110, $y + 2); // Ajusta para el texto Total
        $pdf->Cell(40, 7, 'Total: $', 0, 0, 'R');
        $pdf->Cell(40, 7, number_format($total, 2), 0, 1, 'R');

        if (ob_get_length()) {
            ob_end_clean(); // Limpia solo si hay contenido en el buffer
        }

        $pdf->Output();
    }

    public function encabezadoPdf($pdf, $encabezado, $fecha, $numeroDocumento, $documentoFe, $movimiento, $proveedor, $codigoProyecto, $nombreProyecto, $observacion, $headers, $widths, $totaEncabezados)
    {
        // Logo
        $pdf->Image(base_url . 'Assets/img/logo.jpg', 10, 4, 78, 25);
        $pdf->Ln(20);

        // Título del reporte
        $pdf->SetFont('', 'B', 14);
        $pdf->Cell(190, 8, $encabezado, 0, 1, 'C');
        $pdf->Ln(7);

        //Datos de la cotización
        $pdf->SetFont('', '', 11);
        $pdf->Cell(50, 6, 'Fecha:', 0, 0, 'L');
        $pdf->Cell(60, 6, $fecha, 0, 1, 'L');


        $pdf->Cell(50, 6, 'Número Documento:', 0, 0, 'L');
        $pdf->Cell(60, 6, $numeroDocumento, 0, 1, 'L');

        $pdf->Cell(50, 6, 'Número Documento Fe:', 0, 0, 'L');
        $pdf->Cell(60, 6, $documentoFe, 0, 1, 'L');


        $pdf->Cell(50, 6, 'Movimiento:', 0, 0, 'L');
        $pdf->Cell(60, 6, $movimiento, 0, 1, 'L');

        $pdf->Cell(50, 6, 'Proveedor:', 0, 0, 'L');
        $pdf->Cell(60, 6, $proveedor, 0, 1, 'L');

        $pdf->Cell(50, 6, 'Proyecto:', 0, 0, 'L');
        $pdf->Cell(30, 6, $codigoProyecto, 0, 0, 'L');
        // Crear un MultiCell para el nombre largo
        $pdf->SetX($pdf->GetX()); // Asegura la posición correcta
        $pdf->MultiCell(114, 6, $nombreProyecto, 0, 'L');

        $pdf->Cell(50, 6, 'Observación:', 0, 0, 'L');
        $pdf->Cell(60, 6, $observacion, 0, 1, 'L');

        $pdf->Ln(6);


        $pdf->SetFillColor(230, 230, 230); // Color gris claro
        $pdf->SetFont('', '', 10);

        // Imprimir encabezado con MultiCell
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        foreach ($headers as $i => $header) {
            $alignment = match ($i) {
                0 => 'L',
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

        $result = $this->model->historialCompras($start, $length, $search);
        $data = $result['encabezadoMov'];
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

    public function limiteProveedor(string $codigoProveedor)
    {
        $data = $this->model->getLimiteProveedor($codigoProveedor);
        $this->sendJsonResponse($data);
    }

    public function salir()
    {
        session_destroy();
        header("location: " . base_url);
    }

    public function vaciarDetalleCompra()
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
}
?>
<?php
// Habilitar el reporte de todos los errores
error_reporting(E_ALL);
ini_set('display_errors', '1');
