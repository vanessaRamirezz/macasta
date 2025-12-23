<?php
class EstadosProyectos extends Controller
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
        $start = $_POST['start']; 
        $length = $_POST['length']; 
        $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : ""; // Búsqueda por defecto
        $customSearch = isset($_POST['searchValue']) ? $_POST['searchValue'] : ""; // Búsqueda personalizada

        // Si hay búsqueda personalizada, la aplicamos
        if (!empty($customSearch)) {
            $search = $customSearch;
        }

        $result = $this->model->getEstadoProyectos($start, $length, $search);
        $data = $result['estadoProyecto'];
        $total = $result['total'];

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] =
                '<div class="text-center">
                        <button class="btn btn-editar" type="button" data-id="' . $data[$i]["codigo"] . '"><i class="fas fa-edit"></i></button>
                    <div/>';
        }

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
        $codigoEstado = trim($_POST['codigoEstadoProyecto']);
        $nombre = trim($_POST['nombreEstado']);
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        if (strpos($codigoEstado, ' ') !== false) {
            $msg = "El campo 'Código' no debe contener espacios.";
        } else if (empty($codigoEstado) || empty($nombre)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else {
            $data = $this->model->registrarEstadoProyecto($codigoEstado, $nombre, $codigoUsuario);
            if ($data == "ok") {
                $msg = "si";
            } else if ($data == "existe") {
                $msg = "El codigo de Estado ya existe";
            } else {
                $msg = "Error al registrar";
            }
        }
        $this->sendJsonResponse($msg);
    }

    public function modificar()
    {
        $nombre = trim($_POST['nombreEstado']);
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        $codigoEstado = trim($_POST['codigoEstadoProyecto']);
        if (empty($codigoEstado) || empty($nombre)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else {
            $data = $this->model->modificarEstadoProyecto($nombre, $codigoUsuario, $codigoEstado);
            if ($data == "modificado") {
                $msg = "modificado";
            } else {
                $msg = "Error al modificar";
            }
        }
        $this->sendJsonResponse($msg);
    }

    public function editar(string $codigoEstado)
    {
        $data = $this->model->editarEstadoProyecto($codigoEstado);
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
}
?>
<?php
// Habilitar el reporte de todos los errores
error_reporting(E_ALL);
ini_set('display_errors', '1');


