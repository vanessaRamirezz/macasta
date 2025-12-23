<?php
class Aplicaciones extends Controller
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
        $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : "";
        $customSearch = isset($_POST['searchValue']) ? $_POST['searchValue'] : "";

        if (!empty($customSearch)) {
            $search = $customSearch;
        }

        $result = $this->model->getAplicaciones($start, $length, $search);
        $data = $result['aplicaciones'];
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
        $codigo = trim($_POST['aplicacionCodigo']);
        $nombre = trim($_POST['nombreAplicacion']);
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        if (empty($codigo) || empty($nombre)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else {
            $data = $this->model->registrarAplicacion($codigo, $nombre, $codigoUsuario);
            if ($data == "ok") {
                $msg = "si";
            } else if ($data == "existe") {
                $msg = "El codigo de aplicación ya existe";
            } else {
                $msg = "Error al registrar";
            }
        }
        $this->sendJsonResponse($msg);
    }

    public function modificar()
    {
        $codigo = $_POST['aplicacionCodigo'];
        $nombre = trim($_POST['nombreAplicacion']);
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        if (empty($codigo) || empty($nombre)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else {
            $data = $this->model->modificarAplicacion($nombre, $codigoUsuario, $codigo);
            if ($data == "modificado") {
                $msg = "modificado";
            } else {
                $msg = "Error al modificar";
            }
        }
        $this->sendJsonResponse($msg);
    }

    public function editar(string $codigo)
    {
        $data = $this->model->editarAplicacion($codigo);
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
}
?>
<?php
// Habilitar el reporte de todos los errores
error_reporting(E_ALL);
ini_set('display_errors', '1');


