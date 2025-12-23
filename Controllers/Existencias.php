<?php
class Existencias extends Controller
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
    
    public function existencias(string $codigoProducto)
    {
        $data = $this->model->getExistencias($codigoProducto);
        $this->sendJsonResponse($data);
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


