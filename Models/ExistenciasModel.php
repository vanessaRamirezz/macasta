<?php
class ExistenciasModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    // para select buscador 
    public function searchProducto($query)
    {
        $sql = "SELECT p.codigoProducto AS codigo,
                    p.nombreProducto AS nombre
                FROM productos p
                LEFT JOIN existencias e ON p.codigoProducto = e.codigoProducto
                WHERE p.nombreProducto LIKE ?
                GROUP BY p.codigoProducto
                ORDER BY p.fechaCreacion DESC
                LIMIT 5;
                ";
        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    public function getExistencias($codigoProducto)
    {
        $sql = "SELECT cantidadProducto, codigoProyecto FROM existencias
                WHERE codigoProducto = ?;
                ";
        $datos = array($codigoProducto);
        $data = $this->selectAll($sql, $datos);
        return $data;
    }
}
