<?php
if (isset($_POST["guardarDatos"])) { // si se presiono boton
    // conectar a BD
    include("Conexion.php");
    $con = new Conexion();
    $con->conectarBD();

    $sql = "SELECT * FROM puntaje";
    $resultado = $con->doQuery($sql);
    if ($resultado->num_rows > 0) //si existe registro
    {
        $resultado = $resultado->fetch_assoc();
        // si tiempo de usuario actual mejor que el mejor se actualiza el mejor en BD
        if ($_POST["segundos"] >= $resultado["segundos"]) {
            $iniciales = $resultado["iniciales"];
            $sql = "UPDATE puntaje
                SET iniciales='" . $_POST["iniciales"] . "',
                segundos='" . $_POST["segundos"] . "'
                WHERE iniciales='$iniciales'";
            $resultado = $con->doQuery($sql);
        }
    } else { // si no existe registro
        $sql = "INSERT INTO puntaje (iniciales, segundos)
                VALUES('" . $_POST["iniciales"] . "',
                '" . $_POST["segundos"] . "')";
        $resultado = $con->doQuery($sql);
    }
} //if boton guardarDatos presionado

header("Location: ./index.php");
die();
