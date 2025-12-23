<?php
class ClientesModel extends Query
{

    private $codigo,
        $nombre,
        $nrc,
        $telefono,
        $contacto,
        $limiteCredito,
        $saldo,
        $codigoUsuario,
        $tipoIdentificacion,
        $numeroIdentifiacion,
        $nit,
        $codigoActividadEconomica,
        $nombreComercial,
        $departamento,
        $municipio,
        $complemento,
        $correo,
        $tipoPersona;
    public function __construct()
    {
        parent::__construct();
    }

    public function getClientes($start, $length, $search = "")
    {
        $searchQuery = "";
        $params = [];
        if (!empty($search)) {
            $searchQuery = "WHERE codigoCliente LIKE :search OR nombreCliente LIKE :search";
            $params = [':search' => '%' . $search . '%'];
        }

        // Consulta principal: obtener los datos paginados
        $sql = "SELECT codigoCliente AS codigo, 
            nombreCliente AS nombre,
            nrc,
            numeroTelefonoCliente AS telefono, 
            contactoCliente AS contacto, 
            limiteCreditoCliente AS creditoLimite, 
            saldoCliente AS saldo 
            FROM clientes 
            $searchQuery
            ORDER BY fechaCreacion DESC 
            LIMIT $start, $length";

        $data = $this->selectAll($sql, $params);

        // Consulta secundaria: obtener el total
        $sqlTotal = "SELECT COUNT(*) AS total FROM clientes $searchQuery";
        $total = $this->select($sqlTotal, $params)['total'] ?? 0;

        return [
            'clientes' => $data,
            'total' => $total
        ];
    }

    public function registrarCliente(
        string $codigo,
        string $nombre,
        ?string $nrc,
        ?string $telefono,
        ?string $contacto,
        float $limiteCredito,
        float $saldo,
        string $codigoUsuario,
        $tipoIdentificacion,
        $numeroIdentifiacion,
        $nit,
        $codigoActividadEconomica,
        $nombreComercial,
        $departamento,
        $municipio,
        $complemento,
        $correo,
        $tipoPersona
    ) {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
        $this->nrc =  $nrc;
        $this->telefono = $telefono;
        $this->contacto = $contacto;
        $this->limiteCredito = $limiteCredito;
        $this->saldo = $saldo;
        $this->codigoUsuario = $codigoUsuario;
        $this->tipoIdentificacion =  $tipoIdentificacion;
        $this->numeroIdentifiacion =  $numeroIdentifiacion;
        $this->nit =  $nit;
        $this->codigoActividadEconomica =  $codigoActividadEconomica;
        $this->nombreComercial =  $nombreComercial;
        $this->departamento =  $departamento;
        $this->municipio =  $municipio;
        $this->complemento =  $complemento;
        $this->correo =  $correo;
        $this->tipoPersona =  $tipoPersona;
        $verificar = "SELECT * FROM clientes WHERE codigoCliente = ?";
        $existe = $this->select($verificar, [$this->codigo]);
        if (empty($existe)) {
            $sql = "INSERT INTO clientes(codigoCliente, nombreCliente, nrc, numeroTelefonoCliente, contactoCliente, limiteCreditoCliente, saldoCliente, codigoUsuario, tipoIdentificacion, numeroIdentificacion, nit, codigoActividadEconomica, nombreComercial, departamento, municipio, complemento, correo, tipoPersona) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $datos = array($this->codigo, $this->nombre, $this->nrc, $this->telefono, $this->contacto, $this->limiteCredito, $this->saldo, $this->codigoUsuario, $this->tipoIdentificacion, $this->numeroIdentifiacion, $this->nit, $this->codigoActividadEconomica, $this->nombreComercial, $this->departamento, $this->municipio, $this->complemento, $this->correo, $this->tipoPersona);
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

    public function modificarCliente(
        string $nombre,
        ?string $nrc,
        ?string $telefono,
        ?string $contacto,
        float $limiteCredito,
        float $saldo,
        string $codigoUsuario,
        $tipoIdentificacion,
        $numeroIdentifiacion,
        $nit,
        $codigoActividadEconomica,
        $nombreComercial,
        $departamento,
        $municipio,
        $complemento,
        $correo,
        string $codigo
    ) {
        $this->nombre = $nombre;
        $this->nrc =  $nrc;
        $this->telefono = $telefono;
        $this->contacto = $contacto;
        $this->limiteCredito = $limiteCredito;
        $this->saldo = $saldo;
        $this->codigoUsuario = $codigoUsuario;
        $this->tipoIdentificacion = $tipoIdentificacion;
        $this->numeroIdentifiacion = $numeroIdentifiacion;
        $this->nit = $nit;
        $this->codigoActividadEconomica = $codigoActividadEconomica;
        $this->nombreComercial = $nombreComercial;
        $this->departamento = $departamento;
        $this->municipio = $municipio;
        $this->complemento = $complemento;
        $this->correo = $correo;
        $this->codigo = $codigo;
        $sql = "UPDATE clientes SET nombreCliente = ?, nrc = ?, numeroTelefonoCliente = ?, contactoCliente = ?, limiteCreditoCliente = ?, saldoCliente = ?, codigoUsuario = ?, tipoIdentificacion = ?, numeroIdentificacion = ?, nit = ?, codigoActividadEconomica = ?, nombreComercial = ?, departamento = ?, municipio = ?, complemento = ?, correo = ? WHERE codigoCliente = ?";
        $datos = array($this->nombre, $this->nrc,  $this->telefono, $this->contacto, $this->limiteCredito, $this->saldo, $this->codigoUsuario, $this->tipoIdentificacion, $this->numeroIdentifiacion, $this->nit, $this->codigoActividadEconomica, $this->nombreComercial, $this->departamento, $this->municipio, $this->complemento, $this->correo, $this->codigo);
        $data = $this->guardar($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function editarCliente(string $codigoCliente)
    {
        $sql = "SELECT 
                c.codigoCliente AS codigo,
                c.nombreCliente AS nombre,
                c.tipoIdentificacion AS identificacion,
                IFNULL(d.valor, '---') AS nombreIdentificacion,
                IFNULL(c.numeroIdentificacion,'---') AS numeroIdentificacion,
                IFNULL(c.nrc,'---') AS numeroRe,
                IFNULL(c.numeroTelefonoCliente, '---') AS telefono,
                IFNULL(c.contactoCliente, '---') AS contacto,
                IFNULL(c.nit,'---') AS nit,
                IFNULL(c.nombreComercial,'---') AS comercial,
                IFNULL(c.codigoActividadEconomica,'---') AS actividad,
                IFNULL(a.valor,'---') AS nombreActividad,
                IFNULL(c.departamento,'---') AS departamentoC,
                IFNULL(dp.valor,'---') AS nombreDepartamento,
                IFNULL(c.municipio,'---') AS municipioC,
                IFNULL(m.valor,'---') AS nombreMunicipio,
                IFNULL(c.complemento,'---') AS complement,
                IFNULL(c.correo,'---') AS email,
                c.limiteCreditoCliente AS creditoLimite,
                c.saldoCliente AS saldo,
                c.tipoPersona AS persona
            FROM clientes c
            LEFT JOIN documentoIdentificacion d ON c.tipoIdentificacion = d.codigo
            LEFT JOIN actividadesEconomicas a ON c.codigoActividadEconomica = a.codigo
            LEFT JOIN departamentos dp ON c.departamento = dp.codigo_departamento
            LEFT JOIN municipios m ON c.municipio = m.codigo AND c.departamento = m.codigoDepartamento
            WHERE c.codigoCliente = ?";

        $datos = array($codigoCliente);
        $data = $this->select($sql, $datos);
        return $data;
    }



    // nuevos campos para clientes
    public function seleccionarTipoPersona($query)
    {
        // Modificar la consulta para filtrar por el código '01'
        $sql = "SELECT codigo,
                    valor AS nombre
                FROM tipoPersona
                WHERE valor LIKE ? 
                ORDER BY codigo DESC";

        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    public function buscarDocumentoIdentificacion($query)
    {
        // Modificar la consulta para filtrar por el código '01'
        $sql = "SELECT codigo,
                    valor AS nombre
                FROM documentoIdentificacion
                WHERE valor LIKE ? 
                ORDER BY codigo DESC";

        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    public function buscarDepartamentos($query)
    {
        // Modificar la consulta para filtrar por el código '01'
        $sql = "SELECT codigo_departamento AS codigo,
                    valor AS nombre
                FROM departamentos
                WHERE valor LIKE ? 
                ORDER BY codigo DESC";

        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    public function buscarMunicipios($idDepartamento)
    {
        // Modificar la consulta para filtrar por el código '01'
        $sql = "SELECT * from municipios WHERE codigoDepartamento = ?";

        $datos = array($idDepartamento);
        return $this->selectAll($sql, $datos);
    }

    public function buscarActividadEcocomica($query)
    {
        // Modificar la consulta para filtrar por el código '01'
        $sql = "SELECT codigo,
                    valor AS nombre
                FROM actividadesEconomicas
                WHERE valor LIKE ? 
                ORDER BY codigo DESC LIMIT 50";

        $params = ["%$query%"];
        return $this->selectAll($sql, $params);
    }

    // public function accionUsuario(int $estadoUsuario, string $codigoUsuario){
    //     $this->codigo = $codigoUsuario;
    //     $this->estado = $estadoUsuario;
    //     $sql  = "UPDATE usuarios SET estado = ? WHERE codigoUsuario = ?";
    //     $datos = array($this->estado, $this->codigo);
    //     $data = $this->guardar($sql, $datos);
    //     return $data;

    // }
}
