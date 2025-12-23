<?php

class HistorialContingenciaModel extends Query
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getEventosContingenciaValidos()
    {
        $ids = [];

        // Evento activo (solo uno máximo)
        $sqlActivo = "SELECT id FROM historialEventoContingencia WHERE estadoContingenciaId = 1 LIMIT 1";
        $activo = $this->select($sqlActivo);
        if ($activo) {
            $ids[] = $activo['id'];
        }

        // Último evento finalizado
        $sqlCerrado = "SELECT id FROM historialEventoContingencia 
                   WHERE estadoContingenciaId != 1 AND fechaFin IS NOT NULL 
                   ORDER BY fechaFin DESC LIMIT 1";
        $cerrado = $this->select($sqlCerrado);
        if ($cerrado) {
            $ids[] = $cerrado['id'];
        }

        return $ids;
    }

    public function listarDtesJson()
    {
        // Obtener eventos válidos
        $idsContingencia = $this->getEventosContingenciaValidos();

        if (empty($idsContingencia)) {
            return []; // No hay eventos válidos
        }

        // Preparar placeholders
        $placeholders = implode(',', array_fill(0, count($idsContingencia), '?'));
        $params = $idsContingencia;

        // Consulta de DTE
        $sql = "SELECT
                codigoGeneracion AS codigo,
                tipoDte AS tipo
            FROM dte_encabezado
            WHERE tipoDte IN ('01','03','05','14') 
              AND tipoOperacion = 2 
              AND (selloRecepcion IS NULL OR selloRecepcion = '') 
              AND eventoContingenciaId IN ($placeholders)";

        return $this->selectAll($sql, $params);
    }

    public function getEventoCerradoMasReciente()
    {
        $sql = "SELECT * FROM historialEventoContingencia 
            WHERE estadoContingenciaId != 1 AND fechaFin IS NOT NULL 
            ORDER BY fechaFin DESC LIMIT 1";
        return $this->select($sql);
    }


    // public function listarFe($start, $length, $search = "")
    // {
    //     // 1. Obtener eventos válidos
    //     $idsContingencia = $this->getEventosContingenciaValidos();

    //     if (empty($idsContingencia)) {
    //         return ['encabezadoFe' => [], 'total' => 0]; // No hay eventos válidos
    //     }

    //     // 2. Preparar placeholders dinámicos
    //     $placeholders = implode(',', array_fill(0, count($idsContingencia), '?'));
    //     $params = $idsContingencia;

    //     // 3. Agregar condiciones básicas
    //     $searchQuery = "WHERE e.tipoDte = '01' 
    //                 AND e.tipoOperacion = 2 
    //                 AND (e.selloRecepcion IS NULL OR e.selloRecepcion = '') 
    //                 AND e.eventoContingenciaId IN ($placeholders)";

    //     // 4. Filtro por fechaEmision
    //     if (!empty($search)) {
    //         $searchQuery .= " AND e.fechaEmision = ?";
    //         $params[] = $search;
    //     }

    //     // 5. Consulta principal
    //     $sql = "SELECT e.numeroControl AS correlativo,
    //                e.codigoGeneracion AS codigo,
    //                c.nombreCliente AS cliente
    //         FROM dte_encabezado e
    //         LEFT JOIN clientes c ON e.receptor = c.codigoCliente
    //         $searchQuery
    //         ORDER BY e.numeroControl DESC
    //         LIMIT $start, $length";

    //     $data = $this->selectAll($sql, $params);

    //     // 6. Total
    //     $sqlTotal = "SELECT COUNT(*) AS total 
    //              FROM dte_encabezado e 
    //              $searchQuery";
    //     $total = $this->select($sqlTotal, $params)['total'] ?? 0;

    //     return [
    //         'encabezadoFe' => $data,
    //         'total' => $total
    //     ];
    // }

    public function listarFe($start, $length, $search = "")
    {
        // 1. Obtener eventos válidos
        $idsContingencia = $this->getEventosContingenciaValidos();

        if (empty($idsContingencia)) {
            return ['encabezadoDte' => [], 'total' => 0]; // No hay eventos válidos
        }

        // 2. Preparar placeholders dinámicos
        $placeholders = implode(',', array_fill(0, count($idsContingencia), '?'));
        $params = $idsContingencia;

        // 3. Agregar condiciones básicas
        $searchQuery = "WHERE e.tipoDte IN ('01', '03', '05', '14')
                    AND e.tipoOperacion = 2 
                    AND (e.selloRecepcion IS NULL OR e.selloRecepcion = '') 
                    AND e.eventoContingenciaId IN ($placeholders)";

        // 4. Filtro por fechaEmision
        if (!empty($search)) {
            $searchQuery .= " AND e.fechaEmision = ?";
            $params[] = $search;
        }

        // 5. Consulta principal
        $sql = "SELECT e.numeroControl AS correlativo,
                   e.codigoGeneracion AS codigo,
                   c.nombreCliente AS cliente,
                   e.enEvento AS incluido,
                   t.nombreTipoDocumento,
                   e.tipoDte
            FROM dte_encabezado e
            LEFT JOIN clientes c ON e.receptor = c.codigoCliente
            LEFT JOIN tiposDocumentos t ON e.tipoDte = t.codigoTipoDocumento
            $searchQuery
            ORDER BY e.numeroControl DESC
            LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        // 6. Total
        $sqlTotal = "SELECT COUNT(*) AS total 
                 FROM dte_encabezado e 
                 $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        return [
            'encabezadoDte' => $data,
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
                        c.codigoActividadEconomica AS actividadEconomica,
                        a.valor,
                        c.departamento,
                        c.municipio,
                        c.complemento,
                        c.numeroTelefonoCliente AS telefono,
                        c.correo,
                        ht.nit AS nitTercero,
                        ht.nombre AS nombreTercero
        FROM dte_encabezado e
        LEFT JOIN modeloFacturacion v ON e.tipoModelo = v.codigo
        LEFT JOIN clientes c ON e.receptor = c.codigoCliente
        LEFT JOIN historialVentaTerceros ht ON e.numeroControl = ht.codigoDeControl
        LEFT JOIN actividadesEconomicas a ON c.codigoActividadEconomica = a.codigo
        WHERE e.numeroControl = ?';
        $datos = array($numeroControl);
        return $this->select($sql, $datos);
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

    public function getEmpresa()
    {
        $sql = "SELECT * FROM empresa";
        $data = $this->select($sql);
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

    public function getDocRelaionados(string $numeroControl)
    {
        $sql = 'SELECT * FROM historialDocumentosRelacionadosDTE
        WHERE numeroDeControl = ?';
        $datos = array($numeroControl);
        return $this->selectAll($sql, $datos);
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
        WHERE idNumeroControl = ?';
        $datos = array($numeroControl);
        return $this->selectAll($sql, $datos);
    }

    public function getNombreCondicionOperacion($codigo)
    {
        $sql = "SELECT nombre FROM condicionOperacion WHERE codigo = ?";
        $params = [$codigo];
        $resultado = $this->select($sql, $params);

        return $resultado ? $resultado['nombre'] : 'Desconocido';
    }

    public function getProducto(string $codigoProducto)
    {
        $sql = 'SELECT * FROM productos
        WHERE codigoProducto = ?';
        $datos = array($codigoProducto);
        return $this->select($sql, $datos);
    }

    public function guardarJsonEventoFirmado($firma, $idEvento)
    {
        $sql = "UPDATE historialEventoContingencia SET jsonFirmado = ? WHERE id = ?";
        $datos = [$firma, $idEvento];
        $data = $this->guardar($sql, $datos);
        return ($data == 1) ? "ok" : "error";
    }

    public function obtenerEstadoContingencia()
    {
        // Modificar la consulta para filtrar por el código '01'
        $sql = "SELECT
                estadoContingenciaId
                FROM historialEventoContingencia where estadoContingenciaId = 1";

        return $this->select($sql);
    }

    public function actualizarEstadoEventoCon($estado, $fechaHora, $mensaje, $selloRecepcion, $jsonFirmado, $observaciones, $codigoGeneracion, $id)
    {
        $sql = "UPDATE historialEventoContingencia SET estado = ?, fechaHora = ?, mensaje = ?, selloRecibido = ?, jsonFirmado = ?, observaciones = ?, codigoGeneracionC = ? WHERE id = ?";
        $datos = array($estado, $fechaHora, $mensaje, $selloRecepcion, $jsonFirmado, $observaciones, $codigoGeneracion, $id);
        $data =  $this->guardar($sql, $datos);
        return ($data == 1) ? "ok" : "error";
    }

    public function obtenerDtesFirmadosPendientes()
    {
        // Obtener el evento más reciente cerrado
        $sqlEvento = "SELECT id FROM historialEventoContingencia 
                  WHERE estadoContingenciaId != 1 AND fechaFin IS NOT NULL
                  ORDER BY fechaFin DESC LIMIT 1";
        $evento = $this->select($sqlEvento);

        if (!$evento) {
            return [];
        }

        $eventoId = $evento['id'];

        // Obtener los documentos firmados asociados a ese evento
        $sql = "SELECT documentoFirmado 
            FROM dte_encabezado 
            WHERE eventoContingenciaId = ? 
              AND estado = 'PENDIENTE'
            LIMIT 100";
        $datos = [$eventoId];
        $resultados = $this->selectAll($sql, $datos);

        // Extraer los documentos firmados como arreglo de strings
        $documentos = array_map(function ($fila) {
            return $fila['documentoFirmado'];
        }, $resultados);

        return $documentos;
    }

    public function registrarLote($idEnvio, $codigoLote, $fhProcesamiento, $descripcionMsg, $ambiente, $estado, $versionApp)
    {
        $sql = "INSERT INTO lotes_enviados (idEnvio, codigoLote, fhProcesamiento, descripcionMsg, ambiente, estado, versionApp)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        $datos = [$idEnvio, $codigoLote, $fhProcesamiento, $descripcionMsg, $ambiente, $estado, $versionApp];
        $this->guardar($sql, $datos);

        // Obtener el ID insertado
        return $this->ultimoId(); // este método debe retornar el último ID autoincremental insertado
    }

    public function asociarDtesAlLote($loteId, $estado, $selloRecepcion, $eventoContingenciaId)
    {
        $sql = "UPDATE dte_encabezado
        SET loteId = ?, estado = ?, selloRecepcion = ?
        WHERE eventoContingenciaId = ? AND loteId IS NULL";

        $datos = [$loteId, $estado, $selloRecepcion, $eventoContingenciaId];
        return $this->guardar($sql, $datos) ? "ok" : "error";
    }

    public function contarLotesEnviadosHoy($ambiente)
    {
        $fechaHoy = date('Y-m-d');
        $sql = "SELECT COUNT(*) AS total 
            FROM lotes_enviados 
            WHERE ambiente = ? 
              AND DATE(fhProcesamiento) = ?";
        $data = $this->select($sql, [$ambiente, $fechaHoy]);
        return $data['total'] ?? 0;
    }

    public function getSelloContingencia(string $id)
    {
        $sql = 'SELECT * FROM historialEventoContingencia
        WHERE id = ? AND selloRecibido IS NULL';
        $datos = array($id);
        return $this->select($sql, $datos);
    }


    public function actualizarEstadoDTEComoIncluido($codigoGeneracion)
    {
        $sql = "UPDATE dte_encabezado SET enEvento = 'INCLUIDO' WHERE codigoGeneracion = ?";
        return $this->guardar($sql, [$codigoGeneracion]);
    }

    public function actualizarEstadoDTEComoEnviado($codigoGeneracion)
    {
        $sql = "UPDATE dte_encabezado SET enEvento = 'ENVIADO' WHERE codigoGeneracion = ?";
        return $this->guardar($sql, [$codigoGeneracion]);
    }

    public function obtenerCodigosGeneracionPendientesPorEvento($eventoId)
    {
        $sql = "SELECT codigoGeneracion 
            FROM dte_encabezado 
            WHERE eventoContingenciaId = ? 
              AND estado = 'PENDIENTE'";
        return $this->selectAll($sql, [$eventoId]);
    }
    
    public function getTipoDte(string $numeroControl)
    {
        $sql = 'SELECT tipoDte FROM dte_encabezado
        WHERE numeroControl = ?';
        $datos = array($numeroControl);
        return $this->select($sql, $datos);
    }

    public function searchLote($query, $fecha = null)
    {
        $metodos = new Metodos;
        $data = $metodos->variablesGlobales();
        $ambiente = $data['ambiente'];

        $sql = "SELECT codigoLote AS codigo
            FROM lotes_enviados
            WHERE codigoLote LIKE ?";

        $params = ["%$query%"];

        if (!empty($fecha)) {
            $sql .= " AND DATE(fhProcesamiento) = ? AND ambiente = ?";
            $params[] = $fecha;
            $params[] = $ambiente;
        }

        $sql .= " ORDER BY fhProcesamiento DESC LIMIT 5";

        return $this->selectAll($sql, $params);
    }


    public function actualizarEstadoYSello($selloRecepcion, $fhProcesamiento, $observaciones, $codigoGeneracion)
    {
        $sql = "UPDATE dte_encabezado SET selloRecepcion = ?, fhProcesamiento = ?, observaciones = ? WHERE codigoGeneracion = ?";
        return $this->guardar($sql, [$selloRecepcion, $fhProcesamiento, $observaciones, $codigoGeneracion]);
    }

    public function actuliarEstadoRechazado($estado, $codigoGeneracion)
    {
        $sql = "UPDATE dte_encabezado SET estado = 'RECHAZADO' WHERE codigoGeneracion = ?";
        return $this->guardar($sql, [$estado, $codigoGeneracion]);
    }
}