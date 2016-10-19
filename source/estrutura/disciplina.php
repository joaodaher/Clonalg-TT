<?php
/**
 * Description of disciplina
 *
 * @author João Daher
 */
class disciplina {
    public $nome;
    public $codigo;
    public $ch_T;
    public $ch_P;
    public $ofertas; //array of {oferta.php}

    /**
     * Cria uma nova disciplina
     * @param [String] $nome Nome completo da disciplina
     * @param [String] $codigo Codigo da disciplina, com 3 letras e 3 numeros
     * @param [int] $ch_T Carga horaria teorica, em horas
     * @param [int] $ch_P Carga horaria pratica, em horas
     */
    function __construct($nome, $codigo, $ch_T, $ch_P) {
        $this->nome = $nome;
        $this->codigo = $codigo;
        $this->ch_T = $ch_T;
        $this->ch_P = $ch_P;
    }



    /**
     * Cria uma nova oferta para a disciplina
     * @param [array of String] $turmas Vetor de turmas que irão cursar (ex: ["10A","14A","22A"])
     * @param [int] $vagas Numero de vagas disponibilizadas (ex: 45)
     * @param [int] $ch Carga horária pratica ou teorica, em horas (ex: 68)
     * @param [bool] $pratica TRUE, se a carga horaria for pratica
     * @param [int] $tam_aula Tamanho maximo de cada aula, em horas; de 1 a 3 (ex: 3)
     */
    public function criar_Oferta($turmas, $vagas, $ch, $pratica, $tam_aula){
        $this->ofertas[] = new oferta($turmas, $vagas, $ch, $pratica, $this->codigo, $tam_aula);
    }

    /**
     * Busca por uma (ou mais) ofertas baseada em uma (ou mais) turma(s) que cursa(m) a disciplina
     * "10A" retorna sua respectiva instancia de {oferta.php}
     * ["10A","14A"] retorna sua respectiva instancia de {oferta.php}
     * @param [String] $turma Turma(s) presente(s) em uma oferta (ex: "10A")
     * @return [array of {oferta.php}] Ofertas procuradas
     */
    public function buscar_Ofertas($turmas){
        $ofertas = array();
        foreach($this->ofertas as $oferta){
            //print 'Verificando se '.(is_array($turma)?implode(' ', $turma):$turma).' esta na '.$this->codigo.' ('.implode(' ',$oferta->turmas).'): ';
            if(tools::search_array($turmas, $oferta->turmas)){
                //print '<b>SIM!</b> <br/>';
                $ofertas[] = $oferta;
            }
            //else print 'NAO! <br/>';
        }
        if(count($ofertas)>2) print '<h2>ERRO GRAVISSIMO em disciplina::buscar_Ofertas</h2>';
        return (count($ofertas)==0 ? null : $ofertas);
    }

    /**Busca por uma (ou mais) ofertas que são cursadas por alunos de um determinado curso
     * "G010" retorna sua(s) respectiva(s) instancia(s) de {oferta.php}
     * @param [String] $cod_curso Codigo do curso (ex: "G010")
     * @return [array of {oferta.php}] Ofertas procuradas
     */
    public function buscar_Ofertas_Curso($cod_curso){
        $ofertas = array();
        foreach($this->ofertas as $oferta){
            if(tools::search_sub_array(substr($cod_curso,2,2), $oferta->turmas)){
               $ofertas[] = $oferta;
            }
        }
        //if(count($ofertas)>2) print '<h2>ERRO GRAVISSIMO em disciplina::buscar_Ofertas_Curso</h2>';
        return (count($ofertas)==0 ? null : $ofertas);
    }

    /**
     * Sugere um horario completo para todas as ofertas da disciplina
     * @param [array of {pavilhao.php}] $pavilhoes Vetor com todos os pavilhoes
     */
    public function sugerir_Alocacao_Ofertas($pavilhoes){
        //print 'Alocando aulas da Disciplina '.$this->nome.'<br/>';
        foreach($this->ofertas as $oferta){
            //print '   Oferta da turma '.$oferta->turma.'<br/>';
            $oferta->sugerir_Horario_Completo($pavilhoes);
        }
    }

    /**
     * Troca o horario das aulas de uma determinada turma
     * @param [array of String] $turmas Vetor com as turmas que cursam a oferta (ex: ["10A","22A"])
     * @param [float] $tx_troca Probabilidade de trocar o horario (ex: 0.8 = 80%)
     */
    public function trocar_Horario_Oferta($turmas, $tx_troca){
        $ofertas = $this->buscar_Ofertas($turmas);
        foreach($ofertas as $oferta){
            $oferta->trocar_Horarios($tx_troca);
        }
    }

    /**
     * Troca o local das aulas de uma determinada turma
     * @param [array of String] $turmas Vetor com as turmas que cursam a oferta (ex: ["10A","22A"])
     * @param [array of {pavilhao.php}] $pavilhoes Vetor com todos os pavilhoes
     * @param [float] $tx_troca Probabilidade de trocar o horario (ex: 0.8 = 80%)
     */
    public function trocar_Salas_Oferta($turmas, $pavilhoes, $tx_troca){
        $ofertas = $this->buscar_Ofertas($turmas);
        foreach($ofertas as $oferta){
            $oferta->trocar_Salas($tx_troca, $pavilhoes);
        }
    }
}

?>
