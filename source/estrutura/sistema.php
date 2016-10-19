<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1');

class sistema {
    //grandes estruturas
    public $cursos;
    public $departamentos;
    public $pavilhoes;

    //taxas de adequacao
    private $folga_espaco;

    //variaveis de avaliacao
    private $pnt_chq_horario;
    private $pnt_chq_local;
    private $pnt_espacamento;
    private $pnt_janelas;
    private $pnt_alternancia;
    private $pnt_lab; 
    private $pnt_turno;
    private $pnt_isolamento; 
    private $pnt_proximidade;
    private $pnt_exclusividade; 
    private $pnt_capacidade;


    /**
     * Construtor do Sistema
     * - Cria os departamentos (ver: data::criar_Dptos)
     * - Cria os cursos (ver: data::criar_Cursos)
     * - Cria os pavilhoes (ver: data::criar_Espacos)
     */
    function __construct() {
        $this->departamentos = data::criar_Dptos();
        $this->cursos = data::criar_Cursos($this->departamentos);
        $this->pavilhoes = data::criar_Espacos($this->cursos);
        
        data::definir_Turnos_Ofertas($this->departamentos, $this->cursos);

        $this->folga_espaco = vars::getStokingGap();

        //carregar variaveis de avaliacao
        $this->pnt_chq_horario = vars::getPntChqHorario();
        $this->pnt_chq_local = vars::getPntChqLocal();
        $this->pnt_espacamento = vars::getPntEspacamento();
        $this->pnt_janelas = vars::getPntJanelas();
        $this->pnt_alternancia = vars::getPntAlternancia();
        $this->pnt_lab = vars::getPntLab();
        $this->pnt_turno = vars::getPntTurno();
        $this->pnt_isolamento = vars::getPntIsolamento();
        $this->pnt_proximidade = vars::getPntProximidade();
        $this->pnt_exclusividade = vars::getPntExclusividade();
        $this->pnt_capacidade = vars::getPntCapacidade();
    }

    /**
     * Cria uma oferta para uma disciplina
     * @param {espaco.php} instance] $espaco  Local das aulas
     * @param String $cod_disciplina Codigo da disciplina
     * @param String $turma Nome da turma (ex: "10A")
     * @param String $dia Dia da semana (ex: "Terca")
     * @param String $horario Hora do dia, formato 24h (ex: "9:00")
     * @param int $duracao Duracao em horas (ex: 1)
     */
    private function ofertar_Disciplina($espaco, $cod_disciplina, $turma, $dia, $horario, $duracao) {
        $nome_dpto = tools::buscar_Dpto_por_Disc($this->departamentos, $cod_disciplina, $turma);
        $dpto = tools::buscar_Dpto($this->departamentos, $nome_dpto); //descobrir qual departamento oferece a disciplina

        $dpto->ofertar_Disciplina($cod_disciplina, $turma, $dia, $hora, $duracao, $espaco);
    }

    /**
     * Cria horarios aleatorios para todas as ofertas
     */
    public function gerar_Horarios_Aleatorios() {
        foreach ($this->departamentos as $dpto) {
            $dpto->sugerir_Horarios_Completos($this->pavilhoes);
        }
    }

    /**
     * Verifica quais as ofertas que um determinado aluno deve cursar
     * @param String $cod_curso Codigo do curso do aluno (ex: "G010")
     * @param int $periodo Periodo/Semestre em que o aluno esta (ex: 6)
     * @return array of array of {ofertas.php} Vetor com as ofertas do aluno agrupadas por disciplina (ex: array["GCC101"] = array of {ofertas.php})
     */
    private function obter_Compromissos_Aluno($cod_curso, $periodo){
        //1- OBTER CURSO
        $curso = tools::buscar_Curso_por_Cod($this->cursos, $cod_curso);

        //2- OBTER DISCIPLINAS RELEVANTES
        $matriz = $curso->matrizes[$periodo];
        $matriz_periodo = $matriz->obter_Disciplinas_Periodo($periodo);

        //3- OBTER AS OFERTAS RELEVANTES
        $horarios = array();
        foreach ($matriz_periodo as $entrada) {
            $disciplina = $entrada[0];

            $ofertas = $disciplina->buscar_Ofertas_Curso($cod_curso);
            if ($ofertas == null){
                continue;
            }

            $horarios[$disciplina->codigo] = $ofertas;
        }

        return $horarios;
    }

    /**
     * Monta o horario de um suposto aluno baseado em seu periodo e turma
     * "G010","7" retorna sua respectiva instancia de {horario.php}
     * @param String $cod_curso Codigo do curso do aluno (ex: "G010")
     * @param int $periodo Periodo/Semestre em que o aluno esta (ex: 6)
     * @return [grid] horario Horario do aluno, onde cada slot contem um vetor de [oferta, local]
     * Exemplo de retorno: horario["Terca"]["8:00"]=[ [{oferta.php},{espaco.php}] , [{oferta.php},{espaco.php}] ]
     */
    public function obter_Horario_Aluno($cod_curso, $periodo) {
        $horarios = $this->obter_Compromissos_Aluno($cod_curso, $periodo);

        //4- CRIAR O HORARIO DO ALUNO
        $horario_aluno = vars::getGenericGrid();

        foreach ($horarios as $cod_disc => $ofertas) {
            foreach($ofertas as $oferta){
                //4.1 - OBTER O HORARIO DA OFERTA
                $horario_disc = $oferta->obter_Horario_Resumido();
                foreach ($horario_disc as $compromisso) {
                    $dia = $compromisso[0];
                    $hora = $compromisso[1];
                    $local = $compromisso[2];
                    //print 'DIA: '.$dia.' - HORA: '.$hora.' - LOCAL: '.$local->nome.'<br/>';
                    $horario_aluno[$dia][$hora][] = array($oferta, $local);
                }
            }
        }
        return $horario_aluno;
    }

    /**
     * Efetua a troca de horarios baseado na taxa de troca individual de hora/local
     * @param float $tx_troca_horario Probabilidade de trocar o horario de cada aula da oferta
     * @param float $tx_troca_local Probabilidade de trocar o local de cada aula da oferta
     */
    public function trocar_Grade($tx_troca_horario, $tx_troca_local) {
        foreach ($this->departamentos as $dpto) {
            foreach ($dpto->disciplinas as $disc) {
                foreach ($disc->ofertas as $oferta) {
                    if ($tx_troca_horario != 0){
                        $oferta->trocar_Horarios($tx_troca_horario);
                    }
                    if ($tx_troca_local != 0){
                        $oferta->trocar_Salas($tx_troca_local, $this->pavilhoes);
                    }
                }
            }
        }
    }


    //AVALIACOES================================================================

    /**
     * Realiza a avaliacao de toda a populacao, verificando a quantidade de 
     * infracoes as restricoes impostas e aplicando pesos de acordo com a relevancia
     * OBS: Controla individualmente a avaliacao de local e horario, a fim de criar
     * taxa de mutacao individuais
     * @return [array]
     *          [0] = resultado numerico da avaliacao (0-1)
     *          [1]
     *              ['chq_horario'][] = {oferta.php} object
     *              ['chq_local'][] = {oferta.php} object
     *              ['espacamento']["Cod Curso"]["Periodo"][] = {oferta.php} object
     *              ['janela']["Cod Curso"]["Periodo"][] = {oferta.php} object
     *              ['alternancia']["Cod Curso"]["Periodo"][] = {oferta.php} object
     *              ['lab']["Dpto"][] = {oferta.php} object
     *              ['turno']["Dpto"][] = {oferta.php} object
     *              ['isolamento']["Dpto"][] = {oferta.php} object
     *              ['proximidade']["Dpto"][] = {oferta.php} object
     *              ['exclusividade']["Dpto"][] = {oferta.php} object
     *              ['capacidade']["Dpto"][] = {oferta.php} object
     */
    public function avaliar(){
        $avaliacao_horario = 0;
        $avaliacao_local = 0;
        
        $info = array();
        
        //CHOQUES DE HORARIO
        $retorno = $this->choques_Horario();
        $avaliacao_horario += (float)($retorno[0] * $this->pnt_chq_horario);
        $info['chq_horario'] = $retorno[1];
        
        //CHOQUES DE ESPACO
        $retorno = $this->choques_Espaco();
        $avaliacao_local += $retorno[0] * $this->pnt_chq_local;
        $info['chq_local'] = $retorno[1];
        
        //DISTRIBUICAO (espacamento, janela, alternancia)
        $retorno = $this->distribuicao_Aula();
        foreach($retorno as $cod_curso=>$periodos){
            foreach($periodos as $periodo=>$itens){
                //ESPACAMENTO
                $avaliacao_horario += count($itens[0]) * $this->pnt_espacamento;
                $info['espacamento'][$cod_curso][$periodo] = $itens[0];
                
                //JANELA
                $avaliacao_horario += count($itens[1]) * $this->pnt_janelas;
                $info['janela'][$cod_curso][$periodo] = $itens[1];
                
                //ALTERNANCIA
                $avaliacao_horario += count($itens[2]) * $this->pnt_alternancia;
                $info['alternancia'][$cod_curso][$periodo] = $itens[2];
            }
        }
        
        //ADEQUACAO (lab, isolamento, proximidade, exclusividade, capacidade)
        $retorno = $this->adequacao_Aulas();
        foreach($retorno as $dpto=>$itens){
            //LABORATORIO
            $avaliacao_local += count($itens['lab']) * $this->pnt_lab;
            $info['lab'][$dpto] = $itens['lab'];
            
            //TURNO
            $avaliacao_horario += count($itens['turno']) * $this->pnt_turno;
            $info['turno'][$dpto] = $itens['turno'];
            
            //ISOLAMENTO
            $avaliacao_local += count($itens['isolamento']) * $this->pnt_isolamento;
            $info['isolamento'][$dpto] = $itens['isolamento'];
            
            //PROXIMIDADE
            $avaliacao_local += count($itens['proximidade']) * $this->pnt_proximidade;
            $info['proximidade'][$dpto] = $itens['proximidade'];
            
            //EXCLUSIVIDADE
            $avaliacao_local += count($itens['exclusividade']) * $this->pnt_exclusividade;
            $info['exclusividade'][$dpto] = $itens['exclusividade'];
            
            //CAPACIDADE
            $avaliacao_local += count($itens['capacidade']) * $this->pnt_capacidade;
            $info['capacidade'][$dpto] = $itens['capacidade'];
        }
        
        $fitness['horario'] = tools::normalizar($avaliacao_horario);
        $fitness['espaco'] = tools::normalizar($avaliacao_local);
        $fitness['geral'] = tools::normalizar($avaliacao_horario + $avaliacao_local);
        
        return array($fitness, $info);
    }
    
    /**
     * Calcula a quantidade de aulas simultaneas para um mesmo aluno
     * @return [array]
     *          [0] : (int) Quantidade de choques de horario
     *          [1] : [Curso][Periodo][Dia][Hora] = array of {oferta.php}
     */
    public function choques_Horario() {
        $choques = 0;
        $info = array();
        foreach ($this->cursos as $curso) {
            foreach ($curso->matrizes as $periodo => $matriz) {
                $retorno = $this->choques_Horario_Aluno($curso, $periodo);

                if ($retorno[0] != 0) { //se houve pelo menos um choque...
                    $choques += $retorno[0];
                    $info[$curso->codigo][$periodo] = $retorno[1];
                }
            }
        }
        return array($choques, $info);
    }

    /**
     * Conta a quantidade de choques de HORARIO de aulas para TODAS as turmas de um determinado periodo dum curso
     * Turmas diferentes, de um mesmo curso, (10A e 10B, por exemplo) nao sao consideradas choques.
     * Choques de local sao irrelevantes nesse momento.
     * @param {matriz.php} object $matriz Matriz Curricular a ser analisada
     * @param int $num_periodo Periodo da turma desejada
     * @return [array]
     *          [0] : int Quantidade de choques de horario
     *          [1] : [Dia][Hora] = array of {oferta.php}
     */
    private function choques_Horario_Aluno($curso, $periodo) {
        //print '<br/><b>Verificando horario de aluno de '.$curso->codigo.' no '.$periodo.' periodo</b><br/>';
        $choques = 0;
        $info = array();

        $horario = $this->obter_Horario_Aluno($curso->codigo, $periodo);

        foreach ($horario as $dia => $horas) {
            foreach ($horas as $hora => $compromissos) {

                $qtd_compromissos = count($compromissos);
                if ($qtd_compromissos > 1) { //se houver +1 aula no horario...
                    //print 'Opa! '.$qtd_compromissos.' aulas de uma vez...<br/>';
                    $mesma_turma = tools::ofertas_de_Mesma_Turma($compromissos, $curso->codigo);
                    if($mesma_turma){ //se as aulas forem da mesma turma...
                        $choques += $qtd_compromissos;
                        foreach ($compromissos as $compromisso) {
                            //print '....... '.$compromisso[0].'<br/>';
                            $info[$dia][$hora][] = $compromisso[0]; //oferta
                        }
                        // $info[$dia][$hora] =  [{oferta.php}, {oferta.php}]
                    }

                }
            }
        }

        return array($choques, ($choques != 0 ? $info : null));
    }

    /**
     * Calcula a quantidade de aulas simultaneas em um mesmo local
     * @return [array]
     *          [0] : int Quantidade de choques de espaco
     *          [1] : [Sala][Dia][Hora] = array of {oferta.php}
     */
    public function choques_Espaco() {
        $info = array();
        $choque = 0;
        foreach ($this->pavilhoes as $pavilhao) {
            foreach ($pavilhao->salas as $sala)
                foreach ($sala->obter_Ocupacao() as $dia => $horas) {
                    foreach ($horas as $hora => $ofertas) {
                        $qtd_ofertas = count($ofertas);
                        if ($qtd_ofertas > 1) {
                            $choque += $qtd_ofertas - 1;
                            $info[$sala->nome][$dia][$hora] = $ofertas;
                        }
                    }
                }
        }
        return array($choque, $info);
    }

    /**
     * Verifica a disposicao/distribuicao das aulas ao longo da semana, checando:
     * - Espacamentos: se aulas iguais tem os devidos intervalos
     * - Janelas: se aulas diferentes nao tem horarios vagos entre si
     * - Alternacao Noturna: se aulas noturnas alternam seus horarios durante a noite
     * @return [array]
     *              ["Cod Curso"]["Periodo"]
     *                                  [0][] = {oferta.php} object : espacamentos inadequados (MAIS = PIOR)
     *                                  [1][] = {oferta.php} object : janelas encontradas  (MAIS = PIOR)
     *                                  [2][] = {oferta.php} object : inalternancias noturnas  (MAIS = PIOR)
     */
    public function distribuicao_Aula() {
        foreach ($this->cursos as $curso) {
            foreach ($curso->matrizes as $periodo => $matriz) {
                $info_aluno = $this->distribuicao_Aula_Aluno($curso->codigo, $periodo);
                $info[$curso->codigo][$periodo] = $info_aluno;
            }
        }
        return $info;
    }


    /**
     * Verifica a disposicao/distribuicao das aulas ao longo da semana para um determinado aluno
     * @param [String] $cod_curso Codigo do curso do aluno (ex: "G010")
     * @param [int] $periodo Periodo do aluno (ex: 3)
     * @return [array]
     *              [0][] = {oferta.php} object : espacamentos inadequados
     *              [1][] = {oferta.php} object : janelas encontradas
     *              [2][] = {oferta.php} object : nao alternancias noturnas
     */
    private function distribuicao_Aula_Aluno($cod_curso, $periodo) {
        $espacamento = array();
        $janelas = array();
        $alternancia = array();

        $compromissos = $this->obter_Compromissos_Aluno($cod_curso, $periodo);
        foreach($compromissos as $disc=>$ofertas){
            foreach($ofertas as $oferta){
                if(!$this->verificar_Espacamento($oferta)){
                    $espacamento[] = $oferta;
                }

                if(!$this->verificar_Alternancia_Noturna($oferta)){
                    $alternancia[] = $oferta;
                }
            }
        }

        $janelas = $this->verificar_Janelas($cod_curso, $periodo);

        return array($espacamento, $janelas, $alternancia);
    }


    /**
     * Verifica o tempo vago entre os blocos de aula, seguindo as exigencias:
     * 2 Creditos: sem espaçamento
     * 3 Creditos: espaçamento de nenhum ou 2 dias
     * 4 Creditos: espaçamento de 2 dias
     * 6 Creditos: espaçamento de 2 dias entre cada
     * @param [{oferta.php} object] $oferta Oferta da disciplina
     * @return [bool] TRUE, se o espacamento esta adequado
     */
    private function verificar_Espacamento($oferta){
        $resumo = tools::obter_Resumo_Aulas($oferta->horario);

        foreach($resumo as $aula){
            $dias[] = $aula[0];
        }

        $num_dia1 = tools::dia2numero($dias[0]);
        $num_dia2 = (count($dias) > 1) ? tools::dia2numero($dias[1]) : null;
        $num_dia3 = (count($dias) > 2) ? tools::dia2numero($dias[2]) : null;

        //print 'Oferta de '.$oferta->creditos.' creditos. ('.$oferta->cod_disc_relacionada.')<br/>';
        switch($oferta->creditos){
            case 1:
            case 2:
                return true;

            case 3:
                if($num_dia2 == null){ //3 creditos consecutivos
                    return true;
                }
                else{ //2 creditos + 1 credito
                    return ($num_dia2 - $num_dia1 == 2);
                }

            case 4:
                return ($num_dia2 - $num_dia1 == 2);

            case 6:
                return ($num_dia2 - $num_dia1 == 2) && ($num_dia3 - $num_dia2 == 2);

            default:
                print 'ERRO ao verificar o espacamento: '.$oferta->cod_disc_relacionada.($oferta->pratica?'P':'T').implode('',$oferta->turmas);
                return false;
                break;
        }
    }


    /**
     * Verifica a existencia de horarios vagos entre aulas
     * - Intervalos (almoco, lanche e janta) nao sao considerados janelas [ver: var.xml]
     * @param [{oferta.php} object] $oferta Oferta da disciplina
     * @return [array][] = {oferta.php} object :janela ocorre apos esta oferta
     */
    private function verificar_Janelas($cod_curso, $periodo){
        $horario = $this->obter_Horario_Aluno($cod_curso, $periodo);

        $ocorrencias = 0;
        $janelas = array();

        foreach($horario as $dia=>$horas){
            $creditos_dia = tools::contar_Creditos_no_Dia($horario, $dia);
            $sequencia = false;
            foreach($horas as $hora=>$slot){
                if($hora == vars::getLunchTime() || $hora == vars::getSnackTime() || $hora == vars::getNightTime()){
                    $sequencia = false;
                }

                if($slot != null){
                    $sequencia = true;
                    $creditos_dia--;

                    $oferta = $slot[0][0];
                    //print '<i>Analisando: <i/>'.$oferta->cod_disc_relacionada.' ('.$dia.' - '.$hora.')<br/>';

                }

                else if($slot==null && $sequencia && $creditos_dia > 0){
                    $janelas[] = $oferta;
                    //$janelas[] = array($dia,$hora);
                    $sequencia = false;
                    //print '<b>Janela após: </b>'.$oferta->cod_disc_relacionada.' ('.$dia.' - '.$hora.')<br/>';

                }
            }
        }
        return $janelas;
    }


    /**
     * Verifica se, para ofertas noturnas, as aulas tem seus horarios alternados ao
     * decorrer da semana. As aulas devem ser ofertas as 19h e 21h (nao necessariamente nessa ordem)
     * independentemente da ordem.
     * - Independe dos dias em que as aulas estao (19h e 21h do mesmo dia é aceito)
     * - Oferta diurnas sao sumariamente consideradas TRUE
     * @param [{oferta.php} object] $oferta Oferta da disciplina
     * @return [bool] TRUE, se a oferta esta alternanda (ou se nao se aplica)
     */
    private function verificar_Alternancia_Noturna($oferta){
        if(!tools::verificar_Oferta_Noturna($this->cursos, $this->departamentos, $oferta)){
            return true;
        }
        else{
            $aulas = tools::obter_Resumo_Aulas($oferta->horario);

            foreach($aulas as $aula){
                $hora_inicio[] = $aula[1];
            }

            $qtd_aulas = count($aulas);
            if($qtd_aulas == 1){ //apenas 1 aula
                return true;
            }

            $diferenca = $hora_inicio[0] - $hora_inicio[1];
            $diferenca = ($diferenca < 0) ? ($diferenca*(-1)) : ($diferenca);

            if($qtd_aulas == 2){
                return ($diferenca == 2);
            }
            else{
                $diferenca2 = $hora_inicio[1] - $hora_inicio[2];
                $diferenca2 = ($diferenca2 < 0) ? ($diferenca2*(-1)) : ($diferenca2);

                return ($diferenca == 2) && ($diferenca2 == 2);
            }
        }
    }

    /**
     * Avalia a adequacao de todos os pavilhoes em relacao aos cursos que o estao utilizando
     * - Oferta em laboratorio  X  Espaco eh um laboratorio
     * - Cursos que frequentam sao diurnos/noturnos   X   Aulas estao no periodo de dia/noite
     * - Aulas no periodo da noite   X   Local isolado
     * - Proximidade entre os cursos que frequentam e o local
     * - Local é exclusivo para a disciplina
     * - Capacidade do local   X   Vagas disponiveis na oferta
     * @return [array]
     *              ["Departamento"]
     *                  ['lab'][] = [{oferta.php} object, dia, hora] : ofertas NAO adequadas 
     *                  ['turno'][] = [{oferta.php} object, dia, hora] : ofertas NAO adequadas 
     *                  ['isolamento'][] = [{oferta.php} object, dia, hora] : ofertas NAO adequadas 
     *                  ['proximidade'][] = [{oferta.php} object, dia, hora] : ofertas NAO adequadas 
     *                  ['exclusividade'][] = [{oferta.php} object, dia, hora] : ofertas NAO adequadas 
     *                  ['capacidade'][] = [{oferta.php} object, dia, hora] : ofertas NAO adequadas 
     */
    private function adequacao_Aulas() {
        $avaliacao = 0;
        $info = array();
        //print 'Avaliando adequação...<br/>';
        foreach ($this->departamentos as $dpto) {
            $info[$dpto->nome]['lab'] = null;
            $info[$dpto->nome]['turno'] = null;
            $info[$dpto->nome]['isolamento'] = null;
            $info[$dpto->nome]['proximidade'] = null;
            $info[$dpto->nome]['exclusividade'] = null;
            $info[$dpto->nome]['capacidade'] = null;

            foreach ($dpto->disciplinas as $disciplina) {
                foreach ($disciplina->ofertas as $oferta) {
                    $cursos_noturnos = tools::verificar_Oferta_Noturna($this->cursos, $this->departamentos, $oferta);

                    //avaliar individualmente cada slot do horario a fim de...
                    foreach ($oferta->horario->grade as $dia => $horas) {
                        foreach ($horas as $hora => $sala) {
                            if ($sala == null)
                                continue; //apenas se tiver aula nesse horario


                            //.... avaliar laboratorios
                            if ($oferta->pratica != $sala->laboratorio) {
                                $info[$dpto->nome]['lab'][] = array($oferta, $dia, $hora);
                            }

                            //... avaliar aulas diurnas/noturnas
                            $aula_noturna = $hora >= vars::getEveningTime();
                            if ($cursos_noturnos != $aula_noturna) {
                                $info[$dpto->nome]['turno'][] = array($oferta, $dia, $hora);
                            }

                            //... avaliar isolamento em aulas noturnas
                            if ($cursos_noturnos){
                                $pavilhao = tools::buscar_Pavilhao_por_Sala($sala->nome, $this->pavilhoes);
                                if($pavilhao->isolado){
                                    $info[$dpto->nome]['isolamento'][] = array($oferta, $dia, $hora);
                                }
                            }

                            //... avaliar a proximidade do curso com a sala
                            $distante = false;
                            $cursos_envolvidos = tools::buscar_Cursos_da_Oferta($this->cursos, $this->departamentos, $oferta);
                            $pavilhao = tools::buscar_Pavilhao_por_Sala($sala->nome, $this->pavilhoes);

                            //print 'Analisando '.$pavilhao->nome.' - '.count($cursos_envolvidos).' cursos...<br/>';
                            //print_r($pavilhao);
                            foreach ($cursos_envolvidos as $curso_envolvido){
                                $proximo = false;

                                foreach($pavilhao->cursos_preferenciais as $curso_preferencial){
                                    if($curso_preferencial->codigo == $curso_envolvido->codigo){
                                        $proximo = true;
                                    }
                                }

                                if($proximo == false){ //se houver um curso distante na sala
                                    $distante = true;
                                    break;
                                }
                            }


                            if ($distante) { //se todos os cursos sao proximos
                                $info[$dpto->nome]['proximidade'][] = array($oferta, $dia, $hora);
                            }

                            //... avaliar se a sala eh exclusiva para a oferta
                            if (!in_array($disciplina->codigo, $sala->disciplinas_exclusivas)) {
                                $info[$dpto->nome]['exclusividade'][] = array($oferta, $dia, $hora);
                            }

                            //... avaliar a capacidade da sala
                            if ($oferta->vagas * (1 + $this->folga_espaco) > $sala->capacidade) {
                                $info[$dpto->nome]['capacidade'][] = array($oferta, $dia, $hora);
                            }
                        }
                    }
                }
            }
        }
        return $info;
    }


}

?>
