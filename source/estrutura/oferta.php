<?php
include 'horario.php';

/**
 * Representa a disciplina OFERTADA por um departamento
 * Tem as caracteristicas inerentes à oferta: turmas, vagas e o horario
 * OBS: pre-requisitos sao irrelevantes, pois nao fazem diferenca para as aulas, apenas restringem quem podera cursar.
 */

class oferta {
    public $cod_disc_relacionada;
    public $turmas; //array of String
    public $vagas; //int
    public $creditos; //int
    public $pratica; //boolean
    public $noturna; //boolean
    public $professor; //ainda nao utilizado
    public $horario; //{horario.php}

    /**
     * Cria uma nova oferta
     * @param [array of String] $turmas Vetor de turmas que cursam a oferta (ex: ["10A","22A","02A"])
     * @param [int] $vagas Quantidade de vagas
     * @param [int] $ch Carga horaria pratica ou teorica da oferta, em horas (ex: 68)
     * @param [bool] $pratica TRUE, se a carga horaria é pratica
     * @param [String] $cod_disc_relacionada Codigo da disciplina que ofertou
     * @param [int] $tam_aula Tamanho maximo de cada aula, de 1 a 3
     */
    function __construct($turmas, $vagas, $ch, $pratica, $cod_disc_relacionada, $tam_aula) {
        $this->turmas = $turmas;
        $this->vagas = $vagas;
        $this->creditos = $ch/vars::getSemesterLenght();
        $this->pratica = $pratica;
        $this->cod_disc_relacionada = $cod_disc_relacionada;

        $this->horario = new horario($this, $this->creditos, $tam_aula);
    }
    
    /**
     * Define qual o turno da oferta
     * @param boolean $noturna TRUE, se for uma oferta noturna
     */
    public function definir_Turno($noturna){
        $this->horario->definir_Turno($noturna);
    }


    /**
     * Verifica os horarios e lugares em que a disciplina eh ofertada
     * @return [array of array] Vetor contendo o horario resumido,
     * seguindo a ordem "dia", "hora" e "local" (ex: array[4] = ["Sexta", "8:00", {espaco.php}] )
     */
    public function obter_Horario_Resumido(){
        $grade = array();
        foreach($this->horario->grade as $nome_dia=>$dia){
            foreach($dia as $nome_hora=>$espaco){
                if($espaco!= null){ //se estiver ocupado
                    $grade[] = array($nome_dia, $nome_hora, $espaco);
                }
            }
        }
        return $grade;
    }

    /**
     * Sugere um novo horario completo para a oferta
     * @param array of {pavilhao.php} $pavilhoes Vetor com todos os pavilhoes
     */
    public function sugerir_Horario_Completo($pavilhoes){
        $this->horario->sugerir_Grade_Completa($pavilhoes);
    }

    /**
     * Troca os horarios de aula
     * @param float $tx_troca Probabilidade de trocar o horario (ex: 0.8)
     */
    public function trocar_Horarios($tx_troca){
       //print '<b>Tentando troca de horario de '.$this->cod_disc_relacionada.'</b> ('.implode(' ',$this->turmas).')<br/>';
       $this->horario->trocar_Horarios($tx_troca);
    }

    /**
     * Troca as salas de aula
     * @param float $tx_troca Probabilidade de trocar o local (ex: 0.8)
     * @param array of {pavilhao.php} $pavilhoes Vetor com todos os pavilhoes
     */
    public function trocar_Salas($tx_troca, $pavilhoes){
       //print '<b>Tentando troca de sala de '.$this->cod_disc_relacionada.'</b> ('.implode(' ',$this->turmas).')<br/>';
       $this->horario->trocar_Salas($tx_troca, $pavilhoes);
    }

    /**
     * Verifica se TODAS as aulas sao no periodo da noite
     * @return bool TRUE, se nao ha aulas diurnas
     */
    public function verificar_Horario_Noturno(){
        foreach($this->horario->grade as $dia=>$horas){
            foreach ($horas as $hora=>$espaco) {
               if($espaco != null && $hora<$this->horario->inicio_noite){
                   return false;
               }
            }
        }
        return true;
    }

}
?>
