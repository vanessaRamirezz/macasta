<?php
class Home extends Controller{

    public function __construct()
    {
        session_start();
        parent::__construct();
    }

    public function index(){
        if(!empty($_SESSION['codigoUsuario'])){
            header("Location: ".base_url. "Usuarios");
        }
        $this->views->getView($this, "index");
    }
}