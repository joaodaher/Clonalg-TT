<?php

/**
 * Restrições tratadas:
 * - Proibe ofertar mais de 1 aula da disciplina no mesmo horario
 */
class horario {

    private $oferta;
    private $num_dias;
    private $num_horas_dia;
    
    private $hora_minima;
    private $hora_maxima;
    
    private $inicio_manha;
    private $fim_manha;
    private $inicio_tarde;
    private $fim_tarde;
    private $inicio_noite;
    private $fim_noite;
    
    private $creditos;
    public $tam_aula;
    
    public $grade;

    /**
     * Cria uma nova grade para uma oferta
     * @param [{oferta.php} object] $oferta Oferta que utiliza o horario
     * @param [int] $creditos Quantidade de creditos
     * @param [int] $tam_aula Tamanho maximo de uma aula, de 1 a 3
     */
    function __construct($oferta, $creditos, $tam_aula) {
        $this->oferta = $oferta;
        $this->creditos = $creditos;
        $this->tam_aula = $tam_aula;
        
        $this->inicio_manha = vars::getMorningTime();
        $this->fim_manha = vars::getLunchTime();
        $this->inicio_tarde = vars::getAfternoonTime();
        $this->fim_tarde = vars::getSnackTime();
        $this->inicio_noite = vars::getEveningTime();
        $this->fim_noite = vars::getNightTime();
        
        $this->num_dias = vars::getWeekLenght();
        $this->num_horas_dia = vars::getDayLenght();
        $this->grade = vars::getGenericGrid();
    }
    
    /**
     * Define as variaveis que controlam o turno da oferta
     * @param boolean $noturna TRUE, se a oferta for noturna
     */
    public function definir_Turno($noturna){
        if(!$noturna){ //diurno
            $this->hora_minima = $this->inicio_manha; //8h
            $this->hora_maxima = $this->fim_tarde; //16h
        }
        else{ //noturno
            $this->hora_minima = $this->inicio_noite; //19h
            $this->hora_maxima = $this->fim_noite; //23h
        }
    }

    /**
     * Verifica se um horario esta ocupado por alguma aula
     * @param String $dia Dia da semana (ex:"Terca")
     * @param String $hora Hora do dia (ex:"17:00")
     * @param int $duracao Duracao da aula, em horas (ex: 2)
     * @return boolean TRUE, se estiver disponivel
     */
    private function verificar_Disp_Horario($dia, $hora, $duracao) {
        //print 'DIA: '.$dia.' HORA: '.$hora.' DURACAO: '.$duracao.'<br/>';        
        if ((int)$hora < (int)$this->hora_minima){ //heuristica
            //print "rejeitado: abaixo da hora minima\n";
            return false;
        }
        
        if ((int)($hora + $duracao - 1) >= (int)$this->hora_maxima ){ //heuristica
            //print "rejeitado: acima da hora maxima.\n";
            return false;
        }
            
        for ($i = 0; $i < $duracao; $i++){
            //para cursos diurnos, nao permitir horario do almoco
            if((int)($hora+$i) >= (int)$this->fim_manha && (int)($hora+$i) < (int)$this->inicio_tarde){ //heuristica
                //print "rejeitado: ".($hora+$i)." eh horario de almoco (".$this->fim_manha." e ".$this->inicio_tarde.")\n";
                return false;
            }
            
            //se tiver outra aula da mesma oferta
            if ($this->grade[$dia][$hora+$i.":00"] != null){ //heuristica
                 //print "rejeitado: ja ocupado\n";
                return false;
            }
        }
        return true;
    }

    /**
     * Aloca a disciplina em um espaco em um determinado horario
     * @param {espaco.php} $espaco_atual O local em que a oferta esta alocada atuamente
     * @param String $dia Dia em que a oferta estava alocada que(ex:"Terca")
     * @param String $horar Horario em que a oferta estava alocada (ex:"16:00"))
     * @param int $duracao Duracao da aula, em horas (ex: 2)
     */
    private function alocar_Aula($sala, $dia, $hora, $duracao) {
        for ($i = 0; $i < $duracao; $i++) {
            $this->grade[$dia][$hora + $i . ':00'] = $sala;
            $sala->marcar_Ocupacao($dia, $hora + $i . ':00', $this->oferta->cod_disc_relacionada, $this->oferta);
        }
    }

    /**
     * Desaloca a disciplina de um espaco em um determinado horario, e as ocorrencias consecutivas
     * @param [{espaco.php} object] $espaco_atual O local em que a oferta esta alocada atuamente
     * @param [String] $dia Dia em que a oferta estava alocada que(ex:"Terca")
     * @param [String] $horar Horario em que a oferta estava alocada (ex:"16:00")
     */
    private function desalocar_Aula($espaco_atual, $dia, $hora) {
        $duracao = $this->descobrir_duracao_aula($dia, $hora);
        //print '<b>HORA INICIAL: '.$hora.'</b> ('.$duracao.' h)<br/>';
        for ($i = 0; $i < $duracao; $i++) {
            //print 'Desalocando na '.$dia.' as '.($hora+$i).':00'.'<br/>';
            $this->grade[$dia][$hora + $i . ':00'] = null;
            $espaco_atual->desmarcar_Ocupacao($dia, $hora + $i . ':00', $this->oferta->cod_disc_relacionada, $this->oferta);
        }
    }

    /**
     * Apaga qualquer horario anterior e define outro aleatoriamente
     * @param [array of {espaco.php}] $pavilhoes Vetor com todos os pavilhoes disponiveis
     */
    public function sugerir_Grade_Completa($pavilhoes) {
        $this->limpar_Grade();

        /* Creditos     Tam_Aula
         *     2           2        : 1 aula de 2 horas
         *     3           2        : 1 aula de 2 horas e 1 aula de 2 horas
         *     3           3        : 1 aula de 3 horas
         *     4           2        : 2 aulas de 2 horas
         *     6           2        : 3 aulas de 2 horas
         */

        $creditos_restantes = $this->creditos;
        while ($creditos_restantes > 0) {
            $duracao = $this->tam_aula;
            //print "FALTA SUGERIR: ".$creditos_restantes." CREDITOS PARA ".$this->oferta->cod_disc_relacionada."\n\n";
            if ($duracao > $this->creditos) {
                $duracao = $this->creditos;
            }
            $creditos_restantes -= $duracao;
            
            $this->sugerir_Horario($pavilhoes, $duracao);
        }
    }

    /**
     * Sugere uma nova aula para a disciplina em um dos pavilhoes propostos
     * Nao ha a possibilidade de serem ofertadas no mesmo horario
     * USE: function sugerir_grade_completa
     * @param [array of {espaco.php}] $pavilhoes Todos os pavilhoes disponiveis para alocar a oferta
     */
    private function sugerir_Horario($pavilhoes, $duracao) {
        $max_slots = $this->num_dias * $this->num_horas_dia;
        //print 'Maximo '.$max_slots.' slots.<br/>';
        do {
            $prob = rand(1, $max_slots);
            $horario_escolhido = tools::prob_Para_Horario($prob);
            //print 'PROB: '.$prob.' DIA: '.$horario_escolhido[0].' HORA: '.$horario_escolhido[1].'\n';
            $dia = $horario_escolhido[0];
            $hora = $horario_escolhido[1];
            //print "Tentando horario ".$dia." - ".$hora."\n";
        } while ($this->verificar_Disp_Horario($dia, $hora, $duracao) == false);
        $sala_escolhida = $this->sugerir_Sala($pavilhoes);
        //print '...sugerido para '.$dia.' - '.$hora.' (+ '.$duracao.')<br/>';
        $this->alocar_Aula($sala_escolhida, $dia, $hora, $duracao);
    }

    /**
     * Escolhe aleatoriamente um local dentre os pavilhoes pedidos
     * @param [array of {sala.php}] $pavilhoes Todos os pavilhoes disponiveis para alocar a oferta
     * @return [{sala.php} object] sala sugerido
     */
    private function sugerir_Sala($pavilhoes) {
        $prob = rand(0, count($pavilhoes) - 1); //sorteia um pavilhao
        $pavilhao = $pavilhoes[$prob];
        //print 'Pavilhao escolhido: '.$pavilhao->nome.'<br/>';
        $prob = rand(0, count($pavilhao->salas) - 1);
        return $pavilhao->salas[$prob];
    }

    /**
     * Apaga completamente todas as aulas da disciplina
     */
    private function limpar_Grade() {
        foreach ($this->grade as $dia => $horas) {
            foreach ($horas as $hora => $sala) {
                if ($sala != null) {
                    $this->grade[$dia][$hora] = null;
                    $sala->desmarcar_Ocupacao($dia, $hora, $this->oferta->cod_disc_relacionada, $this->oferta);
                }
            }
        }
    }

    /**
     * Efetua a troca aleatoria de horario de cada aula da disciplina
     * O local eh mantido
     * @param [float] $tx_troca Probabilidade de trocar o horario (ex: 0.3 = 30%)
     */
    public function trocar_Horarios($tx_troca) {
        $duracao = 0;

        foreach ($this->grade as $dia => $horas) {
            foreach ($horas as $hora => $sala) {
                if($duracao > 0){
                    $duracao--;
                    continue;
                }

                $prob = (float) (rand(0, 100) / 100);

                if ($sala != null && $prob <= $tx_troca) {
                    //print 'h';
                    //print 'Troca de horario em '.$this->oferta->cod_disc_relacionada.' atualmente na '.$dia.' - '.$hora.'<br/>';
                    $duracao = $this->descobrir_duracao_aula($dia, $hora);

                    $pavilhao_imaginario = new pavilhao(null, null, null);
                    $pavilhao_imaginario->salas[] = $sala;

                    $this->desalocar_Aula($sala, $dia, $hora);
                    $this->sugerir_Horario(array($pavilhao_imaginario), $duracao);
                }
            }
        }
    }

    /**
     * Efetua a troca aleatoria do local dentre os pavilhoes informados
     * O horario eh mantido
     * @param [float] $tx_troca Probabilidade de trocar o local (ex: 0.3 = 30%)
     * @param [array of {sala.php}] $pavilhoes Todos os pavilhoes disponiveis para alocar a oferta
     */
    public function trocar_Salas($tx_troca, $pavilhoes) {
        $duracao = 0;
        foreach ($this->grade as $dia => $horas) {
            foreach ($horas as $hora => $sala) {
                if($duracao > 0){
                    $duracao--;
                    continue;
                }

                $prob = (float) (rand(0, 100) / 100);
                if ($sala != null && $prob <= $tx_troca) {
                    //print "s";
                    $espaco_escolhido = $this->sugerir_sala($pavilhoes);
                    $duracao = $this->descobrir_Duracao_Aula($dia, $hora);

                    $this->desalocar_Aula($sala, $dia, $hora);
                    $this->alocar_Aula($espaco_escolhido, $dia, $hora, $duracao);
                }
            }
        }
    }

    /**
     * Informa a duração da aula em determinado horario
     * @param [String] $dia Dia da semana (ex: "Segunda")
     * @param [String] $hora Hora do dia em que a aula começa (ex: "8:00")
     * @return [int] Duracao da aula
     */
    private function descobrir_Duracao_Aula($dia, $hora) {
        $duracao = 0;
        //print 'Tentando descobrir duração da aula na '.$dia.' - '.$hora.'<br/>';
        //print_r($this->grade[$dia]);
        for($i=(int)$hora; $i<(int)vars::getNightTime(); $i++){
            $sala = $this->grade[$dia][$i.':00'];

            if ($sala == null) {
                //print 'Duracao: '.$duracao.' (garantia: sala vazia)<br/>';
                return $duracao;
            } else if ($duracao == $this->tam_aula) { //raros casos de 2 aulas "diferentes" estarem consecutivas
                //print 'Duracao: '.$duracao.' (garantia: tamanho maximo)<br/>';
                return $duracao;
            } else {
                $duracao++;
            }
        }
    }
}

?>
