<?php
class Bancos extends Controller
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

    // cuenta bancaria
    public function listar()
    {
        $start = $_POST['start'];
        $length = $_POST['length'];
        $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : "";
        $customSearch = isset($_POST['searchValue']) ? $_POST['searchValue'] : "";

        if (!empty($customSearch)) {
            $search = $customSearch;
        }

        $result = $this->model->getCuentaBancaria($start, $length, $search);
        $data = $result['cuentaBancaria'];
        $total = $result['total'];

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] =
                '<div class="text-center">
                    <button class="btn btn-editar" type="button" data-id="' . $data[$i]["codigo"] . '"><i class="fas fa-edit"></i></button>
                </div>';
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
        $codigoCuentaBancaria = trim($_POST['codigoCuentaBancaria']);
        $nombreCuentaBancaria = trim($_POST['nombreCuentaBancaria']);
        $codigoBanco = $_POST['selectBanco'] ?? null;
        $saldoInicial = isset($_POST['saldoInicial']) ? (float) str_replace(',', '', $_POST['saldoInicial']) : 0;
        $ingresos = isset($_POST['ingresos']) ? (float) str_replace(',', '', $_POST['ingresos']) : 0;
        $salidas = isset($_POST['salidas']) ? (float) str_replace(',', '', $_POST['salidas']) : 0;
        $saldo = isset($_POST['saldo']) ? (float) str_replace(',', '', $_POST['saldo']) : 0;
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        if (strpos($codigoCuentaBancaria, ' ') !== false) {
            $msg = "El campo 'Código Cuenta Bancaria' no debe contener espacios.";
        } else if (empty($codigoBanco) || empty($codigoCuentaBancaria) || empty($nombreCuentaBancaria) || empty($saldoInicial)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else {
            try {
                $this->model->iniciarTransaccion();
                $data = $this->model->registrarCuenta($codigoCuentaBancaria, $nombreCuentaBancaria, $codigoBanco, $saldoInicial, $ingresos, $salidas, $saldo, $codigoUsuario);
                if ($data == "ok") {

                    //calcular saldo de la cuenta al crearla
                    $nuevoSaldoCuenta = (float)$saldoInicial + (float)$ingresos - (float)$salidas;
                    $resultado = $this->model->actualizarSaldoCuentas($nuevoSaldoCuenta, $codigoCuentaBancaria);
                    if ($resultado != "ok") {
                        throw new Exception("Error al actualizar saldo para la cuenta");
                    }

                    $this->model->confirmarTransaccion();
                    $msg = "si";
                } else if ($data == "existe") {
                    $this->model->revertirTransaccion();
                    $msg = "El codigo de Cuenta ya existe";
                } else {
                    $this->model->revertirTransaccion();
                    $msg = "Error al registrar";
                }
            } catch (Exception $e) {
                $this->model->revertirTransaccion();
                $msg = "Error inesperado: " . $e->getMessage();
            }
        }
        $this->sendJsonResponse($msg);
    }

    public function modificar()
    {
        $nombreCuentaBancaria = trim($_POST['nombreCuentaBancaria']);
        $saldoInicial = isset($_POST['saldoInicial']) ? (float) str_replace(',', '', $_POST['saldoInicial']) : 0;
        $ingresos = isset($_POST['ingresos']) ? (float) str_replace(',', '', $_POST['ingresos']) : 0;
        $salidas = isset($_POST['salidas']) ? (float) str_replace(',', '', $_POST['salidas']) : 0;
        $saldo = isset($_POST['saldo']) ? (float) str_replace(',', '', $_POST['saldo']) : 0;
        $codigoUsuario = trim($_SESSION['codigoUsuario']);

        $codigoCuentaBancaria = trim($_POST['codigoCuentaBancaria']);
        if (empty($nombreCuentaBancaria)) {
            $msg = "El nombre de banco no puede ingresarse vacío";
        } else if (empty($saldoInicial)) {
            $msg = "El saldo inicial no puede ingresarse vacio";
        } else {
            try {
                $this->model->iniciarTransaccion();

                $data = $this->model->modificarCuenta($nombreCuentaBancaria, $saldoInicial, $ingresos, $salidas, $saldo, $codigoUsuario, $codigoCuentaBancaria);

                if ($data == "modificado") {

                    //actualizar el saldo Inicial de la cuenta para recalcular el saldo de la cuenta
                    $datos  = $this->model->obtenerSaldoCuenta($codigoCuentaBancaria);
                    $ingresoCuentaActual = (float) ($datos[0]['ingresos'] ?? 0);
                    $salidaActualCuenta = (float) ($datos[0]['salidas'] ?? 0);
                    $nuevoSaldoCuenta = (float)$saldoInicial + (float)$ingresoCuentaActual - (float)$salidaActualCuenta;
                    $resultado = $this->model->actualizarSaldoCuentas($nuevoSaldoCuenta, $codigoCuentaBancaria);
                    if ($resultado != "ok") {
                        throw new Exception("Error al actualizar saldo para la cuenta");
                    }

                    $this->model->confirmarTransaccion();
                    $msg = "modificado";
                } else {
                    $this->model->revertirTransaccion();
                    $msg = "Error al modificar";
                }
            } catch (Exception $e) {
                $this->model->revertirTransaccion();
                $msg = "Error inesperado: " . $e->getMessage();
            }
        }

        $this->sendJsonResponse($msg);
    }

    public function editar(string $codigoCuentaBancaria)
    {
        $data = $this->model->editarCuentaBancaria($codigoCuentaBancaria);
        $this->sendJsonResponse($data);
    }


    // codigo banco 
    public function listarBancos()
    {
        $start = $_POST['start'];
        $length = $_POST['length'];
        $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : "";
        $customSearch = isset($_POST['searchValue']) ? $_POST['searchValue'] : "";

        if (!empty($customSearch)) {
            $search = $customSearch;
        }

        $result = $this->model->getBancos($start, $length, $search);
        $data = $result['banco'];
        $total = $result['total'];

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] =
                '<div class="text-center">
                    <button class="btn btn-editar" type="button" data-id="' . $data[$i]["codigo"] . '"><i class="fas fa-edit"></i></button>
                </div>';
        }
        $result = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data
        );

        $this->sendJsonResponse($result);
    }

    public function ingresar()
    {
        $codigoBanco = trim($_POST['codigoBancoRegistrar']);
        $nombreBanco = trim($_POST['nombreBancoRegistrar']);

        // Validar que el código de banco no contenga espacios
        if (strpos($codigoBanco, ' ') !== false) {
            $msg = "El campo 'Código Banco' no debe contener espacios.";
        } else if (empty($codigoBanco) || empty($nombreBanco)) {
            $msg = "Todos los campos con (*) son obligatorios";
        } else {
            $data = $this->model->registrarBanco($codigoBanco, $nombreBanco);
            if ($data == "ok") {
                $msg = "si";
            } else if ($data == "existe") {
                $msg = "El código de Banco ya existe";
            } else {
                $msg = "Error al registrar";
            }
        }

        $this->sendJsonResponse($msg);
    }


    public function actualizar()
    {
        $nombreBanco = trim($_POST['nombreBancoRegistrar']);
        $codigoBanco = trim($_POST['codigoBancoRegistrar']);
        if (empty($nombreBanco)) {
            $msg = "El nombre de banco no puede ingresarse vacío";
        } else {
            $data = $this->model->modificarBanco($nombreBanco, $codigoBanco);
            if ($data == "modificado") {
                $msg = "modificado";
            } else {
                $msg = "Error al modificar";
            }
        }

        $this->sendJsonResponse($msg);
    }

    public function editarBanco(string $codigoBanco)
    {
        $data = $this->model->editarBanco($codigoBanco);
        $this->sendJsonResponse($data);
    }

    // Listado
    public function listarBancosRegistros()
    {
        $start = $_POST['start'];
        $length = $_POST['length'];
        $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : "";
        $customSearch = isset($_POST['searchValue']) ? $_POST['searchValue'] : "";

        if (!empty($customSearch)) {
            $search = $customSearch;
        }

        $result = $this->model->getListado($start, $length, $search);
        $data = $result['banco'];
        $total = $result['total'];

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['acciones'] =
                '<div class="text-center">
                    <button class="btn btn-editar" type="button" data-id="' . $data[$i]["codigo"] . '"><i class="fas fa-eye"></i></button>
                </div>';
        }
        $result = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data
        );

        $this->sendJsonResponse($result);
    }

    public function listadoCuentas(string $codigoBanco)
    {
        $data = $this->model->verListado($codigoBanco);
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


