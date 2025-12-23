<?php
class DetalleTemporalModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    // para select buscador 
    public function searchProducto($query)
    {
        $sql = "SELECT p.codigoProducto AS codigo,
                        p.nombreProducto AS nombre,
                        -- e.cantidadProducto AS cantidad,
                        p.costoProducto AS costo,
                        p.precioVenta AS precio
        FROM productos p
        -- LEFT JOIN existencias e ON p.codigoProducto = e.codigoProducto
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
        // Contar cuántos ítems lleva ese usuario
        $sqlCount = "SELECT COUNT(*) AS total FROM detalleTemporal WHERE id_Usuario = ?";
        $row = $this->select($sqlCount, [$idUsuario]);
        $nuevoItem = $row['total'] + 1;

        $sql = "INSERT INTO detalleTemporal(item, codigoProducto, cantidad, costoProducto, precioVenta, total, id_Usuario)
                VALUES (?,?,?,?,?,?,?)";
        $datos = array($nuevoItem, $idProducto, $cantidad, $costo, $precio, $total, $idUsuario);
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

    // Para actualizar existencias de productos
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
}
