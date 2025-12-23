<?php

class Listados extends Controller
{
    private $facturacionModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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

    public function listarFe()
    {
        $start = $_POST['start']; // Índice de inicio
        $length = $_POST['length']; // Número de registros por página
        $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : ""; // Búsqueda por defecto
        $customSearch = isset($_POST['searchValue']) ? $_POST['searchValue'] : ""; // Búsqueda personalizada

        // Si hay búsqueda personalizada, la aplicamos
        if (!empty($customSearch)) {
            $search = $customSearch;
        }

        $result = $this->model->getFe($start, $length, $search);

        $data = $result['dteFe'];
        $total = $result['total'];

         for ($i = 0; $i < count($data); $i++) {
            // Estado con color
            $estado = $data[$i]['estado'];
            $clase = '';
            if ($estado === 'INVALIDADO') {
                $clase = 'text-danger font-weight-bold'; // rojo
            } elseif ($estado === 'PROCESADO' || $estado === 'PROCESADO EN CONTINGENCIA') {
                $clase = 'text-success font-weight-bold'; // verde
            }

            $data[$i]['estado'] = '<span class="' . $clase . '">' . $estado . '</span>';

            // Botón PDF + JSON
            $data[$i]['acciones'] =
                '<div class="text-center">
                    <button class="btn btn-editar" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-file-pdf"></i></button>
                    <button class="btn btn-json" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-code"></i></button>
                    <button class="btn btn-enviar-correo" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-envelope"></i></button>
                </div>';

            // Botón invalidar
            $data[$i]['invalidar'] =
                '<div class="text-center">
                    <button class="btn btn-invalidar" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-solid fa-ban"></i></button>
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

    public function listarCcf()
    {
        $start = $_POST['start']; // Índice de inicio
        $length = $_POST['length']; // Número de registros por página
        $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : ""; // Búsqueda por defecto
        $customSearch = isset($_POST['searchValue']) ? $_POST['searchValue'] : ""; // Búsqueda personalizada

        // Si hay búsqueda personalizada, la aplicamos
        if (!empty($customSearch)) {
            $search = $customSearch;
        }

        $result = $this->model->getCcf($start, $length, $search);

        $data = $result['dteCcf'];
        $total = $result['total'];

         for ($i = 0; $i < count($data); $i++) {
            // Estado con color
            $estado = $data[$i]['estado'];
            $clase = '';
            if ($estado === 'INVALIDADO') {
                $clase = 'text-danger font-weight-bold'; // rojo
            } elseif ($estado === 'PROCESADO' || $estado === 'PROCESADO EN CONTINGENCIA') {
                $clase = 'text-success font-weight-bold'; // verde
            }

            $data[$i]['estado'] = '<span class="' . $clase . '">' . $estado . '</span>';

            // Botón PDF + JSON
            $data[$i]['acciones'] =
                '<div class="text-center">
                    <button class="btn btn-editar" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-file-pdf"></i></button>
                    <button class="btn btn-json" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-code"></i></button>
                    <button class="btn btn-enviar-correo" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-envelope"></i></button>
                </div>';

            // Botón invalidar
            $data[$i]['invalidar'] =
                '<div class="text-center">
                    <button class="btn btn-invalidar" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-solid fa-ban"></i></button>
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

    public function listarNc()
    {
        $start = $_POST['start']; // Índice de inicio
        $length = $_POST['length']; // Número de registros por página
        $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : ""; // Búsqueda por defecto
        $customSearch = isset($_POST['searchValue']) ? $_POST['searchValue'] : ""; // Búsqueda personalizada

        // Si hay búsqueda personalizada, la aplicamos
        if (!empty($customSearch)) {
            $search = $customSearch;
        }

        $result = $this->model->getNc($start, $length, $search);

        $data = $result['dteNc'];
        $total = $result['total'];

        for ($i = 0; $i < count($data); $i++) {
            // Estado con color
            $estado = $data[$i]['estado'];
            $clase = '';
            if ($estado === 'INVALIDADO') {
                $clase = 'text-danger font-weight-bold'; // rojo
            } elseif ($estado === 'PROCESADO' || $estado === 'PROCESADO EN CONTINGENCIA') {
                $clase = 'text-success font-weight-bold'; // verde
            }

            $data[$i]['estado'] = '<span class="' . $clase . '">' . $estado . '</span>';

            // Botón PDF + JSON
            $data[$i]['acciones'] =
                '<div class="text-center">
                    <button class="btn btn-editar" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-file-pdf"></i></button>
                    <button class="btn btn-json" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-code"></i></button>
                    <button class="btn btn-enviar-correo" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-envelope"></i></button>
                </div>';

            // Botón invalidar
            $data[$i]['invalidar'] =
                '<div class="text-center">
                    <button class="btn btn-invalidar" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-solid fa-ban"></i></button>
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

public function listarFse()
    {
        $start = $_POST['start']; // Índice de inicio
        $length = $_POST['length']; // Número de registros por página
        $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : ""; // Búsqueda por defecto
        $customSearch = isset($_POST['searchValue']) ? $_POST['searchValue'] : ""; // Búsqueda personalizada

        // Si hay búsqueda personalizada, la aplicamos
        if (!empty($customSearch)) {
            $search = $customSearch;
        }

        $result = $this->model->getFse($start, $length, $search);

        $data = $result['dteFse'];
        $total = $result['total'];

        for ($i = 0; $i < count($data); $i++) {
            // Estado con color
            $estado = $data[$i]['estado'];
            $clase = '';
            if ($estado === 'INVALIDADO') {
                $clase = 'text-danger font-weight-bold'; // rojo
            } elseif ($estado === 'PROCESADO' || $estado === 'PROCESADO EN CONTINGENCIA') {
                $clase = 'text-success font-weight-bold'; // verde
            }

            $data[$i]['estado'] = '<span class="' . $clase . '">' . $estado . '</span>';

            // Botón PDF + JSON
            $data[$i]['acciones'] =
                '<div class="text-center">
                    <button class="btn btn-editar" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-file-pdf"></i></button>
                    <button class="btn btn-json" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-code"></i></button>
                    <button class="btn btn-enviar-correo" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-envelope"></i></button>
                </div>';

            // Botón invalidar
            $data[$i]['invalidar'] =
                '<div class="text-center">
                    <button class="btn btn-invalidar" type="button" data-id="' . $data[$i]["correlativo"] . '"><i class="fas fa-solid fa-ban"></i></button>
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
    

    public function generarPdfFe($numeroControl, $retornarRuta = false)
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

            $this->escribirEtiqueta($pdf, 14, 26, 'Sello de Recepción:', $datos['selloRecibido'], 26);
        } else {
            $this->escribirEtiqueta($pdf, 14, 26, 'Sello de Recepción:', '', 26);
        }


        if ($datos['estado'] == 'PROCESADO' || $datos['estado'] == 'INVALIDADO' || $datos['estado'] == 'PROCESADO EN CONTINGENCIA') {
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
            $this->escribirEtiqueta($pdf, 113, 58, 'Carnet de Residente:', $datos['numDocumento'], 30, 50);
        } else if ($datos['identificacion'] == '03') {
            $this->escribirEtiqueta($pdf, 113, 58, 'Pasaporte:', $datos['numDocumento'], 30, 50);
        } else if ($datos['identificacion'] == '13') {
            $this->escribirEtiqueta($pdf, 113, 58, 'DUI:', $datos['numDocumento'], 30, 50);
        } else if ($datos['identificacion'] == '36') {
            $this->escribirEtiqueta($pdf, 113, 58, 'NIT:', $datos['numDocumento'], 30, 50);
        } else if ($datos['identificacion'] == '37') {
            $this->escribirEtiqueta($pdf, 113, 58, 'Identificación:', $datos['numDocumento'], 30, 50);
        }

        if (!empty($datos['telefono'])) {
            $this->escribirEtiqueta($pdf, 113, 61, 'Telefono:', $datos['telefono'], 30, 50);
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
        $yInicioDocRel = 85;

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
            $codigoProducto = $prod['codigo'] ?? null;

            if (!empty($codigoProducto)) {
                $producto = $this->model->getProducto($codigoProducto);
                if (!empty($producto['nombreProducto'])) {
                    $prod['codigo'] = $producto['nombreProducto'];
                } else {
                    $prod['codigo'] = $codigoProducto;
                }
            } elseif (!empty($prod['descripcion'])) {
                $prod['codigo'] = $prod['descripcion'];
            } else {
                $prod['codigo'] = '---';
            }

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

                //$align = 'C';
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
                if ($key === 'numItem' && is_numeric($valor)) {
                    $valor = number_format($valor, 0);
                } elseif ($key === 'cantidad' && is_numeric($valor)) {
                    $valor = number_format($valor, 2);
                } elseif (is_numeric($valor)) {
                    $valor = number_format($valor, 2);
                }

                // Alineación
                $align = 'C';
                if ($key === 'codigo') $align = 'L';

                if (in_array($key, ['precioUni', 'noGrabado', 'montoDescu', 'ventaNoSuj', 'ventaExenta', 'ventaGrabada'])) $align = 'R';

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

        if ($retornarRuta) {
            $nombreSeguro = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', 'FACTURA_N_' . $datos['numeroControl']);
            $rutaAbsoluta = realpath(__DIR__ . '/../temp') . '/' . $nombreSeguro . '.pdf';

            $pdf->Output($rutaAbsoluta, 'F'); // Guarda el PDF en la ruta absoluta
            return 'temp/' . $nombreSeguro . '.pdf'; // Devuelve ruta relativa para el email

        } else {
            $pdf->Output('FACTURA_N_' . $datos['numeroControl'] . '.pdf', 'I'); // Lo muestra en navegador
        }
    }
    public function generarPdfFeJSON($numeroControl, $retornarRuta = false)
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
                'precioUni' => (float)$doc['precioUni'],
                'montoDescu' => (float)$doc['montoDescu'],
                'ventaNoSuj' => (float)$doc['ventaNoSuj'],
                'ventaExenta' => (float)$doc['ventaExenta'],
                'ventaGravada' => (float)$doc['ventaGrabada'],
                'tributos' => $doc['tributos'],
                'psv' => (float)$doc['psv'],
                'noGravado' => (float)$doc['noGrabado'],
                'ivaItem' =>  (float)$doc['ivaItem']
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
            'montoPago' => (float)$datos['pagoMontoPago'],
            'referencia' => $datos['referencia'],
            'plazo' => $datos['plazo'],
            'periodo' => $datos['periodo'],
        ]];
        $resumen = (object)[
            'totalNoSuj' => (float)$datos['totalNoSujeta'],
            'totalExenta' => (float)$datos['totalExenta'],
            'totalGravada' => (float)$datos['totalGravada'],
            'subTotalVentas' => (float)$datos['subTotalVentas'],
            'descuNoSuj' => (float)$datos['descuNoSujeta'],
            'descuExenta' => (float)$datos['descuExenta'],
            'descuGravada' => (float)$datos['descuGravada'],
            'porcentajeDescuento' => (float)$datos['porcentajeDescuento'],
            'totalDescu' => (float)$datos['totalDescu'],
            'tributos' => null,
            'subTotal' => (float)$datos['subTotal'],
            'ivaRete1' => (float)$datos['ivaRete1'],
            'reteRenta' => (float)$datos['reteRenta'],
            'montoTotalOperacion' => (float)$datos['montoTotalOperacion'],
            'totalNoGravado' => (float)$datos['totalNoGravado'],
            'totalPagar' => (float)$datos['totalPagar'],
            'totalLetras' => $text,
            'totalIva' => (float)$datos['totalIva'],
            'saldoFavor' => (float)$datos['saldoFavor'],
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

       if ($retornarRuta) {
            $nombreSeguro = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', 'FACTURA_N_' . $numeroControl);
            $rutaAbsoluta = realpath(__DIR__ . '/../temp') . '/' . $nombreSeguro . '.json';

            file_put_contents($rutaAbsoluta, json_encode($estructuraDTE, JSON_UNESCAPED_UNICODE));
            return 'temp/' . $nombreSeguro . '.json'; // ruta relativa para usar con PHPMailer

        } else {
            header('Content-Type: application/json');
            echo json_encode($estructuraDTE, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }


     public function generarPdfCcf($numeroControl, $retornarRuta = false)
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
        $pdf->SetTitle('COMPROBANTE DE CREDITO FISCAL_N_' . $datos['numeroControl'] . '.pdf', 'I');

        $codigoDepartamentoR = $datos['departamento'];
        $codigoMunicipioR = $datos['municipio'];
        $nombreDepartamentoReceptor = $this->model->getNombreDepartamento($codigoDepartamentoR);
        $nombreMunicipioReceptor = $this->model->getNombreMunicipio($codigoMunicipioR, $codigoDepartamentoR);
        $complementoR = $datos['complemento'];

        // Dibujar borde redondeado
        $pdf->SetLineWidth(0.5);

        $pdf->Image(base_url . 'Assets/img/logo.jpg', 15, 0, 35); // Logo

        $this->centrarCelda($pdf, 180, 3, 'DOCUMENTO TRIBUTARIO ELECTRÓNICO', 8, 'B');
        $this->centrarCelda($pdf, 180, 9, 'COMPROBANTE DE CRÉDITO FISCAL', 8, 'B');

        $this->escribirEtiqueta($pdf, 10, 20, 'Código de Generación:', $datos['codigoGeneracion'], 30);
        $this->escribirEtiqueta($pdf, 14, 23, 'Número de Control:', $datos['numeroControl'], 26);

        if (!empty($datos['selloRecibido'])) {
            $this->escribirEtiqueta($pdf, 14, 26, 'Sello de Recepción:', $datos['selloRecibido'], 26);
        } else {
            $this->escribirEtiqueta($pdf, 14, 26, 'Sello de Recepción:', '', 26);
        }

        // codigo QR
        if ($datos['estado'] == 'PROCESADO' || $datos['estado'] == 'INVALIDADO' || $datos['estado'] == 'PROCESADO EN CONTINGENCIA') {
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
        if (!empty($datos['cliente'])) {
            $this->escribirEtiqueta($pdf, 113, 47, 'Nombre o razón social:', $datos['cliente'], 30, 50);
        }
        if (!empty($datos['nit'])) {
            $this->escribirEtiqueta($pdf, 113, 51, 'NIT:', $datos['nit'], 30, 50);
        }
        $this->escribirEtiqueta($pdf, 113, 55, 'NRC:', $datos['nrc'], 30, 50);
        if (!empty($datos['valor'])) {
            $this->escribirEtiqueta($pdf, 113, 59, 'Actividad económica:', $datos['valor'], 30, 50);
        }
        $direccion = [
            $nombreDepartamentoReceptor,
            $nombreMunicipioReceptor,
            $complementoR,
        ];

        $direccionFormateada = implode(', ', array_filter($direccion));

        $this->escribirEtiqueta($pdf, 113, 67, 'Dirección:', $direccionFormateada, 30, 50);
        $this->escribirEtiqueta($pdf, 113, 75, 'Correo electrónico:', $datos['correo'], 30, 50);
        if (!empty($datos['nombreComercial'])) {
            $this->escribirEtiqueta($pdf, 113, 83, 'Nombre Comercial:', $datos['nombreComercial'], 30, 50);
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
            'codigo'    => 60,
            'precioUni'      => 20,
            'montoDescu'     => 20,
            'noGrabado'      => 20,
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
            'montoDescu'     => 'Descuento por Item',
            'noGrabado'      => 'Otros montos no afectos.',
            'ventaNoSuj'     => 'Ventas No Sujetas',
            'ventaExenta'    => 'Ventas Exentas',
            'ventaGrabada'   => 'Ventas Gravadas'
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
            $codigoProducto = $prod['codigo'] ?? null;

            if (!empty($codigoProducto)) {
                $producto = $this->model->getProducto($codigoProducto);
                if (!empty($producto['nombreProducto'])) {
                    $prod['codigo'] = $producto['nombreProducto'];
                } else {
                    $prod['codigo'] = $codigoProducto;
                }
            } elseif (!empty($prod['descripcion'])) {
                $prod['codigo'] = $prod['descripcion'];
            } else {
                $prod['codigo'] = '---';
            }


            $alturas = [];

            // CALCULAR ALTURA NECESARIA PARA CADA CELDA (especialmente descripción)
            foreach (array_keys($anchos) as $key) {

                $valor = $prod[$key] ?? '';


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
                if ($key === 'numItem' && is_numeric($valor)) {
                    $valor = number_format($valor, 0);
                } elseif ($key === 'cantidad' && is_numeric($valor)) {
                    $valor = number_format($valor, 2);
                } elseif (is_numeric($valor)) {
                    $valor = number_format($valor, 2);
                }

                // Alineación
                $align = 'C';
                if ($key === 'codigo') $align = 'L';
                if (in_array($key, ['precioUni', 'noGrabado', 'montoDescu', 'ventaNoSuj', 'ventaExenta', 'ventaGrabada'])) $align = 'R';

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

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Suma Total de Operaciones:', number_format($datos['subTotalVentas'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas no sujetas:', number_format($datos['descuNoSujeta'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas exentas:', number_format($datos['descuExenta'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas gravadas:', number_format($datos['descuGravada'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'IVA 13%', number_format($datos['tributosValor'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Sub-Total:', number_format($datos['subTotal'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'IVA Percibido:', number_format($datos['ivaPerci1'], 2), 50, 30);
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

        // Ahora el bloque de “Valor en Letras / Condición de la Operación” al lado izquierdo
        $yOperacion = $yResumen; // para que empiece alineado con el resumen

        // Verificar si cabe en la página
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

        if ($retornarRuta) {
            $nombreSeguro = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', 'COMPROBANTE DE CREDITO FISCAL_N_' . $datos['numeroControl']);
            $rutaAbsoluta = realpath(__DIR__ . '/../temp') . '/' . $nombreSeguro . '.pdf';

            $pdf->Output($rutaAbsoluta, 'F'); // Guarda el PDF en la ruta absoluta
            return 'temp/' . $nombreSeguro . '.pdf'; // Devuelve ruta relativa para el email

        } else {
            $pdf->Output('COMPROBANTE DE CREDITO FISCAL_N_' . $datos['numeroControl'] . '.pdf', 'I');
        }
    }
    public function generarPdfCcfJSON($numeroControl, $retornarRuta = false)
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
            'nit' => $datos['nit'],
            'nrc' => $datos['nrc'],
            'nombre' => $datos['cliente'],
            'codActividad' => $datos['actividadEconomica'],
            'descActividad' => $datos['valor'],
            'nombreComercial' => $datos['nombreComercial'],
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
                // Tomar cada campo por separado
                $nombre = $doc['nombre'] ?? null;
                $nit = isset($doc['nit']) && trim($doc['nit']) !== '' ? trim($doc['nit']) : null;
                $docIdentificacion = isset($doc['docIdentificacion']) && trim($doc['docIdentificacion']) !== '' ? trim($doc['docIdentificacion']) : null;
                $tipoServicio = isset($doc['tipoServicio']) ? (int)$doc['tipoServicio'] : null;

                // Verificar si todos están vacíos
                $todosVacios = is_null($nombre) && is_null($nit) && is_null($docIdentificacion) && is_null($tipoServicio);

                $medico = $todosVacios ? null : [
                    'nombre' => $nombre,
                    'nit' => $nit,
                    'docIdentificacion' => $docIdentificacion,
                    'tipoServicio' => $tipoServicio
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
                'codigo' => $doc['codigo'],
                'codTributo' => null,
                'descripcion' => $descripcion,
                'cantidad' => $doc['cantidad'],
                'uniMedida' => $doc['uniMedida'],
                'precioUni' => (float)$doc['precioUni'],
                'montoDescu' => (float)$doc['montoDescu'],
                'ventaNoSuj' => (float)$doc['ventaNoSuj'],
                'ventaExenta' => (float)$doc['ventaExenta'],
                'ventaGravada' => (float)$doc['ventaGrabada'],
                'tributos' => json_decode($doc['tributos'], true),
                'psv' => (float)$doc['psv'],
                'noGravado' => (float)$doc['noGrabado'],
                // 'ivaItem' =>  $doc['ivaItem']
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

        $tributosResumen = [
            (object)[
                'codigo' => '20',
                'descripcion' => 'Impuesto al Valor Agregado 13%',
                'valor' => (float)$datos['tributosValor'],
            ]
        ];
        $pagos = [(object)[
            'codigo' => $datos['pagoCodigo'],
            'montoPago' => (float)$datos['pagoMontoPago'],
            'referencia' => $datos['referencia'],
            'plazo' => $datos['plazo'],
            'periodo' => $datos['periodo'],
        ]];
        $resumen = (object)[
            'totalNoSuj' => (float)$datos['totalNoSujeta'],
            'totalExenta' => (float)$datos['totalExenta'],
            'totalGravada' => (float)$datos['totalGravada'],
            'subTotalVentas' => (float)$datos['subTotalVentas'],
            'descuNoSuj' => (float)$datos['descuNoSujeta'],
            'descuExenta' => (float)$datos['descuExenta'],
            'descuGravada' => (float)$datos['descuGravada'],
            'porcentajeDescuento' => (float)$datos['porcentajeDescuento'],
            'totalDescu' => (float)$datos['totalDescu'],
            'tributos' => $tributosResumen,
            'subTotal' => (float)$datos['subTotal'],
            'ivaPerci1' => (float)$datos['ivaPerci1'],
            'ivaRete1' => (float)$datos['ivaRete1'],
            'reteRenta' => (float)$datos['reteRenta'],
            'montoTotalOperacion' => (float)$datos['montoTotalOperacion'],
            'totalNoGravado' => (float)$datos['totalNoGravado'],
            'totalPagar' => (float)$datos['totalPagar'],
            'totalLetras' => $text,
            // 'totalIva' => $datos['totalIva'],
            'saldoFavor' => (float)$datos['saldoFavor'],
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

        if ($retornarRuta) {
            $nombreSeguro = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', 'COMPROBANTE DE CREDITO FISCAL_N_' . $numeroControl);
            $rutaAbsoluta = realpath(__DIR__ . '/../temp') . '/' . $nombreSeguro . '.json';

            file_put_contents($rutaAbsoluta, json_encode($estructuraDTE, JSON_UNESCAPED_UNICODE));
            return 'temp/' . $nombreSeguro . '.json'; // ruta relativa para usar con PHPMailer

        } else {
            header('Content-Type: application/json');
            echo json_encode($estructuraDTE, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function generarPdfNc($numeroControl, $retornarRuta = false)
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
        $pdf->SetTitle('NOTA DE CREDITO_N_' . $datos['numeroControl'] . '.pdf', 'I');

        $codigoDepartamentoR = $datos['departamento'];
        $codigoMunicipioR = $datos['municipio'];
        $nombreDepartamentoReceptor = $this->model->getNombreDepartamento($codigoDepartamentoR);
        $nombreMunicipioReceptor = $this->model->getNombreMunicipio($codigoMunicipioR, $codigoDepartamentoR);
        $complementoR = $datos['complemento'];

        // Dibujar borde redondeado
        $pdf->SetLineWidth(0.5);

        $pdf->Image(base_url . 'Assets/img/logo.jpg', 15, 0, 35); // Logo

        $this->centrarCelda($pdf, 180, 3, 'DOCUMENTO TRIBUTARIO ELECTRÓNICO', 8, 'B');
        $this->centrarCelda($pdf, 180, 9, 'NOTA DE CRÉDITO', 8, 'B');

        $this->escribirEtiqueta($pdf, 10, 20, 'Código de Generación:', $datos['codigoGeneracion'], 30);
        $this->escribirEtiqueta($pdf, 14, 23, 'Número de Control:', $datos['numeroControl'], 26);

        if (!empty($datos['selloRecibido'])) {
            $this->escribirEtiqueta($pdf, 14, 26, 'Sello de Recepción:', $datos['selloRecibido'], 26);
        } else {
            $this->escribirEtiqueta($pdf, 14, 26, 'Sello de Recepción:', '', 26);
        }

        // codigo QR
        if ($datos['estado'] == 'PROCESADO' || $datos['estado'] == 'INVALIDADO' || $datos['estado'] == 'PROCESADO EN CONTINGENCIA') {
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
        if (!empty($datos['cliente'])) {
            $this->escribirEtiqueta($pdf, 113, 47, 'Nombre o razón social:', $datos['cliente'], 30, 50);
        }
        if (!empty($datos['nit'])) {
            $this->escribirEtiqueta($pdf, 113, 51, 'NIT:', $datos['nit'], 30, 50);
        }
        $this->escribirEtiqueta($pdf, 113, 55, 'NRC:', $datos['nrc'], 30, 50);
        if (!empty($datos['valor'])) {
            $this->escribirEtiqueta($pdf, 113, 59, 'Actividad económica:', $datos['valor'], 30, 50);
        }
        $direccion = [
            $nombreDepartamentoReceptor,
            $nombreMunicipioReceptor,
            $complementoR,
        ];

        $direccionFormateada = implode(', ', array_filter($direccion));

        $this->escribirEtiqueta($pdf, 113, 67, 'Dirección:', $direccionFormateada, 30, 50);
        $this->escribirEtiqueta($pdf, 113, 75, 'Correo electrónico:', $datos['correo'], 30, 50);
        if (!empty($datos['nombreComercial'])) {
            $this->escribirEtiqueta($pdf, 113, 83, 'Nombre Comercial:', $datos['nombreComercial'], 30, 50);
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


        // CUERPO DE CREDITO FISCAL
        // Definición de anchos
        $anchos = [
            'numItem'        => 13,
            'cantidad'       => 15,
            // 'uniMedida'      => 15,
            'codigo'    => 80,
            'precioUni'      => 20,
            'montoDescu'     => 20,
            // 'noGrabado'      => 20,
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
            'montoDescu'     => 'Descuento por Item',
            // 'noGrabado'      => 'Otros montos no afectos.',
            'ventaNoSuj'     => 'Ventas No Sujetas',
            'ventaExenta'    => 'Ventas Exentas',
            'ventaGrabada'   => 'Ventas Gravadas'
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
            $codigoProducto = $prod['codigo'];
            $descripcionNota = $prod['descripcionNotaC'];
            $producto = $this->model->getProducto($codigoProducto);

            if (isset($producto['nombreProducto'])) {
                $prod['codigo'] = $descripcionNota . ' - ' . $producto['nombreProducto'];
            } else if (!empty($producto['descripcion'])) {
                $prod['codigo'] = $descripcionNota . ' - ' .  $prod['descripcion'];
            }

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
                if ($key === 'codigo') $align = 'L';
                if (in_array($key, ['precioUni', 'montoDescu', 'ventaNoSuj', 'ventaExenta', 'ventaGrabada'])) $align = 'R';

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
                if ($key === 'codigo') $align = 'L';
                if (in_array($key, ['precioUni', 'noGrabado', 'montoDescu', 'ventaNoSuj', 'ventaExenta', 'ventaGrabada'])) $align = 'R';

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

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Suma Total de Operaciones:', number_format($datos['subTotalVentas'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas no sujetas:', number_format($datos['descuNoSujeta'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas exentas:', number_format($datos['descuExenta'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global Desc., Rebajas y otros a ventas gravadas:', number_format($datos['descuGravada'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'IVA 13%', number_format($datos['tributosValor'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Sub-Total:', number_format($datos['subTotal'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'IVA Percibido:', number_format($datos['ivaPerci1'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'IVA Retenido:', number_format($datos['ivaRete1'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto Total de la Operación:', number_format($datos['montoTotalOperacion'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        // Ahora el bloque de “Valor en Letras / Condición de la Operación” al lado izquierdo
        $yOperacion = $yResumen; // para que empiece alineado con el resumen

        // Verificar si cabe en la página
        if ($yOperacion + 10 > $espacioDisponible) {
            $pdf->AddPage();
            $yOperacion = 10;
        }

        $pdf->RoundedRect(10, $yOperacion, 110, 20, 1, '1111');
        $yEtiqueta = $yResumen + 3;
        $altoFila = 5;

        /// Total en dolares
        $total = $datos['montoTotalOperacion'];
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

        if ($retornarRuta) {
            $nombreSeguro = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', 'NOTA DE CREDITO_N_' . $datos['numeroControl']);
            $rutaAbsoluta = realpath(__DIR__ . '/../temp') . '/' . $nombreSeguro . '.pdf';

            $pdf->Output($rutaAbsoluta, 'F'); // Guarda el PDF en la ruta absoluta
            return 'temp/' . $nombreSeguro . '.pdf'; // Devuelve ruta relativa para el email

        } else {
            $pdf->Output('NOTA DE CREDITO_N_' . $datos['numeroControl'] . '.pdf', 'I');
        }
    }
    public function generarPdfNcfJSON($numeroControl, $retornarRuta = false)
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
        ];

        $receptor = (object)[
            'nit' => $datos['nit'],
            'nrc' => $datos['nrc'],
            'nombre' => $datos['cliente'],
            'codActividad' => $datos['actividadEconomica'],
            'descActividad' => $datos['valor'],
            'nombreComercial' => $datos['nombreComercial'],
            'direccion' => [
                'departamento' => $datos['departamento'],
                'municipio' => $datos['municipio'],
                'complemento' => $datos['complemento'],
            ],
            'telefono' => $datos['telefono'], //opcional
            'correo' => $datos['correo']
        ];

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
                'descripcion' => $doc['descripcionNotaC'] . ' - ' . $descripcion,
                'precioUni' => (float)$doc['precioUni'],
                'montoDescu' => (float)$doc['montoDescu'],
                'ventaNoSuj' => (float)$doc['ventaNoSuj'],
                'ventaExenta' => (float)$doc['ventaExenta'],
                'ventaGravada' => (float)$doc['ventaGrabada'],
                'tributos' => json_decode($doc['tributos'], true),
            ];
        }

        /// Total en dolares
        $total = $datos['montoTotalOperacion'];
        $fmt = new NumberFormatter("es_ES", NumberFormatter::SPELLOUT);
        $montoEntero = floor($total);
        $montoDecimal = round(($total - $montoEntero) * 100);
        $textEntero = strtoupper($fmt->format($montoEntero));
        $textDecimal = str_pad($montoDecimal, 2, "0", STR_PAD_LEFT);
        $text = $textEntero . " CON " . $textDecimal . "/100 USD";
        /// Total en dolares

        $tributosResumen = [
            (object)[
                'codigo' => '20',
                'descripcion' => 'Impuesto al Valor Agregado 13%',
                'valor' => (float)$datos['tributosValor'],
            ]
        ];
        // $pagos = [(object)[
        //     'codigo' => $datos['pagoCodigo'],
        //     'montoPago' => (float)$datos['pagoMontoPago'],
        //     'referencia' => $datos['referencia'],
        //     'plazo' => $datos['plazo'],
        //     'periodo' => $datos['periodo'],
        // ]];
        $resumen = (object)[
            'totalNoSuj' => (float)$datos['totalNoSujeta'],
            'totalExenta' => (float)$datos['totalExenta'],
            'totalGravada' => (float)$datos['totalGravada'],
            'subTotalVentas' => (float)$datos['subTotalVentas'],
            'descuNoSuj' => (float)$datos['descuNoSujeta'],
            'descuExenta' => (float)$datos['descuExenta'],
            'descuGravada' => (float)$datos['descuGravada'],
            // 'porcentajeDescuento' => (float)$datos['porcentajeDescuento'],
            'totalDescu' => (float)$datos['totalDescu'],
            'tributos' => $tributosResumen,
            'subTotal' => (float)$datos['subTotal'],
            'ivaPerci1' => (float)$datos['ivaPerci1'],
            'ivaRete1' => (float)$datos['ivaRete1'],
            'reteRenta' => (float)$datos['reteRenta'],
            'montoTotalOperacion' => (float)$datos['montoTotalOperacion'],
            'totalLetras' => $text,
            'condicionOperacion' => $datos['condicionOperacion'],
            // 'totalPagar' => (float)$datos['totalPagar'],
            // 'totalIva' => $datos['totalIva'],
            // 'saldoFavor' => (float)$datos['saldoFavor'],
            // 'pagos' => $pagos,
            // 'numPagoElectronico' => $datos['numPagoElectronico'],
        ];

        $extension = null;
        $apendice = null;
        $estructuraDTE = [
            "identificacion" => $identificacion,
            "documentoRelacionado" => $documentoRelacionado,
            "emisor" => $emisor,
            "receptor" => $receptor,
            'ventaTercero' => $ventaTercero,
            'cuerpoDocumento' => $cuerpoDocumento,
            'resumen' => $resumen,
            'extension' => $extension,
            'apendice' => $apendice,
            'selloRecibido' => $datos['selloRecibido']
        ];

        if ($retornarRuta) {
            $nombreSeguro = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', 'NOTA DE CREDITO_N_' . $numeroControl);
            $rutaAbsoluta = realpath(__DIR__ . '/../temp') . '/' . $nombreSeguro . '.json';

            file_put_contents($rutaAbsoluta, json_encode($estructuraDTE, JSON_UNESCAPED_UNICODE));
            return 'temp/' . $nombreSeguro . '.json'; // ruta relativa para usar con PHPMailer

        } else {
            header('Content-Type: application/json');
            echo json_encode($estructuraDTE, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function generarPdfFse($numeroControl, $retornarRuta = false)
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
        $pdf->SetTitle('FACTURA_SUJETO_EXCLUIDO_N_' . $datos['numeroControl'] . '.pdf', 'I');

        $codigoDepartamentoR = $datos['departamento'];
        $codigoMunicipioR = $datos['municipio'];
        $nombreDepartamentoReceptor = $this->model->getNombreDepartamento($codigoDepartamentoR);
        $nombreMunicipioReceptor = $this->model->getNombreMunicipio($codigoMunicipioR, $codigoDepartamentoR);
        $complementoR = $datos['complemento'];
        //borde redondeado
        $pdf->SetLineWidth(0.5);

        $pdf->Image(base_url . 'Assets/img/logo.jpg', 15, 0, 35); // Logo

        $this->centrarCelda($pdf, 180, 3, 'DOCUMENTO TRIBUTARIO ELECTRÓNICO', 8, 'B');
        $this->centrarCelda($pdf, 180, 9, 'FACTURA SUJETO EXCLUIDO', 8, 'B');

        $this->escribirEtiqueta($pdf, 10, 20, 'Código de Generación:', $datos['codigoGeneracion'], 30);
        $this->escribirEtiqueta($pdf, 14, 23, 'Número de Control:', $datos['numeroControl'], 26);

        if (!empty($datos['selloRecibido'])) {

            $this->escribirEtiqueta($pdf, 14, 26, 'Sello de Recepción:', $datos['selloRecibido'], 26);
        } else {
            $this->escribirEtiqueta($pdf, 14, 26, 'Sello de Recepción:', '', 26);
        }


        if ($datos['estado'] == 'PROCESADO' || $datos['estado'] == 'INVALIDADO' || $datos['estado'] == 'PROCESADO EN CONTINGENCIA') {
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
        $direccion = [
            $nombreDepartamentoReceptor,
            $nombreMunicipioReceptor,
            $complementoR,
        ];

        $direccionFormateada = implode(', ', array_filter($direccion));

        $this->escribirEtiqueta($pdf, 113, 67, 'Dirección:', $direccionFormateada, 30, 50);

        $yRectAsociado = 85 + 5;


        // CUERPO DE FACTURA
        // Definición de anchos
        $anchos = [
            'numItem'        => 20,
            'cantidad'       => 15,
            'codigo'    => 70,
            'precioUni'      => 30,
            'montoDescu'     => 30,
            'compra'     => 30,
            // 'ventaGrabada'   => 17
        ];

        // Encabezados legibles
        $encabezados = [
            'numItem'        => 'N°',
            'cantidad'       => 'Cantidad',
            'codigo'    => 'Descripción',
            'precioUni'      => 'Precio Unitario',
            'montoDescu'     => 'Descuento por Item',
            'compra'     => 'Ventas',
        ];

        // Estilos de tabla
        $pdf->SetLineWidth(0.05); // Ultra delgado
        $pdf->SetFillColor(230, 230, 230); // Fondo gris claro para encabezado

        $altoEncabezado = 10;
        $yInicial = $yRectAsociado + 5;
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
            $codigoProducto = $prod['codigo'] ?? null;

            if (!empty($codigoProducto)) {
                $producto = $this->model->getProducto($codigoProducto);
                if (!empty($producto['nombreProducto'])) {
                    $prod['codigo'] = $producto['nombreProducto'];
                } else {
                    $prod['codigo'] = $codigoProducto;
                }
            } elseif (!empty($prod['descripcion'])) {
                $prod['codigo'] = $prod['descripcion'];
            } else {
                $prod['codigo'] = '---';
            }

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
                if ($key === 'codigo') $align = 'L';
                if (in_array($key, ['precioUni', 'montoDescu', 'compra'])) $align = 'R';

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
                if ($key === 'codigo') $align = 'L';
                if (in_array($key, ['precioUni', 'montoDescu', 'compra'])) $align = 'R';

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

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Sumatoria de ventas:', number_format($datos['totalCompra'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Monto global de Descuento, Bonificación, Rebajas y otros al total de operaciones.:', number_format($datos['descu'], 2), 50, 30);
        $yEtiqueta += $altoFila;
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Sub-Total:', number_format($datos['subTotal'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'IVA Retenido:', number_format($datos['ivaRete1'], 2), 50, 30);
        $yEtiqueta += $altoFila;

        $this->escribirEtiqueta($pdf, 130, $yEtiqueta, 'Retención de Renta:', number_format($datos['reteRenta'], 2), 50, 30);
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

        if ($retornarRuta) {
            $nombreSeguro = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', 'FACTURA_SUJETO_EXCLUIDO_N_' . $datos['numeroControl']);
            $rutaAbsoluta = realpath(__DIR__ . '/../temp') . '/' . $nombreSeguro . '.pdf';

            $pdf->Output($rutaAbsoluta, 'F'); // Guarda el PDF en la ruta absoluta
            return 'temp/' . $nombreSeguro . '.pdf'; // Devuelve ruta relativa para el email

        } else {
            $pdf->Output('FACTURA_SUJETO_EXCLUIDO_N_' . $datos['numeroControl'] . '.pdf', 'I'); // Lo muestra en navegador
        }
    }
    public function generarPdfFseJSON($numeroControl, $retornarRuta = false)
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

        $emisor = (object) [
            'nit' => $empresa['nit'],
            'nrc' => $empresa['nrc'],
            'nombre' => $empresa['nombre'],
            'codActividad' => $empresa['codActividad'],
            'descActividad' => $empresa['descActividad'],
            "direccion" => [
                'departamento' => $empresa['departamento'],
                'municipio' => $empresa['municipio'],
                'complemento' => $empresa['direccion'],
            ],
            'telefono' => $empresa['telefono'],
            'codEstableMH' => $datosFactura['codEstableMH'],
            'codEstable' => $datosFactura['codEstable'],
            'codPuntoVentaMH' => $datosFactura['codPuntoVentaMH'],
            'codPuntoVenta' => $datosFactura['codPuntoVenta'],
            'correo' => $empresa['correo']
        ];

        $sujetoExcluido = (object)[
            'tipoDocumento' => $datos['identificacion'],
            'numDocumento' => $datos['numDocumento'],
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
                'cantidad' => $doc['cantidad'],
                'codigo' => $doc['codigo'],
                'uniMedida' => $doc['uniMedida'],
                'descripcion' => $descripcion,
                'precioUni' => (float)$doc['precioUni'],
                'montoDescu' => (float)$doc['montoDescu'],
                'compra' =>  (float)$doc['compra']
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

        $detallePagos = $this->model->getDetallesPago($datos['numeroControl']);
        $pagos = [];
        foreach ($detallePagos as $row) {
            $pagos[] = [
                'codigo' => $row['codigoId'],
                'montoPago' => (float)$row['montoPago'],
                'referencia' => $row['referencia'],
                'plazo' => null,
                'periodo' => null,
            ];
        }
        $resumen = (object)[
            'totalCompra' => (float)$datos['totalCompra'],
            'descu' => (float)$datos['descu'],
            'totalDescu' => (float)$datos['totalDescu'],
            'subTotal' => (float)$datos['subTotal'],
            'ivaRete1' => (float)$datos['ivaRete1'],
            'reteRenta' => (float)$datos['reteRenta'],
            'totalPagar' => (float)$datos['totalPagar'],
            'totalLetras' => $text,
            'condicionOperacion' => $datos['condicionOperacion'],
            'pagos' => $pagos,
            'observaciones' => $datos['observacionesResumen'],
        ];

        $apendice = null;
        $estructuraDTE = [
            "identificacion" => $identificacion,
            "emisor" => $emisor,
            "sujetoExcluido" => $sujetoExcluido,
            'cuerpoDocumento' => $cuerpoDocumento,
            'resumen' => $resumen,
            'apendice' => $apendice,
            'selloRecibido' => $datos['selloRecibido']
        ];

        if ($retornarRuta) {
            $nombreSeguro = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', 'FACTURA_SUJETO_EXCLUIDO_N_' . $numeroControl);
            $rutaAbsoluta = realpath(__DIR__ . '/../temp') . '/' . $nombreSeguro . '.json';

            file_put_contents($rutaAbsoluta, json_encode($estructuraDTE, JSON_UNESCAPED_UNICODE));
            return 'temp/' . $nombreSeguro . '.json'; // ruta relativa para usar con PHPMailer

        } else {
            header('Content-Type: application/json');
            echo json_encode($estructuraDTE, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
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
    
    // evento de invalidacion
    public function seleccionarTipoI()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->searchTipoIn($query);
        $this->sendJsonResponse($data);
    }

    public function generarEv($numeroControl)
    {
        $data = $this->model->datosFe($numeroControl);
        $this->sendJsonResponse($data);
    }

    public function generarInvalidacion()
    {
        date_default_timezone_set('America/El_Salvador');
        header('Content-Type: application/json');

        try {
            $this->model->iniciarTransaccion();
            // Traer los datos del modelo (como lo haces en generarPdfFe)
            $facturacion = new Facturacion;
            $numeroControl = $_POST['numeroControl'];
            $tipoInvalidacion = isset($_POST['selectTipoI']) ? $_POST['selectTipoI'] : null;
            $motivoInvalidacion = $_POST['motInvalidacion'];
            $reemplazo = $_POST['codigoGeneracionRemmplazo'];
            $datosFactura = $this->facturacionModel->datosFactura();
            $codigoGeneracion = $facturacion->codigoGeneracion();
            $empresa = $this->model->getEmpresa();
            $datos = $this->model->datosFe($numeroControl);
            $codigoUsuario = trim($_SESSION['codigoUsuario']);
            $nombreUsuario = trim($_SESSION['nombreCompleto']);
            $tipoIdentifiUsuario = trim($_SESSION['tipoIdentificacion']);
            $numeroIdentifiUsuario = trim($_SESSION['numeroIdentificacion']);

            $fecha = date('Y-m-d');
            $hora = date('H:i:s');


            // armar json
            $identificacion = (object)[
                'version' => 2,
                'ambiente' => $datosFactura['ambiente'],
                'codigoGeneracion' => $codigoGeneracion,
                'fecAnula' => $fecha,
                'horAnula' => $hora,
            ];

            $emisor = (object) [
                'nit' => $empresa['nit'],
                'nombre' => $empresa['nombre'],
                'tipoEstablecimiento' => $empresa['tipoEstablecimiento'],
                'nomEstablecimiento' => $empresa['nombre'],
                'codEstableMH' => $datosFactura['codEstableMH'],
                'codEstable' => $datosFactura['codEstable'],
                'codPuntoVentaMH' => $datosFactura['codPuntoVentaMH'],
                'codPuntoVenta' => $datosFactura['codPuntoVenta'],
                'telefono' => $empresa['telefono'],
                'correo' => $empresa['correo'],
            ];

            $montoTotalOperacion = 0;

            if (empty($tipoInvalidacion)) {
                $msg = "Seleccione tipo de invalidación";
                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
            } else if (empty($motivoInvalidacion)) {
                $msg = "Escriba el motivo por el cual se invalida el documento";
                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
            } else if (strlen($motivoInvalidacion) < 5 || strlen($motivoInvalidacion) > 250) {
                $msg = "El motivo de invalidación debe tener entre 5 y 250 caracteres";
                $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
            }


            if ($datos['tipoDte'] == '01' || $datos['tipoDte'] == '03') {
                $montoTotalOperacion = $datos['montoTotalOperacion'];
            } else if ($datos['tipoDte'] == '05') {
                $montoTotalOperacion = 0;
            }

            if ($tipoInvalidacion == 1 || $tipoInvalidacion == 3) {
                if (empty($reemplazo)) {
                    $msg = "Debe agregar el documento que reemplaza esta factura";
                    $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                } else {
                    $expresionRegular = "/^[A-F0-9]{8}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{12}$/";
                    $codigoGeneracionR = $reemplazo;
                    if (!preg_match($expresionRegular, $reemplazo)) {
                        $msg = "El codigo de generación que reemplaza no tiene el formato requerido";
                        $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                    } else {
                        $codigoGeneracionR = $reemplazo;
                    }
                }
            } else if ($tipoInvalidacion == 2 || $datos['tipoDte'] == '05') {
                $codigoGeneracionR = null;
            }

            if (empty($datos['identificacion'])) {
                $tipoDocumento = '36';
                $numDocumento = $datos['nit'];
            } else {
                $tipoDocumento = $datos['identificacion'];
                $numDocumento = $datos['numDocumento'];
            }

            $documento = (object) [
                "tipoDte" => $datos['tipoDte'],
                "codigoGeneracion" => $datos['codigoGeneracion'],
                "selloRecibido" => $datos['selloRecepcion'],
                "numeroControl" => $datos['numeroControl'],
                "fecEmi" => $datos['fechaEmision'],
                "montoIva" => (float)$montoTotalOperacion,
                "codigoGeneracionR" => $codigoGeneracionR,
                "tipoDocumento" => $tipoDocumento,
                "numDocumento" => $numDocumento,
                "nombre" => $datos['cliente'],
                "telefono" => $datos['telefono'] ?? null,
                "correo" => $datos['correo'] ?? null,
            ];

            $motivo = (object) [
                "tipoAnulacion" => (int)$tipoInvalidacion,
                "motivoAnulacion" => $motivoInvalidacion,
                "nombreResponsable" => $empresa['nombre'], //emisor
                "tipDocResponsable" => '36', //emisor
                "numDocResponsable" => $empresa['nit'], //emisor
                "nombreSolicita" => $nombreUsuario, // quien envia usuario
                "tipDocSolicita" => $tipoIdentifiUsuario, // quien envia usuario
                "numDocSolicita" => $numeroIdentifiUsuario //quien envia usuario
            ];

            $estructuraInvalidar = [
                "identificacion" => $identificacion,
                "emisor" => $emisor,
                "documento" => $documento,
                "motivo" => $motivo
            ];

            // var_dump($estructuraInvalidar);exit;
            $token = $facturacion->obtenerYGuardarToken();
            if (!isset($token['token'])) {
                // Mostrar o devolver el mensaje de error
                $this->sendJsonResponse(['status' => 'error', 'message' => $token['message']]);
                return;
            }


	$empresaId = 'MacastA01';
            $jsonFirmado = $facturacion->firmarJson($estructuraInvalidar, $empresa['nit'], $empresaId);
            if (!$jsonFirmado) {
                throw new Exception("Error al firmar el JSON o generarlo");
            }

            $postData = json_encode([
                'ambiente' => $datosFactura['ambiente'],
                'idEnvio' => 1,
                'version' => 2,
                'documento' => $jsonFirmado['body']
            ], JSON_UNESCAPED_UNICODE);


            $ch = curl_init('https://api.dtes.mh.gob.sv/fesv/anulardte');
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

            $decoded = json_decode($response, true);

            if ($decoded && isset($decoded['estado']) && $decoded['estado'] === 'PROCESADO' && !empty($decoded['selloRecibido'])) {
                $fhProcesamientoOriginal = $decoded['fhProcesamiento'];
                $fhProcesamiento = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $fhProcesamientoOriginal)));
                $estado = 'INVALIDADO';
                $observaciones = is_array($decoded['observaciones']) ? json_encode($decoded['observaciones'], JSON_UNESCAPED_UNICODE) : $decoded['observaciones'];

                $invalidar = $this->model->registrarInvalidar(
                    $decoded['version'],
                    $decoded['ambiente'],
                    $decoded['versionApp'],
                    $decoded['estado'],
                    $decoded['codigoGeneracion'],
                    $decoded['selloRecibido'],
                    $fhProcesamiento,
                    $decoded['codigoMsg'],
                    $decoded['descripcionMsg'],
                    $observaciones,
                    $fecha,
                    $hora,
                    $jsonFirmado['body'],
                    $documento->codigoGeneracion
                );

                $this->model->actualizarEstadoDte($estado, $documento->codigoGeneracionR, $codigoUsuario, $motivo->tipoAnulacion, $motivo->motivoAnulacion, $documento->codigoGeneracion);

                if (!$invalidar) {
                    // throw new Exception("No se pudo registrar el lote en la base de datos.");
                    $msg = "No se pudo registrar el lote en la base de datos.";
                    $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
                }

                $this->model->confirmarTransaccion();
                $this->sendJsonResponse([
                    'status' => 'success',
                    'emision' => $decoded
                ]);
                return;
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => $response]);
            }
        } catch (Exception $e) {
            $this->model->revertirTransaccion();
            $msg = $e->getMessage();
            $this->sendJsonResponse([
                'status' => 'error',
                'message' => $msg
            ]);
        }
    }
    
    // enviar correo
    public function generarEnviar($numeroControl)
    {
        $data = $this->model->datosFe($numeroControl);
        $this->sendJsonResponse($data);
    }

        public function enviarFacturaPorCorreo()
    {
        $emailCliente = $_POST['correoE'];
        $numeroControl = $_POST['control'];
        // Generar archivos temporales

        $tipoDte = $_POST['dte'];
        $pdfRelativo = '';
        $jsonRelativo = '';
        switch ($tipoDte) {
            case '01':
                $pdfRelativo = $this->generarPdfFe($numeroControl, true);
                $jsonRelativo = $this->generarPdfFeJSON($numeroControl, true);
                break;
            case '03':
                $pdfRelativo = $this->generarPdfCcf($numeroControl, true);
                $jsonRelativo = $this->generarPdfCcfJSON($numeroControl, true);
                break;
            case '05':
                $pdfRelativo = $this->generarPdfNc($numeroControl, true);
                $jsonRelativo = $this->generarPdfNcfJSON($numeroControl, true);
                break;
            case '14':
                $pdfRelativo = $this->generarPdfFse($numeroControl, true);
                $jsonRelativo = $this->generarPdfFseJSON($numeroControl, true);
                break;
            default:
                # code...
                break;
        }

        $pdfPath = realpath(__DIR__ . '/../' . $pdfRelativo);   // <-- ruta completa
        $jsonPath = realpath(__DIR__ . '/../' . $jsonRelativo); // <-- ruta completa

        $email = $emailCliente;

        require 'vendor/autoload.php';
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
            $mail->addAddress($email);

            $mail->addAttachment($pdfPath, $numeroControl . '.pdf');
            $mail->addAttachment($jsonPath, $numeroControl . '.json');

            if (!file_exists($pdfPath) || !file_exists($jsonPath)) {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Archivos PDF o JSON no encontrados.']);
                return;
            }


            $mail->isHTML(true);
            $mail->Subject = $numeroControl;
            $mail->Body = "
                            <p>Estimado cliente,</p>
                            <p>Adjunto encontrará su dte en formato <strong>PDF</strong> y <strong>JSON</strong>.</p>
                            <p>Número de control: <strong>$numeroControl</strong></p>
                            <p>Gracias por su preferencia.</p>
                        ";


            $mail->send();

            // Limpieza
            unlink($pdfPath);
            unlink($jsonPath);

            $this->sendJsonResponse(['status' => 'success', 'message' => 'Correo enviado correctamente.']);
        } catch (Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Error al enviar correo: ' . $mail->ErrorInfo]);
            exit;
        }
    }
}