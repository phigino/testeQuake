<?php
/*
 * Classe que lê o arquilo.log e faz a separação dos dados.
 * @author Paulo Higino <phigino@gmail.com> 
 * @version 0.1 
 */

class game extends banco {

    private $dados; //arquivo que recebe os dados limpos
    private $quantPartidas = 0; //separa por partida
    
    //Função que inicia o processo de lentura e envia os dados para serem inseridos no banco
    public function inicia($log) {
        $handle = fopen($log, 'r') or die('File opening failed');
        $this->getLinhaKill($handle);
        $this->executa($this->dados);
    }
//Detecta onde tem morte
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
    
// verifica quando inicia um novo jogo
    private function verificaInicioJogo($dados) {
        if (stristr($dados, "InitGame")) {
            $this->quantPartidas = ++$this->quantPartidas;
            $this->dados[$this->quantPartidas]['total_kills'] = 0;
        }
    }
// Pega os jogadores de cada partida
    private function getPlayers($dados) {
        if (stristr($dados, "ClientUserinfoChanged")) {
            
            $part = substr(strrchr($dados, ":"), 0);
            $part1 = substr($part, 2);$part2 = explode('\t\\', $part1);
            $part3 = explode('n\\', $part2[0]);
            $indice = trim($part3[0]);
            $nome = trim($part3[1]);
            $this->dados[$this->quantPartidas]['player'][$indice] = $nome;
            $this->dados[$this->quantPartidas]['kills'][$nome] = 0;
        }
    }
// Calcula as mortes totais e a pontuação de cada jogador
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
//Quarda o motivo da morte e a quantidade que ocorreu
    private function causaMorte($param) {

        if (isset($this->dados[$this->quantPartidas]['causa_morte'][$param])) {
            ++$this->dados[$this->quantPartidas]['causa_morte'][$param];
        } else {
            $this->dados[$this->quantPartidas]['causa_morte'][$param] = 1;
        }
    }

}

/*
 * Classe conecta no banco, insere dados e faz consulta.
 * @author Paulo Higino <phigino@gmail.com> 
 * @version 0.1 
 */

class banco {

    private $conexao;
    
   
//Conecta no banco de dados
    public function __construct() {
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
// Prepara os dados para serem inseridos no banco de dados
    public function executa($param) {
       
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
//insere uma linha no banco de dados
    private function insertPartida($total, $players, $kills, $motivo, $partida) {

        $sql = "INSERT INTO partida (total_kills, players, kills, motivo,partida)
    VALUES ('$total', '$players', '$kills', '$motivo', '$partida' )";
 
        if ($this->conexao->query($sql) === TRUE) {
            echo "OKTC";
        } else {
            echo 'erro ao salvar no banco';
        }
    }
//consulta no banco de dados as informações
    private function select($campo, $where = 'motivo') {

        $sql = "SELECT * FROM `partida` WHERE `$where` like '' ";
        
        $result = $this->conexao->query($sql);
        return $result->fetchAll();
    }
    //Pega os dados de cada jogador
    public function Getresults() {
        $dados = $this->select("players");
        foreach ($dados as $value) {
            $result[$value["players"]]['nome'] = $value["players"];
            $kill = (int)$value["kills"];
            $result[$value["players"]]['kill'][] = $kill;
            
           
        }
        return $result;
    }
    //pega os dados do motivo das mortes por partida
    public function getRelatorio() {
        
        $dados = $this->select("motivo","players");
        foreach ($dados as $value) {
            $result[$value['partida']]['id'] = $value['partida'];
            $result[$value['partida']]['total'] = $value['total_kills'];
            $result[$value['partida']]['motivo'][$value['motivo']] = $value['kills'];
            
        }
        
        return $result;
        
    }
}

//*$objeto = new game('games.log');
/*
 * Para inserir os dados no banco pela primeira vez
 * $objeto = new game();
 * $objeto->inicia('games.log');
 */
