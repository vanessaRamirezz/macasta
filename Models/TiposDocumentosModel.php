<?php
class TiposDocumentosModel extends Query
{

    private $codigo, $nombre, $codigoUsuario;
    public function __construct()
    {
        parent::__construct();
    }

    public function getTiposDocumentos($start, $length, $search = "")
    {
        $searchQuery = "";
        $params = [];
        if (!empty($search)) {
            $searchQuery = "WHERE codigoTipoDocumento LIKE :search OR nombreTipoDocumento LIKE :search";
            $params = [':search' => '%' . $search . '%'];
        }

        // Consulta principal: obtener los datos paginados
        $sql = "SELECT codigoTipoDocumento AS codigo, 
                nombreTipoDocumento AS nombre
                FROM tiposDocumentos 
                $searchQuery
                ORDER BY fechaCreacion DESC 
                LIMIT $start, $length";
            
        $data = $this->selectAll($sql, $params);

        // Consulta secundaria: obtener el total
        $sqlTotal = "SELECT COUNT(*) AS total FROM tiposDocumentos $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        return [
            'aplicaciones' => $data,
            'total' => $total
        ];
    }
    public function registrarTipoDocumento(string $codigo, string $nombre, string $codigoUsuario)
    {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
        $this->codigoUsuario = $codigoUsuario;
        $verificar = "SELECT * FROM tiposDocumentos WHERE codigoTipoDocumento = ?";
        $existe = $this->select($verificar, [$this->codigo]);
        if (empty($existe)) {
            $sql = "INSERT INTO tiposDocumentos(codigoTipoDocumento, nombreTipoDocumento, codigoUsuario) VALUES (?,?,?)";
            $datos = array($this->codigo, $this->nombre,$this->codigoUsuario);
            $data = $this->guardar($sql, $datos);
            if ($data == 1) {
                $rest = "ok";
            } else {
                $rest = "error";
            }
        } else {
            $rest = "existe";
        }
        return $rest;
    }
    public function modificarTipoDocumento( string $codigo, string $nombre, string $codigoUsuario)
    {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
        $this->codigoUsuario = $codigoUsuario;
        $sql = "UPDATE tiposDocumentos SET nombreTipoDocumento = ?, codigoUsuario = ? WHERE codigoTipoDocumento = ?";
        $datos = array($this->codigo, $this->nombre, $this->codigoUsuario);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }
    public function editarTipoDocumento(string $codigo)
    {
        $sql = "SELECT codigoTipoDocumento AS codigo,
                        nombreTipoDocumento AS nombre
                        FROM tiposDocumentos WHERE codigoTipoDocumento = ?";
        $datos = array($codigo);
        $data = $this->select($sql, $datos);
        return $data;
    }
}
