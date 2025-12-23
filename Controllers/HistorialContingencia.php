<?php
class HistorialContingencia extends Controller
{

    private $facturacionModel;

    public function __construct()
    {
        session_start();
        parent::__construct();
        $this->facturacionModel = new FacturacionModel();
    }

    public function index()
    {
        if (empty($_SESSION['codigoUsuario'])) {
            header("Location: " . base_url);
        }
        $this->views->getView($this, "index");
    }

    // public function listar()
    // {
    //     $start = $_POST['start']; // Índice de inicio
    //     $length = $_POST['length']; // Número de registros por página
    //     $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : ""; // Búsqueda por defecto
    //     $customSearch = isset($_POST['searchValue']) ? $_POST['searchValue'] : ""; // Búsqueda personalizada

    //     // Si hay búsqueda personalizada, la aplicamos
    //     if (!empty($customSearch)) {
    //         $search = $customSearch;
    //     }

    //     $result = $this->model->listarFe($start, $length, $search);

    //     $data = $result['encabezadoFe'];
    //     $total = $result['total'];

    //     for ($i = 0; $i < count($data); $i++) {
    //         $data[$i]['acciones'] =
    //             '<div class="text-center">
    //                     <button class="btn btn-editar" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-file-pdf"></i></button>
    //                     <button class="btn btn-json" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-code"></i></button>
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

        $result = $this->model->listarFe($start, $length, $search);

        $data = $result['encabezadoDte'];
        $total = $result['total'];

        for ($i = 0; $i < count($data); $i++) {
            $incluido = $data[$i]['incluido'] === 'INCLUIDO'
                ? '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Incluido</span>'
                : '<span class="badge badge-secondary"><i class="far fa-circle"></i> No incluido</span>';

            $data[$i]['incluido'] = $incluido;

            $data[$i]['acciones'] =
                '<div class="text-center">
            <button class="btn btn-editar" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-file-pdf"></i></button>
            <button class="btn btn-json" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-code"></i></button>
        </div>';
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

    public function generarPdfFe($numeroControl)
    {
        require('Libraries/tcpdf/tcpdf.php');

        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterMargin(15);

        $datos = $this->model->datosFe($numeroControl);
        $empresa = $this->model->getEmpresa();
        $codigoDepartamento = $empresa['departamento'];
        $codigoMunicipio = $empresa['municipio'];
        $complemento = $empresa['direccion'];
        $nombreDepartamento = $this->model->getNombreDepartamento($codigoDepartamento);
        $nombreMunicipio = $this->model->getNombreMunicipio($codigoMunicipio, $codigoDepartamento);

        $docRelacionado = $this->model->getDocRelaionados($numeroControl);
        $docAsociado = $this->model->getDocRelaionadosAsociados($numeroControl);
        $cuerpoDocumento = $this->model->getDTECuerpo($numeroControl);


        $pdf->AddPage();
        $pdf->SetTitle('FACTURA_N_' . $datos['numeroControl'] . '.pdf', 'I');

        //borde redondeado
        $pdf->SetLineWidth(0.5);

        $pdf->Image(base_url . 'Assets/img/logo.jpg', 15, 0, 35); // Logo

        $this->centrarCelda($pdf, 180, 3, 'DOCUMENTO TRIBUTARIO ELECTRÓNICO', 8, 'B');
        $this->centrarCelda($pdf, 180, 9, 'FACTURA', 8, 'B');

        $this->escribirEtiqueta($pdf, 10, 20, 'Código de Generación:', $datos['codigoGeneracion'], 30);
        $this->escribirEtiqueta($pdf, 14, 23, 'Número de Control:', $datos['numeroControl'], 26);

        if (!empty($datos['selloRecibido'])) {

            $this->escribirEtiqueta($pdf, 14, 26, 'Sello de Recepción:', $datos, 26);
        } else {
            $this->escribirEtiqueta($pdf, 14, 26, 'Sello de Recepción:', '', 26);
        }


        if (!$datos['estado'] == 'PROCESADO') {
            $qrContenido = 'https://admin.factura.gob.sv/consultaPublica?ambiente=' . $datos['ambiente'] . '&codGen=' . $datos['codigoGeneracion'] . '&fechaEmi=' . $datos['fechaEmision'] . '';
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
        }


        $this->escribirEtiqueta($pdf, 135, 20, 'Modelo de Facturación:', $datos['modelo'], 32);
        $modeloTransmision = $this->model->getTipoModeloTransmision();
        $codigoModelo = $datos['tipoModelo'];
        $nombreTransmision = isset($modeloTransmision['modeloTransmision'][$codigoModelo]['nombre'])
            ? $modeloTransmision['modeloTransmision'][$codigoModelo]['nombre']
            : 'Desconocido';
        $this->escribirEtiqueta($pdf, 138, 23, 'Tipo de Transmisión:', $nombreTransmision, 29);
        $this->escribirEtiqueta($pdf, 130, 26, 'Fecha y hora de generación:', $datos['fechaEmision'] . ' ' . $datos['horaEmision'], 37);

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
        $this->escribirEtiqueta($pdf, 12, 71, 'Número de teléfono:', $empresa['telefono'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 75, 'Correo electrónico:', $empresa['correo'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 79, 'Nombre Comercial:', $empresa['nombre'], 30, 50);
        $this->escribirEtiqueta($pdf, 12, 83, 'Tipo de establecimiento:', 'Casa matriz', 30, 50);

        // Rectángulo para RECEPTOR
        $this->escribirEtiqueta($pdf, 150, 40, 'RECEPTOR', '');
        $pdf->RoundedRect(111, 45, 90, 45, 3, '1111');

        $this->escribirEtiqueta($pdf, 113, 51, 'Nombre o razón social:', $datos['cliente'], 30, 50);
        if ($datos['identificacion'] == '02') {
            $this->escribirEtiqueta($pdf, 113, 55, 'Carnet de Residente:', $datos['numDocumento'], 30, 50);
        } else if ($datos['identificacion'] == '03') {
            $this->escribirEtiqueta($pdf, 113, 55, 'Pasaporte:', $datos['numDocumento'], 30, 50);
        } else if ($datos['identificacion'] == '13') {
            $this->escribirEtiqueta($pdf, 113, 55, 'DUI:', $datos['numDocumento'], 30, 50);
        } else if ($datos['identificacion'] == '36') {
            $this->escribirEtiqueta($pdf, 113, 55, 'NIT:', $datos['numDocumento'], 30, 50);
        } else if ($datos['identificacion'] == '37') {
            $this->escribirEtiqueta($pdf, 113, 55, 'Identificación:', $datos['numDocumento'], 30, 50);
        }

        if (!empty($datos['telefono'])) {
            $this->escribirEtiqueta($pdf, 113, 59, 'Telefono:', $datos['telefono'], 30, 50);
        }

        // Rectángulo para CUENTA DE TERCEROS
        if (!empty($datos['nitTercero'])) {
            $this->escribirEtiqueta($pdf, 89.5, 95, 'VENTA A CUENTA DE TERCEROS', '', 45);
            $pdf->RoundedRect(10, 98, 195, 5, 1, '1111');
            if (!empty($datos)) {
                $this->escribirEtiqueta($pdf, 12, 99, 'NIT:', $datos['nitTercero'], 8);
                $this->escribirEtiqueta($pdf, 65, 99, 'Nombre, denominacion o razón social:', $datos['nombreTercero'], 50);
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
                    if (!empty($doc['codigoAsociado']) == 3) {
                        $pdf->SetXY(12, $yDatos);

                        $nitMedico = !empty($doc['nit']) ? $doc['nit'] : (!empty($doc['docIdentificacion']) ? $doc['docIdentificacion'] : '');

                        $codigoTipo = isset($doc['tipoServicio']) ? $doc['tipoServicio'] : null;
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
            } else if (!empty($docAsociado[0]['codigoAsociado'])) {

                $pdf->SetFont('helvetica', '', 7);
                $yDatos = $yEncabezado + $altoFila;

                $tiposServicio = $this->model->getTipoServicioMedico();

                // $medico = $docAsociado['codigoAsociado'];
                $pdf->SetXY(12, $yDatos);

                $nitMedico = !empty($docAsociado[0]['nit']) ? $docAsociado[0]['nit'] : (!empty($docAsociado[0]['docIdentificacion']) ? $docAsociado[0]['docIdentificacion'] : '');

                $codigoTipo = isset($docAsociado[0]['tipoServicio']) ? $docAsociado[0]['tipoServicio'] : null;
                $tipoServicioNombre = isset($tiposServicio[0]['tipoServicioMedico'][$codigoTipo]['nombre'])
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
            'codigo'    => 60,
            'precioUni'      => 20,
            'noGrabado'      => 20,
            'montoDescu'     => 20,
            'ventaNoSuj'     => 15,
            'ventaExenta'    => 15,
            'ventaGrabada'   => 17
        ];

        // Encabezados legibles
        $encabezados = [
            'numItem'        => 'N°',
            'cantidad'       => 'Cantidad',
            // 'uniMedida'      => 'Unidad',
            'codigo'    => 'Descripción',
            'precioUni'      => 'Precio Unitario',
            'noGrabado'      => 'Otros montos no afectos.',
            'montoDescu'     => 'Descuento por Item',
            'ventaNoSuj'     => 'Ventas No Sujetas',
            'ventaExenta'    => 'Ventas Exentas',
            'ventaGrabada'   => 'Ventas Gravadas'
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
            $codigoProducto = $prod['codigo'];
            $producto = $this->model->getProducto($codigoProducto);

            if (isset($producto['nombreProducto'])) {
                $prod['codigo'] = $producto['nombreProducto'];
            } else if (!empty($producto['descripcion'])) {
                $prod['codigo'] = $prod['descripcion'];
            }

            $alturas = [];

            // CALCULAR ALTURA NECESARIA PARA CADA CELDA (especialmente descripción)
            foreach (array_keys($anchos) as $key) {

                $valor = $prod[$key];

                if (in_array($key, ['numItem', 'cantidad', 'uniMedida'])) {
                    $valor = number_format($valor, 0);
                } elseif (is_numeric($valor)) {
                    $valor = number_format($valor, 2);
                }

                $align = 'C';
                if ($key === 'codigo') $align = 'L';
                if (in_array($key, ['precioUni', 'noGrabado', 'montoDescu', 'ventaNoSuj', 'ventaExenta', 'ventaGrabada'])) $align = 'R';

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
                if (in_array($key, ['numItem', 'cantidad']) && is_numeric($valor)) {
                    $valor = number_format($valor, 0);
                } elseif (is_numeric($valor)) {
                    $valor = number_format($valor, 2);
                }

                // Alineación
                $align = 'C';
                if ($key === 'codigo') $align = 'L';
                if (in_array($key, ['precioUni', 'noGrabado', 'montoDescu', 'ventaNoSuj', 'ventaExenta', 'ventaGrabada'])) $align = 'R';

                $pdf->MultiCell($anchos[$key], $altoFila, $valor, 0, $align, false, 0, '', '', true, 0, false, true, $altoFila, 'M');
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

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Sumatoria de ventas:', number_format($datos['subTotalVentas'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas no sujetas:', number_format($datos['descuNoSujeta'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas exentas:', number_format($datos['descuExenta'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas gravadas:', number_format($datos['descuGravada'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Nombre del Tributo', 'Valor del tributo', 50, 30); // Asumimos que esto es solo etiqueta fija
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Sub-Total:', number_format($datos['subTotal'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'IVA Retenido:', number_format($datos['ivaRete1'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Retención de Renta:', number_format($datos['reteRenta'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto Total de la Operación:', number_format($datos['montoTotalOperacion'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Total Otros Montos No Afectos de la Operación:', number_format($datos['totalNoGravado'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Total a Pagar:', number_format($datos['totalPagar'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $yOperacion = $yResumen;

        if ($yOperacion + 10 > $espacioDisponible) {
            $pdf->AddPage();
            $yOperacion = 10;
        }

        $pdf->RoundedRect(10, $yOperacion, 110, 20, 1, '1111');
        $yEtiqueta = $yResumen + 3;
        $altoFila = 5;

        /// Total en dolares
        $total = $datos['totalPagar'];
        $fmt = new NumberFormatter("es_ES", NumberFormatter::SPELLOUT);
        $montoEntero = floor($total);
        $montoDecimal = round(($total - $montoEntero) * 100);
        $textEntero = strtoupper($fmt->format($montoEntero));
        $textDecimal = str_pad($montoDecimal, 2, "0", STR_PAD_LEFT);
        $text = $textEntero . " CON " . $textDecimal . "/100 USD";
        /// Total en dolares
        // Escribir etiquetas
        $this->escribirEtiqueta($pdf, 12, $yEtiqueta, 'Valor en Letras:', $text, 20, 70);
        $yEtiqueta += $altoFila;
        $nombreCondicion = $this->model->getNombreCondicionOperacion($datos['condicionOperacion']);
        $this->escribirEtiqueta($pdf, 12, $yEtiqueta, 'Condición de la Operación:', $nombreCondicion, 20, 20);

        $pdf->Output('FACTURA_N_' . $datos['numeroControl'] . '.pdf', 'I');
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

    public function generarPdfFeJSON($numeroControl)
    {
        header('Content-Type: application/json');

        // Traer los datos del modelo (como lo haces en generarPdfFe)
        $datos = $this->model->datosFe($numeroControl);
        $datosFactura = $this->facturacionModel->datosFactura();
        $docRelacionado = $this->model->getDocRelaionados($numeroControl);
        $empresa = $this->model->getEmpresa();
        $docAsociado = $this->model->getDocRelaionadosAsociados($numeroControl);
        $cuerpoDocumentos = $this->model->getDTECuerpo($numeroControl);

        // armar json
        $identificacion = (object)[
            'version' => $datos['versionDte'],
            'ambiente' => $datos['ambiente'],
            'tipoDte' => $datos['tipoDte'],
            'numeroControl' => $datos['numeroControl'],
            'codigoGeneracion' => $datos['codigoGeneracion'],
            'tipoModelo' => $datos['tipoModelo'],
            'tipoOperacion' => $datos['tipoOperacion'],
            'tipoContingencia' => $datos['tipoContingencia'],
            'motivoContin' => $datos['motivoContingencia'] ?? null,
            'fecEmi' => $datos['fechaEmision'],
            'horEmi' => $datos['horaEmision'],
            'tipoMoneda' => $datosFactura['tipoMoneda'],
        ];

        $documentoRelacionado = null;
        if (!empty($docRelacionado)) {
            $documentoRelacionado = [];

            foreach ($docRelacionado as $doc) {
                $documentoRelacionado[] = [
                    'tipoDocumento' => $doc['tipoDocumento'],
                    'tipoGeneracion' => (int) $doc['tipoGeneracion'],
                    'numeroDocumento' => $doc['numeroDocumento'],
                    'fechaEmision' => $doc['fechaEmision'],
                ];
            }
        }

        $emisor = (object) [
            'nit' => $empresa['nit'],
            'nrc' => $empresa['nrc'],
            'nombre' => $empresa['nombre'],
            'codActividad' => $empresa['codActividad'],
            'descActividad' => $empresa['descActividad'],
            'nombreComercial' => $empresa['nombre'],
            'tipoEstablecimiento' => $empresa['tipoEstablecimiento'],
            "direccion" => [
                'departamento' => $empresa['departamento'],
                'municipio' => $empresa['municipio'],
                'complemento' => $empresa['direccion'],
            ],
            'telefono' => $empresa['telefono'],
            'correo' => $empresa['correo'],
            'codEstableMH' => $datosFactura['codEstableMH'],
            'codEstable' => $datosFactura['codEstable'],
            'codPuntoVentaMH' => $datosFactura['codPuntoVentaMH'],
            'codPuntoVenta' => $datosFactura['codPuntoVenta'],
        ];

        $receptor = (object)[
            'tipoDocumento' => $datos['identificacion'],
            'numDocumento' => $datos['numDocumento'],
            'nrc' => $datos['nrc'],
            'nombre' => $datos['cliente'],
            'codActividad' => $datos['actividadEconomica'],
            'descActividad' => $datos['valor'],
            'direccion' => [
                'departamento' => $datos['departamento'],
                'municipio' => $datos['municipio'],
                'complemento' => $datos['complemento'],
            ],
            'telefono' => $datos['telefono'], //opcional
            'correo' => $datos['correo']
        ];

        $otrosDocumentosAsociados = null;
        if (!empty($docAsociado)) {
            $otrosDocumentosAsociados = [];

            foreach ($docAsociado as $doc) {

                $medico = [
                    'nombre' => $doc['nombre'] ?? null,
                    'nit' => isset($doc['nit']) && trim($doc['nit']) !== '' ? trim($doc['nit']) : null,
                    'docIdentificacion' => isset($doc['docIdentificacion']) && trim($doc['docIdentificacion']) !== '' ? trim($doc['docIdentificacion']) : null,
                    'tipoServicio' => isset($doc['tipoServicio']) ? (int)$doc['tipoServicio'] : null
                ];

                $otrosDocumentosAsociados[] = [
                    'codDocAsociado' => $doc['codigoAsociado'],
                    'descDocumento' => $doc['descDocumento'],
                    'detalleDocumento' => $doc['detalleDocumento'],
                    'medico' => $medico
                ];
            }
        }

        $ventaTercero = null;
        if (!empty($datos['nitTercero']) || !empty($datos['nombreTercero'])) {
            $ventaTercero = (object)[
                'nit' => $datos['nitTercero'],
                'nombre' => $datos['nombreTercero'],
            ];
        }

        $cuerpoDocumento = [];
        foreach ($cuerpoDocumentos as $doc) {
            $descripcion = null;

            if (!empty($doc['codigo'])) {
                $producto = $this->model->getProducto($doc['codigo']);
                if (!empty($producto['nombreProducto'])) {
                    $descripcion = $producto['nombreProducto'];
                }
            }

            // Si no hubo código o no se encontró producto, usar la descripción manual
            if (empty($descripcion)) {
                $descripcion = $doc['descripcion'];
            }

            $cuerpoDocumento[] = [
                'numItem' => $doc['numItem'],
                'tipoItem' => $doc['tipoItem'],
                'numeroDocumento' => $doc['idNumeroDocumento'],
                'cantidad' => $doc['cantidad'],
                'codigo' => $doc['codigo'],
                'codTributo' => null,
                'uniMedida' => $doc['uniMedida'],
                'descripcion' => $descripcion,
                'precioUni' => $doc['precioUni'],
                'montoDescu' => $doc['montoDescu'],
                'ventaNoSuj' => $doc['ventaNoSuj'],
                'ventaExenta' => $doc['ventaExenta'],
                'ventaGravada' => $doc['ventaGrabada'],
                'tributos' => $doc['tributos'],
                'psv' => $doc['psv'],
                'noGravado' => $doc['noGrabado'],
                'ivaItem' =>  $doc['ivaItem']
            ];
        }

        /// Total en dolares
        $total = $datos['totalPagar'];
        $fmt = new NumberFormatter("es_ES", NumberFormatter::SPELLOUT);
        $montoEntero = floor($total);
        $montoDecimal = round(($total - $montoEntero) * 100);
        $textEntero = strtoupper($fmt->format($montoEntero));
        $textDecimal = str_pad($montoDecimal, 2, "0", STR_PAD_LEFT);
        $text = $textEntero . " CON " . $textDecimal . "/100 USD";
        /// Total en dolares

        $pagos = [(object)[
            'codigo' => $datos['pagoCodigo'],
            'montoPago' => $datos['pagoMontoPago'],
            'referencia' => $datos['referencia'],
            'plazo' => $datos['plazo'],
            'periodo' => $datos['periodo'],
        ]];
        $resumen = (object)[
            'totalNoSuj' => $datos['totalNoSujeta'],
            'totalExenta' => $datos['totalExenta'],
            'totalGravada' => $datos['totalGravada'],
            'subTotalVentas' => $datos['subTotalVentas'],
            'descuNoSuj' => $datos['descuNoSujeta'],
            'descuExenta' => $datos['descuExenta'],
            'descuGravada' => $datos['descuGravada'],
            'porcentajeDescuento' => $datos['porcentajeDescuento'],
            'totalDescu' => $datos['totalDescu'],
            'tributos' => null,
            'subTotal' => $datos['subTotal'],
            'ivaRete1' => $datos['ivaRete1'],
            'reteRenta' => $datos['reteRenta'],
            'montoTotalOperacion' => $datos['montoTotalOperacion'],
            'totalNoGravado' => $datos['totalNoGravado'],
            'totalPagar' => $datos['totalPagar'],
            'totalLetras' => $text,
            'totalIva' => $datos['totalIva'],
            'saldoFavor' => $datos['saldoFavor'],
            'condicionOperacion' => $datos['condicionOperacion'],
            'pagos' => $pagos,
            'numPagoElectronico' => $datos['numPagoElectronico'],
        ];

        $extension = null;
        $apendice = null;
        $estructuraDTE = [
            "identificacion" => $identificacion,
            "documentoRelacionado" => $documentoRelacionado,
            "emisor" => $emisor,
            "receptor" => $receptor,
            'otrosDocumentos' => $otrosDocumentosAsociados,
            'ventaTercero' => $ventaTercero,
            'cuerpoDocumento' => $cuerpoDocumento,
            'resumen' => $resumen,
            'extension' => $extension,
            'apendice' => $apendice,
            'selloRecibido' => $datos['selloRecibido']
        ];

        echo json_encode($estructuraDTE, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function salir()
    {
        session_destroy();
        header("location: " . base_url);
    }

    public function generar()
    {
        date_default_timezone_set('America/El_Salvador');

        $metodos = new Metodos;
        $dataMetodos = $metodos->variablesGlobales();
        $codigoGeneracion = $metodos->codigoGeneracion();
        $empresa = $this->model->getEmpresa();
        try {
            $this->model->iniciarTransaccion();

            $estadoContingencia = $this->model->obtenerEstadoContingencia();
            $eventoActivo = $estadoContingencia['estadoContingenciaId'] ?? null;

            if ($eventoActivo == 1) {
                $msg = "No puede generar evento con contingencia activa";
                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                return;
            } else if ($eventoActivo == null) {
                // Obtener evento cerrado más reciente para el JSON
                $cont = $this->model->getEventoCerradoMasReciente();
                if (!$cont) {
                    throw new Exception("No hay contingencia finalizada para transmitir.");
                }

                $dteFactura = $this->model->listarDtesJson();
                if (empty($dteFactura)) {
                    throw new Exception("No hay DTE pendientes para transmitir.");
                }

                // Armado de fechas
                $fInicio = date('Y-m-d', strtotime($cont['fechaInicio']));
                $hInicio = date('H:i:s', strtotime($cont['fechaInicio']));
                $fFin = date('Y-m-d', strtotime($cont['fechaFin']));
                $hFin = date('H:i:s', strtotime($cont['fechaFin']));

                $fTransmision = date('Y-m-d');
                $hTransmision = date('H:i:s');

                // Validar rango
                $finTimestamp = strtotime($cont['fechaFin']);
                $transmisionTimestamp = strtotime("$fTransmision $hTransmision");

                if ($transmisionTimestamp < $finTimestamp) {
                    throw new Exception("La fecha/hora de transmisión no puede ser anterior al fin de la contingencia");
                }
                if (($transmisionTimestamp - $finTimestamp) > 86400) {
                    throw new Exception("La transmisión excede las 24 horas permitidas.");
                }


                //IDENTIFICACION
                $identificacion = (object)[
                    "version" => 3,
                    "ambiente" => $dataMetodos['ambiente'],
                    "codigoGeneracion" => $codigoGeneracion,
                    "fTransmision" => $fTransmision,
                    "hTransmision" => $hTransmision
                ];

                //EMISOR
                $nombreResponsable = $_SESSION['nombreCompleto'];
                $tipoIdentificacion = $_SESSION['tipoIdentificacion'];
                $numeroDocResponsable = $_SESSION['numeroIdentificacion'];
                $emisor = (object)[
                    "nit" => $empresa['nit'],
                    "nombre" => $empresa['nombre'],
                    "nombreResponsable" => $nombreResponsable,
                    "tipoDocResponsable" => $tipoIdentificacion,
                    "numeroDocResponsable" => $numeroDocResponsable,
                    "tipoEstablecimiento" => $empresa['tipoEstablecimiento'],
                    "codEstableMH" => $dataMetodos['codEstableMH'],
                    "codPuntoVenta" => $dataMetodos['codPuntoVenta'],
                    "telefono" => $empresa['telefono'],
                    "correo" => $empresa['correo']
                ];

                //DETALLE
                $detalle = [];
                $maxItems = 1000;
                $noItem = 1;
                // Solo tomar los primeros 1000 elementos
                foreach (array_slice($dteFactura, 0, $maxItems) as $doc) {
                    $detalle[] = [
                        "noItem" => $noItem++,
                        "codigoGeneracion" => $doc['codigo'],
                        "tipoDoc" => $doc['tipo']
                    ];
                }

                //MOTIVO
                $motivo = (object) [
                    "fInicio" => $fInicio,
                    "fFin" => $fFin,
                    "hInicio" => $hInicio,
                    "hFin" => $hFin,
                    "tipoContingencia" => (int)$cont['tipoContingencia'],
                    "motivoContingencia" => $cont['motivoContingencia']
                ];


                // JSON COMPLETO
                $json = [
                    "identificacion" => $identificacion,
                    "emisor" => $emisor,
                    "detalleDTE" => $detalle,
                    "motivo" => $motivo
                ];


                $this->model->confirmarTransaccion();
                $this->sendJsonResponse([
                    'status' => 'success',
                    'dteJson' => $json,
                    'nit' => $emisor->nit,
                    'passwordPri' => PASSWORD_PRIVADA,
                ]);
            }
        } catch (Exception $e) {
            $this->model->revertirTransaccion();
            $msg = "Error: " . $e->getMessage();
            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
            return;
        }
    }

    public function emitirFirmado()
    {
        try {
            $this->model->iniciarTransaccion();

            $data = json_decode(file_get_contents("php://input"), true);
            $firmado = $data['firmado'] ?? null;
            $dteJson = $data['dteJson'] ?? null;
            $empresa = $this->model->getEmpresa();
            $metodos = new Metodos;
            $cont = $this->model->getEventoCerradoMasReciente();
            $token = $metodos->obtenerYGuardarToken();
            if (!isset($token['token'])) {
                // Mostrar o devolver el mensaje de error
                $this->sendJsonResponse(['status' => 'error', 'message' => $token['message']]);
                return;
            }


            $postData = json_encode([
                'nit' => $empresa['nit'],
                'documento' => $firmado
            ], JSON_UNESCAPED_UNICODE);


            $ch = curl_init(URL_EVENTO_CONTINGENCIA);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: ' . $token['token'],
                'Content-Type: application/json',
                'User-Agent: sistema-facturacion'
            ]);

            $response = curl_exec($ch);
            curl_close($ch);
            $decoded = json_decode($response, true);


            if ($decoded && isset($decoded['estado']) && $decoded['estado'] === 'RECIBIDO' && !empty($decoded['selloRecibido'])) {
                $estado = $decoded['estado'];
                $fechaHora = DateTime::createFromFormat('d/m/Y H:i:s', $decoded['fechaHora']);
                if (!$fechaHora) {
                    throw new Exception("Formato de fecha recibido inválido: " . $decoded['fechaHora']);
                }
                $fechaHora = $fechaHora->format('Y-m-d H:i:s');
                $mensaje = $decoded['mensaje'];
                $selloRecibido = $decoded['selloRecibido'];
                $observaciones = is_array($decoded['observaciones']) ? json_encode($decoded['observaciones'], JSON_UNESCAPED_UNICODE) : $decoded['observaciones'];
                $resultEvento = $this->model->actualizarEstadoEventoCon($estado, $fechaHora, $mensaje, $selloRecibido, $firmado, $observaciones, $dteJson['identificacion']['codigoGeneracion'], $cont['id']);
                if ($resultEvento != "ok") {
                    throw new Exception("Error al actualizar el evento: " . $cont['id']);
                }

                foreach ($dteJson['detalleDTE'] as $dte) {
                    $codigo = $dte['codigoGeneracion'];
                    $this->model->actualizarEstadoDTEComoIncluido($codigo);
                }


                $this->model->confirmarTransaccion();

                $this->sendJsonResponse([
                    'status' => 'success',
                    'emision' => $decoded
                ]);
            } else {
                $this->sendJsonResponse([
                    'status' => 'error',
                    'message' => $decoded  // ← así se muestra la respuesta cruda tal cual
                ]);
                $this->model->revertirTransaccion();

                throw new Exception($decoded);
            }
        } catch (Throwable $e) {
            $this->model->revertirTransaccion();
            $this->sendJsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

           public function emitirLote()
    {
        date_default_timezone_set('America/El_Salvador');

        try {
            $this->model->iniciarTransaccion();

            $metodos = new Metodos;
            $empresa = $this->model->getEmpresa();
            $nit = $empresa['nit'];
            $codigoGeneracion = $metodos->codigoGeneracion(); // UUID v4
            $cont = $this->model->getEventoCerradoMasReciente();

            $variablesGlobales = $metodos->variablesGlobales();
            $ambiente = $variablesGlobales['ambiente'];

            if (!$cont) {
                throw new Exception("No hay contingencia finalizada para transmitir.");
            }

            $ambiente = $ambiente;
            $horaActual = (int)date('H');
            $minutoActual = (int)date('i');
            $horaCompleta = $horaActual + ($minutoActual / 60);

            // Validar si estás en contingencia (si hay evento cerrado)
            $esContingencia = $cont ? true : false;

            // if (!$esContingencia) {
            //if ($ambiente === "00" && ($horaCompleta < 8 || $horaCompleta >= 17)) {
            //    throw new Exception("El horario de envío en ambiente de pruebas es de 08:00 a 17:00.");
            //}

            //if ($ambiente === "01" && !($horaCompleta >= 22 || $horaCompleta < 5)) {
            //    throw new Exception("El horario de envío en producción es de 22:00 a 05:00.");
//}
            // }


            $selloPendiente = $this->model->getSelloContingencia($cont['id']);
            if ($selloPendiente) {
                throw new Exception("No se puede emitir el lote: el evento de contingencia aún no ha recibido el sello.");
            }

            // // Validar límite diario
            // $limiteLotes = $ambiente === "01" ? 400 : 300;
            // $enviadosHoy = $this->model->contarLotesEnviadosHoy($ambiente);

            // if ($enviadosHoy >= $limiteLotes) {
            //     throw new Exception("Ya se alcanzó el límite de $limiteLotes lotes enviados hoy para el ambiente $ambiente. Enviados: $enviadosHoy");
            // }


            // Obtener hasta 1000 DTEs firmados
            $dtesFirmados = $this->model->obtenerDtesFirmadosPendientes();

            if (empty($dtesFirmados)) {
                throw new Exception("No hay DTEs agregados para enviar en lote.");
            }

            $token = $metodos->obtenerYGuardarToken();
            if (!isset($token['token'])) {
                throw new Exception("No se pudo obtener el token.");
            }

            // Preparar payload JSON
            $postData = json_encode([
                'ambiente' => $ambiente,
                'idEnvio' => $codigoGeneracion,
                'version' => 1,
                'nitEmisor' => $nit,
                'documentos' => array_slice($dtesFirmados, 0, 1000)
            ], JSON_UNESCAPED_UNICODE);

            // Enviar a Hacienda
            $ch = curl_init(URL_RECEPCION_LOTE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: ' . $token['token'],
                'Content-Type: application/json',
                'User-Agent: sistema-facturacion'
            ]);

            $response = curl_exec($ch);
            curl_close($ch);
            $decoded = json_decode($response, true);

            if ($decoded && isset($decoded['codigoMsg']) && $decoded['codigoMsg'] === '001') {
                $fhProcesamientoOriginal = $decoded['fhProcesamiento']; // ej: "03/07/2025 00:27:01"
                $fhProcesamiento = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $fhProcesamientoOriginal)));
                $loteId = $this->model->registrarLote(
                    $decoded['idEnvio'],
                    $decoded['codigoLote'],
                    $fhProcesamiento,
                    $decoded['descripcionMsg'],
                    $decoded['ambiente'],
                    $decoded['estado'],
                    $decoded['versionApp'],
                );

                if (!$loteId) {
                    throw new Exception("No se pudo registrar el lote en la base de datos.");
                }


                $estado = 'PROCESADO EN CONTINGENCIA';
                $selloRecibido = 'ACTUALIZAR';
                $codigos = $this->model->obtenerCodigosGeneracionPendientesPorEvento($cont['id']);
                $resultado = $this->model->asociarDtesAlLote($loteId, $estado, $selloRecibido, $cont['id']);
                if ($resultado !== "ok") {
                    throw new Exception("No se pudo actualizar los DTE con el ID del lote.");
                }

                foreach ($codigos as $fila) {
                    $this->model->actualizarEstadoDTEComoEnviado($fila['codigoGeneracion']);
                }


                $this->model->confirmarTransaccion(); // COMMIT

                $this->sendJsonResponse([
                    'status' => 'success',
                    'emision' => $decoded,
                ]);
                return;
            } else {
                $this->sendJsonResponse([
                    'status' => 'error',
                    'message' => $decoded  // ← así se muestra la respuesta cruda tal cual
                ]);
                $this->model->revertirTransaccion();

                throw new Exception($decoded);
            }
        } catch (Exception $e) {
            $this->model->revertirTransaccion(); // ROLLBACK
            $this->sendJsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
            return;
        }
    }

    public function verPdfDte($numeroControl)
    {
        $data =  $this->model->getTipoDte($numeroControl);

        if (!$data) {
            die("No se encontró el documento con número de control: $numeroControl");
        }

        $tipoDte = $data['tipoDte'];

        $listados = new Listados;

        switch ($tipoDte) {
            case '01':
                $listados->generarPdfFe($numeroControl, false);
                break;
            case '03':
                $listados->generarPdfCcf($numeroControl, false);
                break;
            case '05':
                $listados->generarPdfNc($numeroControl, false);
                break;
            case '14':
                $listados->generarPdfFse($numeroControl, false);
                break;
            default:
                die("Tipo de DTE no válido: $tipoDte");
        }
    }

    public function verJsonDte($numeroControl)
    {
        $data =  $this->model->getTipoDte($numeroControl);

        if (!$data) {
            die("No se encontró el documento con número de control: $numeroControl");
        }

        $tipoDte = $data['tipoDte'];

        $listados = new Listados;

        switch ($tipoDte) {
            case '01':
                $listados->generarPdfFeJSON($numeroControl, false);
                break;
            case '03':
                $listados->generarPdfCcfJSON($numeroControl, false);
                break;
            case '05':
                $listados->generarPdfNcfJSON($numeroControl, false);
                break;
            case '14':
                $listados->generarPdfFseJSON($numeroControl, false);
                break;
            default:
                die("Tipo de DTE no válido: $tipoDte");
        }
    }

    public function consultarLote()
    {
        $query = $_GET['q'] ?? '';
        $fecha = $_GET['fecha'] ?? ''; // <-- nueva variable fecha

        $data = $this->model->searchLote($query, $fecha);
        $this->sendJsonResponse($data);
    }

    public function consultarLotes()
    {
        $metodos = new Metodos;
        $data = json_decode(file_get_contents('php://input'), true);

        $fecha = $data['fecha'] ?? null;
        $lote = $data['lote'] ?? null;

        if (!$fecha || !$lote) {
            $this->sendJsonResponse([
                'status' => 'error',
                'message' => 'Fecha y lote son requeridos'
            ]);
            return;
        }

        // Obtener token
        $token = $metodos->obtenerYGuardarToken();
        if (!isset($token['token'])) {
            $this->sendJsonResponse([
                'status' => 'error',
                'message' => $token['message'] ?? 'No se pudo obtener token'
            ]);
            return;
        }

        $url = "https://apitest.dtes.mh.gob.sv/fesv/recepcion/consultadtelote/" . urlencode($lote);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $token['token'],
            'Content-Type: application/json',
            'User-Agent: sistema-facturacion'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            $this->sendJsonResponse([
                'status' => 'error',
                'message' => "Error en la consulta. Código HTTP: $httpCode. Respuesta: $response",
            ]);
            return;
        }

        $consultaMH = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendJsonResponse([
                'status' => 'error',
                'message' => 'Respuesta JSON inválida del servidor.',
                'raw_response' => $response
            ]);
            return;
        }

        // Actualizar cada DTE procesado
        if (isset($consultaMH['procesados']) && is_array($consultaMH['procesados'])) {
            foreach ($consultaMH['procesados'] as $docProcesado) {
                $codigoGeneracion = $docProcesado['codigoGeneracion'] ?? null;
                $selloRecibido = $docProcesado['selloRecibido'] ?? null;
                $fechaProcesamiento = $docProcesado['fhProcesamiento'];
                $observaciones = is_array($docProcesado['observaciones']) ? json_encode($docProcesado['observaciones'], JSON_UNESCAPED_UNICODE) : $docProcesado['observaciones'];
                if ($codigoGeneracion) {
                    // Actualizar estado y sello en BD a 'procesado' o 'aceptado'
                    $this->model->actualizarEstadoYSello($selloRecibido, $fechaProcesamiento, $observaciones, $codigoGeneracion);
                }
            }
        }

        // Actualizar cada DTE rechazado
        if (isset($consultaMH['rechazados']) && is_array($consultaMH['rechazados'])) {
            foreach ($consultaMH['rechazados'] as $docRechazado) {
                $codigoGeneracion = $docRechazado['codigoGeneracion'] ?? null;
                if ($codigoGeneracion) {
                    // Actualizar estado en BD a 'rechazado' o 'pendiente' y sin sello
                    $this->model->actuliarEstadoRechazado($codigoGeneracion);
                }
            }
        }

        $this->sendJsonResponse([
            'status' => 'success',
            'consulta' => $consultaMH,
        ]);
    }
}