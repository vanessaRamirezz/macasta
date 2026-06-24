<?php
class ListadosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }


    public function getFe($start, $length, $search = "")
    {
        $searchQuery = "WHERE e.tipoDte = '01' AND e.estado IN ('PROCESADO', 'INVALIDADO', 'PROCESADO EN CONTINGENCIA') AND ambiente = '01'";  // siempre filtramos tipoDte = '01'
        $params = [];

        if (!empty($search)) {
            $searchQuery .= " AND c.nombreCliente LIKE :search";
            $params[':search'] = "%$search%"; // búsqueda parcial
        }

        $sql = "SELECT 
                e.id,
                e.numeroControl AS correlativo,
                e.codigoGeneracion AS codigo,
                c.nombreCliente AS cliente,
                e.estado,
                DATE_FORMAT(e.fechaEmision, '%d/%m/%Y') AS fecha
            FROM dte_encabezado e
            LEFT JOIN clientes c ON e.receptor = c.codigoCliente
            $searchQuery
            ORDER BY e.fechaEmision DESC
            LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        $sqlTotal = "SELECT COUNT(*) AS total 
                 FROM dte_encabezado e
                 LEFT JOIN clientes c ON e.receptor = c.codigoCliente
                 $searchQuery";

        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        return [
            'dteFe' => $data,
            'total' => $total
        ];
    }

    public function getCcf($start, $length, $search = "")
    {
        $searchQuery = "WHERE e.tipoDte = '03' AND e.estado IN ('PROCESADO','INVALIDADO', 'PROCESADO EN CONTINGENCIA') AND ambiente = '00'";  // siempre filtramos tipoDte = '01'
        $params = [];

        if (!empty($search)) {
            $searchQuery .= " AND c.nombreCliente LIKE :search";
            $params[':search'] = "%$search%"; // búsqueda parcial
        }

        $sql = "SELECT 
                e.id,
                e.numeroControl AS correlativo,
                e.codigoGeneracion AS codigo,
                c.nombreCliente AS cliente,
                e.estado,
                DATE_FORMAT(e.fechaEmision, '%d/%m/%Y') AS fecha
            FROM dte_encabezado e
            LEFT JOIN clientes c ON e.receptor = c.codigoCliente
            $searchQuery
            ORDER BY e.fechaEmision DESC
            LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        $sqlTotal = "SELECT COUNT(*) AS total 
                 FROM dte_encabezado e
                 LEFT JOIN clientes c ON e.receptor = c.codigoCliente
                 $searchQuery";

        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        return [
            'dteCcf' => $data,
            'total' => $total
        ];
    }

    public function getNc($start, $length, $search = "")
    {
        $searchQuery = "WHERE e.tipoDte = '05' AND e.estado IN ('PROCESADO','INVALIDADO', 'PROCESADO EN CONTINGENCIA') AND ambiente = '01'";  // siempre filtramos tipoDte = '01'
        $params = [];

        if (!empty($search)) {
            $searchQuery .= " AND c.nombreCliente LIKE :search";
            $params[':search'] = "%$search%"; // búsqueda parcial
        }

        $sql = "SELECT 
                e.id,
                e.numeroControl AS correlativo,
                e.codigoGeneracion AS codigo,
                c.nombreCliente AS cliente,
                e.estado,
                DATE_FORMAT(e.fechaEmision, '%d/%m/%Y') AS fecha
            FROM dte_encabezado e
            LEFT JOIN clientes c ON e.receptor = c.codigoCliente
            $searchQuery
            ORDER BY e.fechaEmision DESC
            LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        $sqlTotal = "SELECT COUNT(*) AS total 
                 FROM dte_encabezado e
                 LEFT JOIN clientes c ON e.receptor = c.codigoCliente
                 $searchQuery";

        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        return [
            'dteNc' => $data,
            'total' => $total
        ];
    }
    
        public function getFse($start, $length, $search = "")
    {
        $searchQuery = "WHERE e.tipoDte = '14' AND e.estado IN ('PROCESADO','INVALIDADO', 'PROCESADO EN CONTINGENCIA') AND ambiente = '01'";
        $params = [];

        if (!empty($search)) {
            $searchQuery .= " AND c.nombreCliente LIKE :search";
            $params[':search'] = "%$search%"; // búsqueda parcial
        }

        $sql = "SELECT 
                e.id,
                e.numeroControl AS correlativo,
                e.codigoGeneracion AS codigo,
                c.nombreCliente AS cliente,
                e.estado,
                DATE_FORMAT(e.fechaEmision, '%d/%m/%Y') AS fecha
            FROM dte_encabezado e
            LEFT JOIN clientes c ON e.receptor = c.codigoCliente
            $searchQuery
            ORDER BY e.fechaEmision DESC
            LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        $sqlTotal = "SELECT COUNT(*) AS total 
                 FROM dte_encabezado e
                 LEFT JOIN clientes c ON e.receptor = c.codigoCliente
                 $searchQuery";

        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        return [
            'dteFse' => $data,
            'total' => $total
        ];
    }
    

    public function datosFe(string $numeroControl)
    {
        $sql = 'SELECT e.*,
                        v.valor AS modelo,
                        c.nombreCliente AS cliente,
                        c.tipoIdentificacion AS identificacion,
                        c.numeroIdentificacion AS numDocumento,
                        c.nrc AS nrc,
                        c.nit AS nit,
                        c.codigoActividadEconomica AS actividadEconomica,
                        a.valor,
                        c.departamento,
                        c.municipio,
                        c.complemento,
                        c.numeroTelefonoCliente AS telefono,
                        c.correo,
                        ht.nit AS nitTercero,
                        e.selloRecepcion AS selloRecibido,
                        ht.nombre AS nombreTercero,
                        c.nombreComercial,
                        e.id
        FROM dte_encabezado e
        LEFT JOIN modeloFacturacion v ON e.tipoModelo = v.codigo
        LEFT JOIN clientes c ON e.receptor = c.codigoCliente
        LEFT JOIN historialVentaTerceros ht ON e.numeroControl = ht.codigoDeControl
        LEFT JOIN actividadesEconomicas a ON c.codigoActividadEconomica = a.codigo
        WHERE e.id = ?';
        $datos = array($numeroControl);
        return $this->select($sql, $datos);
    }

    public function getDocRelaionados(string $numeroControl)
    {
        $sql = 'SELECT * FROM historialDocumentosRelacionadosDTE
        WHERE numeroDeControl = ?';
        $datos = array($numeroControl);
        return $this->selectAll($sql, $datos);
    }

    public function getEmpresa()
    {
        $sql = "SELECT * FROM empresa";
        $data = $this->select($sql);
        return $data;
    }

    public function getDocRelaionadosAsociados(string $numeroControl)
    {
        $sql = 'SELECT * FROM historialDocAsociados
        WHERE numeroDeControl = ?';
        $datos = array($numeroControl);
        return $this->selectAll($sql, $datos);
    }

    public function getDTECuerpo(string $numeroControl)
    {
        $sql = 'SELECT * FROM dte_cuerpo
        WHERE id_dte_encabezado = ?';
        $datos = array($numeroControl);
        return $this->selectAll($sql, $datos);
    }

    public function getProducto(string $codigoProducto)
    {
        $sql = 'SELECT * FROM productos
        WHERE codigoProducto = ?';
        $datos = array($codigoProducto);
        return $this->select($sql, $datos);
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

    public function getNombreCondicionOperacion($codigo)
    {
        $sql = "SELECT nombre FROM condicionOperacion WHERE codigo = ?";
        $params = [$codigo];
        $resultado = $this->select($sql, $params);

        return $resultado ? $resultado['nombre'] : 'Desconocido';
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
    
    // evento de invalidacion
    public function searchTipoIn($query)
    {
        $sql = "SELECT codigoI AS codigo,
        descripcionI AS nombre
        FROM tipoInvalidacion
        WHERE descripcionI LIKE ?
        LIMIT 5 ";
        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }
    
    public function registrarInvalidar(
        $versionI,
        $ambiente,
        $versionApp,
        $estado,
        $codigoGeneracionId,
        $selloRecibido,
        $fhProcesamiento,
        $codigoMsg,
        $descripcionMsg,
        $observaciones,
        $fechaAnulacion,
        $horaAnulacion,
        $firma,
        $codigoGeneracionDteInv
    ) {
        $sql = "INSERT INTO dteInvalidados ( versionI, ambiente, versionApp, estado, codigoGeneracionId, selloRecibido, fhProcesamiento, codigoMsg, descripcionMsg, observaciones, fechaAnulacion, horaAnulacion, jsonFirmadoI, codigoGeneracionDteInv) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $datos = array(
            $versionI,
            $ambiente,
            $versionApp,
            $estado,
            $codigoGeneracionId,
            $selloRecibido,
            $fhProcesamiento,
            $codigoMsg,
            $descripcionMsg,
            $observaciones,
            $fechaAnulacion,
            $horaAnulacion,
            $firma,
            $codigoGeneracionDteInv
        );
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }

        return $res;
    }
    
     public function actualizarEstadoDte(string $estado, $codigoReemplazo, $idUsuario, $tipoAnulacion, $motivoAnulacion, string $codigoGeneracion)
    {
        $sql = "UPDATE dte_encabezado SET estado = ?, codigoGeneracionReemplazo = ?, idUsuario = ?, tipoAnulacion = ?, motivoInvalidacion = ? WHERE codigoGeneracion = ?";
        $datos = array($estado, $codigoReemplazo, $idUsuario, $tipoAnulacion, $motivoAnulacion, $codigoGeneracion);
        return $this->guardar($sql, $datos);
    }
    
     public function getDetallesPago(string $numeroControl)
    {
        $sql = 'SELECT * FROM detallesPagos
        WHERE numeroControlId = ?';
        $datos = array($numeroControl);
        return $this->selectAll($sql, $datos);
    }
}

// 
// // Mostrar todos los errores, warnings y notices
// error_reporting(E_ALL);

// // Mostrar errores directamente en la salida (pantalla)
// ini_set('display_errors', 1);

// // Opcional: mostrar también errores fatales en errores de inicio
// ini_set('display_startup_errors', 1);