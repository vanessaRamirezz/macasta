<?php
class Proveedores extends Controller
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

        $result = $this->model->getProveedores($start, $length, $search);
        $data = $result['proveedores'];
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
        $numeroTelefono = trim($_POST['numeroTelefono']);
        $contacto = trim($_POST['contacto']);
        $limiteCredito = isset($_POST['limiteCredito']) ? (float) str_replace(',', '', $_POST['limiteCredito']) : 0;
        $saldo = isset($_POST['saldo']) ? (float) str_replace(',', '', $_POST['saldo']) : 0;
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        if (strpos($codigo, ' ') !== false) {
            $msg = "El campo 'Código proveedor' no debe contener espacios.";
        } else if (empty($codigo) || empty($nombre) || empty($numeroTelefono)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else if (!$this->validarNumero($numeroTelefono)) {
            $msg = "Registre un numero de Telefono valido";
        } else {
            $data = $this->model->registrarProveedor($codigo, $nombre, $numeroTelefono, $contacto, $limiteCredito, $saldo, $codigoUsuario);
            if ($data == "ok") {
                $msg = "si";
            } else if ($data == "existe") {
                $msg = "El codigo de Proveedor ya existe";
            } else {
                $msg = "Error al registrar";
            }
        }
        $this->sendJsonResponse($msg);
    }

    public function modificar()
    {
        $nombre = trim($_POST['nombre']);
        $numeroTelefono = trim($_POST['numeroTelefono']);
        $contacto = trim($_POST['contacto']);
        $limiteCredito = isset($_POST['limiteCredito']) ? (float) str_replace(',', '', $_POST['limiteCredito']) : 0;
        $saldo = isset($_POST['saldo']) ? (float) str_replace(',', '', $_POST['saldo']) : 0;
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        $codigo = $_POST['codigo'];
        if (empty($nombre) || empty($numeroTelefono)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else if (!$this->validarNumero(($numeroTelefono))) {
            $msg = "Registre un numero de Telefono valido";
        } else {
            $data = $this->model->modificarProveedor($nombre, $numeroTelefono, $contacto, $limiteCredito, $saldo, $codigoUsuario, $codigo);
            if ($data == "modificado") {
                $msg = "modificado";
            } else {
                $msg = "Error al modificar";
            }
        }
        $this->sendJsonResponse($msg);
    }

    public function editar(string $codigoProveedor)
    {
        $data = $this->model->editarProveedor($codigoProveedor);
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
