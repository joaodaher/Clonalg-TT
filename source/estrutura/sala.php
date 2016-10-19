<?php
//ini_set( "display_errors", 0);
class sala{
	public $nome; //string
	public $laboratorio; //boolean
	public $capacidade; //int
	public $disciplinas_exclusivas; //array
        private $ocupacao; //grid

	function __construct($nome, $laboratorio, $capacidade, $disciplinas_exclusivas) {
            $this->nome = $nome;
            $this->laboratorio = $laboratorio;
            $this->capacidade = $capacidade;
            $this->disciplinas_exclusivas = $disciplinas_exclusivas;
            $this->ocupacao = vars::getGenericGrid();
        }

        public function obter_Ocupacao(){
            return $this->ocupacao;
        }

        public function marcar_Ocupacao($dia, $hora, $cod_disc, $oferta){
            $this->ocupacao[$dia][$hora][$cod_disc] = $oferta;
        }

        public function desmarcar_Ocupacao($dia, $hora, $cod_disc, $oferta){
            if(!array_key_exists($dia, $this->ocupacao)) print 'WTH??! Dia: '.$dia.' pra '.$oferta->cod_disc_relacionada.'??<br/>';
            if(!array_key_exists($hora, $this->ocupacao[$dia])) print 'WTH??! Hora: '.$hora.' pra '.$oferta->cod_disc_relacionada.'??<br/>';
            foreach ($this->ocupacao[$dia][$hora] as $cod_disc_alocada=>$oferta_alocada) {
                $mesmaDisciplina = $cod_disc==$cod_disc_alocada;
                $mesmasTurmas = tools::comparar_Vetores($oferta_alocada->turmas, $oferta->turmas);

                if($mesmaDisciplina && $mesmasTurmas){
                    unset($this->ocupacao[$dia][$hora][$cod_disc_alocada]);
                }
            }
        }

        /**
         *
         * @param [String] $dia Dia da semana (ex: "Terca")
         * @param [String] $hora Hora do dia (ex: "8:00")
         * @param [int] $duracao Duracao da aula, em horas (ex: 3)
         * @return [bool] TRUE, se estiver ocupada em pelo menos um dos horarios
         */
        public function verificar_Ocupacao($dia, $hora, $duracao){
            if(array_key_exists($dia, $this->ocupacao)){ //se houver alguma aula no dia
                for($i=0; $i<$duracao; $i++){ //verificar se ha aulas nos horarios pedidos
                    if(array_key_exists($hora+$i.':00', $this->ocupacao[$dia])){
                        return true;
                    }
                }
            }
            return false;
        }

}
?>
