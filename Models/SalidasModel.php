<?php
class SalidasModel extends Query
{
    private $numeroDocumento, $documentoFe,
        $fecha, $codigoProveedor,
        $codigoCliente, $codigoTipoMovimiento,
        $total, $codigoProyecto,
        $observacion;

    public function __construct()
    {
        parent::__construct();
    }

    public function registrarSalida(
        string $numeroDocumento,
        string $documentoFe,
        string $fecha,
        string $codigoProveedor,
        string $codigoTipoMovimiento,
        string $codigoProyecto,
        string $observacion
    ) {
        $this->numeroDocumento = $numeroDocumento;
        $this->documentoFe = $documentoFe;
        $this->fecha = $fecha;
        $this->codigoProveedor = $codigoProveedor;
        $this->codigoTipoMovimiento = $codigoTipoMovimiento;
        $this->codigoProyecto = $codigoProyecto;
        $this->observacion = $observacion;

        $verificar = "SELECT * FROM encabezadoMovimiento WHERE numeroDocumento = ?";
        $existe = $this->select($verificar, [$this->numeroDocumento]);

        if (empty($existe)) {
            $sql = "INSERT INTO encabezadoMovimiento (numeroDocumento, numeroDocumentoFe, fecha, codigoProveedor, codigoTipoMovimiento, codigoProyecto, observacion)
                VALUES (?,?,?,?,?,?,?)";
            $datos = array(
                $this->numeroDocumento,
                $this->documentoFe,
                $this->fecha,
                $this->codigoProveedor,
                $this->codigoTipoMovimiento,
                $this->codigoProyecto,
                $this->observacion
            );
            $data = $this->guardar($sql, $datos);
            if ($data == 1) {
                $res = "ok";
            } else {
                $res = "error";
            }
        } else {
            $res = "existe";
        }
        return $res;
    }
}
