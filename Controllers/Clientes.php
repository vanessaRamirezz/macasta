<?php
class Clientes extends Controller
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

        $result = $this->model->getClientes($start, $length, $search);
        $data = $result['clientes'];
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
        $tipoPersona = $_POST['tipoPersona'];
        $tiposValidos = ['1', '2'];
        if (!in_array($tipoPersona, $tiposValidos)) {
            $this->sendJsonResponse("Tipo de persona inválido.", 400);
            return;
        }
        $codigo = trim($_POST['codigo']);
        $nombre = trim($_POST['nombre']);
        $nrc = empty($_POST['nrc']) ? NULL : $_POST['nrc'];
        $numeroTelefono = empty($_POST['numeroTelefono']) ? NULL : $_POST['numeroTelefono'];
        $contacto = empty($_POST['contacto']) ? NULL : $_POST['contacto'];
        $limiteCredito = isset($_POST['limiteCredito']) ? (float) str_replace(',', '', $_POST['limiteCredito']) : 0;
        $saldo = isset($_POST['saldo']) ? (float) str_replace(',', '', $_POST['saldo']) : 0;
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        $tipoIdentificacion = empty($_POST['tipoIdentificacion']) ? NULL : $_POST['tipoIdentificacion'];
        $numeroIdentifiacion = empty($_POST['numeroDocumento']) ? NULL : $_POST['numeroDocumento'];
        $nit = empty($_POST['nit']) ? NULL : $_POST['nit'];
        $codigoActividadEconomica = empty($_POST['actividadEconomica']) ? NULL : $_POST['actividadEconomica'];
        $nombreComercial = empty($_POST['nombreComercial']) ? NULL : $_POST['nombreComercial'];
        $departamento = empty($_POST['departamento']) ? NULL : $_POST['departamento'];
        $municipio = empty($_POST['municipio']) ? NULL : $_POST['municipio'];
        $complemento = trim($_POST['complemento']);
        $correo = empty($_POST['correo']) ? NULL : $_POST['correo'];

        if ($tipoPersona == 2) {
            if (empty($codigo)) {
                $msg = "El campo Código es requerido.";
            } else if (strpos($codigo, ' ') !== false) {
                $msg = "El campo Código cliente no debe contener espacios.";
            } else if (empty($nombre)) {
                $msg = "El nombre es requerido";
            } else if (empty($tipoIdentificacion)) {
                $msg = "Identificación es requerida";
            } else if (empty($numeroIdentifiacion)) {
                $msg = "Número de identificación es requerido";
            } else if (!empty($this->validarNrc($nrc))) {
                $msg = "El NRC no tiene el formato requerido";
            } else if (!empty($this->validarCodActividad($codigoActividadEconomica))) {
                $msg = "La actividad económica no tiene el formato requerido";
            } else if (empty($departamento) || empty($municipio) || empty($complemento)) {
                $msg = "Dirección es requerida";
            } else if (!empty($this->validarDepartamento($departamento))) {
                $msg = "Departamento no existe";
            } else if (!empty($this->validarMunicipio($municipio))) {
                $msg = "El municipio no tiene el formato requerido";
            } else if (!empty($this->validarNumero($numeroTelefono))) {
                $msg = "Registre un número de teléfono válido";
            } else if (!empty($this->validarCorreo($correo))) {
                $msg = "Formato de correo incorrecto";
            } else {
                // Validación del número de identificación según tipo
                $pattern = '';
                if ($tipoIdentificacion == '36') {
                    $pattern = "/^([0-9]{14}|[0-9]{9})$/";
                } else if ($tipoIdentificacion == '13') {
                    $pattern = "/^[0-9]{8}-[0-9]{1}$/";
                } else {
                    throw new Exception("Error: tipo de identificación no soportado.");
                }

                // Si el campo número de identificación no está vacío, validamos
                if (!empty($numeroIdentifiacion) && !preg_match($pattern, $numeroIdentifiacion)) {
                    $msg = "Formato de número de documento inválido";
                    $this->sendJsonResponse($msg);
                    return;
                }


                try {
                    $this->validarMunicipioPorDepartamento($departamento, $municipio);
                } catch (Exception $e) {
                    $this->sendJsonResponse($e->getMessage());
                    return;
                }

                // Registrar cliente
                $data = $this->model->registrarCliente($codigo, $nombre, $nrc, $numeroTelefono, $contacto, $limiteCredito, $saldo, $codigoUsuario, $tipoIdentificacion, $numeroIdentifiacion, $nit, $codigoActividadEconomica, $nombreComercial, $departamento, $municipio, $complemento, $correo, $tipoPersona);
                if ($data == "ok") {
                    $msg = "si";
                } else if ($data == "existe") {
                    $msg = "El código de cliente ya existe";
                } else {
                    $msg = "Error al registrar";
                }
            }
        } else if ($tipoPersona == 1) {
            if (empty($codigo)) {
                $msg = "El campo Código es requerido.";
            } else if (strpos($codigo, ' ') !== false) {
                $msg = "El campo Código cliente no debe contener espacios.";
            } else if (empty($nombre)) {
                $msg = "El nombre es requerido";
            } else if (empty($nrc)) {
                $msg = "El NRC es requerido";
            } else if (!empty($this->validarNrc($nrc))) {
                $msg = "El NRC no tiene el formato requerido";
            } else if (empty($nit)) {
                $msg = "El NIT es requerido";
            } else if (!empty($this->validarNit($nit))) {
                $msg = "El NIT no tiene el formato requerido";
            } else if (empty($codigoActividadEconomica)) {
                $msg = "La actividad economica es requerida";
            } else if (!empty($this->validarCodActividad($codigoActividadEconomica))) {
                $msg = "La actividad económica no tiene el formato requerido";
            } else if (empty($departamento) || empty($municipio) || empty($complemento)) {
                $msg = "Dirección es requerida";
            } else if (!empty($this->validarDepartamento($departamento))) {
                $msg = "Departamento no existe";
            } else if (!empty($this->validarMunicipio($municipio))) {
                $msg = "El municipio no tiene el formato requerido";
            } else if (!empty($this->validarNumero($numeroTelefono))) {
                $msg = "Registre un número de teléfono válido";
            } else if (empty($correo)) {
                $msg = "El correo electronico es requerido";
            } else if (!empty($this->validarCorreo($correo))) {
                $msg = "Formato de correo incorrecto";
            } else {

                try {
                    $this->validarMunicipioPorDepartamento($departamento, $municipio);
                } catch (Exception $e) {
                    $this->sendJsonResponse($e->getMessage());
                    return;
                }

                // Registrar cliente
                $data = $this->model->registrarCliente($codigo, $nombre, $nrc, $numeroTelefono, $contacto, $limiteCredito, $saldo, $codigoUsuario, $tipoIdentificacion, $numeroIdentifiacion, $nit, $codigoActividadEconomica, $nombreComercial, $departamento, $municipio, $complemento, $correo, $tipoPersona);
                if ($data == "ok") {
                    $msg = "si";
                } else if ($data == "existe") {
                    $msg = "El código de cliente ya existe";
                } else {
                    $msg = "Error al registrar";
                }
            }
        }

        $this->sendJsonResponse($msg);
    }



    public function modificar()
    {
        $tipoPersona = $_POST['tipoPersona'];
        $tiposValidos = ['1', '2'];
        if (!in_array($tipoPersona, $tiposValidos)) {
            $this->sendJsonResponse("Tipo de persona inválido.", 400);
            return;
        }

        $nombre = trim($_POST['nombre']);
        $nrc = (empty($_POST['nrc']) || $_POST['nrc'] === "---") ? NULL : $_POST['nrc'];
        $numeroTelefono = (empty($_POST['numeroTelefono']) || $_POST['numeroTelefono'] === "null" || $_POST['numeroTelefono'] === "---") ? NULL : $_POST['numeroTelefono'];
        $contacto = (empty($_POST['contacto']) || $_POST['contacto'] === "---") ? NULL : $_POST['contacto'];
        $limiteCredito = isset($_POST['limiteCredito']) ? (float) str_replace(',', '', $_POST['limiteCredito']) : 0;
        $saldo = isset($_POST['saldo']) ? (float) str_replace(',', '', $_POST['saldo']) : 0;
        $codigoUsuario = trim($_SESSION['codigoUsuario']);
        $tipoIdentificacion = (empty($_POST['tipoIdentificacion']) || $_POST['tipoIdentificacion'] === "---") ? NULL : $_POST['tipoIdentificacion'];
        $numeroIdentifiacion = (empty($_POST['numeroDocumento']) || $_POST['numeroDocumento'] === "---") ? NULL : $_POST['numeroDocumento'];
        $nit = (empty($_POST['nit']) || $_POST['nit'] === "---") ? NULL : $_POST['nit'];
        $codigoActividadEconomica = (empty($_POST['actividadEconomica']) || $_POST['actividadEconomica'] === "---") ? NULL : $_POST['actividadEconomica'];
        $nombreComercial = (empty($_POST['nombreComercial']) || $_POST['nombreComercial'] === "---") ? NULL : $_POST['nombreComercial'];
        $departamento = (empty($_POST['departamento']) || $_POST['departamento'] === "---") ? NULL : $_POST['departamento'];
        $municipio = (empty($_POST['municipio']) || $_POST['municipio'] === "---") ? NULL : $_POST['municipio'];
        $complemento = trim($_POST['complemento']);
        $correo = (empty($_POST['correo']) || $_POST['correo'] === "---") ? NULL : $_POST['correo'];
        $codigo = $_POST['codigo'];

        if ($tipoPersona == 2) {
            if (empty($nombre)) {
                $msg = "El nombre es requerido";
            } else if (empty($tipoIdentificacion)) {
                $msg = "Identificación es requerida";
            } else if (empty($numeroIdentifiacion)) {
                $msg = "Número de identificación es requerido";
            } else if (!empty($this->validarNrc($nrc))) {
                $msg = "El NRC no tiene el formato requerido";
            } else if (!empty($this->validarCodActividad($codigoActividadEconomica))) {
                $msg = "La actividad económica no tiene el formato requerido";
            } else if (empty($departamento) || empty($municipio) || empty($complemento)) {
                $msg = "Dirección es requerida";
            } else if (!empty($this->validarDepartamento($departamento))) {
                $msg = "Departamento no existe";
            } else if (!empty($this->validarMunicipio($municipio))) {
                $msg = "El municipio no tiene el formato requerido";
            } else if (!empty($this->validarNumero($numeroTelefono))) {
                $msg = "Registre un número de teléfono válido";
            } else if (!empty($this->validarCorreo($correo))) {
                $msg = "Formato de correo incorrecto";
            } else {
                // Validación del número de identificación según tipo
                $pattern = '';
                if ($tipoIdentificacion == '36') {
                    $pattern = "/^([0-9]{14}|[0-9]{9})$/";
                } else if ($tipoIdentificacion == '13') {
                    $pattern = "/^[0-9]{8}-[0-9]{1}$/";
                } else {
                    throw new Exception("Error: tipo de identificación no soportado.");
                }

                // Si el campo número de identificación no está vacío, validamos
                if (!empty($numeroIdentifiacion) && !preg_match($pattern, $numeroIdentifiacion)) {
                    $msg = "Formato de número de documento inválido";
                    $this->sendJsonResponse($msg);
                    return;
                }


                try {
                    $this->validarMunicipioPorDepartamento($departamento, $municipio);
                } catch (Exception $e) {
                    $this->sendJsonResponse($e->getMessage());
                    return;
                }

                // Registrar cliente
                $data = $this->model->modificarCliente($nombre, $nrc, $numeroTelefono, $contacto, $limiteCredito, $saldo, $codigoUsuario, $tipoIdentificacion, $numeroIdentifiacion, $nit, $codigoActividadEconomica, $nombreComercial, $departamento, $municipio, $complemento, $correo, $codigo);
                if ($data == "modificado") {
                    $msg = "modificado";
                } else {
                    $msg = "Error al modificar";
                }
            }
        } else if ($tipoPersona == 1) {
            if (empty($nombre)) {
                $msg = "El nombre es requerido";
            } else if (empty($nrc)) {
                $msg = "El NRC es requerido";
            } else if (!empty($this->validarNrc($nrc))) {
                $msg = "El NRC no tiene el formato requerido";
            } else if (empty($nit)) {
                $msg = "El NIT es requerido";
            } else if (!empty($this->validarNit($nit))) {
                $msg = "El NIT no tiene el formato requerido";
            } else if (empty($codigoActividadEconomica)) {
                $msg = "La actividad economica es requerida";
            } else if (!empty($this->validarCodActividad($codigoActividadEconomica))) {
                $msg = "La actividad económica no tiene el formato requerido";
            } else if (empty($departamento) || empty($municipio) || empty($complemento)) {
                $msg = "Dirección es requerida";
            } else if (!empty($this->validarDepartamento($departamento))) {
                $msg = "Departamento no existe";
            } else if (!empty($this->validarMunicipio($municipio))) {
                $msg = "El municipio no tiene el formato requerido";
            } else if (!empty($this->validarNumero($numeroTelefono))) {
                $msg = "Registre un número de teléfono válido";
            } else if (empty($correo)) {
                $msg = "El correo electronico es requerido";
            } else if (!empty($this->validarCorreo($correo))) {
                $msg = "Formato de correo incorrecto";
            } else {

                try {
                    $this->validarMunicipioPorDepartamento($departamento, $municipio);
                } catch (Exception $e) {
                    $this->sendJsonResponse($e->getMessage());
                    return;
                }

                $data = $this->model->modificarCliente($nombre, $nrc, $numeroTelefono, $contacto, $limiteCredito, $saldo, $codigoUsuario, $tipoIdentificacion, $numeroIdentifiacion, $nit, $codigoActividadEconomica, $nombreComercial, $departamento, $municipio, $complemento, $correo, $codigo);
                if ($data == "modificado") {
                    $msg = "modificado";
                } else {
                    $msg = "Error al modificar";
                }
            }
        }

        $this->sendJsonResponse($msg);
    }

    public function editar(string $codigoCliente)
    {
        $data = $this->model->editarCliente($codigoCliente);
        $this->sendJsonResponse($data);
    }

    // campos nuevos para clientes
    public function tipoPersona()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->seleccionarTipoPersona($query);
        $this->sendJsonResponse($data);
    }

    public function documentoIdentificacion()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->buscarDocumentoIdentificacion($query);
        $this->sendJsonResponse($data);
    }

    public function departamentos()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->buscarDepartamentos($query);
        $this->sendJsonResponse($data);
    }

    public function municipios()
    {
        $idDepartamento = $_GET['codigoDepartamento'] ?? null;

        if ($idDepartamento === null) {
            $this->sendJsonResponse(['error' => 'Falta el parámetro'], 400);
            return;
        }

        $data = $this->model->buscarMunicipios($idDepartamento);
        $this->sendJsonResponse($data);
    }


    public function actividadEconomica()
    {
        $query = $_GET['q'] ?? '';
        $data = $this->model->buscarActividadEcocomica($query);
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

    public function validarNumero($numeroTelefono)
    {
        if (empty($numeroTelefono)) {
            return false;
        }

        $expresion = "/^[0-9]+$/";
        if (!preg_match($expresion, $numeroTelefono)) {
            return true;
        }

        return false;
    }

    public function validarNrc($nrc)
    {
        if (empty($nrc)) {
            return false;
        }

        // Expresión regular con delimitadores `/`
        $expresion = "/^[0-9]{1,8}$/";

        if (!preg_match($expresion, $nrc)) {
            return true;
        }

        return false;
    }

    public function validarCodActividad($codigoActividadEconomica)
    {
        if (empty($codigoActividadEconomica)) {
            return false;
        }
        // Expresión regular con delimitadores `/`
        $expresion = "/^[0-9]{2,6}$/";

        if (!preg_match($expresion, $codigoActividadEconomica)) {
            return true;
        }
        return false;
    }

    public function validarDepartamento($departamento)
    {
        if (empty($departamento)) {
            return false;
        }
        // Expresión regular con delimitadores `/`
        $expresion = "/0[1-9]|1[0-4]$/";

        if (!preg_match($expresion, $departamento)) {
            return true;
        }

        return false;
    }

    public function validarMunicipio($municipio)
    {
        if (empty($municipio)) {
            return false;
        }
        // Expresión regular con delimitadores `/`
        $expresion = "/^[0-9]{2}$/";

        if (!preg_match($expresion, $municipio)) {
            return true;
        }

        return false;
    }

    public function validarCorreo($correo)
    {
        if (empty($correo)) {
            return false;
        }
        // Expresión regular con delimitadores `/`
        $expresion = "/^[^@\s]+@[^@\s]+\.[^@\s]+$/";

        if (!preg_match($expresion, $correo)) {
            return true;
        }

        return false;
    }

    public function validarNit($nit)
    {
        if (empty($nit)) {
            return false;
        }
        // Expresión regular con delimitadores `/`
        $expresion = "/^([0-9]{14}|[0-9]{9})$/";

        if (!preg_match($expresion, $nit)) {
            return true;
        }

        return false;
    }

    public function validarMunicipioPorDepartamento($departamento, $municipio)
    {
        // Validación del municipio basado en el departamento
        $municipiosValidos = [
            '00' => ['00'],
            '01' => ['13', '14', '15'],
            '02' => ['14', '15', '16', '17'],
            '03' => ['17', '18', '19', '20'],
            '04' => ['34', '35', '36'],
            '05' => ['23', '24', '25', '26', '27', '28'],
            '06' => ['20', '21', '22', '23', '24'],
            '07' => ['17', '18'],
            '08' => ['23', '24', '26'],
            '09' => ['10', '11'],
            '10' => ['14', '15'],
            '11' => ['24', '25', '26'],
            '12' => ['21', '22', '23'],
            '13' => ['27', '28'],
            '14' => ['19', '20'],
        ];

        if (!isset($municipiosValidos[$departamento])) {
            throw new Exception("El departamento no tiene municipios válidos definidos.");
        }

        $pattern = "/^(" . implode('|', $municipiosValidos[$departamento]) . ")$/";
        if (!empty($municipio) && !preg_match($pattern, $municipio)) {
            throw new Exception("Error: el municipio no corresponde al departamento.");
        }
    }
}
?>
<?php
// Habilitar el reporte de todos los errores
error_reporting(E_ALL);
ini_set('display_errors', '1');
