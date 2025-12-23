<?php
class InicioModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getDatos (string $table){
        $sql = "SELECT COUNT(*) AS total FROM $table";
        $data = $this->select($sql);
        return $data;
    }
}
