<?php

/**
 * Description of curso
 * @author joaodaher
 */
class curso {
    public $nome; //("Ciencia da Computacao")
    public $codigo; //("G010")
    public $dpto_vinculado; //("DCC")
    public $matrizes; //array[5]=matriz_curricular.php
    public $noturno; //boolean
    
    /**
     * Construtor de um curso
     * @param [String] $nome Nome completo do curso (ex: "Ciencia da Computacao")
     * @param [String] $codigo Codigo do curso, com 1 letra e 3 digitos (ex: "G010")
     * @param [String] $dpto_vinculado Sigla do departamento ao qual o curso eh vinculado (ex: "DCC")
     * @param [bool] $noturno Turno do curso (TRUE, se for noturno)
     */
    function __construct($nome, $codigo, $dpto_vinculado, $noturno) {
        $this->nome = $nome;
        $this->codigo = $codigo;
        $this->dpto_vinculado = $dpto_vinculado;
        $this->noturno = $noturno;
    }

    /**
     * Acrescenta um novo periodo para o curso, com sua respectiva grade curricular
     * @param [int] $numero Periodo do curso
     * @param [{matriz.php} object] $matriz Matriz Curricular do periodo acrescentado
     */
    public function adicionar_Periodo($numero, $matriz){
        $this->matrizes[$numero] = $matriz;
    }
    
    /**
     * Busca por todas as matrizes curriculares que cursam uma determinada disciplina no periodo corrente
     * @param [String] $cod_disc Codigo da disciplina
     * @return [array of {matriz.php}] Vetor de matrizes que cursam a disciplina, onde o indice representa o periodo
     */
    public function obter_Matrizes_da_Disciplina($cod_disc){ //verificar utilidade (e simplificar)
        $matrizes_que_cursam = array();
        foreach($this->matrizes as $periodo=>$matriz){
            $entradas = $matriz->obter_Entradas_Periodo($periodo); //verifica apenas as disciplinas de cada periodo
            foreach($entradas as $entrada){
                $disciplina = $entrada[0];
                if($disciplina->codigo == $cod_disc){
                    $matrizes_que_cursam[$periodo] = $matriz;
                }
            }
        }
        return $matrizes_que_cursam;
    }
    
    /**
     * Busca a matriz curricular baseada em seu codigo
     * "200801" retorna sua respectiva instancia de {matriz.php}
     * @param [String] $nome_matriz Codigo da matriz curricular (ex: "200801")
     * @return [{matriz.php} object] Matriz curricular
     */
    public function obter_Matriz($nome_matriz){
        foreach($this->matrizes as $matriz){
            if($matriz->nome == $nome_matriz){
                return $matriz;
            }
        }
    }
    
    /**
     * Verifica se uma das turmas pedidas é de alunos deste curso.
     * @param [array of String] $turmas Vetor com turmas (ex: ["10A", "14A", "02B"])
     * @return [bool] TRUE, se uma das turmas pedidas é de alunos deste curso  
     */
    public function turma_Pertence($turmas){
        //verificar se este curso tem uma turma dentre as que foram informadas
        foreach($turmas as $turma){
            if(substr($turma, 0, 2) == substr($this->codigo, 2, 2)){
                return true;
            }
        }
        return false;
    }
    
}

?>
