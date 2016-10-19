<?php
/**
 * Description of pavilhao
 *
 * @author João Daher
 */
class pavilhao {
    public $nome; //string
    public $cursos_preferenciais; //array of {curso.php}
    public $isolado; //boolean
    public $salas; //array of {sala.php}

    function __construct($nome, $cursos_preferenciais, $isolado) {
        $this->nome = $nome;
        $this->cursos_preferenciais = $cursos_preferenciais;
        $this->isolado = $isolado;
        $this->salas = array();
    }


    public function adicionar_Sala($nome, $laboratorio, $capacidade, $disciplinas_exclusivas){
        $sala = new sala($nome, $laboratorio, $capacidade, $disciplinas_exclusivas);
        $this->salas[] = $sala;
    }

    public function buscar_Sala($nome){
        foreach($this->salas as $sala){
            if($sala->nome == $nome){
                return $sala;
            }
        }
        return null;
    }

}

?>