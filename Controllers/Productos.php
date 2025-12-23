<?php
class Productos extends Controller
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

    public function buscarProveedores(){
        $query = $_GET['q'] ?? '';
        $data = $this->model->searchProveedores($query);
        $this->sendJsonResponse($data);
    }

    public function buscarAgrupaciones(){
        $query = $_GET['q'] ?? '';
        $data = $this->model->searchAgrupaciones($query);
        $this->sendJsonResponse($data);
    }

    public function listar()
    {
        $start = $_POST['start']; // Índice de inicio
        $length = $_POST['length']; // Número de registros por página
        $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : ""; // Búsqueda por defecto
        $customSearch = isset($_POST['searchValue']) ? $_POST['searchValue'] : ""; // Búsqueda personalizada

        // Si hay búsqueda personalizada, la aplicamos
        if (!empty($customSearch)) {
            $search = $customSearch;
        }

        // Obtén los datos paginados y filtrados, y el total
        $result = $this->model->getProductos($start, $length, $search);
        $data = $result['productos'];
        $total = $result['total'];

        // Agrega las acciones a cada producto
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] =
                '<div class="text-center">
                <button class="btn btn-editar" type="button" id="btn-color" data-id="' . $data[$i]["codigo"] . '">
                    <i class="fas fa-edit"></i>
                </button>
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

    public function registrar()
    {
        $nombre = trim($_POST['nombre']);
        $codigo = trim($_POST['codigo']);
        $codigoProveedor = isset($_POST['codigoProveedor']) ? $_POST['codigoProveedor'] : null;
        $costo = isset($_POST['costo']) ? (float) str_replace(',', '', $_POST['costo']) : 0;
        $precio = isset($_POST['precio']) ? (float) str_replace(',', '', $_POST['precio']) : 0;
        // $cantidad = trim($_POST['cantidad']);
        $codigoAgrupacion = empty($_POST['codigoAgrupacion']) ? NULL : $_POST['codigoAgrupacion'];
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        if (strpos($codigo, ' ') !== false) {
            $msg = "El campo 'Código producto' no debe contener espacios.";
        } else if (empty($nombre) || empty($codigo) || empty($codigoProveedor) ) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else {
            $data = $this->model->registrarProducto($codigo, $nombre, $costo, $precio, $codigoProveedor, $codigoAgrupacion, $codigoUsuario);
            if ($data == "ok") {
                $msg = "si";
            } else if ($data == "existe") {
                $msg = "El codigo de Producto ya existe";
            } else {
                $msg = "Error al registrar";
            }
        }
        $this->sendJsonResponse($msg);
    }

    public function modificar()
    {
        $nombre = trim($_POST['nombre']);
        $codigoProveedor = isset($_POST['codigoProveedor']) ? $_POST['codigoProveedor'] : null;
        $costo = isset($_POST['costo']) ? (float) str_replace(',', '', $_POST['costo']) : 0;
        $precio = isset($_POST['precio']) ? (float) str_replace(',', '', $_POST['precio']) : 0;
        // $cantidad = trim($_POST['cantidad']);
        $codigoAgrupacion = isset($_POST['codigoAgrupacion']) && $_POST['codigoAgrupacion'] !== "null" && !empty($_POST['codigoAgrupacion']) ? $_POST['codigoAgrupacion'] : NULL;
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        $codigo = $_POST['codigo'];
        if (empty($nombre) || empty($codigoProveedor)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else {
            $data = $this->model->modificarProducto($nombre, $costo, $precio, $codigoProveedor, $codigoAgrupacion, $codigoUsuario, $codigo);
            if ($data == "modificado") {
                $msg = "modificado";
            } else {
                $msg = "Error al modificar";
            }
        }
        $this->sendJsonResponse($msg);
    }

    public function editar(string $codigoProducto)
    {
        $data = $this->model->editarProducto($codigoProducto);
        $this->sendJsonResponse($data);
    }

    public function salir()
    {
        session_destroy();
        header("location: " . base_url);
    }
    
    public function validarTexto($input)
    {
        $expresion = "/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s&]+$/";
        if (preg_match($expresion, $input)) {
            return true;
        } else {
            return false;
        }
    }

    public function validarNumero($input)
    {
        $expresion = "/^[0-9]+$/";
        if (preg_match($expresion, $input)) {
            return true;
        } else {
            return false;
        }
    }
}
?>
<?php
// Habilitar el reporte de todos los errores
error_reporting(E_ALL);
ini_set('display_errors', '1');


