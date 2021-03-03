<?php
class Tablero
{
    private $matriz;
    private $maxNumMinas;
    private $N;
    public function __construct($N)
    {
        $this->matriz = array();
        $this->N = $N;
        $this->maxNumMinas = round(($N * $N) * 0.35); //35% de las casillas contienen minas
        $this->crearMatriz();
    }
    public function getMatriz()
    {
        return $this->matriz;
    }
    public function getValMatriz($i, $j)
    {
        return $this->matriz[$i][$j];
    }
    public function getMaxNumMinas()
    {
        return $this->maxNumMinas;
    }
    public function getN()
    {
        return $this->N;
    }
    // funcion que aumenta el contador de minas si la casilla no es una mina
    private function aumentarContMinas($i, $j)
    {
        if ($this->matriz[$i][$j] != -1)
            $this->matriz[$i][$j] += 1;
    }
    private function crearMatriz()
    {
        //aqui se va a generar la matriz de juego
        $i = 0;
        $j = 0;

        //inicializar elementos de la matriz
        for ($i = 0; $i < $this->N; $i++) {
            for ($j = 0; $j < $this->N; $j++) {
                $this->matriz[$i][$j] = 0;
            }
        }
        //poner minas en posiciones aleatorias
        $n = $this->N - 1; //maximo de i o j
        $numMinas = 0;
        while ($numMinas < $this->maxNumMinas) {
            $i = rand(0, $n);
            $j = rand(0, $n);
            if ($this->matriz[$i][$j] == -1) //si hay mina se salta el ponerla
                continue;

            //como no hay una mina se va a poner una y a actualizar casillas cercanas
            $this->matriz[$i][$j] = -1; //-1 para minas
            if ($i - 1 >= 0) //revisando hacia arriba
            {
                $this->aumentarContMinas($i - 1, $j); //arriba
                if ($j - 1 >= 0) // arriba y a la izquierda
                    $this->aumentarContMinas($i - 1, $j - 1);
                if ($j + 1 <= $n) // arriba y a la derecha
                    $this->aumentarContMinas($i - 1, $j + 1);
            }
            if ($j - 1 >= 0) //revisando hacia izquierda
                $this->aumentarContMinas($i, $j - 1);
            if ($j + 1 <= $n) //revisando hacia izquierda
                $this->aumentarContMinas($i, $j + 1);

            if ($i + 1 <= $n) //revisando hacia abajo
            {
                $this->aumentarContMinas($i + 1, $j); //abajo
                if ($j - 1 >= 0) // abajo y a la izquierda
                    $this->aumentarContMinas($i + 1, $j - 1);
                if ($j + 1 <= $n) // abajo y a la derecha
                    $this->aumentarContMinas($i + 1, $j + 1);
            }
            ++$numMinas;
        } //while
    }
}
