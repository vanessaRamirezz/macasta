<?php
class Agrupaciones extends Controller
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

        $result = $this->model->getAgrupaciones($start, $length, $search);
        $data = $result['agrupaciones'];
        $total = $result['total'];

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] =
                '<div class="text-center">
                        <button class="btn btn-editar" type="button" data-id="' . $data[$i]["codigo"] . '"><i class="fas fa-edit"></i></button>
                    <div/>';
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
        $codigoAgrupacion = trim($_POST['agrupacionCodigo']);
        $nombre = trim($_POST['nombreAgrupacion']);
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        if (strpos($codigoAgrupacion, ' ') !== false) {
            $msg = "El campo 'Código Agrupación' no debe contener espacios.";
        } else if (empty($codigoAgrupacion) || empty($nombre)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else {
            $data = $this->model->registrarAgrupacion($codigoAgrupacion, $nombre, $codigoUsuario);
            if ($data == "ok") {
                $msg = "si";
            } else if ($data == "existe") {
                $msg = "El codigo de Agrupación ya existe";
            } else {
                $msg = "Error al registrar";
            }
        }
        $this->sendJsonResponse($msg);
    }

    public function modificar()
    {
        $nombre = trim($_POST['nombreAgrupacion']);
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        $codigoAgrupacion = trim($_POST['agrupacionCodigo']);
        if (empty($codigoAgrupacion) || empty($nombre)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else {
            $data = $this->model->modificarAgrupacion($nombre, $codigoUsuario, $codigoAgrupacion);
            if ($data == "modificado") {
                $msg = "modificado";
            }else {
                $msg = "Error al modificar";
            }
        }
        $this->sendJsonResponse($msg);
    }
    public function editar(string $codigoAgrupacion)
    {
        $data = $this->model->editarAgrupacion($codigoAgrupacion);
        $this->sendJsonResponse($data);
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

    public function salir()
    {
        session_destroy();
        header("location: " . base_url);
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




