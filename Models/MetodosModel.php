<?php

class MetodosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    // public function generarCorrelativos($codigoTipoDocumento, $ambiente)
    // {
    //     $anio = date('Y'); // ahora es '2025', no '25'

    //     try {
    //         $sql = "SELECT ultimo_correlativo FROM correlativosDte 
    //             WHERE anio = ? AND tipoDocumento = ? AND ambiente = ? FOR UPDATE";

    //         $params = [$anio, $codigoTipoDocumento, $ambiente];
    //         $row = $this->select($sql, $params);

    //         if ($row) {
    //             $nuevo = $row['ultimo_correlativo'] + 1;
    //             $sqlUpdate = "UPDATE correlativosDte 
    //                     SET ultimo_correlativo = ? 
    //                     WHERE anio = ? AND tipoDocumento = ? AND ambiente = ?";
    //             $this->guardar($sqlUpdate, [$nuevo, $anio, $codigoTipoDocumento, $ambiente]);
    //         } else {
    //             $nuevo = 1;
    //             $sqlInsert = "INSERT INTO correlativosDte (anio, tipoDocumento, ambiente, ultimo_correlativo)
    //                     VALUES (?, ?, ?, ?)";
    //             $this->guardar($sqlInsert, [$anio, $codigoTipoDocumento, $ambiente, $nuevo]);
    //         }

    //         return str_pad($nuevo, 15, "0", STR_PAD_LEFT);
    //     } catch (Exception $e) {
    //         throw $e;
    //     }
    // }

    public function reservarCorrelativo($codigoTipoDocumento, $ambiente)
    {
        $anio = date('Y');

        try {
            // Comenzamos una transacción para evitar conflictos
            // $this->iniciarTransaccion();

            // Bloqueamos fila para evitar concurrencia
            $sql = "SELECT ultimo_correlativo FROM correlativosDte 
                WHERE anio = ? AND tipoDocumento = ? AND ambiente = ? FOR UPDATE";
            $params = [$anio, $codigoTipoDocumento, $ambiente];
            $row = $this->select($sql, $params);

            if ($row) {
                $nuevo = $row['ultimo_correlativo'] + 1;
            } else {
                $nuevo = 1;
                // Insertamos en tabla correlativosDte para crear registro base
                $sqlInsertBase = "INSERT INTO correlativosDte (anio, tipoDocumento, ambiente, ultimo_correlativo)
                            VALUES (?, ?, ?, 0)";
                $this->guardar($sqlInsertBase, [$anio, $codigoTipoDocumento, $ambiente]);
            }

            // Insertar correlativo reservado en tabla temporal
            $sqlInsertTemp = "INSERT INTO correlativosDteTemporal (anio, tipoDocumento, correlativo, ambiente, reservado)
                        VALUES (?, ?, ?, ?, 1)";
            $this->guardar($sqlInsertTemp, [$anio, $codigoTipoDocumento, $nuevo, $ambiente]);

            // $this->confirmarTransaccion();

            return str_pad($nuevo, 15, "0", STR_PAD_LEFT);
        } catch (Exception $e) {
            // $this->revertirTransaccion();
            throw $e;
        }
    }

    public function obtenerCorrelativoTemporal($tipoDocumento, $ambiente)
    {
        $anio = date('Y');
        $sql = "SELECT correlativo 
            FROM correlativosDteTemporal 
            WHERE anio = ? AND tipoDocumento = ? AND ambiente = ? 
            ORDER BY id DESC LIMIT 1";
        $params = [$anio, $tipoDocumento, $ambiente];
        return $this->select($sql, $params); // retorna solo el entero
    }



    public function confirmarCorrelativo($tipoDocumento, $ambiente, $correlativoReservado)
    {
        $anio = date('Y');

        try {
            // $this->iniciarTransaccion();

            // Obtener correlativo actual
            $sql = "SELECT ultimo_correlativo FROM correlativosDte
                WHERE anio = ? AND tipoDocumento = ? AND ambiente = ? FOR UPDATE";
            $row = $this->select($sql, [$anio, $tipoDocumento, $ambiente]);

            if ($row) {
                $ultimo = $row['ultimo_correlativo'];
                if ($correlativoReservado > $ultimo) {
                    // Actualizar correlativo definitivo
                    $sqlUpdate = "UPDATE correlativosDte SET ultimo_correlativo = ? 
                            WHERE anio = ? AND tipoDocumento = ? AND ambiente = ?";
                    $this->guardar($sqlUpdate, [$correlativoReservado, $anio, $tipoDocumento, $ambiente]);
                }
            } else {
                // Por si no existía, insertamos con el correlativo confirmado
                $sqlInsert = "INSERT INTO correlativosDte (anio, tipoDocumento, ambiente, ultimo_correlativo)
                        VALUES (?, ?, ?, ?)";
                $this->guardar($sqlInsert, [$anio, $tipoDocumento, $ambiente, $correlativoReservado]);
            }

            // Borramos correlativo reservado de la tabla temporal
            $sqlDeleteTemp = "DELETE FROM correlativosDteTemporal 
                        WHERE anio = ? AND tipoDocumento = ? AND correlativo = ? AND ambiente = ?";
            $this->guardar($sqlDeleteTemp, [$anio, $tipoDocumento, $correlativoReservado, $ambiente]);

            // $this->confirmarTransaccion();
        } catch (Exception $e) {
            // $this->revertirTransaccion();
            throw $e;
        }
    }

    public function liberarCorrelativo($tipoDocumento, $ambiente, $correlativoReservado)
    {
        $anio = date('Y');

        try {
            $sqlDeleteTemp = "DELETE FROM correlativosDteTemporal 
                          WHERE anio = ? AND tipoDocumento = ? AND correlativo = ? AND ambiente = ?";
            $this->guardar($sqlDeleteTemp, [$anio, $tipoDocumento, $correlativoReservado, $ambiente]);
        } catch (Exception $e) {
            throw $e;
        }
    }


    // obtener el estado de la contingencia
    public function obtenerEstadoContingencia()
    {
        // Modificar la consulta para filtrar por el código '01'
        $sql = "SELECT
                estadoContingenciaId
                FROM historialEventoContingencia where estadoContingenciaId = 1";

        return $this->select($sql);
    }

    // obtener los datos de la contingencia
    public function datosContingencia()
    {
        $sql = "SELECT
                    *
                FROM historialEventoContingencia 
                WHERE estadoContingenciaId = 1";
        $data = $this->select($sql); // asegúrate de que este método retorna UNA sola fila
        return $data;
    }

    // obtener datos de la empresa MACASTA
    public function getEmpresa()
    {
        $sql = "SELECT * FROM empresa";
        $data = $this->select($sql);
        return $data;
    }

    // obtener clientes
    public function getCliente($codigoCliente)
    {
        $sql = "SELECT c.* ,
        a.valor
            FROM clientes c
                LEFT JOIN actividadesEconomicas a ON c.codigoActividadEconomica = a.codigo
                WHERE codigoCliente = ?";
        $datos = array($codigoCliente);
        $data = $this->select($sql, $datos);
        return $data;
    }

    // obtener los productos por el id para seleccionar desde el modal
    public function getProducto($id)
    {
        $sql = "SELECT * FROM productos WHERE codigoProducto = ? ";
        $datos = array($id);
        $data = $this->select($sql, $datos);
        return $data;
    }

    // consultar el detalle temporal
    public function consultarDetalle($codigoProducto, $idUsuario)
    {
        $sql = "SELECT * FROM detalleTemporal WHERE codigoProducto = ? AND id_Usuario = ?";
        $datos = array($codigoProducto, $idUsuario);
        $data = $this->select($sql, $datos);
        return $data;
    }

    // obtener el ultimo id en la tabla temporal para validaciones de limites de envio en un dte
    public function obtenerUltimoIdDetalle($idUsuario)
    {
        $sql = "SELECT MAX(id) AS max_id FROM detalleTemporal WHERE id_Usuario = ?";
        return $this->select($sql, [$idUsuario]);
    }

    public function getWebToken()
    {
        $sql = "SELECT * FROM webToken WHERE fecha_expira > NOW() ORDER BY token DESC LIMIT 1";
        return $this->select($sql);
    }

    public function guardarToken(
        $token,
        $fecha_obtenido,
        $fecha_expira,
    ) {
        $sql = "INSERT INTO webToken ( token, fecha_obtenido, fecha_expira) VALUES (?, ?, ?)";

        $datos = array(
            $token,
            $fecha_obtenido,
            $fecha_expira
        );
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }

        return $res;
    }

    public function getIdCliente(string $nombreCliente)
    {
        $sql = "SELECT codigoCliente FROM clientes WHERE nombreCliente = ?";
        $datos = array($nombreCliente);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function getExistencia(string $codigoProducto, string $codigoProyecto)
    {
        $sql = "SELECT cantidadProducto FROM existencias WHERE codigoProducto = ? AND codigoProyecto = ?";
        $datos = array($codigoProducto, $codigoProyecto);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    public function actualizarExistenciasM(int $cantidad, string $codigoProyecto, $codigoProducto)
    {
        // Verificar si el producto ya existe para ese proyecto
        $verificar = "SELECT * FROM existencias WHERE codigoProyecto = ? AND codigoProducto = ?";
        $existe = $this->select($verificar, [$codigoProyecto, $codigoProducto]);

        if (empty($existe)) {
            // Insertar si no existe
            $sql = "INSERT INTO existencias (codigoProducto, cantidadProducto, codigoProyecto) VALUES (?, ?, ?)";
            $datos = array($codigoProducto, $cantidad, $codigoProyecto);
        } else {
            // Actualizar cantidad si el proyecto existe
            $sql = "UPDATE existencias SET cantidadProducto = ? WHERE codigoProducto = ? AND codigoProyecto = ?";
            $datos = array($cantidad, $codigoProducto, $codigoProyecto);
        }

        $data = $this->guardar($sql, $datos);
        return ($data == 1) ? "ok" : "error";
    }

    public function obtenerSaldoCuenta($codigoCuentaBancaria)
    {
        $sql = "SELECT saldoInicial, ingresos, saldo, salidas FROM cuentaBancaria WHERE codigoCuentaBancaria = ?";
        $datos = array($codigoCuentaBancaria);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    public function actualizarIngresoCuenta($ingreso,  $codigoCuentaBancaria)
    {
        $sql = "UPDATE cuentaBancaria SET ingresos = ? WHERE codigoCuentaBancaria = ?";
        $datos = array($ingreso, $codigoCuentaBancaria);

        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function actualizarSaldoCuentas($saldo,  $codigoCuentaBancaria)
    {
        $sql = "UPDATE cuentaBancaria SET saldo = ? WHERE codigoCuentaBancaria = ?";
        $datos = array($saldo, $codigoCuentaBancaria);

        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }
    
        public function obtenerIdContingenciaActiva(int $estado)
    {
        $sql = "SELECT id FROM historialEventoContingencia WHERE estadoContingenciaId  = ?";
        $datos = array($estado);
        $data = $this->select($sql, $datos);
        return $data;
    }
}