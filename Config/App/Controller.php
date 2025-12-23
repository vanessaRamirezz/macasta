<?php
class Controller
{

    protected $views, $model;

    public function __construct()
    {
        $this->views = new Views();
        $this->cargarModel();
    }

    public function cargarModel()
    {
        $model = get_class($this) . "Model";
        $ruta = "Models/" . $model . ".php";
        if (file_exists($ruta)) {
            require_once $ruta;
            $this->model = new $model();
        }
    }

    public function sendJsonResponse($data)
    {
        // Encabezados CORS para permitir peticiones desde otros orígenes
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
}
