<?php
class CotizacionModel extends Query
{
    private
        $fecha,
        $codigoCliente,
        $codigoProyecto,
        $codigoProducto,
        $cantidad,
        $costoProducto,
        $precioVenta,
        $total,
        $codigoUsuario,
        $codigoCotizacion,
        $tipoCotizacion,
        $subTotal,
        $iva;

    public function __construct()
    {
        parent::__construct();
    }

    // para select buscador 
    public function searchProducto($query)
    {
        $sql = "SELECT p.codigoProducto AS codigo,
                        p.nombreProducto AS nombre,
                        e.cantidadProducto AS cantidad,
                        p.costoProducto AS costo,
                        p.precioVenta AS precio
        FROM productos p
        LEFT JOIN existencias e ON p.codigoProducto = e.codigoProducto
        WHERE p.nombreProducto LIKE ?
        ORDER BY fechaCreacion DESC
        LIMIT 5 ";
        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    // para cuando se seleccione el producto del select
    public function getProducto(string $id)
    {
        $sql = "SELECT * FROM productos WHERE codigoProducto = ? ";
        $datos = array($id);
        $data = $this->select($sql, $datos);
        return $data;
    }

    // consulta si hay un codigo de producto ya en el detalle para sumar cantidades
    public function consultarDetalle($codigoProducto, $idUsuario)
    {
        $sql = "SELECT * FROM detalleTemporal WHERE codigoProducto = ? AND id_Usuario = ?";
        $datos = array($codigoProducto, $idUsuario);
        $data = $this->select($sql, $datos);
        return $data;
    }

    // se gun la funciona de arriba si no hay lo registra
    public function registrarDetalle(string $idProducto, int $cantidad, string $costo, string $precio, string $total, string $idUsuario)
    {
        $sql = "INSERT INTO detalleTemporal(codigoProducto, cantidad, costoProducto, precioVenta, total, id_Usuario)
                VALUES (?,?,?,?,?,?)";
        $datos = array($idProducto, $cantidad, $costo, $precio, $total, $idUsuario);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }

        return $res;
    }

    // si hay actualiza la cantidad 
    public function actualizarDetalle(int $totalCantidad, float $costoProducto, float $precioVenta, string $totalActualizar, string $idProducto, string $idUsuario)
    {
        $sql = "UPDATE detalleTemporal SET cantidad = ?, costoProducto = ?, precioVenta = ?, total = ? WHERE codigoProducto = ? AND id_Usuario = ?";
        $datos = array($totalCantidad, $costoProducto, $precioVenta, $totalActualizar, $idProducto, $idUsuario);
        $data = $this->guardar($sql, $datos);
        return ($data == 1) ? "modificado" : "error";
    }

    // // Para actualizar existencias de productos
    // public function actualizarPrecioProducto(float $precioCosto, float $precioVenta, string $codigoProducto)
    // {
    //     $sql = "UPDATE productos SET costoProducto = ?, precioVenta = ? WHERE codigoProducto = ?";
    //     $datos = array($precioCosto, $precioVenta, $codigoProducto);

    //     $data = $this->guardar($sql, $datos);
    //     if ($data == 1) {
    //         $res = "ok";
    //     } else {
    //         $res = "error";
    //     }
    //     return $res;
    // }

    public function getDetalle(string $id)
    {
        $sql = "SELECT d.*, p.nombreProducto
        FROM detalleTemporal d
        INNER JOIN productos p ON d.codigoProducto = p.codigoProducto
        WHERE d.id_Usuario = ?";
        $datos = array($id);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }


    /// registrar
    public function comprobar(string $id_Usuario)
    {
        $sql = "SELECT SUM(total) AS probar FROM detalleTemporal WHERE id_Usuario = ?";
        $datos = array($id_Usuario);
        $data = $this->select($sql, $datos);
        return $data;
    }

    public function registrarCotz(
        string $codigoCotizacion,
        string $codigoCliente,
        string $codigoProyecto,
        float $subTotal,
        float $iva,
        float $total,
    ) {
        $this->codigoCotizacion = $codigoCotizacion;
        $this->codigoCliente = $codigoCliente;
        $this->codigoProyecto = $codigoProyecto;
        $this->total = $total;
        $this->subTotal = $subTotal;
        $this->iva = $iva;

        $verificar = "SELECT * FROM cotizaciones WHERE idCotizacion = ?";
        $existe = $this->select($verificar, [$this->codigoCotizacion]);

        if (empty($existe)) {
            $sql = "INSERT INTO cotizaciones (idCotizacion, codigoCliente, codigoProyecto, subTotal, iva, total)
            VALUES (?,?,?,?,?,?)";
            $datos = array(
                $this->codigoCotizacion,
                $this->codigoCliente,
                $this->codigoProyecto,
                $this->subTotal,
                $this->iva,
                $this->total,
            );
            $data = $this->guardar($sql, $datos);
            if ($data == 1) {
                $res = "ok";
            } else {
                $res = "error";
            }
        } else {
            $res = "existe";
        }
        return $res;
    }

    public function registrarDetalleCotz(
        string $codigoCotizacion,
        string $codigoProyecto,
        string $codigoProducto,
        int $cantidad,
        float $costoProducto,
        float $precioVenta,
        float $total
    ) {
        $this->codigoCotizacion = $codigoCotizacion;
        $this->codigoProyecto = $codigoProyecto;
        $this->codigoProducto = $codigoProducto;
        $this->cantidad = $cantidad;
        $this->costoProducto = $costoProducto;
        $this->precioVenta = $precioVenta;
        $this->total = $total;

        $sql = "INSERT INTO detalleCotizacion (idCotizacion, codigoProyecto, codigoProducto, cantidad, costoProducto, precioVenta, total)
                VALUES (?,?,?,?,?,?,?)";
        $datos = array(
            $this->codigoCotizacion,
            $this->codigoProyecto,
            $this->codigoProducto,
            $this->cantidad,
            $this->costoProducto,
            $this->precioVenta,
            $this->total,
        );
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function vaciarDetalle($idUsuario)
    {
        // Eliminar los registros del usuario
        $sql = "DELETE FROM detalleTemporal WHERE id_Usuario = ?";
        $datos = array($idUsuario);
        $data = $this->guardar($sql, $datos);

        if ($data == 1) {
            // Si se eliminaron registros, reiniciar el AUTO_INCREMENT
            $sqlReset = "ALTER TABLE detalleTemporal AUTO_INCREMENT = 1";
            $this->guardar($sqlReset, array());

            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }

    // genrar pdf de cotizacion
    public function getCotizacion($codigoCotizacion)
    {
        $sql = "SELECT 
            c.idCotizacion,
            cl.nombreCliente,
            cl.nrc,
            py.nombreProyecto,
            DATE(c.fecha) AS fecha,
            p.nombreProducto,
            dc.cantidad,
            dc.costoProducto,
            dc.precioVenta,
            dc.total,
            c.total AS totales,
            c.iva,
            c.subTotal,
            ac.valor
        FROM cotizaciones c
        LEFT JOIN detalleCotizacion dc ON c.idCotizacion = dc.idCotizacion
        LEFT JOIN clientes cl ON c.codigoCliente = cl.codigoCliente
        LEFT JOIN proyectos py ON c.codigoProyecto = py.codigoProyecto
        LEFT JOIN productos p ON dc.codigoProducto = p.codigoProducto
        LEFT JOIN actividadesEconomicas ac ON ac.codigo = cl.codigoActividadEconomica
        WHERE c.idCotizacion = ? ";
        $datos = array($codigoCotizacion);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }

    public function historialCotizaciones($start, $length, $search = "")
    {
        $searchQuery = "";
        $params = [];

        if (!empty($search)) {
            $searchQuery = "WHERE fecha = :search";
            $params = [':search' => $search];
        }

        $sql = "SELECT c.idCotizacion AS codigo,
                    cl.nombreCliente AS cliente,
                    py.nombreProyecto AS proyecto,
                    DATE(c.fecha) AS fecha
                FROM cotizaciones c
                INNER JOIN clientes cl ON c.codigoCliente = cl.codigoCliente
                INNER JOIN proyectos py ON c.codigoProyecto = py.codigoProyecto
                $searchQuery
                ORDER BY c.fecha DESC
                LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        $sqlTotal = "SELECT COUNT(*) AS total FROM cotizaciones en $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        return [
            'cotizacionesE' => $data,
            'total' => $total
        ];
    }

    public function vaciarDetalleCancelar($idUsuario)
    {
        $sql = "DELETE FROM detalleTemporal WHERE id_Usuario = ?";
        $datos = array($idUsuario);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            // Si se eliminaron registros, reiniciar el AUTO_INCREMENT
            $sqlReset = "ALTER TABLE detalleTemporal AUTO_INCREMENT = 1";
            $this->guardar($sqlReset, array());
            $res = "ok";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function getClienteNrc($codigoCliente)
    {
        $sql = "SELECT nrc FROM clientes
                WHERE codigoCliente = ?";
        $datos = array($codigoCliente);
        $data = $this->select($sql, $datos);
        return $data;
    }
}
