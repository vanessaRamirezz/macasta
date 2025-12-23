<?php
class Salidas extends Controller
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

    public function registrar()
    {
        $numeroDocumento = trim($_POST['numeroDocumento']);
        $documentoFe = trim($_POST['numeroDocumentoFe']);
        $fecha = trim($_POST['fechaCompra']);
        $codigoProveedor = isset($_POST['selectProveedor']) ? $_POST['selectProveedor'] : null;
        $codigoTipoMovimiento = isset($_POST['selectTipoMovimiento']) ? $_POST['selectTipoMovimiento'] : null;
        $codigoProyecto = isset($_POST['codigoProyecto']) ? $_POST['codigoProyecto'] : null;
        $observacion = !empty($_POST['observacion']) ? $_POST['observacion'] : 'Ninguna';
        if (empty($numeroDocumento) || empty($documentoFe) || empty($fecha) || empty($codigoProveedor) || empty($codigoTipoMovimiento) || empty($codigoProyecto)) {
            $msg = "Todos los campos con (*) son obligatorios";
        }  else {
            $data = $this->model->registrarSalida($numeroDocumento, $documentoFe, $fecha, $codigoProveedor, $codigoTipoMovimiento, $codigoProyecto, $observacion);
            if ($data == "ok") {
                $msg = "si";
            } else if ($data == "existe") {
                $msg = "El codigo de Documento ya existe";
            } else {
                $msg = "Error al registrar";
            }
        }
        $this->sendJsonResponse($msg);
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


