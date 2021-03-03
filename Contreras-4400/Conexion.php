<?php
class Conexion
{
    private $con;
    public function __construct()
    {
    }
    public function getCon()
    {
        return $this->con;
    }
    public function doQuery($sql)
    {
        $resultado = $this->con->query($sql);
        if (!$resultado) {
            die("Falló algo en la base de datos. " . $this->con->connect_error);
        }
        return $resultado;
    }
    public function conectarBD()
    {
        $this->con = new mysqli("localhost", "root", "", "buscaminas");
        if ($this->con->connect_errno) {
            die("No se puede conectar a la base de datos. " . $this->con->connect_error);
        }
    }
}
