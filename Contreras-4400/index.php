<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscaminas</title>

    <link rel="stylesheet" type="text/css" href="styles/normalize.css">
    <link rel="stylesheet" type="text/css" href="styles/style.css">
</head>

<body>
    <h1>Buscaminas</h1>
    <?php

    /* Si aun no se ha enviado la pulsacion de boton en POST,
    se pide valor de N. */
    if (!isset($_POST["enviarN"]) || isset($_POST["destroySession"])) {
        // iniciar session para revisar si hay residuos de un juego anterior 
        session_start();
    ?>
        <form method="POST" action="">
            <fieldset>
                <label for="valorN">Ingrese N:</label>
                <input type="number" placeholder="8-20" min="8" max="20" id="valorN" name="valorN" required>
                <br>
                <input type="submit" id="enviarN" name="enviarN" value="Comenzar">
            </fieldset>
        </form>
        <?php
        //eliminar variable de sesion
        if (isset($_SESSION["juego"])) {
            unset($_POST["enviarN"]);
            session_unset();
            session_destroy();
        }
    } //if no se ha enviado nada por POST, no hay valor de N

    /* Si ya se tiene valor de N en POST, o llego confirmacion de boton se procede a
       preparar todo para el juego */
    if (isset($_POST["enviarN"])) {
        $N = (int)$_POST["valorN"];
        include("Juego.php");
        session_start();
        $juego = null; //definir variable de juego
        //no hay juego en session, reinicio de juego
        if (!isset($_SESSION["juego"]) || isset($_POST["reiniciar"])) {
            //destruir variables de sesion, principalmente por fin de juego
            if (isset($_SESSION["finJuego"]))
                unset($_SESSION["finJuego"]);

            $juego = new Juego($N);
            $_SESSION["juego"] = $juego;
        } else // ya existe juego en sesion
        {
            $juego = $_SESSION["juego"];
            if (isset($_POST["fila"])) {
                $marcar = false;
                if (isset($_POST["marcarMina"]) && $_POST["marcarMina"] == 1)
                    $marcar = true;

                $juego->hacerVisible((int)$_POST["fila"], (int)$_POST["columna"], $marcar);
            }
        } // else ya existe juego en session
        ?>

        <?php
        /* Seccion donde se crea el formulario de fin de juego, donde se muestra tiempo, mejor tiempo
           y se intenta actualizar datos en BD. */
        if (isset($_SESSION["finJuego"])) { //fin de juego
        ?>
            <form method="POST" action="guardarDatos.php">
                <h2>Has <?php echo ($_SESSION["finJuego"] == 1 ? "ganado" : "perdido"); ?>.</h2>
                <fieldset>
                    <?php
                    if ($_SESSION["finJuego"] == 1) { //si gano pide nombre
                    ?>
                        <label for="iniciales">Ingrese iniciales de nombre:</label>
                        <input type="text" id="iniciales" name="iniciales" placeholder="Iniciales de nombre" maxlength="4" required>
                        <br>

                        <p>Su tiempo es de <time><?php echo $juego->getTiempoTranscurrido(); ?></time> segundos.</p>
                    <?php
                    }
                    //pedir datos a bd y mostrar
                    include("Conexion.php");
                    $conexion = new Conexion();
                    $conexion->conectarBD();
                    $sql = "SELECT * FROM puntaje";
                    $resultado = $conexion->doQuery($sql);
                    if ($resultado->num_rows > 0) //si existe registro muestra
                    {
                        $resultado = $resultado->fetch_assoc();
                    ?>
                        <p>Mejor jugador: <?php echo $resultado["iniciales"]; ?> con <?php echo $resultado["segundos"]; ?> segundos</p>
                    <?php
                    } //existe registro
                    else { //si no existe registro anima a jugar
                    ?>
                        <p>¡Aún no hay registro de mejor jugador, sé el primero!</p>
                    <?php
                    } //no existe registro

                    if ($_SESSION["finJuego"] == 1) //si gano deja guardar datos
                    {
                    ?>
                        <input type="hidden" id="segundos" name="segundos" value="<?php echo $juego->getTiempoTranscurrido(); ?>">
                        <input type="submit" id="guardarDatos" name="guardarDatos" value="Aceptar">
                    <?php
                    } //si gano deja guardar datos
                    ?>
                </fieldset>
            </form> <!-- form de fin de juego -->
        <?php
        } //if fin de juego

        /* Aqui se muestra el tablero y otras informaciones del juego */
        ?>
        <div id="contenedorJuego">
            <p>Tiempo: <time><?php echo $juego->getTiempoTranscurrido(); ?> segundos</time></p>
            <div>
                <p>Minas marcadas: <?php echo $juego->getMinasMarcadas() . "/" . $juego->getMaxMinas(); ?></p>
            </div>
            <form id="celdasJuego" method="POST" action="">
                <label for="marcarMina">Marcar/desmarcar una mina</label>
                <input type="checkbox" <?php if (isset($_POST["marcarMina"])) echo "checked"; ?> id="marcarMina" name="marcarMina" value="1">
                <br>
                <table>
                    <?php
                    for ($i = 0; $i < $N; $i++) {
                    ?>
                        <tr>
                            <?php
                            for ($j = 0; $j < $N; $j++) {
                            ?>
                                <td class="td">
                                    <button type="button" class="celda" <?php if (!isset($_SESSION["finJuego"])) echo 'onclick="celdaPresionada(' . $i . ',' . $j . ')"'; ?>>
                                        <?php
                                        switch ($juego->esVisible($i, $j)) {
                                            case -1:
                                                echo "&#9760";
                                                break;
                                            case 1: //numero
                                                echo $juego->getValMatriz($i, $j);
                                                break;
                                            case 2: //bandera
                                                echo "&#9873";
                                                break;
                                        } //switch
                                        ?>
                                    </button>
                                </td>
                            <?php
                            } //for j
                            ?>
                        </tr>
                    <?php
                    } //for i
                    ?>
                </table>
                <input type="hidden" id="valorN" name="valorN" value="<?php echo $N; ?>">
                <input type="hidden" id="enviarN" name="enviarN" value="1">
                <input type="hidden" id="fila" name="fila" value="">
                <input type="hidden" id="columna" name="columna" value="">
                <br>
                <button type="submit" name="reiniciar" id="reiniciar">Reiniciar tablero</button>
                <button type="submit" name="destroySession" id="destroySession">Reiniciar todo</button>
            </form> <!-- form #celdasJuego -->
        </div> <!-- div #contenedorJuego -->
    <?php
    } //if definido enviarN (juego)
    ?>
    <?php ?>
    <script type="application/javascript" src="scripts/script.js"></script>
</body>

</html>