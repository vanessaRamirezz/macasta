<?php

class Query extends Conexion
{
    private $pdo, $con, $sql, $datos;
    public function __construct()
    {
        $this->pdo = new Conexion();
        $this->con = $this->pdo->conect();
    }

    public function select(string $sql, array $params = [])
    {
        $this->sql = $sql;
        $result = $this->con->prepare($this->sql); // Preparar la consulta
        $result->execute($params); // Ejecutar la consulta con los parámetros
        $data = $result->fetch(PDO::FETCH_ASSOC); // Obtener el resultado
        return $data; // Devolver los datos
    }

    public function selectAll(string $sql, array $params = [])
    {
        $this->sql = $sql;
        $result = $this->con->prepare($this->sql);
        $result->execute($params); // Ejecutar la consulta con los parámetros
        $data = $result->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }


    public function guardar(string $sql, array $datos)
    {
        $this->sql = $sql;
        $this->datos = $datos;
        $insert = $this->con->prepare($this->sql);
        $data = $insert->execute($this->datos);
        if ($data) {
            $res = 1;
        } else {
            $res = 0;
        }
        return $res;
    }

    public function iniciarTransaccion()
    {
        $this->con->beginTransaction();
    }

    public function confirmarTransaccion()
    {
        $this->con->commit();
    }

    public function revertirTransaccion()
    {
        $this->con->rollBack();
    }

    public function ultimoId()
    {
        return $this->con->lastInsertId();
    }
}
