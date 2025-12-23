<?php
class ProductosModel extends Query
{

    private $codigo, $nombre, $costo, $precio, $codigoProveedor, $codigoAgrupacion, $codigoUsuario, $cantidad;
    public function __construct()
    {
        parent::__construct();
    }

    public function searchProveedores($query)
    {
        $sql = "SELECT codigoProveedor AS codigo,
                        nombreProveedor AS nombre
                FROM proveedores
                WHERE nombreProveedor LIKE ?
                ORDER BY fechaCreacion DESC
                LIMIT 5 ";
        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    public function searchAgrupaciones($query)
    {
        $sql = "SELECT codigoAgrupacion AS codigo,
                        nombreAgrupacion AS nombre
                FROM agrupaciones
                WHERE nombreAgrupacion LIKE ?
                LIMIT 5 ";
        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    public function getProductos($start, $length, $search = "")
    {
        // Agregar la condición de búsqueda
        $searchQuery = "";
        $params = [];
        if (!empty($search)) {
            $searchQuery = "WHERE p.codigoProducto LIKE :search OR p.nombreProducto LIKE :search";
            $params = [':search' => '%' . $search . '%'];
        }

        // Consulta para obtener productos con el total
        $sql = "SELECT 
                    p.codigoProducto AS codigo,
                    p.nombreProducto AS nombre,
                    p.costoProducto AS costo,
                    p.precioVenta AS precio,
                    SUM(IFNULL(e.cantidadProducto , '0')) AS cantidad, 
                    pr.nombreProveedor AS proveedor,
                    a.nombreAgrupacion AS agrupacion
                FROM 
                    productos p
                INNER JOIN 
                    proveedores pr ON p.codigoProveedor = pr.codigoProveedor
                LEFT JOIN 
                    existencias e ON p.codigoProducto = e.codigoProducto
                LEFT JOIN
                    agrupaciones a ON p.codigoAgrupacion = a.codigoAgrupacion
                $searchQuery
                GROUP BY p.codigoProducto
                ORDER BY p.fechaCreacion DESC
                LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        $sqlTotal = "SELECT COUNT(*) AS total FROM productos p $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        // Devuelve los productos y el total
        return [
            'productos' => $data,
            'total' => $total
        ];
    }

    public function registrarProducto(string $codigo, string $nombre, float $costo, float $precio, string $codigoProveedor, $codigoAgrupacion, string $codigoUsuario)
    {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
        $this->costo = $costo;
        $this->precio = $precio;
        $this->codigoProveedor = $codigoProveedor;
        $this->codigoAgrupacion = empty($codigoAgrupacion) ? NULL : $codigoAgrupacion;
        $this->codigoUsuario = $codigoUsuario;
        // $this->cantidad = $cantidad;
        $verificar = "SELECT * FROM productos WHERE codigoProducto = ?";
        $existe = $this->select($verificar, [$this->codigo]);
        if (empty($existe)) {
            $sql = "INSERT INTO productos(codigoProducto, nombreProducto, costoProducto, precioVenta, codigoProveedor, codigoAgrupacion, codigoUsuario) VALUES (?,?,?,?,?,?,?)";
            $datos = array($this->codigo, $this->nombre, $this->costo, $this->precio, $this->codigoProveedor, $this->codigoAgrupacion, $this->codigoUsuario);
            $data = $this->guardar($sql, $datos);

            // $sqlCantidad = "INSERT INTO existencias(codigoProducto, cantidadProducto) VALUES (?,?)";
            // $datosCantidad = array($this->codigo, $this->cantidad);
            // $data = $this->guardar($sqlCantidad, $datosCantidad);
            if ($data == 1) {
                $rest = "ok";
            } else {
                $rest = "error";
            }
        } else {
            $rest = "existe";
        }
        return $rest;
    }

    public function modificarProducto(string $nombre, float $costo, float $precio, string $codigoProveedor, $codigoAgrupacion, string $codigoUsuario, string $codigo)
    {
        $this->nombre = $nombre;
        $this->costo = $costo;
        $this->precio = $precio;
        $this->codigoProveedor = $codigoProveedor;
        $this->codigoAgrupacion = empty($codigoAgrupacion) ? NULL : $codigoAgrupacion;
        $this->codigoUsuario = $codigoUsuario;
        // $this->cantidad = $cantidad;
        $this->codigo = $codigo;
        $sql = "UPDATE productos SET nombreProducto = ?, costoProducto = ?, precioVenta = ?, codigoProveedor = ?, codigoAgrupacion = ?, codigoUsuario = ? WHERE codigoProducto = ?";
        $datos = array($this->nombre, $this->costo, $this->precio, $this->codigoProveedor, $this->codigoAgrupacion, $this->codigoUsuario, $this->codigo);
        $data = $this->guardar($sql, $datos);

        // $sqlCantidad = "UPDATE existencias SET cantidadProducto = ? WHERE codigoProducto = ?";
        // $datosCantidad = array($this->cantidad, $this->codigo);
        // $data = $this->guardar($sqlCantidad, $datosCantidad);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function editarProducto(string $codigoProducto)
    {
        $sql = "SELECT p.codigoProducto AS codigo,
                        p.nombreProducto AS nombre,
                        IFNULL(e.cantidadProducto, '0') AS cantidad,
                        pro.codigoProveedor AS proveedor,
                        pro.nombreProveedor AS nombreProveedor,
                        p.costoProducto AS costo,
                        p.precioVenta AS precio,
                        a.codigoAgrupacion AS agrupacion,
                        IFNULL(a.nombreAgrupacion, '-----') AS nombreAgrupacion
                FROM productos p
                LEFT JOIN 
                    existencias e ON p.codigoProducto = e.codigoProducto
                LEFT JOIN proveedores pro ON p.codigoProveedor = pro.codigoProveedor
                LEFT JOIN agrupaciones a ON p.codigoAgrupacion = a.codigoAgrupacion
                WHERE p.codigoProducto = ? ";
        $datos = array($codigoProducto);
        $data = $this->select($sql, $datos);
        return $data;
    }
}
