<?php
class matriz_curricular{
    public $nome; //"200801"
    public $curso; //"G010"
    public $entradas; //array[]=[disciplina.php, obrig?, periodo, pre_req]
    
    function __construct($nome, $curso, $entradas) {
        $this->nome = $nome;
        $this->curso = $curso;
        $this->entradas = $entradas;
    }
    
    public function obter_Disciplinas_Periodo($periodo){
        foreach($this->entradas as $entrada){
            if($entrada[2] == $periodo){
                $disciplinas_escolhidas[] = $entrada;
            }
        }
        return $disciplinas_escolhidas;
    }
    
    	
}
?>
