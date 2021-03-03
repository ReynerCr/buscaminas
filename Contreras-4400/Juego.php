<?php
include("Tablero.php");
class juego
{
    private $tablero; // crea y almacena los valores reales de la matriz de juego
    private $matrizVisible; // para mantener si el valor real de algo en tablero es visible o no
    private $minasMarcadas; // numero de minas marcadas
    private $fechaDeInicio; // almacena la fecha de inicio
    private $N; // numero de filas o columnas
    private $maxCasillas; // $N*$N
    private $casillasDescubiertas; // numero de casillas que se han descubierto
    public function __construct($N)
    {
        $this->N = $N;
        $this->tablero = new Tablero($N);
        $this->minasMarcadas = 0;
        $this->casillasDescubiertas = 0;
        $this->maxCasillas = ($N * $N);
        date_default_timezone_set('America/Barbados'); //Barbados tiene el mismo huso horario de Venezuela
        $this->fechaDeInicio = date("d-m-Y H:i:s");
        $this->matrizVisible = array();
        for ($i = 0; $i < $N; $i++) { //inicializar elementos de matrizVisible en 0
            for ($j = 0; $j < $N; $j++) {
                $this->matrizVisible[$i][$j] = 0; // no visible
            }
        } //for $i
    }
    public function getValMatriz($i, $j)
    {
        return $this->tablero->getValMatriz($i, $j);
    }
    public function esVisible($i, $j)
    {
        return ($this->matrizVisible[$i][$j]);
    }
    public function getMinasMarcadas()
    {
        return $this->minasMarcadas;
    }
    public function getMaxMinas()
    {
        return $this->tablero->getMaxNumMinas();
    }
    public function getFechaInicio()
    {
        return $this->fechaDeInicio;
    }
    private function descubrirCerosAdyacentes($i, $j)
    { // funcion recursiva que descubre las casillas que no tienen mina alrededor
        if ($this->matrizVisible[$i][$j] == 0 && $this->tablero->getValMatriz($i, $j) == 0) {
            if ($this->matrizVisible[$i][$j] == 2)
                --$this->minasMarcadas;
            $this->matrizVisible[$i][$j] = 1;
            ++$this->casillasDescubiertas;
            --$i;
            if ($i >= 0) //revisando hacia arriba
            {
                if ($j - 1 >= 0 && $this->tablero->getValMatriz($i, $j - 1) == 0) // arriba y a la izquierda
                    $this->descubrirCerosAdyacentes($i, $j - 1);
                if ($this->tablero->getValMatriz($i, $j) == 0) //arriba
                    $this->descubrirCerosAdyacentes($i, $j);
                if ($j + 1 < $this->N && $this->tablero->getValMatriz($i, $j + 1) == 0) // arriba y a la derecha
                    $this->descubrirCerosAdyacentes($i, $j + 1);
            }
            ++$i;
            if ($j - 1 >= 0 && $this->tablero->getValMatriz($i, $j - 1) == 0) //revisando hacia izquierda
                $this->descubrirCerosAdyacentes($i, $j - 1);
            if ($j + 1 < $this->N && $this->tablero->getValMatriz($i, $j + 1) == 0) //revisando hacia izquierda
                $this->descubrirCerosAdyacentes($i, $j + 1);
            ++$i;
            if ($i < $this->N) //revisando hacia abajo
            {
                if ($j - 1 >= 0 && $this->tablero->getValMatriz($i, $j - 1) == 0) // abajo y a la izquierda
                    $this->descubrirCerosAdyacentes($i, $j - 1);
                if ($this->tablero->getValMatriz($i, $j) == 0) // abajo
                    $this->descubrirCerosAdyacentes($i, $j);
                if ($j + 1 < $this->N && $this->tablero->getValMatriz($i, $j + 1) == 0) // abajo y a la derecha
                    $this->descubrirCerosAdyacentes($i, $j + 1);
            }
        } //if
    }
    public function hacerVisible($i, $j, $marcar = false)
    { //funcion para hacer que un valor de la matriz real sea visible o no en las casillas
        /* 
            Valor visible:
            0: no descubierta ni marcada.
            1: descubierta.
            2: no descubierta, marcada.
            -1: mina.

            Valor real:
            Valor 
        */
        $valVisible = $this->matrizVisible[$i][$j];
        if ($valVisible != 0 && $valVisible != 2) { //ignorar si ya es visible o no es una bandera
            return; //no hace nada
        }
        if ($marcar) // marcar o desmarcar casilla con bandera
        {
            if ($valVisible == 0) // si desmarcada la marca como mina
            {
                $valVisible = 2;
                ++$this->minasMarcadas;
                $this->comprobarCasillasLibres();
            } else // si marcada como mina la desmarca
            {
                $valVisible = 0;
                --$this->minasMarcadas;
            }
        } else // desmarcar o descubrir casilla
        {
            if ($valVisible == 2) // marcada, para desmarcar sin el checkbox
            {
                $valVisible = 0;
                --$this->minasMarcadas;
            } else //if intentar descubrir casilla
            {
                $valReal = $this->getValMatriz($i, $j);
                if ($valReal != -1) //if no mina
                {
                    $valVisible = 1;
                    if ($valReal == 0) { // si es 0 se intenta descubrir casillas adyacentes
                        $this->descubrirCerosAdyacentes($i, $j);
                        --$this->casillasDescubiertas; //reduzco en 1 para evitar ganar erroneamente
                    }
                    ++$this->casillasDescubiertas;
                    $this->comprobarCasillasLibres(); // comprobar si victoria
                } else // if mina, derrota
                {
                    $valVisible = -1;
                    $this->finalizarJuego(false);
                }
            } //else
        } //else
        $this->matrizVisible[$i][$j] = $valVisible;
    }
    private function comprobarCasillasLibres()
    { //  funcion con simple suma de casillasDescubier + minasMarcadas para comprobar si ya se gana
        if (($this->minasMarcadas <= $this->getMaxMinas()) && ($this->minasMarcadas + $this->casillasDescubiertas) == $this->maxCasillas) //victoria
            $this->finalizarJuego(true);
    }
    private function finalizarJuego($gano)
    { // funcion para fin de juego
        //finJuego almacena entero que indica si gano (1) o perdio (-1)
        $_SESSION["finJuego"] = ($gano ? 1 : -1);
    }
    public function getTiempoTranscurrido()
    { //devuelve el tiempo transcurrido desde el inicio del juego hasta el fin en segundos
        $fecha1 = new DateTime($this->fechaDeInicio);
        $fecha2 = new DateTime(date("Y-m-d H:i:s")); //fecha de cierre
        $intervalo = $fecha1->diff($fecha2); // differencia
        return $intervalo->format("%s");
    }
}
