<?php
/**
 * Classe estatica que fornece ferramentas auxiliares de busca/conversao
 * @author João Daher
 */
class tools {

    /**
     * Procura por uma disciplina baseado em seu codigo
     * "COM101" retorna sua respectiva instancia de {disciplina.php}
     * @param [String] $cod_disc Codigo da disciplina (ex: "COM101")
     * @param [array of {departamento.php}] $departamentos Vetor contendo todos os departamentos
     * @return [{disciplina.php} object] A disciplina procurada
     */
    static function buscar_Disciplina($cod_disc, $departamentos){
        foreach($departamentos as $dpto){ //procurar em cada departamento
            if(tools::search_array(substr($cod_disc,0,3), $dpto->cod_disc)){ //verifica se o codigo da disc eh do dpto
                $disc =  $dpto->buscar_disciplina($cod_disc);
                if($disc==null)print 'DISCIPLINA '.$cod_disc.' NAO CADASTRADA! <br/>';
                return $disc;
            }
        }
        print 'DISCIPLINA '.$cod_disc.' NAO PERTENCE A NENHUM DPTO! <br/>';
        return null;
    }

    /**
     * Busca o departamento responsavel por uma determinada disciplina
     * "COM101" retorna sua respectiva instancia de {departamento.php}
     * @param [String] $cod_disc Codigo da disciplina
     * @param [array of {departamento.php}] $departamentos Vetor contendo todos os departamentos
     * @return [{departamento.php} object] Departamento procurado
     */
    static function buscar_Dpto_por_Disc($cod_disc, $departamentos){
        foreach($departamentos as $dpto){ //procurar em cada departamento
            if(in_array(substr($cod_disc,0,3), $dpto->cod_disc)){ //verifica se o codigo da disc eh do dpto
                return $dpto;
            }
        }
        print 'DISCIPLINA '.$cod.' NAO PERTENCE A NENHUM DEPARTAMENTO! <br/>';
        return null;
    }


    /**
     * Busca um departamento baseado em seu nome
     * "DCC" retorna sua respectiva instancia de {departamento.php}
     * @param [array of {departamento.php}] $dptos Vetor contendo todos os departamentos
     * @param [String] $nome_dpto Nome do departamento
     * @return [{departamento.php} object] Departamento procurado
     */
    static function buscar_Dpto_por_Nome($dptos, $nome_dpto){
        foreach($dptos as $dpto){
            if($dpto->nome == $nome_dpto){
                return $dpto;
            }
        }
        return null;
    }


    /**
     * Busca um curso baseado em seu codigo
     * "G010" retorna sua respectiva instancia de {curso.php}
     * @param [array of {curso.php}] $cursos Vetor contendo todos os cursos
     * @param [String] $cod Codigo do curso, com 1 letra e 3 digitos
     * @return [{curso.php} object] Curso procurado (NULL, se nao encontrado)
     */
    static function buscar_Curso_por_Cod($cursos, $cod){
        foreach($cursos as $curso){
            if($curso->codigo == $cod){
                return $curso;
            }
        }
        print 'CURSO '.$cod.' NAO ENCONTRADO! <br/>';
        return null;
    }

    /**
     * Busca a matriz curricular baseada em seu nome e curso
     * "G010","200801" retorna sua respectiva instancia de {matriz.php}
     * @param [array of {curso.php}] $cursos Vetor contendo todos os cursos
     * @param [String] $curso_matriz Codigo do curso ao qual a matriz pertence (ex: "G010")
     * @param [String] $nome_matriz Codigo de identificacao da matriz (ex: "200801")
     * @return [{matriz.php} object] A matriz curricular procurada
     */
    static function buscar_Matriz($cursos, $curso_matriz, $nome_matriz){
        foreach($cursos as $curso){
            if($curso->codigo == $curso_matriz){
                return $curso->obter_Matriz($nome_matriz);
            }
        }
        print 'MATRIZ '.$curso_matriz.' - '.$nome_matriz.' NAO ENCONTRADA! <br/>';
        return null;
    }

    /**
     * Busca uma oferta baseado no codigo da disciplina e turma(s) que frequenta(m)
     * "COM101","10A" retorna sua respetiva instancia de {oferta.php}
     * "COM101","10A 12A" retorna sua respetiva instancia de {oferta.php}
     * @param [array of {departamento.php}] $dptos Vetor contendo todos os departamentos
     * @param [String] $cod_disc Codigo da disciplina (ex: "COM101")
     * @param [String] $turma Codigo(s) da(s) turma(s) (ex: "10A")
     * @return [{oferta.php} object] A oferta procurada
     */
    static function buscar_Oferta($departamentos, $cod_disc, $turma, $pratica){
        $disc = self::buscar_Disciplina($cod_disc, $departamentos);
        $ofertas = $disc->buscar_Ofertas($turma);
        foreach($ofertas as $oferta){
            if($oferta->pratica == $pratica){
                return $oferta;
            }
        }
        return null;
    }

    /**
     * Busca uma (ou mais) oferta(s) de uma disciplina que seja cursada por alunos de um determinado curso
     * Tambem é possivel restringir a busca para apenas ofertas praticas ou teoricas
     * @param [String] $departamentos Vetor com todos os departamentos
     * @param [String] $cod_disc Codigo da disciplina (ex: "COM101")
     * @param [String $cod_curso~Codigo do curso (ex: "G010")
     * @param [ [bool] $pratica TRUE, se a oferta procurada for pratica ]
     * @return [array of {oferta.php}] Vetor com todas as ofertas (caso $pratica seja omitido)
     *         [{oferta.php} object] Oferta da disciplina (caso $pratica seja informado)
     */
    static function buscar_Ofertas_Curso($departamentos, $cod_disc, $cod_curso, $pratica=null){
        $disc = self::buscar_Disciplina($cod_disc, $departamentos);
        $ofertas = $disc->buscar_Ofertas_Curso($cod_curso);

        if($pratica == null){
            return $ofertas;
        }
        else{
            foreach($ofertas as $oferta){
                if($oferta->pratica == $pratica){
                    return $oferta;
                }
            }
        }
        return null;
    }

    /**
     * Busca por cursos com alunos que frequentam a oferta informada
     * @param [array of {curso.php}] $cursos
     * @param [array of {departamento.php}] $departamentos
     * @param [String] $cod_disc Codigo da disciplina ofertada
     * @param [{oferta.php} object] Oferta da disciplina
     * @return [array of {curso.php}] Vetor de cursos com alunos que cursam a oferta informada
     */
    static function buscar_Cursos_da_Oferta($cursos, $departamentos, $oferta){
        $cursos_envolvidos = array();
        foreach($cursos as $curso){
            if($curso->turma_Pertence($oferta->turmas)){
                $cursos_envolvidos[] = $curso;
            }
        }
        return $cursos_envolvidos;
    }

    /**
     * Busca uma sala de aula ou laboratorio
     * @param [String] $cod_local Codigo do local procurado
     * @param [array of {pavilhao.php}] $pavilhoes Vetor com todos os pavilhoes
     * @return [{sala.php} object] Local procurado
     */
    static function buscar_Local($cod_local, $pavilhoes){
        foreach($pavilhoes as $pavilhao){
            $sala = $pavilhao->buscar_Sala($cod_local, $pavilhoes);
            if($sala != null){
                return $sala;
            }
        }
        print '<i>(tools::buscar_Local)</i> Sala não encontrada...<br/>';
        return null;
    }

    /**
     * Busca  pavilhão ao qual uma detemrinada sala pertence
     * @param [String] $nome_sala Codigo da sala que esta lotada no pavilhao procurada
     * @param [array of {pavilhao.php}] $pavilhoes Vetor com todos os pavilhoes
     * @return [{pavilhao.php} object] Pavilhao procurada
     */
    static function buscar_Pavilhao_por_Sala($nome_sala, $pavilhoes){
        foreach($pavilhoes as $pavilhao){
            $sala = $pavilhao->buscar_Sala($nome_sala, $pavilhoes);
            if($sala != null){
                return $pavilhao;
            }
        }
        return null;
    }
    /**
     * CUIDADO! ESTA FUNÇÃO APAGA A ESTRUTURA DE OFERTAS
     * Remove os ofertas dos departamentos deixando apenas as que tem uma das turmas informadas.
     * Evita a posterior alocação desnecessária de ofertas para turmas foram das que forem informadas
     * Não é alterada nenhuma oferta! Apenas removida as que não atende às especificações!
     * @param [array of {departamento.php}] $departamentos Vetor com todos os departamentos
     * @param [array of String] $turmas Vetor de turmas que se deseja manter as ofertas (ex: ["10A","10B", "10C"])
     */
    static function excluir_Ofertas_Sobressalentes($departamentos, $turmas){
        foreach($departamentos as $dpto){
            foreach($dpto->disciplinas as $key_disc=>$disc){
                foreach($disc->ofertas as $key_oferta=>$oferta){
                    $manter = false;
                    foreach($turmas as $turma){
                        if(self::search_array($turma, $oferta->turmas)){
                            $manter = true;
                            break;
                        }
                    }

                    if(!$manter){
                        unset($disc->ofertas[$key_oferta]);
                    }

                }

                if(count($disc->ofertas) == 0){
                    unset($dpto->disciplinas[$key_disc]);
                }
            }
        }
    }

    /**
     * Verifica se, dentre as ofertas nos compromissos, ha 2 ou mais que sao da mesma turma
     * Util para evitar que turmas distintas de um mesmo curso (10A e 10B, por exemplo) sejam considerados choques
     * @param [array] $compromissos Compromissos do aluno, sendo que:
     * $compromissos[0] = {oferta.php} object
     * @param [String] $cod_curso Codigo do curso, a fim de buscar as turmas
     * @return [bool] TRUE, se houver 2 ou mais ofertas de uma mesma turma
     */
    static function ofertas_de_Mesma_Turma($compromissos, $cod_curso){
        $trecho = substr($cod_curso, 2, 2); //numero da turma (ex: 10)

        $turmas = array();
        foreach ($compromissos as $compromisso) {
            $turmas_oferta = $compromisso[0]->turmas;

            foreach($turmas_oferta as $turma){ //procura pela turma do curso dentre todas que cursam
                if(substr_count($turma, $trecho) == 1){
                    $turma_curso = $turma;
                    break;
                }
            }

            if(self::search_array($turma_curso, $turmas)){ //confere se outra oferta era dessa turma
                return true;
            }
            else{
                $turmas[] = $turma_curso;
            }

        }
        return false;
    }


    /**
     *
     * @param [grid] $horario Horario da oferta, onde cada slot pode (ou nao) ser em {espaco.php}
     * @return [array] Vetor resumindo os horarios, onde ha dia, hora de incio e hora de termino da aula:
     * $resumo[] = ["Terca", "16:00", "18:00"]
     */
    static function obter_Resumo_Aulas($horario){
        foreach($horario->grade as $dia=>$horas){
            $ignorar = 0;
            foreach($horas as $hora=>$slot){
                if($ignorar > 0){
                    $ignorar--;
                    continue;
                }

                if($slot != null){
                    $resumo[] = array($dia, $hora, $hora+$horario->tam_aula.':00');
                    $ignorar = $horario->tam_aula-1;
                }
            }
        }
        return $resumo;
    }

    /**
     * Verifica se uma determinada oferta é cursada por cursos noturnos
     * Se pelo menos uma turma for de um curso noturno, a oferta toda é considerada noturna
     * @param array of {curso.php} $cursos Vetor com todos os cursos
     * @param array of {departamento.php} $departamentos Vetor com todos os departamentos
     * @param {oferta.php} object $oferta Oferta a ser verificada
     * @return bool TRUE, se a oferta for noturna
     */
    static function verificar_Oferta_Noturna($cursos, $departamentos, $oferta){
        //verificar se a oferta deve ser noturna/diurna (se pelo menos UM curso que cursa for a noite, a oferta sera considerada noturna)
        $cursos_envolvidos = tools::buscar_Cursos_da_Oferta($cursos, $departamentos, $oferta);
        $cursos_noturno = false;
        foreach ($cursos_envolvidos as $curso) {
            if ($curso->noturno) {
                return true;
            }
        }
        return false;
    }

    /**
     * Conta a quantidade de aulas alocadas em um determinado dia
     * @param {horario.php} $horario Horario completo
     * @param String $dia Dia da semana a ser analisado (ex: "Quarta")
     * @return int Quantidade de creditos alocados no dia informado 
     */
    static function contar_Creditos_no_Dia($horario, $dia){
        $creditos = 0;
        foreach($horario[$dia] as $hora=>$slot){
            $creditos += ($slot != null) ? 1 : 0;
        }
        return $creditos;
    }

    /**
     * Converte um dia da semana para número, a fim de calcular espaçamentos
     * @param String $dia Dia da semana (ex: "Quarta")
     * @return int Numero que representa do dia, de 1 a 6, e 0 para erro. (ex: 3)
     */
    static function dia2numero($dia){
        //print 'Convertendo '.$dia.'<br/>';
        switch($dia){
            case "Segunda":
                return 1;
                break;

            case "Terca":
                return 2;
                break;

            case "Quarta":
                return 3;
                break;

            case "Quinta":
                return 4;
                break;

            case "Sexta":
                return 5;
                break;

            case "Sabado":
                return 6;
                break;

            default:
                print '<b>ERRO ao converter o dia para numero.</b>';
                return 0;
                break;
        }
    }

    /**
     * Converte um numero inteiro para um slot da grade
     * @param [int] $prob Numero que representa um slot, entre 0 e dias x horas (ex: 38)
     * @return [array of String] Vetor com o dia e a hora (ex: array = ["Terca", "16:00"] )
     */
    static function prob_Para_Horario($prob) {
        $grade = vars::getGenericGrid();
        foreach ($grade as $dia => $horas) {
            foreach ($horas as $hora => $slot) {
                if ($prob == 1) {
                    return array($dia, $hora);
                }
                $prob--;
            }
        }
        return null;
    }

    /**
     * Compara se 2 vetores tem os mesmos elementos, independente da ordem.
     * @param [array] $x Vetor 1
     * @param [array] $y Vetor 2
     * @return [bool] TRUE, se os vetores são iguais
     */
    static function comparar_Vetores($x, $y){
        return count(array_intersect($x, $y)) == count($x);
    }

    /**
     * Busca um (ou mais) elemento(s) em um vetor.
     * @param [anything or array] $needle Item(ns) a se procurar
     * @param [array] $heystack vetor em que se deseja procurar o(s) item(ns)
     * @return [bool] TRUE, se o(s) elemento(s) foram (todos) encontrados.
     */
    static function search_array($needle, $heystack){
        //print 'Array Search: '.$needle.' esta em '.implode(' ', $heystack).'? ';
        if($heystack == null || $needle == null || !is_array($heystack)) return false;

        if(is_array($needle)){
            foreach($needle as $item){
                if(!tools::search_array($item, $heystack)) return false;
            }
            return true;
        }

        else{
            foreach($heystack as $item){
                if($item == $needle){
                    //print 'sim<br/>';
                    return true;
                }
            }
            //print '<b>nao</b><br/>';
            return false;
        }
    }

    /**
     * Busca um trecho de elemento em um vetor.
     * @param [anything or array] $needle Item(ns) a se procurar
     * @param [array] $heystack vetor em que se deseja procurar o(s) item(ns)
     * @return [bool] TRUE, se o(s) elemento(s) foram (todos) encontrados.
     */
    static function search_sub_array($needle, $heystack){
        //print 'Array Search: '.$needle.' esta em '.implode(' ', $heystack).'? ';
        if($heystack == null || $needle == null || !is_array($heystack)) return false;

        foreach($heystack as $item){
            if(substr_count($item, $needle) == 1) return true;
        }
        return false;
    }
    
    /**
     * Realiza a normalizao de um determinado valor, transformando-o em valor absoluto
     * atraves de g(x)=1/(1+x)
     * @param int $x Valor a ser normalizado
     * @return float Valor normalizado 
     */
    static function normalizar($x){
        return (float) 1/(1+$x);
    }
    
    
    /**
     * Avalia e informa a possibilidade de alocar as disciplinas nos espacos fornecidos
     * @param array of {pavilhao.php} $pavilhoes Vetor com pavilhoes de aula
     * @param array of {curso.php} $cursos Vetor com cursos oferecidos 
     */
    static function avaliar_Ocupacao($pavilhoes, $cursos){
        $salas = 0;
        foreach($pavilhoes as $pavilhao){
            foreach($pavilhao->salas as $sala){
                $salas++;
            }
        }
        
        $periodo = array("1"=>0, "2"=>0, "3"=>0, "4"=>0, "5"=>0, "6"=>0, "7"=>0, "8"=>0);
        foreach($cursos as $curso){
            print ".";
            foreach($curso->matrizes as $per=>$matriz){
                $periodo[$per] += count($matriz->obter_Disciplinas_Periodo($per));
            }
        }
        
        
        //procura pela maior lotacao possivel
        $maior = 0;
        foreach($periodo as $qtd){
            //print "checking: ".$qtd;
            if($qtd > $maior){
                $maior = $qtd;
            }
        }
        print "Na pior das hipoteses, havera ".($maior/$salas*100)."% de lotacao.";
    }

}

?>
