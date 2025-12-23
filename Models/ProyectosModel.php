<?php
class ProyectosModel extends Query
{
    private $codigo,
        $nombre,
        $inicio,
        $fin,
        $codigoCliente,
        $valorCotizado,
        $ingresos,
        $salidas,
        $valorRentabilidad,
        $codigoEstadoProyecto,
        $codigoResponsable,
        $nombreResponsable,
        $telefonoResponsable,
        $codigoUsuario;

    public function __construct()
    {
        parent::__construct();
    }

    public function searchCliente($query, $tipo = 'todos')
    {
        $condicionExtra = '';
        $params = ["%$query%"];

        // Agregar condición para clientes con NRC si aplica
        if ($tipo === 'conNrc') {
            $condicionExtra = " AND nrc IS NOT NULL AND nrc != ''";
        }

        $sql = "SELECT codigoCliente AS codigo,
                   nombreCliente AS nombre
            FROM clientes
            WHERE nombreCliente LIKE ? $condicionExtra
            ORDER BY fechaCreacion DESC
            LIMIT 5";

        return $this->selectAll($sql, $params);
    }


    public function searchResponsable($query)
    {
        $sql = "SELECT codigoResponsable AS codigo,
                        nombreResponsable AS nombre,
                        telefonoResponsable AS numero
                FROM responsables
                WHERE nombreResponsable LIKE ?
                LIMIT 5 ";
        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    public function searchEstado($query)
    {
        $sql = "SELECT codigoEstadoProyecto AS codigo,
                        nombreEstadoProyecto AS nombre
                FROM estadoProyecto
                WHERE nombreEstadoProyecto LIKE ?
                LIMIT 5 ";
        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    public function getProyectos($start, $length, $search = "")
    {
        // Agregar la condición de búsqueda
        $searchQuery = "";
        $params = [];
        if (!empty($search)) {
            $searchQuery = "WHERE p.codigoProyecto LIKE :search OR p.nombreProyecto LIKE :search";
            $params = [':search' => '%' . $search . '%'];
        }

        // Consulta para obtener productos con el total
        $sql = "SELECT 
                    p.codigoProyecto AS codigo,
                    p.nombreProyecto AS proyecto,
                    p.fechaInicio AS inicio,
                    p.fechaFin AS fin,
                    c.nombreCliente AS cliente, 
                    r.nombreResponsable AS responsable,
                    a.nombreEstadoProyecto AS estado
                FROM 
                    proyectos p
                INNER JOIN 
                    clientes c ON p.codigoCliente = c.codigoCliente
                INNER JOIN 
                    responsables r ON p.codigoResponsable = r.codigoResponsable
                LEFT JOIN
                    estadoProyecto a ON p.codigoEstadoProyecto = a.codigoEstadoProyecto
                $searchQuery
                ORDER BY p.fechaCreacion DESC
                LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        $sqlTotal = "SELECT COUNT(*) AS total FROM proyectos p $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        // Devuelve los productos y el total
        return [
            'proyectos' => $data,
            'total' => $total
        ];
    }

    public function registrarProyecto(
        string $codigo,
        string $nombre,
        string $inicio,
        string $fin,
        string $codigoCliente,
        float $valorCotizado,
        float $ingresos,
        float $salidas,
        float $valorRentabilidad,
        $codigoEstadoProyecto,
        string $codigoResponsable,
        string $nombreResponsable,
        string $telefonoResponsable,
        string $codigoUsuario
    ) {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
        $this->inicio = $inicio;
        $this->fin = $fin;
        $this->codigoCliente = $codigoCliente;
        $this->valorCotizado = $valorCotizado;
        $this->ingresos = $ingresos;
        $this->salidas = $salidas;
        $this->valorRentabilidad = $valorRentabilidad;
        $this->codigoEstadoProyecto = empty($codigoEstadoProyecto) ? NULL : $codigoEstadoProyecto;
        $this->codigoResponsable = $codigoResponsable;
        $this->nombreResponsable = $nombreResponsable;
        $this->telefonoResponsable = $telefonoResponsable;
        $this->codigoUsuario = $codigoUsuario;

        // Iniciar transacción
        $this->iniciarTransaccion();

        try {
            // Verificar si el proyecto ya existe
            $verificar = "SELECT * FROM proyectos WHERE codigoProyecto = ?";
            $existe = $this->select($verificar, [$this->codigo]);

            if (!empty($existe)) {
                return "existe"; // El proyecto ya existe
            }

            // Verificar si el responsable ya existe
            $verificarResponsable = "SELECT * FROM responsables WHERE codigoResponsable = ?";
            $responsableExiste = $this->select($verificarResponsable, [$this->codigoResponsable]);

            if (empty($responsableExiste)) {
                // Insertar responsable
                $sqlResponsable = "INSERT INTO responsables(codigoResponsable, nombreResponsable, telefonoResponsable, codigoUsuario) 
                                    VALUES (?, ?, ?, ?)";
                $datosResponsables = [$this->codigoResponsable, $this->nombreResponsable, $this->telefonoResponsable, $this->codigoUsuario];
                $resultado = $this->guardar($sqlResponsable, $datosResponsables);
                if ($resultado != 1) {
                    return "error al registrar responsable"; // Error al registrar el responsable
                }
            }

            // Insertar proyecto
            $sql = "INSERT INTO proyectos(codigoProyecto, nombreProyecto, fechaInicio, fechaFin, codigoCliente, 
                                            valorCotizado, ingresos, salidas, codigoResponsable, valorRentabilidad, 
                                            codigoEstadoProyecto) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $datos = [
                $this->codigo,
                $this->nombre,
                $this->inicio,
                $this->fin,
                $this->codigoCliente,
                $this->valorCotizado,
                $this->ingresos,
                $this->salidas,
                $this->codigoResponsable,
                $this->valorRentabilidad,
                $this->codigoEstadoProyecto
            ];

            $data = $this->guardar($sql, $datos);
            if ($data != 1) {
                return "error al registrar proyecto"; // Error al registrar el proyecto
            }

            // Confirmar la transacción
            $this->confirmarTransaccion();
            return "ok"; // Proyecto registrado correctamente
        } catch (Exception $e) {
            // Si algo falla, revertir los cambios
            $this->revertirTransaccion();
            return "error"; // Error en la transacción
        }
    }

    public function modificarProyecto(
        string $nombre,
        string $inicio,
        string $fin,
        string $codigoCliente,
        float $valorCotizado,
        float $ingresos,
        float $salidas,
        float $valorRentabilidad,
        $codigoEstadoProyecto,
        string $codigoResponsable,
        string $nombreResponsable,
        string $telefonoResponsable,
        string $codigoUsuario,
        string $codigo
    ) {
        $this->nombre = $nombre;
        $this->inicio = $inicio;
        $this->fin = $fin;
        $this->codigoCliente = $codigoCliente;
        $this->valorCotizado = $valorCotizado;
        $this->ingresos = $ingresos;
        $this->salidas = $salidas;
        $this->valorRentabilidad = $valorRentabilidad;
        $this->codigoEstadoProyecto = empty($codigoEstadoProyecto) ? NULL : $codigoEstadoProyecto;
        $this->nombreResponsable = $nombreResponsable;
        $this->telefonoResponsable = $telefonoResponsable;
        $this->codigoUsuario = $codigoUsuario;
        $this->codigoResponsable = $codigoResponsable;
        $this->codigo = $codigo;

        // Iniciar la transacción
        $this->iniciarTransaccion();

        try {
            // Verificar si el responsable ya existe
            $verificarResponsable = "SELECT * FROM responsables WHERE codigoResponsable = ?";
            $responsableExiste = $this->select($verificarResponsable, [$this->codigoResponsable]);

            if (empty($responsableExiste)) {
                // Insertar responsable si no existe
                $sqlResponsable = "INSERT INTO responsables(codigoResponsable, nombreResponsable, telefonoResponsable, codigoUsuario) 
                                    VALUES (?, ?, ?, ?)";
                $datosResponsables = [$this->codigoResponsable, $this->nombreResponsable, $this->telefonoResponsable, $this->codigoUsuario];
                $resultadoResponsable = $this->guardar($sqlResponsable, $datosResponsables);
                if ($resultadoResponsable != 1) {
                    throw new Exception("Error al registrar responsable");
                }
            } else {
                // Actualizar responsable si ya existe
                $sqlUpdateResponsable = "UPDATE responsables SET nombreResponsable = ?, telefonoResponsable = ?, codigoUsuario = ? 
                                        WHERE codigoResponsable = ?";
                $datosUpdateResponsable = [$this->nombreResponsable, $this->telefonoResponsable, $this->codigoUsuario, $this->codigoResponsable];
                $resultadoUpdateResponsable = $this->guardar($sqlUpdateResponsable, $datosUpdateResponsable);
                if ($resultadoUpdateResponsable != 1) {
                    throw new Exception("Error al actualizar responsable");
                }
            }

            // Modificar el proyecto
            $sql = "UPDATE proyectos SET nombreProyecto = ?, fechaInicio = ?, fechaFin = ?, codigoCliente = ?, valorCotizado = ?, ingresos = ?, salidas = ?, codigoResponsable = ?, valorRentabilidad = ?, codigoEstadoProyecto = ? WHERE codigoProyecto = ?";
            $datos = [
                $this->nombre,
                $this->inicio,
                $this->fin,
                $this->codigoCliente,
                $this->valorCotizado,
                $this->ingresos,
                $this->salidas,
                $this->codigoResponsable,
                $this->valorRentabilidad,
                $this->codigoEstadoProyecto,
                $this->codigo
            ];

            $data = $this->guardar($sql, $datos);

            if ($data != 1) {
                throw new Exception("Error al modificar el proyecto");
            }

            // Confirmar la transacción
            $this->confirmarTransaccion();

            return "modificado"; // Proyecto modificado correctamente

        } catch (Exception $e) {
            // Si ocurre un error, revertir todos los cambios
            $this->revertirTransaccion();
            return "error: " . $e->getMessage(); // Error detallado
        }
    }



    public function editarProyecto(string $codigo)
    {
        $sql = "SELECT 
                    p.codigoProyecto AS codigo,
                    p.nombreProyecto AS proyecto,
                    p.fechaInicio AS inicio,
                    p.fechaFin AS fin,
                    c.codigoCliente AS cliente,
                    c.nombreCliente AS nombreCliente,
                    p.valorCotizado AS valorCotizado,
                    p.ingresos AS ingresos,
                    p.salidas AS salidas,
                    r.codigoResponsable AS responsable, 
                    r.nombreResponsable AS nombreResponsable,
                    r.telefonoResponsable AS telefono,
                    p.valorRentabilidad AS rentabilidad,
                    a.codigoEstadoProyecto AS estado,
                    IFNULL(a.nombreEstadoProyecto, '-----') AS nombreEstado
                FROM 
                    proyectos p
                INNER JOIN 
                    clientes c ON p.codigoCliente = c.codigoCliente
                INNER JOIN 
                    responsables r ON p.codigoResponsable = r.codigoResponsable
                LEFT JOIN
                    estadoProyecto a ON p.codigoEstadoProyecto = a.codigoEstadoProyecto
                WHERE p.codigoProyecto =  ?";
        $datos = array($codigo);
        $data = $this->select($sql, $datos);
        return $data;
    }
}
