<?php
class Empleados extends Controller
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

        $result = $this->model->getEmpleados($start, $length, $search);
        $data = $result['empleados'];
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
        $codigo = trim($_POST['codigo']);
        $nombre = trim($_POST['nombre']);
        $numeroTelefono = empty(trim($_POST['numeroTelefono'])) ? NULL : trim($_POST['numeroTelefono']);
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        if (strpos($codigo, ' ') !== false) {
            $msg = "El campo 'Código' no debe contener espacios.";
        } else if (empty($codigo) || empty($nombre)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else if ($numeroTelefono !== NULL && !$this->validarNumero($numeroTelefono)) {
            $msg = "Registre un numero de Telefono valido";
        } else {
            $data = $this->model->registrarEmpleado($codigo, $nombre, $numeroTelefono, $codigoUsuario);
            if ($data == "ok") {
                $msg = "si";
            } else if ($data == "existe") {
                $msg = "El codigo de Empleado ya existe";
            } else {
                $msg = "Error al registrar";
            }
        }
        $this->sendJsonResponse($msg);
    }

    public function editar(string $codigo)
    {
        $data = $this->model->editarEmpleado($codigo);
        $this->sendJsonResponse($data);
    }

    public function modificar()
    {
        $nombre = trim($_POST['nombre']);
        $numeroTelefono = empty(trim($_POST['numeroTelefono'])) ? NULL : trim($_POST['numeroTelefono']);
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        $codigo = $_POST['codigo'];
        if (empty($nombre)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else if ($numeroTelefono !== NULL && !$this->validarNumero($numeroTelefono)) {
            $msg = "Registre un numero de Telefono valido";
        } else {
            $data = $this->model->modificarEmpleado($nombre, $numeroTelefono, $codigoUsuario, $codigo);
            if ($data == "modificado") {
                $msg = "modificado";
            } else {
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


