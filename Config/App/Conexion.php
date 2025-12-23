<?php

class Conexion {
    private $connect;
    public function __construct() {
        $pdo = "mysql:host=" . host . ";dbname=" . bd . ";charset=" . charset;
        try {
            $this->connect = new PDO($pdo,user,clave);
            $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Error en la conexion".$e->getMessage();
        }
    }
    public function conect(){
        return $this->connect;
    }
}