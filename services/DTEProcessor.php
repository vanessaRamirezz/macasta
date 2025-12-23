<?php
class DTEProcessor
{
    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function procesarDTE($datosGenerados, $datosEmpresa)
    {
        $codigoGeneracion = $datosGenerados['identificacion']->codigoGeneracion;
        $ambiente = $datosGenerados['identificacion']->ambiente;

        $version = $datosGenerados['identificacion']->version;
        $tipoDte = $datosGenerados['identificacion']->tipoDte;
        $passwordPri = 'CASTAMAGA100';

        $dteExistente = $this->model->buscarDTEPorCodigo($codigoGeneracion);

        if ($dteExistente && $dteExistente['estado_envio'] !== 'FALLIDO') {
            $jsonFirmado = $dteExistente['json_firmado'];
        } else {
            $jsonFirmado = $this->firmarDTE($datosGenerados, $datosEmpresa['nit'], $passwordPri);
            if (!$jsonFirmado) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error al firmar el DTE'
                ]);
                return;
            }

            if (!$dteExistente) {
                $this->model->guardarNuevoDTE([
                    'ambiente' => $ambiente,
                    'intentos' => 1,
                    'versiondte' => $version,
                    'tipo_dte' => $tipoDte,
                    'json_firmado' => $jsonFirmado,
                    'codigo_generacion' => $codigoGeneracion,
                ]);
            }
        }

        $token = $this->model->getWebToken();
        $maxIntentos = 3;
        $intentos = $dteExistente ? $dteExistente['intentos'] : 1;
        $enviado = false;

        for (; $intentos <= $maxIntentos; $intentos++) {
            $respuesta = $this->enviarDTEAHacienda($datosGenerados, $token, $jsonFirmado);

            if ($respuesta['estado'] === 'PROCESADO') {
                $this->model->actualizarEstadoEnvio($codigoGeneracion, 'ENVIADO');
                $enviado = true;
                break;
            }

            sleep(2);
        }

        if (!$enviado) {
            $this->model->actualizarEstadoEnvio($codigoGeneracion, 'FALLIDO');
        }

        return [
            'status' => $enviado ? 'ok' : 'fallido',
            'intentos' => $intentos,
            'respuesta_hacienda' => $respuesta
        ];
    }

    public function firmarDTE($dteJson, $nit, $password)
    {
        $url = 'http://localhost:8113/firmardocumento/';
        $payload = json_encode([
            'nit' => $nit,
            'activo' => true,
            'passwordPri' => $password,
            'dteJson' => $dteJson
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($statusCode === 200 && isset($decoded['status']) && $decoded['status'] === 'OK') {
            return $decoded['body']; // RETORNA EL JSON FIRMADO COMO ARRAY

        }

        return false; // En caso de error
    }


    public function enviarDTEAHacienda($datosGenerados, $token, $jsonFirmado)
    {
        $codigoGeneracion = $datosGenerados['identificacion']->codigoGeneracion;
        $ambiente = $datosGenerados['identificacion']->ambiente;
        $version = $datosGenerados['identificacion']->version;
        $tipoDte = $datosGenerados['identificacion']->tipoDte;
        $tokenReal = $token['token'];
        $url = 'https://api.dtes.mh.gob.sv/fesv/recepciondte';

        $postData = json_encode([
            'ambiente' => $ambiente,
            'idEnvio' => rand(100000, 999999),
            'version' => $version,
            'tipoDte' => $tipoDte,
            'documento' => $jsonFirmado,
            'codigoGeneracion' => $codigoGeneracion,
        ], JSON_UNESCAPED_UNICODE);


        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $tokenReal,
            'Content-Type: application/json',
            'User-Agent: sistema-facturacion'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Devuelve la respuesta cruda directamente decodificada
        $decoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Si no se pudo decodificar, al menos devuelve como texto
        return [
            "estado" => "FALLIDO",
            "codigo_http" => $httpCode,
            "respuesta_texto" => $response
        ];
    }
}
