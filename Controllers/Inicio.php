<?php
class Inicio extends Controller
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
        $data['clientes'] = $this->model->getDatos("clientes");
        $data['proveedores'] = $this->model->getDatos("proveedores");
        $data['proyectos'] = $this->model->getDatos("proyectos");
        $this->views->getView($this, "index", $data);
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


