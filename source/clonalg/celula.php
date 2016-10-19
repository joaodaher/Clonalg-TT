<?php
/**
 * Classe que representa um unico individuo no sistema imune
 * @author João Daher
 */
class celula {
    public $id;
    public $fitness;
    private $fitness_horario;
    private $fitness_espaco;
    
    public $erros_graves;
    
    public $UFLA;
    public $avaliada;
    public $choques_horario;   //OBRIG.  [Curso][Periodo][Dia][Hora] = array of {oferta.php}
    public $choques_espaco;    //OBRIG.  [Sala][Dia][Hora] = array of {oferta.php}
    public $espacamento;       //        ["Cod Curso"]["Periodo"]  = array of {oferta.php}
    public $janelas;           //        ["Cod Curso"]["Periodo"]  = array of {oferta.php}
    public $alternancia;       //        ["Cod Curso"]["Periodo"]  = array of {oferta.php}
    public $lab;               //OBRIG.  ["Departamento"] = array of {oferta.php}
    public $turno;             //OBRIG.  
    public $isolamento;        //        ["Departamento"] = array of {oferta.php}
    public $proximidade;       //        ["Departamento"] = array of {oferta.php}
    public $exclusividade;     //OBRIG.  ["Departamento"] = array of {oferta.php}
    public $capacidade;        //OBRIG.  ["Departamento"] = array of {oferta.php}
    
    function __construct() {
        $this->UFLA = new sistema();        
        $this->id = uniqid();
        
        $this->UFLA->gerar_Horarios_Aleatorios();
        $this->avaliar();
    }

    /**
     * Atualiza todos os atributos da classe referentes
     * aos resultados de avaliacao celular
     */
    private function avaliar(){
        $resultado = $this->UFLA->avaliar();
        
        $this->fitness = $resultado[0]['geral'];
        $this->fitness_horario = $resultado[0]['horario'];
        $this->fitness_espaco = $resultado[0]['espaco'];
        $info = $resultado[1];
        
        $this->choques_horario = $info['chq_horario'];
        $this->choques_espaco = $info['chq_local'];
        $this->espacamento = $info['espacamento'];
        $this->janelas = $info['janela'];
        $this->alternancia = $info['alternancia'];
        $this->lab = $info['lab'];
        $this->turno = $info['turno'];
        $this->isolamento = $info['isolamento'];
        $this->proximidade = $info['proximidade'];
        $this->exclusividade = $info['exclusividade'];
        $this->capacidade = $info['capacidade'];
        
        $this->erros_Graves();
    }

    /**
     * Realiza a hipermutacao da celula e, posteriormente, a avaliacao
     * @param float $taxa Intensificador/inibidor de mutacao (p)
     */
    public function mutar($taxa){
        $taxa_horario = (float)exp(-$taxa * $this->fitness_horario);
        $taxa_espaco = (float)exp(-$taxa * $this->fitness_espaco);
        //print "Tx Horario: ".$taxa_horario." e Tx Espaco: ".$taxa_espaco."\n";
        
        $this->UFLA->trocar_Grade($taxa_horario, $taxa_espaco);
        
        $this->avaliar();
    }
    
    /**
     * Gera um novo clone da celula, com dados iguais, porem ID diferente
     * @return celula.php Clone gerado
     */
    public function clonar(){
        $clone = unserialize(serialize($this));
        $clone->id = uniqid();
        return $clone;
    }

    /**
     * Realiza a contagem da quantidade de infracoes a restricoes obrigatorias
     */
    private function erros_Graves(){
        $qtd = 0;
        foreach($this->choques_horario as $curso=>$periodos){
            foreach($periodos as $periodo=>$dias){
                foreach($dias as $dia=>$horas){
                    foreach($horas as $hora=>$ofertas){
                        $qtd += count($ofertas);
                    }
                }
            }
        }
        
        foreach($this->choques_espaco as $curso=>$periodos){
            foreach($periodos as $periodo=>$dias){
                foreach($dias as $dia=>$horas){
                    foreach($horas as $hora=>$ofertas){
                        $qtd += count($ofertas);
                    }
                }
            }
        }
        
        foreach($this->lab as $dpto=>$ofertas){
            $qtd += count($ofertas);
        }
        
        foreach($this->turno as $dpto=>$ofertas){
            $qtd += count($ofertas);
        }
        
        foreach($this->exclusividade as $dpto=>$ofertas){
            $qtd += count($ofertas);
        }
        
        foreach($this->capacidade as $dpto=>$ofertas){
            $qtd += count($ofertas);
        }
        
        $this->erros_graves = $qtd;
    }

}

?>
