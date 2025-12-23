<?php
class Reportes extends Controller
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

    public function reporteProyectos()
    {
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $codigoCliente = isset($_POST['codigoCliente']) ? $_POST['codigoCliente'] : null;

        // Obtener los datos con los filtros
        $data = $this->model->getRangodeFecha($desde, $hasta, $codigoCliente);

        // Requiere TCPDF
        require('Libraries/tcpdf/tcpdf.php');


        // Inicia el buffer de salida
        ob_start();

        // Crear instancia de TCPDF
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

        // Desactivar encabezado y pie de página
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterMargin(15);

        // Agregar una página
        $pdf->AddPage();
        $pdf->SetTitle('Reporte de Proyectos: ' . $desde . ' a ' . $hasta);

        // Validar si es por cliente y mostrar el nombre del cliente
        if (!empty($codigoCliente)) {
            if (!empty($data) && isset($data[0]['nombreCliente'])) {
                $encabezado = 'REPORTE DE PROYECTOS';
                $totaEncabezados = 10;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [19, 19, 19, 19, 19, 19, 19, 19, 19, 19];
                $headers = ['CÓDIGO', 'NOMBRE', 'INICIO', 'FIN', 'VALOR COTIZADO', 'INGRESOS', 'SALIDAS', 'RENTABILIDAD', 'RESPONSABLE', 'ESTADO'];
                $tipo = 'Cliente: ';
                $nombreTipo = $data[0]['nombreCliente'];

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);


                foreach ($data as $row) {
                    $heights = []; // Almacena la altura de cada celda
                    $values = [$row['codigoProyecto'], $row['nombreProyecto'], $row['fechaInicio'], $row['fechaFin'], $row['valorCotizado'], $row['ingresos'], $row['salidas'], $row['valorRentabilidad'], $row['nombreResponsable'], $row['nombreEstadoProyecto']];

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
                        $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                    }

                    // Segunda pasada: escribir las celdas con la misma altura
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();

                    $paddingVertical = 3; // Ajusta según lo necesites
                    $adjustedHeight = $maxHeight + $paddingVertical;


                    foreach ($widths as $i => $width) {
                        $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                        $x += $width;
                        $pdf->SetXY($x, $y);
                    }

                    $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
                }
            } else {
                $encabezado = 'REPORTE DE PROYECTOS';
                $totaEncabezados = 10;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [19, 19, 19, 19, 19, 19, 19, 19, 19, 19];
                $headers = ['CÓDIGO', 'NOMBRE', 'INICIO', 'FIN', 'VALOR COTIZADO', 'INGRESOS', 'SALIDAS', 'RENTABILIDAD', 'RESPONSABLE', 'ESTADO'];
                $tipo = null;
                $nombreTipo = null;

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                $pdf->Cell(190, 8, 'No hay proyectos para este cliente', 0, 1, 'C');
            }
        } else {
            $encabezado = 'REPORTE DE PROYECTOS';
            $totaEncabezados = 11;
            // Definir los anchos de cada columna (total 190 mm)
            $widths = [17.27, 17.27, 17.27, 17.27, 17.27, 17.27, 17.27, 17.27, 17.27, 17.27, 17.27];
            $headers = ['CÓDIGO', 'NOMBRE', 'INICIO', 'FIN', 'CLIENTE', 'VALOR COTIZADO', 'SALIDAS', 'INGRESOS', 'RENTABILIDAD', 'RESPONSABLE', 'ESTADO'];
            $tipo = null;
            $nombreTipo = null;

            $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);

            foreach ($data as $row) {
                $heights = []; // Almacena la altura de cada celda

                // Primera pasada: calcular la altura más grande de la fila
                foreach ($widths as $i => $width) {
                    $heights[] = $pdf->getStringHeight($width, $row[array_keys($row)[$i]]);
                }

                // Determinar la altura máxima de la fila
                $maxHeight = max($heights);
                $paddingVertical = 3;
                $adjustedHeight = $maxHeight + $paddingVertical;

                // Verificar si hay suficiente espacio en la página antes de escribir la fila
                if ($pdf->GetY() + $adjustedHeight > $pdf->getPageHeight() - 20) {
                    $pdf->AddPage();
                    $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                }

                // Segunda pasada: escribir las celdas con la misma altura
                $x = $pdf->GetX();
                $y = $pdf->GetY();

                $paddingVertical = 3; // Ajusta según lo necesites
                $adjustedHeight = $maxHeight + $paddingVertical;


                foreach ($widths as $i => $width) {
                    $pdf->MultiCell($width, $adjustedHeight, $row[array_keys($row)[$i]], 0, 'L', false);
                    $x += $width;
                    $pdf->SetXY($x, $y); // Mantener la posición alineada
                }

                $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
            }
        }


        $pdf->Ln(5);
        $pdf->Cell(190, 8, 'TOTAL PROYECTOS: ' . count($data), 0, 1, 'L');

        // Finalizar y mostrar el PDF
        $pdf->Output();
    }

    public function reporteCompras()
    {
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $codigoProyecto = isset($_POST['codigoProyecto']) ? $_POST['codigoProyecto'] : null;

        // Obtener los datos con los filtros
        $data = $this->model->getRangodeFechaCompra($desde, $hasta, $codigoProyecto);

        // Requiere TCPDF
        require('Libraries/tcpdf/tcpdf.php');


        // Inicia el buffer de salida
        ob_start();

        // Crear instancia de TCPDF
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

        // Desactivar encabezado y pie de página
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->SetFooterMargin(15);


        // Agregar una página
        $pdf->AddPage();
        $pdf->SetTitle('Reporte de Compras: ' . $desde . ' a ' . $hasta);


        // Validar si es por proyecto y mostrar el nombre del proyecto
        if (!empty($codigoProyecto)) {
            $total = 0.00;  // Inicializamos la suma
            if (!empty($data) && isset($data[0]['nombreProyecto'])) {

                $encabezado = 'REPORTE DE COMPRAS';
                $totaEncabezados = 7;
                $widths = [27.14, 27.14, 27.14, 27.14, 27.14, 27.14, 27.14];
                $headers = ['DOCUMENTO', 'DOCUMENTO FE', 'MOVIMIENTO', 'PROVEEDOR', 'TOTAL', 'OBSERVACION', 'FECHA'];
                $tipo = 'Proyecto: ';
                $nombreTipo = $data[0]['nombreProyecto'];
                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);


                foreach ($data as $row) {
                    $total += (float) $row['total'];
                    $heights = []; // Almacena la altura de cada celda
                    $values = [$row['numeroDocumento'], $row['numeroDocumentoFe'], $row['nombreMovimiento'], $row['nombreProveedor'], $row['total'], $row['observacion'], $row['fecha']];


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
                        $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                    }

                    // Segunda pasada: escribir las celdas con la misma altura
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();

                    $paddingVertical = 3; // Ajusta según lo necesites
                    $adjustedHeight = $maxHeight + $paddingVertical;

                    foreach ($widths as $i => $width) {
                        $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                        $x += $width;
                        $pdf->SetXY($x, $y);
                    }

                    $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa


                }
            } else {
                $encabezado = 'REPORTE DE COMPRAS';
                $totaEncabezados = 7;
                $widths = [27.14, 27.14, 27.14, 27.14, 27.14, 27.14, 27.14];
                $headers = ['DOCUMENTO', 'DOCUMENTO FE', 'MOVIMIENTO', 'PROVEEDOR', 'TOTAL', 'OBSERVACION', 'FECHA'];
                $tipo = null;
                $nombreTipo = null;
                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                // Si no hay compras registradas, mostrar el mensaje
                $pdf->Cell(190, 8, 'No hay compras registradas para el proyecto', 0, 1, 'C');
            }
        } else {
            $encabezado = 'REPORTE DE COMPRAS';
            $totaEncabezados = 8;
            $widths = [23.75, 23.75, 23.75, 23.75, 23.75, 23.75, 23.75, 23.75];
            $headers = ['DOCUMENTO', 'DOCUMENTO FE', 'MOVIMIENTO', 'PROYECTO', 'PROVEEDOR', 'TOTAL', 'OBSERVACION', 'FECHA'];
            $tipo = null;
            $nombreTipo = null;
            $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);

            $total = 0.00;

            foreach ($data as $row) {

                $total += (float) $row['total'];
                $heights = []; // Almacena la altura de cada celda

                // Primera pasada: calcular la altura más grande de la fila
                foreach ($widths as $i => $width) {
                    $heights[] = $pdf->getStringHeight($width, $row[array_keys($row)[$i]]);
                }

                // Determinar la altura máxima de la fila
                $maxHeight = max($heights);
                $paddingVertical = 3;
                $adjustedHeight = $maxHeight + $paddingVertical;

                // Verificar si hay suficiente espacio en la página antes de escribir la fila
                if ($pdf->GetY() + $adjustedHeight > $pdf->getPageHeight() - 20) {
                    $pdf->AddPage();
                    $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                }

                // Segunda pasada: escribir las celdas con la misma altura
                $x = $pdf->GetX();
                $y = $pdf->GetY();

                $paddingVertical = 3; // Ajusta según lo necesites
                $adjustedHeight = $maxHeight + $paddingVertical;

                foreach ($widths as $i => $width) {
                    $pdf->MultiCell($width, $adjustedHeight, $row[array_keys($row)[$i]], 0, 'L', false);
                    $x += $width;
                    $pdf->SetXY($x, $y); // Mantener la posición alineada
                }

                $pdf->Ln($adjustedHeight);
            }
        }

        $pdf->Ln(5);

        $pdf->Cell(190, 8, 'COMPRAS: ' . count($data), 0, 1, 'L');
        $pdf->Cell(190, 8, "TOTAL: " . number_format($total, 2), 0, 1, 'L');
        // Finalizar y mostrar el PDF
        $pdf->Output();
    }

    public function reporteProductos()
    {
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $codigoProveedor = isset($_POST['codigoProveedor']) ? $_POST['codigoProveedor'] : null;
        $codigoProducto = isset($_POST['codigoProducto']) ? $_POST['codigoProducto'] : null;

        // Obtener los datos con los filtros
        $data = $this->model->getRangodeFechaProductos($desde, $hasta, $codigoProveedor, $codigoProducto);

        // Requiere TCPDF
        require('Libraries/tcpdf/tcpdf.php');


        // Inicia el buffer de salida
        ob_start();

        // Crear instancia de TCPDF
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

        // Desactivar encabezado y pie de página
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterMargin(15);

        // Agregar una página
        $pdf->AddPage();
        $pdf->SetTitle('Reporte de Productos: ' . $desde . ' a ' . $hasta);

        // Validar si es por proveedor
        if (!empty($codigoProveedor)) {
            if (!empty($data) && isset($data[0]['nombreProveedor'])) {
                $encabezado = 'REPORTE DE PRODUCTOS';
                $totaEncabezados = 3;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [63.33, 63.33, 63.33];
                $headers = ['NOMBRE', 'COSTO', 'PRECIO'];
                $tipo = 'Proveedor: ';
                $nombreTipo = $data[0]['nombreProveedor'];

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);

                foreach ($data as $row) {
                    $heights = []; // Almacena la altura de cada celda
                    $values = [$row['nombreProducto'], $row['costoProducto'], $row['precioVenta']];

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
                        $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                    }

                    // Segunda pasada: escribir las celdas con la misma altura
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();

                    $paddingVertical = 3; // Ajusta según lo necesites
                    $adjustedHeight = $maxHeight + $paddingVertical;

                    foreach ($widths as $i => $width) {
                        $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                        $x += $width;
                        $pdf->SetXY($x, $y);
                    }

                    $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
                }
            } else {
                $encabezado = 'REPORTE DE PRODUCTOS';
                $totaEncabezados = 3;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [63.33, 63.33, 63.33];
                $headers = ['NOMBRE', 'COSTO', 'PRECIO'];
                $tipo = null;
                $nombreTipo = null;
                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                // Si no hay compras registradas, mostrar el mensaje
                $pdf->Cell(190, 8, 'No hay productos registrados para este proveedor', 0, 1, 'C');
            }
        } else if (!empty($codigoProducto)) {
            if (!empty($data) && isset($data[0]['nombreProducto'])) {

                $nombreProveedor = $data[0]['nombreProducto'];
                $pdf->Cell(250, 8, '', 0, 1, 'C');

                $pdf->Ln(5);

                $widths = [63.33, 63.33, 63.33];

                // Encabezado de la tabla
                $pdf->SetFont('', '', 9);
                $headers = ['NOMBRE', 'COSTO', 'PRECIO'];

                // Imprimir encabezado con MultiCell
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                foreach ($headers as $i => $header) {
                    $pdf->MultiCell($widths[$i], 3, $header, 0, '', false);
                    $x += $widths[$i]; // Mover a la siguiente posición en X
                    $pdf->SetXY($x, $y); // Restaurar Y para mantener la misma línea
                }
                $pdf->Ln(); // Salto de línea después del encabezado

                // Dibujar una línea debajo del encabezado
                $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());

                // Datos de la tabla
                $pdf->SetFont('', '', 9);

                foreach ($data as $row) {
                    $heights = []; // Almacena la altura de cada celda
                    $values = [$row['nombreProducto'], $row['costoProducto'], $row['precioVenta']];

                    // Primera pasada: calcular la altura más grande de la fila
                    foreach ($values as $i => $value) {
                        $heights[] = $pdf->getStringHeight($widths[$i], $value);
                    }

                    // Determinar la altura máxima de la fila
                    $maxHeight = max($heights);

                    // Verificar si hay suficiente espacio en la página antes de escribir la fila
                    if ($pdf->GetY() + $maxHeight > 280) {
                        $pdf->AddPage();
                        $pdf->SetX(10);
                    }

                    // Segunda pasada: escribir las celdas con la misma altura
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();

                    $paddingVertical = 3; // Ajusta según lo necesites
                    $adjustedHeight = $maxHeight + $paddingVertical;

                    foreach ($widths as $i => $width) {
                        $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                        $x += $width;
                        $pdf->SetXY($x, $y);
                    }

                    $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
                }
            } else {
                // Si no hay compras registradas, mostrar el mensaje
                $pdf->Cell(190, 8, 'No hay productos registrados para este proveedor', 0, 1, 'C');
            }
        } else {

            $encabezado = 'REPORTE DE PRODUCTOS';
            $totaEncabezados = 4;
            // Definir los anchos de cada columna (total 190 mm)
            $widths = [47.50, 47.50, 47.50, 47.50];
            $headers = ['NOMBRE', 'COSTO', 'PRECIO', 'PROVEEDOR'];
            $tipo = null;
            $nombreTipo = null;

            $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);

            foreach ($data as $row) {
                $heights = []; // Almacena la altura de cada celda

                // Primera pasada: calcular la altura más grande de la fila
                foreach ($widths as $i => $width) {
                    $heights[] = $pdf->getStringHeight($width, $row[array_keys($row)[$i]]);
                }

                // Determinar la altura máxima de la fila
                $maxHeight = max($heights);
                $paddingVertical = 3;
                $adjustedHeight = $maxHeight + $paddingVertical;

                // Verificar si hay suficiente espacio en la página antes de escribir la fila
                if ($pdf->GetY() + $adjustedHeight > $pdf->getPageHeight() - 20) {
                    $pdf->AddPage();
                    $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                }

                // Segunda pasada: escribir las celdas con la misma altura
                $x = $pdf->GetX();
                $y = $pdf->GetY();

                $paddingVertical = 3; // Ajusta según lo necesites
                $adjustedHeight = $maxHeight + $paddingVertical;

                foreach ($widths as $i => $width) {
                    $pdf->MultiCell($width, $adjustedHeight, $row[array_keys($row)[$i]], 0, 'L', false);
                    $x += $width;
                    $pdf->SetXY($x, $y); // Mantener la posición alineada
                }

                $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
            }
        }

        $pdf->Ln(5);
        $pdf->Cell(190, 8, 'TOTAL PRODUCTOS: ' . count($data), 0, 1, 'L');

        // Finalizar y mostrar el PDF
        $pdf->Output();
    }

    public function reporteMovimientos()
    {
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $codigoCliente = isset($_POST['codigoCliente']) ? $_POST['codigoCliente'] : null;
        $codigoProveedor = isset($_POST['codigoProveedor']) ? $_POST['codigoProveedor'] : null;
        $tipoMovimiento = isset($_POST['tipoMovimiento']) ? $_POST['tipoMovimiento'] : null;
        $codigoEmpleado = isset($_POST['codigoEmpleado']) ? $_POST['codigoEmpleado'] : null;

        // Obtener los datos con los filtros
        $data = $this->model->getRangodeFechaMovimiento($desde, $hasta, $codigoCliente, $codigoProveedor, $tipoMovimiento, $codigoEmpleado);

        // Requiere TCPDF
        require('Libraries/tcpdf/tcpdf.php');


        // Inicia el buffer de salida
        ob_start();

        // Crear instancia de TCPDF
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

        // Desactivar encabezado y pie de página
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterMargin(15);

        // Agregar una página
        $pdf->AddPage();
        $pdf->SetTitle('Reporte Movimientos Financieros: ' . $desde . ' a ' . $hasta);

        // Validar si es por cliente
        if (!empty($codigoCliente)) {
            if (!empty($data) && isset($data[0]['cliente'])) {
                $encabezado = 'REPORTE DE MOVIMIENTOS FINANCIEROS';
                $totaEncabezados = 10;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [19, 19, 19, 19, 19, 19, 19, 19, 19, 19];
                $headers = ['MOVIMIENTO', 'TRANSACCION', 'DOCUMENTO', 'MONTO', 'PROYECTO', 'TIPO DOCUMENTO', 'PAGO', 'BANCO', 'CUENTA', 'FECHA'];
                $tipo = 'Cliente: ';
                $nombreTipo = $data[0]['cliente'];

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);

                foreach ($data as $row) {
                    $heights = []; // Almacena la altura de cada celda
                    $values = [$row['nombreMovimiento'], $row['numeroTransaccion'], $row['numeroDocumento'], $row['monto'], $row['nombreProyecto'], $row['nombreTipoDocumento'], $row['pago'], $row['banco'], $row['cuentaBancaria'], $row['fecha']];

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
                        $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                    }

                    // Segunda pasada: escribir las celdas con la misma altura
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();

                    $paddingVertical = 3; // Ajusta según lo necesites
                    $adjustedHeight = $maxHeight + $paddingVertical;

                    foreach ($widths as $i => $width) {
                        $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                        $x += $width;
                        $pdf->SetXY($x, $y);
                    }

                    $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
                }
            } else {
                $encabezado = 'REPORTE DE MOVIMIENTOS FINANCIEROS';
                $totaEncabezados = 10;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [19, 19, 19, 19, 19, 19, 19, 19, 19, 19];
                $headers = ['MOVIMIENTO', 'TRANSACCION', 'DOCUMENTO', 'MONTO', 'PROYECTO', 'TIPO DOCUMENTO', 'PAGO', 'BANCO', 'CUENTA', 'FECHA'];
                $tipo = null;
                $nombreTipo = null;

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                // Si no hay compras registradas, mostrar el mensaje
                $pdf->Cell(190, 8, 'No hay movimientos para este cliente', 0, 1, 'C');
            }
        } else if (!empty($codigoProveedor)) {
            if (!empty($data) && isset($data[0]['proveedor'])) {
                $encabezado = 'REPORTE DE MOVIMIENTOS FINANCIEROS';
                $totaEncabezados = 10;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [19, 19, 19, 19, 19, 19, 19, 19, 19, 19];
                $headers = ['MOVIMIENTO', 'TRANSACCION', 'DOCUMENTO', 'MONTO', 'PROYECTO', 'TIPO DOCUMENTO', 'PAGO', 'BANCO', 'CUENTA', 'FECHA'];
                $tipo = 'Proveedor: ';
                $nombreTipo = $data[0]['proveedor'];

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);

                foreach ($data as $row) {
                    $heights = []; // Almacena la altura de cada celda
                    $values = [$row['nombreMovimiento'], $row['numeroTransaccion'], $row['numeroDocumento'], $row['monto'], $row['nombreProyecto'], $row['nombreTipoDocumento'], $row['pago'], $row['banco'], $row['cuentaBancaria'], $row['fecha']];

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
                        $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                    }

                    // Segunda pasada: escribir las celdas con la misma altura
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();

                    $paddingVertical = 3; // Ajusta según lo necesites
                    $adjustedHeight = $maxHeight + $paddingVertical;

                    foreach ($widths as $i => $width) {
                        $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                        $x += $width;
                        $pdf->SetXY($x, $y);
                    }

                    $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
                }
            } else {
                $encabezado = 'REPORTE DE MOVIMIENTOS FINANCIEROS';
                $totaEncabezados = 10;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [19, 19, 19, 19, 19, 19, 19, 19, 19, 19];
                $headers = ['MOVIMIENTO', 'TRANSACCION', 'DOCUMENTO', 'MONTO', 'PROYECTO', 'TIPO DOCUMENTO', 'PAGO', 'BANCO', 'CUENTA', 'FECHA'];
                $tipo = null;
                $nombreTipo = null;

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                // Si no hay compras registradas, mostrar el mensaje
                $pdf->Cell(190, 8, 'No hay movimientos para este proveedor', 0, 1, 'C');
            }
        } else if (!empty($codigoEmpleado)) {
            if (!empty($data) && isset($data[0]['empleado'])) {
                $encabezado = 'REPORTE DE MOVIMIENTOS FINANCIEROS';
                $totaEncabezados = 11;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [17, 17, 17, 17, 17, 17, 17, 17, 17, 17, 17];
                $headers = ['MOVIMIENTO', 'TRANSACCION', 'TIPO', 'DOCUMENTO', 'MONTO', 'PROYECTO', 'TIPO DOCUMENTO', 'PAGO', 'BANCO', 'CUENTA', 'FECHA'];
                $tipo = 'Empleado: ';
                $nombreTipo = $data[0]['empleado'];

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);

                foreach ($data as $row) {
                    $heights = []; // Almacena la altura de cada celda
                    $values = [$row['nombreMovimiento'], $row['numeroTransaccion'], $row['tipo'], $row['numeroDocumento'], $row['monto'], $row['nombreProyecto'], $row['nombreTipoDocumento'], $row['pago'], $row['banco'], $row['cuentaBancaria'], $row['fecha']];

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
                        $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                    }

                    // Segunda pasada: escribir las celdas con la misma altura
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();

                    $paddingVertical = 3; // Ajusta según lo necesites
                    $adjustedHeight = $maxHeight + $paddingVertical;

                    foreach ($widths as $i => $width) {
                        $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                        $x += $width;
                        $pdf->SetXY($x, $y);
                    }

                    $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
                }
            } else {
                $encabezado = 'REPORTE DE MOVIMIENTOS FINANCIEROS';
                $totaEncabezados = 11;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [17, 17, 17, 17, 17, 17, 17, 17, 17, 17, 17];
                $headers = ['MOVIMIENTO', 'TRANSACCION', 'TIPO', 'DOCUMENTO', 'MONTO', 'PROYECTO', 'TIPO DOCUMENTO', 'PAGO', 'BANCO', 'CUENTA', 'FECHA'];
                $tipo = null;
                $nombreTipo = null;

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                // Si no hay compras registradas, mostrar el mensaje
                $pdf->Cell(190, 8, 'No hay movimientos para este empleado', 0, 1, 'C');
            }
        } else if (!empty($tipoMovimiento)) {
            if (!empty($data) && isset($data[0]['nombreMovimiento'])) {
                $encabezado = 'REPORTE DE MOVIMIENTOS FINANCIEROS';
                $totaEncabezados = 11;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [15.83, 15.83, 15.83, 15.83, 15.83, 15.83, 15.83, 15.83, 15.83, 15.83, 15.83, 15.83];
                $headers = ['TRANSACCION', 'DOCUMENTO', 'MONTO', 'PROVEEDOR', 'CLIENTE', 'EMPLEADO', 'PROYECTO', 'TIPO DOCUMENTO', 'PAGO', 'BANCO', 'CUENTA', 'FECHA'];
                $tipo = 'Movimiento: ';
                $nombreTipo = $data[0]['nombreMovimiento'];

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);

                foreach ($data as $row) {
                    $heights = []; // Almacena la altura de cada celda

                    $values = [$row['numeroTransaccion'], $row['numeroDocumento'], $row['monto'], $row['proveedor'], $row['cliente'], $row['empleado'], $row['nombreProyecto'], $row['nombreTipoDocumento'], $row['pago'], $row['banco'], $row['cuentaBancaria'], $row['fecha']];

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
                        $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                    }

                    // Segunda pasada: escribir las celdas con la misma altura
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();

                    $paddingVertical = 3; // Ajusta según lo necesites
                    $adjustedHeight = $maxHeight + $paddingVertical;

                    foreach ($widths as $i => $width) {
                        $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                        $x += $width;
                        $pdf->SetXY($x, $y);
                    }

                    $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
                }
            } else {
                $encabezado = 'REPORTE DE MOVIMIENTOS FINANCIEROS';
                $totaEncabezados = 11;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [15.83, 15.83, 15.83, 15.83, 15.83, 15.83, 15.83, 15.83, 15.83, 15.83, 15.83, 15.83];
                $headers = ['TRANSACCION', 'DOCUMENTO', 'MONTO', 'PROVEEDOR', 'CLIENTE', 'EMPLEADO', 'PROYECTO', 'TIPO DOCUMENTO', 'PAGO', 'BANCO', 'CUENTA', 'FECHA'];
                $tipo = null;
                $nombreTipo = null;

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                // Si no hay compras registradas, mostrar el mensaje
                $pdf->Cell(190, 8, 'De este tipo de movimiento no hay registros', 0, 1, 'C');
            }
        } else {
            $encabezado = 'REPORTE DE MOVIMIENTOS FINANCIEROS';
            $totaEncabezados = 13;
            // Definir los anchos de cada columna (total 190 mm)
            $widths = [14.62, 14.62, 14.62, 14.62, 14.62, 14.62, 14.62, 14.62, 14.62, 14.62, 14.62, 14.62, 14.62];
            $headers = ['MOVIMIENTO', 'TRANSACCION', 'DOCUMENTO', 'MONTO', 'PROVEEDOR', 'CLIENTE', 'EMPLEADO', 'PROYECTO', 'TIPO DOCUMENTO', 'PAGO', 'BANCO', 'CUENTA', 'FECHA'];
            $tipo = null;
            $nombreTipo = null;

            $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);

            foreach ($data as $row) {
                $heights = []; // Almacena la altura de cada celda
                $values = [$row['nombreMovimiento'], $row['numeroTransaccion'], $row['numeroDocumento'], $row['monto'], $row['proveedor'], $row['cliente'], $row['empleado'], $row['nombreProyecto'], $row['nombreTipoDocumento'], $row['pago'], $row['banco'], $row['cuentaBancaria'], $row['fecha']];

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
                    $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                }

                // Segunda pasada: escribir las celdas con la misma altura
                $x = $pdf->GetX();
                $y = $pdf->GetY();

                $paddingVertical = 3; // Ajusta según lo necesites
                $adjustedHeight = $maxHeight + $paddingVertical;

                foreach ($widths as $i => $width) {
                    $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                    $x += $width;
                    $pdf->SetXY($x, $y);
                }

                $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
            }
        }

        $pdf->Ln(5);
        $pdf->Cell(190, 8, 'TOTAL MOVIMIENTOS: ' . count($data), 0, 1, 'L');

        // Finalizar y mostrar el PDF
        $pdf->Output();
    }

    public function reporteProveedores()
    {
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];

        // Obtener los datos con los filtros
        $data = $this->model->getRangodeFechaProveedores($desde, $hasta);

        // Requiere TCPDF
        require('Libraries/tcpdf/tcpdf.php');


        // Inicia el buffer de salida
        ob_start();

        // Crear instancia de TCPDF
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

        // Desactivar encabezado y pie de página
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterMargin(15);

        // Agregar una página
        $pdf->AddPage();
        $pdf->SetTitle('Reporte de Proveedores: ' . $desde . ' a ' . $hasta);

        $encabezado = 'REPORTE DE PROVEEDORES';
        $totaEncabezados = 5;
        $widths = [38, 38, 38, 38, 38];
        $headers = ['PROVEEDOR', 'TELÉFONO', 'CONTACTO', 'CRÉDITO', 'SALDO'];
        $tipo = null;
        $nombreTipo = null;

        $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);

        $total = 0.00;
        foreach ($data as $row) {
            $total += (float) $row['saldoProveedor'];
            $heights = []; // Almacena la altura de cada celda

            // Primera pasada: calcular la altura más grande de la fila
            foreach ($widths as $i => $width) {
                $heights[] = $pdf->getStringHeight($width, $row[array_keys($row)[$i]]);
            }

            // Determinar la altura máxima de la fila
            $maxHeight = max($heights);
            $paddingVertical = 3;
            $adjustedHeight = $maxHeight + $paddingVertical;

            // Verificar si hay suficiente espacio en la página antes de escribir la fila
            if ($pdf->GetY() + $adjustedHeight > $pdf->getPageHeight() - 20) {
                $pdf->AddPage();
                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
            }

            // Segunda pasada: escribir las celdas con la misma altura
            $x = $pdf->GetX();
            $y = $pdf->GetY();

            $paddingVertical = 3; // Ajusta según lo necesites
            $adjustedHeight = $maxHeight + $paddingVertical;

            foreach ($widths as $i => $width) {
                $pdf->MultiCell($width, $adjustedHeight, $row[array_keys($row)[$i]], 0, 'L', false);
                $x += $width;
                $pdf->SetXY($x, $y); // Mantener la posición alineada
            }

            $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
        }


        $pdf->Ln(5);
        $pdf->Cell(190, 8, 'PROVEEDORES: ' . count($data), 0, 1, 'L');
        $pdf->Cell(190, 8, "SALDO TOTAL: " . number_format($total, 2), 0, 1, 'L');

        // Finalizar y mostrar el PDF
        $pdf->Output();
    }

    public function reporteClientes()
    {
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];

        // Obtener los datos con los filtros
        $data = $this->model->getRangodeFechaClientes($desde, $hasta);

        // Requiere TCPDF
        require('Libraries/tcpdf/tcpdf.php');


        // Inicia el buffer de salida
        ob_start();

        // Crear instancia de TCPDF
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

        // Desactivar encabezado y pie de página
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterMargin(15);

        // Agregar una página
        $pdf->AddPage();
        $pdf->SetTitle('Reporte de Clientes: ' . $desde . ' a ' . $hasta);

        $encabezado = 'REPORTE DE CLIENTES';
        $totaEncabezados = 7;
        $widths = [27.14, 27.14, 27.14, 27.14, 27.14, 27.14, 27.14];

        $headers = ['CLIENTE', 'NRC', 'ACTIVIDAD ECONOMICA','TELÉFONO', 'CONTACTO', 'CRÉDITO', 'SALDO'];
        $tipo = null;
        $nombreTipo = null;

        $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);

        $total = 0.00;
        foreach ($data as $row) {
            $total += (float) $row['saldoCliente'];
            $heights = []; // Almacena la altura de cada celda

            $values = [$row['nombreCliente'], $row['nrc'] ?? '', $row['actividad'] ?? '', $row['telefono'] ?? '', $row['contacto'] ?? '', $row['limite'], $row['saldoCliente']];

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
                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
            }

            // Segunda pasada: escribir las celdas con la misma altura
            $x = $pdf->GetX();
            $y = $pdf->GetY();

            $paddingVertical = 3; // Ajusta según lo necesites
            $adjustedHeight = $maxHeight + $paddingVertical;

            foreach ($widths as $i => $width) {
                $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                $x += $width;
                $pdf->SetXY($x, $y);
            }

            $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
        }


        $pdf->Ln(5);
        $pdf->Cell(190, 8, 'TOTAL CLIENTES: ' . count($data), 0, 1, 'L');
        $pdf->Cell(190, 8, "SALDO TOTAL: " . number_format($total, 2), 0, 1, 'L');

        // Finalizar y mostrar el PDF
        $pdf->Output();
    }

    public function reporteCotizaciones()
    {
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $codigoCliente = isset($_POST['codigo']) ? $_POST['codigo'] : null;

        // Obtener los datos con los filtros
        $data = $this->model->getRangodeFechaCotizaciones($desde, $hasta, $codigoCliente);

        // Requiere TCPDF
        require('Libraries/tcpdf/tcpdf.php');


        // Inicia el buffer de salida
        ob_start();

        // Crear instancia de TCPDF
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

        // Desactivar encabezado y pie de página
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterMargin(15);


        // Agregar una página
        $pdf->AddPage();
        $pdf->SetTitle('Reporte de Cotizaciones: ' . $desde . ' a ' . $hasta);

        if (!empty($codigoCliente)) {
            $total = 0.00;
            if (!empty($data) && isset($data[0]['nombreCliente'])) {
                $encabezado = 'REPORTE DE COTIZACIONES';
                $totaEncabezados = 4;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [47.5, 47.5, 47.5, 47.5];
                $headers = ['CODIGO', 'PROYECTO', 'VALOR COTIZADO',  'FECHA'];
                $tipo = 'Cliente: ';
                $nombreTipo = $data[0]['nombreCliente'];

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);

                foreach ($data as $row) {
                    $total += (float) $row['total'];
                    $heights = []; // Almacena la altura de cada celda
                    $values = [$row['idCotizacion'], $row['nombreProyecto'], $row['total'], $row['fecha']];

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
                        $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                    }

                    // Segunda pasada: escribir las celdas con la misma altura
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();

                    $paddingVertical = 3; // Ajusta según lo necesites
                    $adjustedHeight = $maxHeight + $paddingVertical;

                    foreach ($widths as $i => $width) {
                        $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                        $x += $width;
                        $pdf->SetXY($x, $y);
                    }

                    $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
                }
            } else {
                $encabezado = 'REPORTE DE COTIZACIONES';
                $totaEncabezados = 4;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [47.5, 47.5, 47.5, 47.5];
                $headers = ['CODIGO', 'PROYECTO', 'VALOR COTIZADO',  'FECHA'];
                $tipo = null;
                $nombreTipo = null;

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                $pdf->Cell(190, 8, 'No hay cotizaciones para este cliente', 0, 1, 'C');
            }
        } else {
            $encabezado = 'REPORTE DE COTIZACIONES GENERAL';
            $totaEncabezados = 5;
            // Definir los anchos de cada columna (total 190 mm)
            $widths = [38, 38, 38, 38, 38];
            $headers = ['CODIGO', 'CLIENTE', 'PROYECTO', 'VALOR COTIZADO', 'FECHA'];
            $tipo = null;
            $nombreTipo = null;

            $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);

            $total = 0.00;
            foreach ($data as $row) {
                $total += (float) $row['total'];
                $heights = []; // Almacena la altura de cada celda

                // Primera pasada: calcular la altura más grande de la fila
                foreach ($widths as $i => $width) {
                    $heights[] = $pdf->getStringHeight($width, $row[array_keys($row)[$i]]);
                }

                // Determinar la altura máxima de la fila
                $maxHeight = max($heights);
                $paddingVertical = 3;
                $adjustedHeight = $maxHeight + $paddingVertical;

                // Verificar si hay suficiente espacio en la página antes de escribir la fila
                if ($pdf->GetY() + $adjustedHeight > $pdf->getPageHeight() - 20) {
                    $pdf->AddPage();
                    $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                }

                // Segunda pasada: escribir las celdas con la misma altura
                $x = $pdf->GetX();
                $y = $pdf->GetY();

                $paddingVertical = 3; // Ajusta según lo necesites
                $adjustedHeight = $maxHeight + $paddingVertical;

                foreach ($widths as $i => $width) {
                    $pdf->MultiCell($width, $adjustedHeight, $row[array_keys($row)[$i]], 0, 'L', false);
                    $x += $width;
                    $pdf->SetXY($x, $y); // Mantener la posición alineada
                }

                $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
            }
        }

        $pdf->Ln(5);
        $pdf->Cell(190, 8, 'COTIZACIONES: ' . count($data), 0, 1, 'L');
        $pdf->Cell(190, 8, "TOTAL: " . number_format($total, 2), 0, 1, 'L');

        // Finalizar y mostrar el PDF
        $pdf->Output();
    }

    public function reporteExistencias()
    {
        $codigoProyecto = isset($_POST['proyecto']) ? $_POST['proyecto'] : null;

        $data = $this->model->getExistencias($codigoProyecto);

        // Requiere TCPDF
        require('Libraries/tcpdf/tcpdf.php');

        // Inicia el buffer de salida
        ob_start();

        // Crear instancia de TCPDF
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

        // Desactivar encabezado y pie de página
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterMargin(15);

        // Agregar una página
        $pdf->AddPage();
        $pdf->SetTitle('Reporte de Existencia proyecto: ' . ($codigoProyecto ? $codigoProyecto : 'Todos'));

        if (!empty($codigoProyecto)) {
            $encabezado = 'REPORTE DE EXISTENCIAS';
            $totaEncabezados = 5;
            $widths = [38, 38, 38, 38, 38];
            $headers = ['CÓDIGO PRODUCTO', 'NOMBRE', 'CANTIDAD', 'COSTO', 'TOTAL'];
            $nombreTipo = isset($data[0]['nombreProyecto']) ? $data[0]['nombreProyecto'] : 'Sin Proyecto';

            $this->encabezadoExistencia($pdf, $encabezado, $headers, $widths, $totaEncabezados, $nombreTipo);

            $total = 0;
            $totalFinal = 0;
            foreach ($data as $row) {
                // Asegurarse de que los valores no sean nulos
                $nombreProducto = isset($row['nombreProducto']) ? $row['nombreProducto'] : 'Sin nombre';
                $cantidadProducto = isset($row['cantidadProducto']) ? $row['cantidadProducto'] : 0;
                $costoProducto = isset($row['costoProducto']) ? $row['costoProducto'] : 0.0;

                $total = (float) $cantidadProducto * (float) $costoProducto;
                $totalFinal += $total;

                $heights = [];
                $values = [$row['codigoProducto'], $nombreProducto, $cantidadProducto, $costoProducto, number_format($total, 2)];

                foreach ($values as $i => $value) {
                    $heights[] = $pdf->getStringHeight($widths[$i], $value);
                }

                $maxHeight = max($heights);
                $paddingVertical = 3;
                $adjustedHeight = $maxHeight + $paddingVertical;

                if ($pdf->GetY() + $adjustedHeight > $pdf->getPageHeight() - 20) {
                    $pdf->AddPage();
                    $this->encabezadoExistencia($pdf, $encabezado, $headers, $widths, $totaEncabezados, $nombreTipo);
                }

                $x = $pdf->GetX();
                $y = $pdf->GetY();
                foreach ($widths as $i => $width) {
                    $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                    $x += $width;
                    $pdf->SetXY($x, $y);
                }

                $pdf->Ln($adjustedHeight);
            }
        } else {
            $encabezado = 'REPORTE DE EXISTENCIAS';
            $totaEncabezados = 7;
            $widths = [27, 27, 27, 27, 27, 27, 27];
            $headers = ['CODIGO PROYECTO', 'NOMBRE', 'CÓDIGO PRODUCTO', 'NOMBRE', 'CANTIDAD', 'COSTO', 'TOTAL'];
            $nombreTipo = 'Todos los proyectos';

            $this->encabezadoExistencia($pdf, $encabezado, $headers, $widths, $totaEncabezados, $nombreTipo);

            $total = 0;
            $totalFinal = 0;
            foreach ($data as $row) {
                // Asegurarse de que los valores no sean nulos
                $idProyecto = isset($row['codigoProyecto']) ? $row['codigoProyecto'] : 'Sin Proyecto';
                $nombreProyecto = isset($row['nombreProyecto']) ? $row['nombreProyecto'] : 'Sin Proyecto';
                $nombreProducto = isset($row['nombreProducto']) ? $row['nombreProducto'] : 'Sin nombre';
                $cantidadProducto = isset($row['cantidadProducto']) ? $row['cantidadProducto'] : 0;
                $costoProducto = isset($row['costoProducto']) ? $row['costoProducto'] : 0.0;

                $total = (float) $cantidadProducto * (float) $costoProducto;
                $totalFinal += $total;

                $heights = [];
                $values = [$idProyecto, $nombreProyecto, $row['codigoProducto'], $nombreProducto, $cantidadProducto, $costoProducto, number_format($total, 2)];

                foreach ($values as $i => $value) {
                    $heights[] = $pdf->getStringHeight($widths[$i], $value);
                }

                $maxHeight = max($heights);
                $paddingVertical = 3;
                $adjustedHeight = $maxHeight + $paddingVertical;

                if ($pdf->GetY() + $adjustedHeight > $pdf->getPageHeight() - 20) {
                    $pdf->AddPage();
                    $this->encabezadoExistencia($pdf, $encabezado, $headers, $widths, $totaEncabezados, $nombreTipo);
                }

                $x = $pdf->GetX();
                $y = $pdf->GetY();
                foreach ($widths as $i => $width) {
                    $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                    $x += $width;
                    $pdf->SetXY($x, $y);
                }

                $pdf->Ln($adjustedHeight);
            }
        }

        $pdf->Ln();
        $pdf->Line(10, $pdf->GetY(), 90, $pdf->GetY()); // Línea divisoria
        $pdf->Ln(2);
        $pdf->Cell(190, 8, 'PRODUCTOS: ' . count($data), 0, 1, 'L');
        $pdf->Cell(190, 8, "TOTAL: " . number_format($totalFinal, 2), 0, 1, 'L');

        // Finalizar y mostrar el PDF
        $pdf->Output();
    }

    public function encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo)
    {
        // Logo
        $pdf->Image(base_url . 'Assets/img/logo.jpg', 10, 4, 78, 25);

        // Título del reporte
        $pdf->SetFont('', 'B', 12);
        $pdf->Cell(250, 8, $encabezado, 0, 1, 'C');

        // Subtítulo con las fechas
        $pdf->SetFont('', '', 10);
        $pdf->Cell(250, 8, 'Desde: ' . $desde . ' Hasta: ' . $hasta, 0, 1, 'C');
        $pdf->Cell(250, 8, $tipo . $nombreTipo, 0, 1, 'C');
        $pdf->Ln(5);


        $pdf->SetFont('', '', 9);
        // Imprimir encabezado con MultiCell
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        foreach ($headers as $i => $header) {
            $pdf->MultiCell($widths[$i], $totaEncabezados, $header, 0, '', false);
            $x += $widths[$i];
            $pdf->SetXY($x, $y);
        }
        $pdf->Ln();
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); // Línea divisoria
        $pdf->Ln(2);
    }

    public function encabezadoExistencia($pdf, $encabezado, $headers, $widths, $totaEncabezados, $nombreTipo)
    {
        // Logo
        $pdf->Image(base_url . 'Assets/img/logo.jpg', 10, 4, 78, 25);

        // Título del reporte
        $pdf->SetFont('', 'B', 12);
        $pdf->Cell(250, 8, $encabezado, 0, 1, 'C');

        // Subtítulo con las fechas
        $pdf->SetFont('', '', 11);
        $pdf->SetX(90); // Asegurar la alineación centrada
        $pdf->MultiCell(90, 8, $nombreTipo, 0, 'C');
        $pdf->Ln(5);


        $pdf->SetFont('', '', 9);
        // Imprimir encabezado con MultiCell
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        foreach ($headers as $i => $header) {
            $pdf->MultiCell($widths[$i], $totaEncabezados, $header, 0, '', false);
            $x += $widths[$i];
            $pdf->SetXY($x, $y);
        }
        $pdf->Ln(9);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); // Línea divisoria
        $pdf->Ln(2);
    }

    public function salir()
    {
        session_destroy();
        header("location: " . base_url);
    }
    
    public function buscarDte()
    {
        $data = $this->model->seleccionarTipoDte();
        $this->sendJsonResponse($data);
    }

    public function reporteFacturas()
    {
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $tipoDocumento = isset($_POST['tipoDocumento']) ? $_POST['tipoDocumento'] : null;

        // Obtener los datos con los filtros
        $data = $this->model->getRangodeFechaFacturas($desde, $hasta, $tipoDocumento);

        // Requiere TCPDF
        require('Libraries/tcpdf/tcpdf.php');


        // Inicia el buffer de salida
        ob_start();

        // Crear instancia de TCPDF
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

        // Desactivar encabezado y pie de página
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterMargin(15);

        // Agregar una página
        $pdf->AddPage();
        $pdf->SetTitle('Reporte de Facturación: ' . $desde . ' a ' . $hasta);


        // Validar si es por cliente y mostrar el nombre del cliente
        if (!empty($tipoDocumento) && ($tipoDocumento == '01' || $tipoDocumento == '03')) {
            if (!empty($data) && isset($data[0]['nombreTipoDocumento'])) {
                $encabezado = 'REPORTE DE FACTURACIÓN';
                $totaEncabezados = 8;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [20, 36.66, 36.66, 37.02, 44.66, 15];
                $headers = ['FECHA', 'NUMERO CONTROL', 'CODIGO GENERACIÓN', 'CLIENTE', 'SELLO RECEPCIÓN', 'TOTAL'];
                $tipo = 'Dte: ';
                $nombreTipo = $data[0]['nombreTipoDocumento'];

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);


                foreach ($data as $row) {
                    $heights = []; // Almacena la altura de cada celda
                    $values = [
                        $row['fechaEmision'] ?? '',
                        $row['numeroControl'] ?? '',
                        $row['codigoGeneracion'] ?? '',
                        $row['nombreCliente'] ?? '',
                        $row['selloRecepcion'] ?? '',
                        $row['totalPagar'] ?? ''
                    ];


                    // Primera pasada: calcular la altura más grande de la fila
                    foreach ($values as $i => $value) {
                        $heights[] = $pdf->getStringHeight($widths[$i], $value);
                    }
                    // Determinar la altura máxima de la fila
                    $maxHeight = max($heights);
                    $paddingVertical = 1;
                    $adjustedHeight = $maxHeight + $paddingVertical;

                    // Verificar si hay suficiente espacio en la página antes de escribir la fila
                    if ($pdf->GetY() + $adjustedHeight > $pdf->getPageHeight() - 20) {
                        $pdf->AddPage();
                        $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                    }

                    // Segunda pasada: escribir las celdas con la misma altura
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();

                    $paddingVertical = 1; // Ajusta según lo necesites
                    $adjustedHeight = $maxHeight + $paddingVertical;


                    foreach ($widths as $i => $width) {
                        $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                        $x += $width;
                        $pdf->SetXY($x, $y);
                    }

                    $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
                }
            } else {
                $encabezado = 'REPORTE DE FACTURACIÓN';
                $totaEncabezados = 8;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [20, 36.66, 36.66, 37.02, 44.66, 15];
                $headers = ['FECHA', 'NUMERO CONTROL', 'CODIGO GENERACIÓN', 'CLIENTE', 'SELLO RECEPCIÓN', 'TOTAL'];
                $tipo = null;
                $nombreTipo = null;

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                $pdf->Cell(190, 8, 'No hay datos', 0, 1, 'C');
            }
        } else if (!empty($tipoDocumento) && ($tipoDocumento == '05')) {
            if (!empty($data) && isset($data[0]['nombreTipoDocumento'])) {
                $encabezado = 'REPORTE DE FACTURACIÓN';
                $totaEncabezados = 8;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [20, 36.66, 36.66, 37.02, 44.66, 15];
                $headers = ['FECHA', 'NUMERO CONTROL', 'CODIGO GENERACIÓN', 'CLIENTE', 'SELLO RECEPCIÓN', 'TOTAL'];
                $tipo = 'Dte: ';
                $nombreTipo = $data[0]['nombreTipoDocumento'];

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);


                foreach ($data as $row) {
                    $heights = []; // Almacena la altura de cada celda
                    $values = [
                        $row['fechaEmision'] ?? '',
                        $row['numeroControl'] ?? '',
                        $row['codigoGeneracion'] ?? '',
                        $row['nombreCliente'] ?? '',
                        $row['selloRecepcion'] ?? '',
                        $row['montoTotalOperacion'] ?? ''
                    ];


                    // Primera pasada: calcular la altura más grande de la fila
                    foreach ($values as $i => $value) {
                        $heights[] = $pdf->getStringHeight($widths[$i], $value);
                    }
                    // Determinar la altura máxima de la fila
                    $maxHeight = max($heights);
                    $paddingVertical = 1;
                    $adjustedHeight = $maxHeight + $paddingVertical;

                    // Verificar si hay suficiente espacio en la página antes de escribir la fila
                    if ($pdf->GetY() + $adjustedHeight > $pdf->getPageHeight() - 20) {
                        $pdf->AddPage();
                        $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                    }

                    // Segunda pasada: escribir las celdas con la misma altura
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();

                    $paddingVertical = 1; // Ajusta según lo necesites
                    $adjustedHeight = $maxHeight + $paddingVertical;


                    foreach ($widths as $i => $width) {
                        $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                        $x += $width;
                        $pdf->SetXY($x, $y);
                    }

                    $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
                }
            } else {
                $encabezado = 'REPORTE DE FACTURACIÓN';
                $totaEncabezados = 8;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [20, 36.66, 36.66, 37.02, 44.66, 15];
                $headers = ['FECHA', 'NUMERO CONTROL', 'CODIGO GENERACIÓN', 'CLIENTE', 'SELLO RECEPCIÓN', 'TOTAL'];
                $tipo = null;
                $nombreTipo = null;

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                $pdf->Cell(190, 8, 'No hay datos', 0, 1, 'C');
            }
        } else if (!empty($tipoDocumento) && ($tipoDocumento == '14')) {
            if (!empty($data) && isset($data[0]['nombreTipoDocumento'])) {
                $encabezado = 'REPORTE DE FACTURACIÓN';
                $totaEncabezados = 8;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [20, 36.66, 36.66, 37.02, 44.66, 15];
                $headers = ['FECHA', 'NUMERO CONTROL', 'CODIGO GENERACIÓN', 'CLIENTE', 'SELLO RECEPCIÓN', 'TOTAL'];
                $tipo = 'Dte: ';
                $nombreTipo = $data[0]['nombreTipoDocumento'];

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);


                foreach ($data as $row) {
                    $heights = []; // Almacena la altura de cada celda
                    $values = [
                        $row['fechaEmision'] ?? '',
                        $row['numeroControl'] ?? '',
                        $row['codigoGeneracion'] ?? '',
                        $row['nombreCliente'] ?? '',
                        $row['selloRecepcion'] ?? '',
                        $row['totalCompra'] ?? ''
                    ];


                    // Primera pasada: calcular la altura más grande de la fila
                    foreach ($values as $i => $value) {
                        $heights[] = $pdf->getStringHeight($widths[$i], $value);
                    }
                    // Determinar la altura máxima de la fila
                    $maxHeight = max($heights);
                    $paddingVertical = 1;
                    $adjustedHeight = $maxHeight + $paddingVertical;

                    // Verificar si hay suficiente espacio en la página antes de escribir la fila
                    if ($pdf->GetY() + $adjustedHeight > $pdf->getPageHeight() - 20) {
                        $pdf->AddPage();
                        $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                    }

                    // Segunda pasada: escribir las celdas con la misma altura
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();

                    $paddingVertical = 1; // Ajusta según lo necesites
                    $adjustedHeight = $maxHeight + $paddingVertical;


                    foreach ($widths as $i => $width) {
                        $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                        $x += $width;
                        $pdf->SetXY($x, $y);
                    }

                    $pdf->Ln($adjustedHeight); // Saltar la línea después de la fila completa
                }
            } else {
                $encabezado = 'REPORTE DE FACTURACIÓN';
                $totaEncabezados = 8;
                // Definir los anchos de cada columna (total 190 mm)
                $widths = [20, 36.66, 36.66, 37.02, 44.66, 15];
                $headers = ['FECHA', 'NUMERO CONTROL', 'CODIGO GENERACIÓN', 'CLIENTE', 'SELLO RECEPCIÓN', 'TOTAL'];
                $tipo = null;
                $nombreTipo = null;

                $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                $pdf->Cell(190, 8, 'No hay datos', 0, 1, 'C');
            }
        } else {
            $encabezado = 'REPORTE DE FACTURACIÓN GENERAL';
            $totaEncabezados = 4; // Tenemos 4 columnas reales
            $widths = [40, 80, 35, 35]; // 4 anchos, uno por columna
            $headers = ['CÓDIGO DTE', 'TIPO DOCUMENTO', 'TOTAL FACTURAS', 'TOTAL'];
            $tipo = null;
            $nombreTipo = null;

            $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);

            if (!empty($data)) {
                foreach ($data as $row) {
                    $values = [
                        $row['tipoDte'] ?? '',
                        $row['nombreTipoDocumento'] ?? '',
                        $row['totalFacturas'] ?? 0,
                        $row['total'] ?? 0,
                    ];

                    $heights = [];
                    foreach ($values as $i => $value) {
                        $heights[] = $pdf->getStringHeight($widths[$i], $value);
                    }

                    $maxHeight = max($heights);
                    $paddingVertical = 3;
                    $adjustedHeight = $maxHeight + $paddingVertical;

                    if ($pdf->GetY() + $adjustedHeight > $pdf->getPageHeight() - 20) {
                        $pdf->AddPage();
                        $this->encabezadoPdf($pdf, $encabezado, $desde, $hasta, $headers, $widths, $totaEncabezados, $tipo, $nombreTipo);
                    }

                    $x = $pdf->GetX();
                    $y = $pdf->GetY();
                    foreach ($widths as $i => $width) {
                        $pdf->MultiCell($width, $adjustedHeight, $values[$i], 0, 'L', false);
                        $x += $width;
                        $pdf->SetXY($x, $y);
                    }
                    $pdf->Ln($adjustedHeight);
                }
            } else {
                $pdf->Cell(190, 8, 'No hay datos', 0, 1, 'C');
            }
        }

        if (!empty($tipoDocumento)) {
            // Cuando filtraste por tipo, simplemente contar las filas
            $pdf->Ln(5);
            $pdf->Cell(190, 8, 'TOTAL FACTURAS: ' . count($data), 0, 1, 'L');
        } else {
            // Cuando no filtraste, $data tiene filas con 'totalFacturas' por cada tipo, sumamos esos totales
            $totalFacturasGlobal = 0;
            $totalFacturasTotal = 0;
            foreach ($data as $row) {
                $totalFacturasGlobal += (int) ($row['totalFacturas'] ?? 0);
                $totalFacturasTotal += (float) ($row['total'] ?? 0);
            }

            $pdf->Ln(5);
            $pdf->Cell(190, 8, 'TOTAL FACTURAS: ' . $totalFacturasGlobal, 0, 1, 'L');
            $pdf->Cell(190, 8, 'TOTAL $ : ' . $totalFacturasTotal, 0, 1, 'L');
        }

        // Finalizar y mostrar el PDF
        $pdf->Output();
    }
}
?>
<?php
// Habilitar el reporte de todos los errores
error_reporting(E_ALL);
ini_set('display_errors', '1');