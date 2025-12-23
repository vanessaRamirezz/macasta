<?php
class DetalleTemporal extends Controller
{

    public function __construct()
    {
        session_start();
        parent::__construct();
    }

    public function index()
    {
        if (empty($_SESSION['codigoUsuario'])) {
            header("Location: " . base_url);
        }
        $this->views->getView($this, "index");
    }

    public function buscarProducto()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->searchProducto($query);
        $this->sendJsonResponse($data);
    }

    public function ingresar()
    {
        $idProducto = isset($_POST['codigoProducto']) ? $_POST['codigoProducto'] : null;
        $cantidad = isset($_POST['cantidadProducto']) ? (int) $_POST['cantidadProducto'] : 0;
        $precioCosto = isset($_POST['precioCosto']) ? (float) str_replace(',', '', $_POST['precioCosto']) : 0;
        $precioVenta = isset($_POST['precioVenta']) ? (float) str_replace(',', '', $_POST['precioVenta']) : 0;

        if (empty($idProducto) || $cantidad <= 0) {
            $msg = "Debe seleccionar un producto y una cantidad válida";
        } else {
            try {
                // Iniciar la transacción
                $this->model->iniciarTransaccion();

                $datos = $this->model->getProducto($idProducto);
                if (!$datos) {
                    throw new Exception("Producto no encontrado");
                }

                $idUsuario = $_SESSION['codigoUsuario'];
                $comprobar = $this->model->consultarDetalle($idProducto, $idUsuario);

                if (empty($comprobar)) {
                    $total = $precioCosto * $cantidad;
                    $data = $this->model->registrarDetalle($idProducto, $cantidad, $precioCosto, $precioVenta, $total, $idUsuario);
                    if ($data !== "ok") {
                        throw new Exception("Error al registrar detalle");
                    }

                    // $resultSaldo = $this->model->actualizarPrecioProducto($precioCosto, $precioVenta, $idProducto);
                    // if ($resultSaldo !== "ok") {
                    //     throw new Exception("Error al actualizar costo y venta del producto");
                    // }

                    $msg = "ok";
                } else {
                    $totalCantidad = $comprobar['cantidad'] + $cantidad;
                    $totalActualizar = $totalCantidad * $precioCosto;
                    $data = $this->model->actualizarDetalle($totalCantidad, $precioCosto, $precioVenta, $totalActualizar, $idProducto, $idUsuario);
                    if ($data !== "modificado") {
                        throw new Exception("Error al modificar detalle");
                    }

                    // $resultSaldo = $this->model->actualizarPrecioProducto($precioCosto, $precioVenta, $idProducto);
                    // if ($resultSaldo !== "ok") {
                    //     throw new Exception("Error al actualizar costo y venta del producto");
                    // }
                    $msg = "modificado";
                }

                // Confirmar la transacción
                $this->model->confirmarTransaccion();
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                $this->model->revertirTransaccion();
                $msg = "Error inesperado: " . $e->getMessage();
            }
        }
        $this->sendJsonResponse($msg);
    }


    public function salir()
    {
        session_destroy();
        header("location: " . base_url);
    }
}
?>
<?php
// Habilitar el reporte de todos los errores
error_reporting(E_ALL);
ini_set('display_errors', '1');


