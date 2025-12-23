<?php
class Usuarios extends Controller
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

        $result = $this->model->getUsuarios($start, $length, $search);
        $data = $result['usuarios'];
        $total = $result['total'];

        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['estado'] == 1) {
                $data[$i]['estado'] = '<span class="badge badge-success">Activo</span>';
                $data[$i]['acciones'] =
                    '<div class="text-center">
                    <button class="btn btn-editar" type="button" data-id="' . $data[$i]["codigo"] . '"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-eliminar" type="button" data-id="' . $data[$i]["codigo"] . '"><i class="fas fa-trash-alt"></i></button>
                <div/>';
            } else {
                $data[$i]['estado'] = '<span class="badge badge-danger">Inactivo</span>';
                $data[$i]['acciones'] =
                    '<div class = "text-center">
                    <button class="btn btn-activar" type="button" data-id="' . $data[$i]["codigo"] . '"><i class="fas fa-minus"></i></button>
                <div/>';
            }
        }
        $result = array(
            "draw" => intval($_POST['draw']),
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data
        );

        $this->sendJsonResponse($result);
    }

    public function validar()
    {
        if (empty($_POST['usuario']) || empty($_POST['clave'])) {
            $msg = "Los campos están vacíos";
        } else {
            $usuario = $_POST['usuario'];
            $clave = $_POST['clave'];

            // Obtener el usuario de la base de datos para verificar si está inactivo
            $data = $this->model->getUsuario($usuario, null, 0);

            if ($data) {
                $msg = "Usuario Inactivo";
            } else {
                // Obtener el usuario activo
                $data = $this->model->getUsuario($usuario, null, 1);

                if ($data) {
                    // Verificar si la contraseña ingresada coincide con el hash almacenado
                    if (password_verify($clave, $data['claveUsuario'])) {
                        // La contraseña es correcta, iniciar sesión
                        $_SESSION['codigoUsuario'] = $data['codigoUsuario'];
                        $_SESSION['nombreUsuario'] = $data['nombreUsuario'];
                        $_SESSION['nivelSeguridadUsuario'] = $data['nivelSeguridadUsuario'];
                        $_SESSION['nombreCompleto'] = $data['nombreCompleto'];
                        $_SESSION['tipoIdentificacion'] = $data['tipoIdentificacion'];
                        $_SESSION['numeroIdentificacion'] = $data['numeroIdentificacion'];
                        $msg = "ok";
                    } else {
                        $msg = "Credenciales incorrectas";
                    }
                } else {
                    $msg = "Credenciales incorrectas";
                }
            }
        }
        $this->sendJsonResponse($msg);
    }



    public function registrar()
    {
        $codigo = trim($_POST['codigo']);
        $nombre = trim($_POST['nombre']);
        $clave = trim($_POST['clave']);
        $confirmar = trim($_POST['confirmar']);
        $nivelSeguridad = trim($_POST['nivelSeguridad']);
        $nombreCompleto = trim($_POST['nombreCom']);
        $tipoIdentificacion = empty($_POST['tipoIdentificacion']) ? NULL : $_POST['tipoIdentificacion'];
        $numeroIdentifiacion = empty($_POST['numeroDocumento']) ? NULL : $_POST['numeroDocumento'];

        if (strpos($codigo, ' ') !== false) {
            $msg = "El campo 'Código' no debe contener espacios.";
        } else if (empty($codigo) || empty($nombre) || empty($clave) || empty($nivelSeguridad) || empty($nombreCompleto) || empty($tipoIdentificacion) || empty($numeroIdentifiacion)) {
            $msg = "Todos los campos son obligatorios";
        } else if ($clave !== $confirmar) {
            $msg = "Las claves no coinciden";
        } else {

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

            // 🔹 Encriptar la clave usando el algoritmo más seguro disponible
            $claveEncriptada = password_hash($clave, PASSWORD_DEFAULT);

            $data = $this->model->registrarUsuario($codigo, $nombre, $claveEncriptada, $nivelSeguridad, $nombreCompleto, $tipoIdentificacion, $numeroIdentifiacion);

            if ($data == "ok") {
                $msg = "si";
            } else if ($data == "existe") {
                $msg = "El código de usuario ya existe";
            } else {
                $msg = "Error al registrar";
            }
        }
        $this->sendJsonResponse($msg);
    }


    public function modificar()
    {
        $codigo = $_POST['codigo'];
        $nombre = trim($_POST['nombre']);
        $nivelSeguridad = trim($_POST['nivelSeguridad']);
        $nombreCompleto = trim($_POST['nombreCom']);
        $tipoIdentificacion = empty($_POST['tipoIdentificacion']) ? NULL : $_POST['tipoIdentificacion'];
        $numeroIdentifiacion = empty($_POST['numeroDocumento']) ? NULL : $_POST['numeroDocumento'];
        if (empty($nombre) || empty($nivelSeguridad) || empty($nombreCompleto) || empty($tipoIdentificacion) || empty($numeroIdentifiacion)) {
            $msg = "Todos los campos son obligatorios";
        } else {
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

            $data = $this->model->modificarUsuario($nombre, $nivelSeguridad, $nombreCompleto, $tipoIdentificacion, $numeroIdentifiacion, $codigo);
            if ($data == "modificado") {
                $msg = "modificado";
            } else {
                $msg = "Error al modificar";
            }
        }
        $this->sendJsonResponse($msg);
    }

    public function editar(string $codigoUsuario)
    {
        $data = $this->model->editarUsuario($codigoUsuario);
        $this->sendJsonResponse($data);
    }

    public function eliminar(string $codigoUsuario)
    {
        $data = $this->model->accionUsuario(0, $codigoUsuario);
        if ($data == 1) {
            $msg = "ok";
        } else {
            $msg = "Error al eliminar";
        }
        $this->sendJsonResponse($msg);
    }

    public function activar(string $codigoUsuario)
    {
        $data = $this->model->accionUsuario(1, $codigoUsuario);
        if ($data == 1) {
            $msg = "ok";
        } else {
            $msg = "Error al activar";
        }
        $this->sendJsonResponse($msg);
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