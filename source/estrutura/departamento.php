<?php

/**
 * Description of departamento
 * @author joaodaher
 */
class departamento {
    public $nome;
    public $cod_disc; //array={'COM','GCC'}
    public $disciplinas; //array[]={disciplina.php}
    //incluir cursos relacionados?
    
    function __construct($nome, $disciplinas) {
        $this->nome = $nome;
        $this->disciplinas = $disciplinas;
        
        $this->cod_disc = array();
        $this->buscar_Codigos();
    }
    
    /**
     * Automaticamente define quais sao os codigos de disciplinas
     * que o departamento oferece.
     */
    private function buscar_Codigos(){
        foreach($this->disciplinas as $disciplina){
            $iniciais = substr($disciplina->codigo, 0, 3);
            if(!tools::search_array($iniciais, $this->cod_disc)){
                $this->cod_disc[] = $iniciais;
            }
        }
    }

    /**
     * Dado o codigo da disciplina, uma turma e se eh pratica, obtem-se a oferta da disciplina
     * @param [String] $cod_disciplina Codigo da disciplina procurada (ex:"GCC186")
     * @param [String] $turma Turma desejada (ex:"10A")
     * @param [bool] $pratica Se a oferta procurada eh pratica (laboratorio)
     * @return [{oferta.php} object] Oferta procurada 
     */
    public function buscar_Oferta($cod_disciplina, $turma){
        foreach($this->disciplinas as $disciplina){
            if($disciplina->codigo == $cod_disciplina){ //se encontrar a disciplina pedida...
                $ofertas = $disciplina->buscar_Ofertas($turma);//verificar se eh a oferta certa da turma pedida
                
                foreach($ofertas as $oferta){
                    if($oferta->pratica == $pratica){
                        return $oferta;
                    }
                }
            }
        }
        return null;
    }
    
    /**
     * Busca uma determinada disciplina baseado em seu código
     * "GCC101" retorna sua respectiva instancia de {disciplina.php}   
     * @param [String] $cod_disciplina Codigo da disciplina procurada
     * @return [{disciplina.php} object] Disciplina procurada
     */
    public function buscar_Disciplina($cod_disciplina){
        foreach($this->disciplinas as $disciplina){
            if($disciplina->codigo == $cod_disciplina){ //se encontrar a disciplina pedida...
                return $disciplina;
            }
        }
        return null;
    }
    
    /**
     * Troca os locais e horarios de uma determinada oferta de acordo com a taxa informada
     * @param [String] $cod_disciplina Codigo da disciplina (ex: "GCC101")
     * @param [array of String] $turmas Vetor de turmas que cursam a disciplina
     * @param [float] $tx_troca Taxa de troca de horario e local
     * @param [array of {pavilhao.php}] $pavilhoes  Vetor com todos os pavilhoes
     */
    public function trocar_Horario_Completo($cod_disciplina, $turmas, $tx_troca, $pavilhoes){
        $this->trocar_Horarios($cod_disciplina, $turmas, $tx_troca);
        $this->trocar_Salas($cod_disciplina, $turmas, $tx_troca, $pavilhoes);
    }
    
    /**
     * Troca o horario das aulas e mantem os mesmo locais
     * @param [String] $cod_disciplina Codigo da disciplina (ex: "GCC101")
     * @param [array of String] $turmas Vetor de turmas que cursam a disciplina
     * @param [float] $tx_troca Probabilidade de efetuar trocas (ex: 0.8 : significa 80% de chance de trocar o horario)
     */    
    public function trocar_Horarios($cod_disciplina, $turmas, $tx_troca){
        $disciplina = $this->buscar_Disciplina($cod_disciplina);
        $disciplina->trocar_Horario_Oferta($turmas, $tx_troca);
    }
    
    /**
     * Troca os locais das aulas e mantem os mesmo horarios
     * @param [String] $cod_disciplina Codigo da disciplina (ex: "GCC101")
     * @param [String] $turma Uma turma que fará a disciplina (ex: "10B")
     * @param [float] $tx_troca Probabilidade de efetuar trocas (ex: 0.8 : significa 80% de chance de trocar o local)
     * @param [array of {pavilhao.php}] $pavilhoes Vetor com todos os pavilhoes
     */
    public function trocar_Salas($cod_disciplina, $turmas, $tx_troca, $pavilhoes){
        $disciplina = $this->buscar_Disciplina($cod_disciplina);
        $disciplina->trocar_Sala_Oferta($turmas, $pavilhoes, $tx_troca);
    }
    
    /**
     * Sugere um novo horario completo para todas as disciplinas
     * @param [array of {pavilhao.php}] $pavilhoes Vetor com todos os pavilhoes
     */
    public function sugerir_Horarios_Completos($pavilhoes){
        foreach($this->disciplinas as $disciplina){
            $disciplina->sugerir_Alocacao_Ofertas($pavilhoes);
        }
    }
    
}

?>
