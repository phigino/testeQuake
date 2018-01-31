<?php

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

//$handle = fopen('games.log', 'r') or die('File opening failed');
// 'games.log'

class game extends banco {

    private $dados;
    private $quantPartidas = 0;

    public function __construct($log) {
        $handle = fopen($log, 'r') or die('File opening failed');
        $this->getLinhaKill($handle);
        //$this->executa($this->dados);
    }

    private function getLinhaKill($param) {

        while (!feof($param)) {
            $dd = fgets($param);
            if (stristr("$dd", "kill")) {
                //$this->dados[$this->quantPartidas][] = $dd;
                $this->calculaMortes($dd);
            } else {
                $this->verificaInicioJogo($dd);
                $this->getPlayers($dd);
            }
        }
    }

    private function verificaInicioJogo($dados) {
        if (stristr($dados, "InitGame")) {
            $this->quantPartidas = ++$this->quantPartidas;
            $this->dados[$this->quantPartidas]['total_kills'] = 0;
        }
    }

    private function getPlayers($dados) {
        if (stristr($dados, "ClientUserinfoChanged")) {
            //this->dados[$this->quantPartidas][] = 'inicio';
            $part = substr(strrchr($dados, ":"), 0);
            $part1 = substr($part, 2);
            //var_dump($player1);

            $part2 = explode('\t\\', $part1);
            $part3 = explode('n\\', $part2[0]);
            $indice = trim($part3[0]);
            $nome = trim($part3[1]);
            $this->dados[$this->quantPartidas]['player'][$indice] = $nome;
            $this->dados[$this->quantPartidas]['kills'][$nome] = 0;
        }
    }

    private function calculaMortes($param) {
        $dados = trim($param);

        $part = explode(" ", $dados);
        $part1 = end($part);

        ++$this->dados[$this->quantPartidas]['total_kills'];
        $this->causaMorte($part1);

        if ($part[2] == "1022") {

            $player = $part[3];
            $part[3] = $this->dados[$this->quantPartidas]['player'][$player];
            --$this->dados[$this->quantPartidas]['kills'][$part[3]];
        } else {
            $player = $part[2];
            $part[2] = $this->dados[$this->quantPartidas]['player'][$player];
            ++$this->dados[$this->quantPartidas]['kills'][$part[2]];
        }
    }

    private function causaMorte($param) {

        if (isset($this->dados[$this->quantPartidas]['causa_morte'][$param])) {
            ++$this->dados[$this->quantPartidas]['causa_morte'][$param];
        } else {
            $this->dados[$this->quantPartidas]['causa_morte'][$param] = 1;
        }
    }

}

class banco {

    private $conexao;

    public function inicia_banco() {
        $servername = "localhost";
        $username = "c1quake";
        $password = "teste";
        $banco = "c1testeQuake";
        try {
            $this->conexao = new PDO("mysql:host=$servername;dbname=$banco", $username, $password);
            // set the PDO error mode to exception
            $this->conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public function executa($param) {
        $this->inicia_banco();
        $cont = 0;
        foreach ($param as $value) {
            //var_dump($value);
            foreach ($value["player"] as $key => $value1) {
                //var_dump($value1);
                $kill = isset($value["kills"][$value1]) ? $value["kills"][$value1] : 0;
                $this->insertPartida($value["total_kills"], $value1, $kill, NULL, $cont);
            }
            if (isset($value["causa_morte"])) {
                foreach ($value["causa_morte"] as $key => $value2) {

                    $this->insertPartida($value["total_kills"], NULL, $value2, $key, $cont);
                }
            }
            ++$cont;
        }
    }

    private function insertPartida($total, $players, $kills, $motivo, $partida) {

        $sql = "INSERT INTO partida (total_kills, players, kills, motivo,partida)
    VALUES ('$total', '$players', '$kills', '$motivo', '$partida' )";
        // auth = 'manual', confirmed = 1, mnethostid = 1 Always. the others are your variables

        if ($this->conexao->query($sql) === TRUE) {
            echo "OKTC";
        } else {
            ////Manage your errors
        }
    }

    public function Getresults() {

        $sql = "SELECT * partida mdl_user";
        $result = $this->conexao->query($sql);
        return $result->fetch(PDO::FETCH_ASSOC);
    }

}

$objeto = new game('games.log');
