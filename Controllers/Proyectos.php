<?php
class Proyectos extends Controller
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

    public function buscarClientes()
    {
        $query = $_GET['q'] ?? '';
        $tipo = $_GET['tipo'] ?? 'todos'; // tipo puede ser "todos" o "conNrc"

        $data = $this->model->searchCliente($query, $tipo);
        $this->sendJsonResponse($data);
    }


    public function buscarResponsables()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->searchResponsable($query);
        $this->sendJsonResponse($data);
    }

    public function buscarEstados()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->searchEstado($query);
        $this->sendJsonResponse($data);
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

        // Obtén los datos paginados y filtrados, y el total
        $result = $this->model->getProyectos($start, $length, $search);
        $data = $result['proyectos'];
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
        $codigo = trim($_POST['codigoProyecto']);
        $nombre = trim($_POST['nombreProyecto']);
        $inicio = trim($_POST['fechaInicio']);
        $fin = trim($_POST['fechaFin']);
        $codigoCliente = isset($_POST['codigoCliente']) ? $_POST['codigoCliente'] : null;
        $valorCotizado = isset($_POST['valorCotizado']) ? (float) str_replace(',', '', $_POST['valorCotizado']) : 0;
        $ingresos = isset($_POST['ingresos']) ? (float) str_replace(',', '', $_POST['ingresos']) : 0;
        $salidas = isset($_POST['salidas']) ? (float) str_replace(',', '', $_POST['salidas']) : 0;
        $valorRentabilidad = isset($_POST['valorRentabilidad']) ? (float) str_replace(',', '', $_POST['valorRentabilidad']) : 0;
        $codigoEstadoProyecto = empty($_POST['estado']) ? NULL : $_POST['estado'];
        $codigoResponsable = trim($_POST['codigoResponsable']);
        $nombreResponsable = trim($_POST['nombreResponsable']);
        $telefonoResponsable = trim($_POST['telefono']);
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        if (strpos($codigo, ' ') !== false) {
            $msg = "El campo 'Código proyecto' no debe contener espacios.";
        } else if (empty($codigo) || empty($nombre) || empty($inicio) || empty($fin) || empty($valorCotizado) || empty($codigoResponsable) || empty($codigoCliente) || empty($nombreResponsable)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else if (strpos($codigoResponsable, ' ') !== false) {
            $msg = "El campo 'Código responsable' no debe contener espacios.";
        } else if (!empty($telefonoResponsable) && !$this->validarNumero($telefonoResponsable)) {
            $msg = "Registre un numero de Telefono valido";
        } else {
            $data = $this->model->registrarProyecto($codigo, $nombre, $inicio, $fin, $codigoCliente, $valorCotizado, $ingresos, $salidas, $valorRentabilidad, $codigoEstadoProyecto, $codigoResponsable, $nombreResponsable, $telefonoResponsable, $codigoUsuario);
            if ($data == "ok") {
                $msg = "si";
            } else if ($data == "existe") {
                $msg = "El codigo de proyecto ya existe";
            } else if ($data == "existes") {
                $msg = "El codigo de responsable ya existe";
            } else {
                $msg = "Error al registrar";
            }
        }
        $this->sendJsonResponse($msg);
    }

    public function modificar()
    {
        $nombre = trim($_POST['nombreProyecto']);
        $inicio = trim($_POST['fechaInicio']);
        $fin = trim($_POST['fechaFin']);
        $codigoCliente = isset($_POST['codigoCliente']) ? $_POST['codigoCliente'] : null;
        $valorCotizado = isset($_POST['valorCotizado']) ? (float) str_replace(',', '', $_POST['valorCotizado']) : 0;
        $ingresos = isset($_POST['ingresos']) ? (float) str_replace(',', '', $_POST['ingresos']) : 0;
        $salidas = isset($_POST['salidas']) ? (float) str_replace(',', '', $_POST['salidas']) : 0;
        $valorRentabilidad = isset($_POST['valorRentabilidad']) ? (float) str_replace(',', '', $_POST['valorRentabilidad']) : 0;
        $codigoEstadoProyecto = isset($_POST['estado']) && $_POST['estado'] !== "null" && !empty($_POST['estado']) ? $_POST['estado'] : NULL;
        $nombreResponsable = trim($_POST['nombreResponsable']);
        $telefonoResponsable = trim($_POST['telefono']);
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        $codigo = trim($_POST['codigoProyecto']);
        $codigoResponsable = trim($_POST['codigoResponsable']);
        if (strpos($codigo, ' ') !== false) {
            $msg = "El campo 'Código proyecto' no debe contener espacios.";
        } else if (empty($codigo) || empty($nombre) || empty($inicio) || empty($fin) || empty($valorCotizado) || empty($codigoResponsable) || empty($codigoCliente) || empty($nombreResponsable)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else if (strpos($codigoResponsable, ' ') !== false) {
            $msg = "El campo 'Código responsable' no debe contener espacios.";
        } else if (!empty($telefonoResponsable) && !$this->validarNumero($telefonoResponsable)) {
            $msg = "Registre un numero de Telefono valido";
        } else {
            $data = $this->model->modificarProyecto($nombre, $inicio, $fin, $codigoCliente, $valorCotizado, $ingresos, $salidas, $valorRentabilidad, $codigoEstadoProyecto, $codigoResponsable, $nombreResponsable, $telefonoResponsable, $codigoUsuario, $codigo);
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
        $data = $this->model->editarProyecto($codigo);
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
