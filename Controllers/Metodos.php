<?php
require 'vendor/autoload.php';

use Ramsey\Uuid\Uuid;

class Metodos extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        parent::__construct();
    }

    //variables globales
    public function variablesGlobales()
    {
        return [
            // SECCION 1 IDENTIFICACION
            'ambiente' => "01", //modo prueba // modo produccion(01)

            //datos para generar el numero de control definidos
            'codigoCasaMatriz' => "M001",
            'codigoPuntoVenta' => "P001",
            'tipoMoneda' => "USD",
            'codEstableMH' => 'M001', //opcional
            'codEstable' => null, //opcional
            'codPuntoVentaMH' => "P001", //opcional
            'codPuntoVenta' => null, //opcional
        ];
    }

    // generar numero de control
    // public function numeroDeControl($tipoDocumento)
    // {
    //     $variblesGlobales = $this->variablesGlobales();
    //     $ambiente = $variblesGlobales['ambiente'];

    //     $codigoCasaMatriz = str_pad($variblesGlobales['codigoCasaMatriz'], 4, "0", STR_PAD_LEFT);  // 4 dígitos
    //     $codigoPuntoVenta = str_pad($variblesGlobales['codigoPuntoVenta'], 4, "0", STR_PAD_LEFT);  // 4 dígitos
    //     $correlativo = $this->model->generarCorrelativos($tipoDocumento, $ambiente);

    //     // Generar el número de control
    //     $numeroControl = "DTE-" . $tipoDocumento . '-' . $codigoCasaMatriz . $codigoPuntoVenta . "-" . $correlativo;

    //     // Definir el patrón regex
    //     $pattern = "/^DTE-[0-9]{2}-[A-Z0-9]{8}-[0-9]{15}$/";

    //     // Validar el número de control
    //     if (!preg_match($pattern, $numeroControl)) {
    //         throw new Exception("Error: El número de control no cumple con el formato requerido.");
    //     }


    //     return $numeroControl;
    // }


    public function numeroDeControl($tipoDocumento)
    {
        $variblesGlobales = $this->variablesGlobales();
        $ambiente = $variblesGlobales['ambiente'];

        $codigoCasaMatriz = str_pad($variblesGlobales['codigoCasaMatriz'], 4, "0", STR_PAD_LEFT);  // 4 dígitos
        $codigoPuntoVenta = str_pad($variblesGlobales['codigoPuntoVenta'], 4, "0", STR_PAD_LEFT);  // 4 dígitos

        // Aquí llamamos al nuevo método que reserva el correlativo temporal
        $correlativo = $this->model->reservarCorrelativo($tipoDocumento, $ambiente);

        // Generar el número de control
        $numeroControl = "DTE-" . $tipoDocumento . '-' . $codigoCasaMatriz . $codigoPuntoVenta . "-" . $correlativo;

        $pattern = "/^DTE-[0-9]{2}-[A-Z0-9]{8}-[0-9]{15}$/";

        if (!preg_match($pattern, $numeroControl)) {
            throw new Exception("Error: El número de control no cumple con el formato requerido.");
        }

        return $numeroControl;
    }


    // generar codigo de generacion
    public function codigoGeneracion()
    {

        $uuid = strtoupper(Uuid::uuid4()->toString());
        $expresionRegular = "/^[A-F0-9]{8}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{12}$/";

        if (!preg_match($expresionRegular, $uuid)) {
            throw new Exception("Error: El codigo de generacion no cumple con el formato requerido.");
        }
        return $uuid;
    }

    // validar formato de fecha yyy-mm-dd
    public function fecha_YYY_MM_DD($fecha)
    {
        // Validar formato "Y-m-d" con expresión regular
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new Exception("Formato de fecha inválido. Debe ser YYYY-MM-DD");
        }

        $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);

        // Verifica que se haya creado correctamente y normaliza la hora
        if (!$fechaObj) {
            throw new Exception("No se pudo crear el objeto DateTime");
        }

        $fechaObj->setTime(0, 0, 0); // Elimina la hora para comparar solo fechas
        return $fechaObj;
    }

    // validar formato de hora hh-mm-ss
    public function hora_HH_MM_SS($horaEmi)
    {
        $pattern = "/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/";

        // Validar la hora con preg_match()
        if (!preg_match($pattern, $horaEmi)) {
            throw new Exception("Error: la hora no tiene el formato requerido.");
        }

        return $horaEmi;
    }

    // combinar fecha y hora en datetime
    public function fechaHora_YYYY_MM_DD_HH_MM_SS($fecha, $hora)
    {
        if ($fecha instanceof DateTime) {
            $fecha = $fecha->format('Y-m-d');
        }

        $fechaHora = DateTime::createFromFormat('Y-m-d H:i:s', "$fecha $hora");
        if (!$fechaHora) {
            throw new Exception("Error: Fecha y hora no pudieron combinarse.");
        }

        return $fechaHora;
    }



    // validar si una fecha es el ultimo dia del mes
    public function esUltimoDiaDelMes(DateTime $fecha)
    {
        $ultimoDia = clone $fecha;
        $ultimoDia->modify('last day of this month');
        return $fecha->format('Y-m-d') === $ultimoDia->format('Y-m-d');
    }

    // validaciones de nit emisor
    public function validarNit($nit)
    {

        // if ($nit === null || $nit === "") {
        //     return null; // se puede omitir
        // }

        // nit sin guiones
        $pattern = "/^([0-9]{14}|[0-9]{9})$/";

        if (!preg_match($pattern, $nit)) {
            $msg = 'El NIT no tiene el formato requerido.';
            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
            return;
        }

        return $nit;
    }

    // validaciones de nrc
    public function validarNrc($nrc)
    {
        $pattern = "/^[0-9]{1,8}$/";

        if (!preg_match($pattern, $nrc)) {
            $msg = 'El NRC no tiene el formato requerido.';
            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
            return;
        }

        return $nrc;
    }

    // validaciones codigos de actividad ecoomicas segun catalogo
    public function validarCodActividad($ActividadEconomica)
    {
        $pattern = "/^[0-9]{2,6}$/";

        if (!preg_match($pattern, $ActividadEconomica)) {
            $msg = 'El codigo de actividad economica no tiene el formato requerido.';
            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
            return;
        }

        return $ActividadEconomica;
    }

    // validar el departamento
    public function validarDepartamento($departamento)
    {
        $pattern = "/^0[1-9]|1[0-4]$/";

        if (!preg_match($pattern, $departamento)) {
            $msg = 'El departamento no tiene el formato requerido.';
            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
            return;
        }

        return $departamento;
    }

    // validar el municipio
    public function validarMunicipio($municipio)
    {
        $pattern = "/^[0-9]{2}$/";

        if (!preg_match($pattern, $municipio)) {
            $msg = 'El municipio no tiene el formato requerido.';
            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
            return;
        }

        return $municipio;
    }

    // validar numero de dui
    public function validarDui($dui)
    {
        // Eliminar cualquier guion del valor
        $dui = str_replace('-', '', $dui);

        $pattern = "/^[0-9]{9}$/";

        if (!preg_match($pattern, $dui)) {
            $msg = 'El DUI no tiene el formato requerido.';
            $this->sendJsonResponse(['status' => 'error', 'message' => $msg]);
            return;
        }

        return $dui;
    }


    // cantidades en letras
    public function cantidadLetras($cantidadConvertir)
    {
        $fmt = new NumberFormatter("es_ES", NumberFormatter::SPELLOUT);
        $montoEntero = floor($cantidadConvertir);
        $montoDecimal = round(($cantidadConvertir - $montoEntero) * 100);
        $textEntero = strtoupper($fmt->format($montoEntero));
        $textDecimal = str_pad($montoDecimal, 2, "0", STR_PAD_LEFT);
        $text = $textEntero . " CON " . $textDecimal . "/100 USD";
        return $text;
    }

    public function obtenerYGuardarToken()
    {
        // 1. Verificar si ya hay un token válido en la base de datos
        $tokenExistente = $this->model->getWebToken();

        if (!empty($tokenExistente) && isset($tokenExistente['token'])) {
            return ['token' => $tokenExistente['token']];
        }

        // 2. Configuración para solicitar nuevo token
        // $url = 'https://apitest.dtes.mh.gob.sv/seguridad/auth';
        $url = URL_AUTENTICAR_EN_API;
        $user = '02101205171019';
        $pwd = 'Macasta251*';
        //$pwd = 'MACASTA1*';
        $maxIntentos = 3;
        $intentos = 0;

        while ($intentos < $maxIntentos) {
            $intentos++;

            $postFields = http_build_query([
                'user' => $user,
                'pwd' => $pwd
            ]);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'User-Agent: MiSistemaFacturacion',
                    'Accept: application/json'
                ],
                CURLOPT_TIMEOUT => 8,
                CURLOPT_CONNECTTIMEOUT => 5
            ]);

            $response = curl_exec($ch);

            // 3. Verificar si hubo error de conexión
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                curl_close($ch);

                if ($intentos >= $maxIntentos) {
                    return [
                        'status' => 'error',
                        'message' => "No se pudo conectar a Hacienda después de $intentos intentos automaticos realizados.",
                        'detalle' => $error_msg
                    ];
                }

                sleep(8);
                continue;
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $decoded = json_decode($response, true);

            // 4. Verificar respuesta de Hacienda
            if ($httpCode === 200 && isset($decoded['status']) && $decoded['status'] === 'OK') {
                $token = $decoded['body']['token'];
                $fecha_obtenido = date('Y-m-d H:i:s');
                $fecha_expira = date('Y-m-d H:i:s', strtotime('+24 hours'));

                $resultado = $this->model->guardarToken($token, $fecha_obtenido, $fecha_expira);

                if ($resultado === "ok") {
                    return ['token' => $token];
                } else {
                    return [
                        'status' => 'error',
                        'message' => 'El token se obtuvo pero no se guardó en la base de datos'
                    ];
                }
            } else {
                // 5. Error en respuesta
                if ($intentos >= $maxIntentos) {
                    return [
                        'status' => 'error',
                        'message' => 'Error al obtener el token después de varios intentos.',
                        'detalle' => isset($decoded['message']) ? $decoded['message'] : $response
                    ];
                }

                sleep(8);
            }
        }

        // 6. Fallback en caso de que todo falle (por seguridad)
        return [
            'status' => 'error',
            'message' => 'Error no identificado al intentar obtener el token'
        ];
    }
}
?>
<?php
// Habilitar el reporte de todos los errores
error_reporting(E_ALL);
ini_set('display_errors', '1');