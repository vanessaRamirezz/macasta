<?php
class Responsables extends Controller
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

        $result = $this->model->getResponsables($start, $length, $search);
        $data = $result['responsables'];
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
        $codigoResponsable = trim($_POST['codigo']);
        $nombreResponsable = trim($_POST['nombre']);
        $telefonoResponsable = trim($_POST['telefono']);
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        if (strpos($codigoResponsable, ' ') !== false) {
            $msg = "El campo 'Código proyecto' no debe contener espacios.";
        } else if (empty($codigoResponsable) || empty($nombreResponsable)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else if (!empty($telefonoResponsable) && !$this->validarNumero($telefonoResponsable)) {
            $msg = "Registre un numero de Telefono valido";
        } else {
            $data = $this->model->registrarResponsable($codigoResponsable, $nombreResponsable, $telefonoResponsable, $codigoUsuario);
            if ($data == "ok") {
                $msg = "si";
            } else if ($data == "existe") {
                $msg = "El codigo de responsable ya existe";
            } else {
                $msg = "Error al registrar";
            }
        }
        $this->sendJsonResponse($msg);
    }

    public function editar(string $codigoResponsable)
    {
        $data = $this->model->editarResponsable($codigoResponsable);
        $this->sendJsonResponse($data);
    }

    public function modificar()
    {
        $nombreResponsable = trim($_POST['nombre']);
        $telefonoResponsable = trim($_POST['telefono']);
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        $codigoResponsable = trim($_POST['codigo']);
        if (empty($nombreResponsable)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else {
            $data = $this->model->modificarResponsable($nombreResponsable, $telefonoResponsable, $codigoUsuario, $codigoResponsable);
            if ($data == "modificado") {
                $msg = "modificado";
            }else {
                $msg = "Error al modificar";
            }
        }
        $this->sendJsonResponse($msg);
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


