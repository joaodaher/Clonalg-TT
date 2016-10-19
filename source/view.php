<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body><div align="center">
            <?php
            session_start();
            ini_set('display_errors',1);
            include 'estrutura\matriz_curricular.php';
            include 'estrutura\data.php';
            include 'estrutura\sistema.php';
            include 'estrutura\disciplina.php';
            include 'estrutura\departamento.php';
            include 'estrutura\pavilhao.php';
            include 'estrutura\sala.php';
            include 'estrutura\tools.php';
            include 'vars.php';
            
            $secao = (isset($_GET["secao"]) ? ($_GET["secao"]) : null);
            $id = (isset($_GET["id"]) ? ($_GET["id"]) : null);

            $versao = (isset($_GET["versao"]) ? ($_GET["versao"]) : null);
            
            $UFLA = unserialize($_SESSION[$versao]);
            $show = new view($secao, $id, $versao, $UFLA);
            $show->start();

            class view {
                private $UFLA;
                private $secao;
                private $id;
                private $versao;
                private $choques_horario;
                private $choques_espaco;
                private $espacamento;
                private $janelas;
                private $alternancia;
                private $lab;
                private $turno;
                private $isolamento;
                private $proximidade;
                private $exclusividade;
                private $capacidade;
                
                function __construct($secao, $id, $versao, $UFLA) {
                    $this->secao = $secao;
                    $this->id = $id;
                    $this->versao = $versao;
                    $this->UFLA = $UFLA;
                }

                public function start() {
                    switch ($this->secao) {
                        case "dpto":
                            $this->mostrar_departamento();
                            break;

                        case "disc":
                            $this->mostrar_disciplina();
                            break;

                        case "curso":
                            $this->mostrar_curso();
                            break;

                        case "matriz":
                            $this->mostrar_matriz();
                            break;

                        case "oferta":
                            $this->mostrar_oferta();
                            break;

                        case "horario":
                            $this->mostrar_horario();
                            break;

                        case "local":
                            $this->mostrar_local();
                            break;

                        case "pavilhao":
                            $this->mostrar_pavilhao();
                            break;

                        case "relatorio":
                            $avaliacao = $this->UFLA->avaliar();
                            $info = $avaliacao[1];

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

                            switch ($this->id) {
                                case "espaco":
                                    $this->mostrar_choques_espaco();
                                    break;

                                case "horario":
                                    $this->mostrar_choques_horario();
                                    break;

                                case "distribuicao":
                                    $this->mostrar_distribuicao();
                                    break;

                                case "adequacao":
                                    $this->mostrar_adequamento();
                                    break;
                            }
                            break;

                        case "debug":
                            $this->debugging();
                            break;

                        default:
                            $this->mostrar_inicial();
                            break;
                    }
                }

                function mostrar_inicial() {
                    print '<h1>Timetable UFLA</h1><br/>';
                    print '<table align\'center\' border=\'1\'>
                   <tr>
                     <td align="center"><b>DEPARTAMENTOS</b></td>
                     <td align="center"><b>CURSOS</b></td>
                     <td align="center"><b>LOCAIS</b></td>
                   </tr>
                   <tr><td align="center">';
                    
                    foreach ($this->UFLA->departamentos as $dpto) {
                        print '<a href=' . $this->link_dpto($dpto->nome) . '>' . $dpto->nome . '</a><br/>';
                    }
                    print '</td><td align="center">';
                    foreach ($this->UFLA->cursos as $curso) {
                        print '<a href=' . $this->link_curso($curso->codigo) . '>' . $curso->nome . '</a><br/>';
                    }
                    print '</td><td align="center">';
                    foreach ($this->UFLA->pavilhoes as $pavilhao) {
                        print '<a href=' . $this->link_pavilhao($pavilhao->nome) . '>' . $pavilhao->nome . '</a><br/>';
                    }
                    print '</td></tr></table>';

                    print '<br/><br/>
                   <table border="1" align="center">
                   <tr><td align="center"><b>Relatórios</b></td></tr>
                   <tr><td align="center">
                     <a href="http://localhost/timetable/view.php?secao=relatorio&id=horario">Choques de Horário</a><br/>
                     <a href="http://localhost/timetable/view.php?secao=relatorio&id=espaco">Choques de Espaço</a><br/>
                     <a href="http://localhost/timetable/view.php?secao=relatorio&id=distribuicao">Distribuição de Aulas</a><br/>
                     <a href="http://localhost/timetable/view.php?secao=relatorio&id=adequacao">Adequação de Aulas</a>
                   </td></tr></table>';
                }

                function mostrar_departamento() {
                    $dpto = tools::buscar_Dpto_por_Nome($this->UFLA->departamentos, $this->id);

                    print '<h1 align=\'center\'>' . $dpto->nome . '</h1>';
                    print '<h2 align=\'center\'>Disciplinas Encarregadas: ';
                    foreach ($dpto->cod_disc as $cod) {
                        print $cod . ' ';
                    }
                    print '</h2>';

                    print 'DISCIPLINAS (' . (count($dpto->disciplinas)) . ')';
                    print '<table border=\'1\'>';
                    print '<tr>
                          <td align=\'center\'>NOME</td>
                          <td align=\'center\'>CODIGO</td>
                       </tr>';
                    foreach ($dpto->disciplinas as $disc) {
                        print '<tr>
                         <td><a href=' . $this->link_disc($disc->codigo) . '>' . $disc->nome . '</a></td>
                         <td>' . $disc->codigo . '</td>
                       </tr>';
                    }
                    print '</table><br/>';
                }

                function mostrar_disciplina() {
                    $disciplina = tools::buscar_Disciplina($this->id, $this->UFLA->departamentos);
                    $dpto = tools::buscar_Dpto_por_Disc($this->id, $this->UFLA->departamentos);

                    print '<h1 align=\'center\'>DISCIPLINA</h1>';
                    print '<b>Nome: </b>' . $disciplina->nome . '<br/>';
                    print '<b>Código: </b>' . $disciplina->codigo . '<br/>';
                    print '<b>Departamento Responsável: </b><a href=' . $this->link_dpto($dpto->nome) . '>' . $dpto->nome . '</a><br/>';

                    print '<table border =\'1\'><tr><td align=\'center\' colspan=\'3\'><b>Ofertas</b></td></tr>
                                        <tr>
                                         <td><b>Turma</b></td>
                                         <td><b>Teorica/Pratica</b></td>
                                         <td><b>Vagas</b></td>';
                    foreach ($disciplina->ofertas as $oferta) {
                        print '<tr>
                         <td><a href=' . $this->link_oferta($oferta) . '>' . (implode(' ', $oferta->turmas)) . '</a></td>
                         <td>' . ($oferta->pratica ? 'Pratica' : 'Teorica') . '</td>
                         <td>' . $oferta->vagas . '</td>
                       </tr>';
                    }
                    print '</table>';
                }

                function mostrar_curso() {
                    $cod = $this->id;
                    $curso = tools::buscar_Curso_por_Cod($this->UFLA->cursos, $cod);
                    print '<h1 align=\'center\'>' . $curso->nome . '</h1>';
                    print '<div align=\'center\'>' . $curso->codigo . '<br/></div>';
                    print '<b>Departamento Responsável:</b>
                     <a href=' . $this->link_dpto($curso->dpto_vinculado) . '>' . $curso->dpto_vinculado . '</a><br/>';

                    print '<table border =\'1\'><tr>
                                 <td><b>Periodo</b></td>
                                 <td><b>Matrizes Curriculares</b></td>
                                 <td><b>Horários Alunos</b></td></tr>';
                    $matrizes = $curso->matrizes;
                    foreach ($matrizes as $periodo => $matriz) {
                        print '<tr>
                    <td><i>' . $periodo . 'º periodo: </i></td>
                    <td><a href=' . $this->link_matriz($matriz) . '>' . $matriz->nome . '</a></td>
                    <td><a href=' . $this->link_horario($curso->codigo, $periodo) . '>Horário</a></td>
                    </tr>';
                    }
                }

                function mostrar_matriz() {
                    $curso_matriz = substr($this->id, 0, 4);
                    $nome_matriz = substr($this->id, 4, 6);
                    $matriz = tools::buscar_Matriz($this->UFLA->cursos, $curso_matriz, $nome_matriz);

                    print '<h1 align=\'center\'>' . $matriz->nome . '</h1>';
                    print '<div align=\'center\'>' . $matriz->curso . '<br/></div>';
                    print '<table border=\'1\'>
                   <tr>
                      <td align=\'center\'><b>Disciplina</b></td>
                      <td align=\'center\'><b>Obrigatória</b></td>
                      <td align=\'center\'><b>Período</b></td>
                      <td align=\'center\'><b>Pré-Requisitos</b></td>
                   </tr>';
                    print count($matriz->entradas) . ' entradas';
                    foreach ($matriz->entradas as $entrada) {
                        $disc = $entrada[0];

                        print '<tr>
                          <td><a href=\'http://localhost/timetable/view.php?secao=disc&id=' . $disc->codigo . '\'>' . $disc->nome . '</a></td>
                          <td>' . ($entrada[1] ? 'S' : 'N') . '</td>
                          <td>' . $entrada[2] . '</td>
                          <td>';

                        if ($entrada[3] != null) {
                            foreach ($entrada[3] as $pre_req) {
                                $disc = tools::buscar_Disciplina($pre_req, $this->UFLA->departamentos);
                                print '<a href=\'http://localhost/timetable/view.php?secao=disc&id=' . $disc->codigo . '\'>' . $disc->codigo . '</a><br/>';
                            }
                        } else {
                            print '<i>(nenhum)</i>';
                        }
                        print '</td></tr>';
                    }
                }

                function mostrar_oferta() {
                    $info_oferta = $this->id;
                    $disc = substr($info_oferta, 0, 6);
                    $pratica = (substr($info_oferta, 6, 1) == 'P' ? true : false);
                    $turma = str_split(substr($info_oferta, 7, strlen($info_oferta)), 3);

                    $oferta = tools::buscar_Oferta($this->UFLA->departamentos, $disc, $turma, $pratica);
                    if ($oferta == null) {
                        print '<h1 align=\'center\'>Oferta não encontrada para estas turmas.</h1>';
                        return;
                    }

                    $turma = $oferta->turmas;
                    $horario = $oferta->horario->grade;

                    print '<div align=\'center\'>';
                    print '<h2><a href=' . $this->link_disc($disc) . '>' . $disc . '</a>
                    - ' . ($pratica ? 'Pratica' : 'Teorica') . ' (' . (implode(' ', $turma)) . ')</h2>';
                    print '<b>HORARIO</b>';
                    print '<table border=\'1\'>
                     <tr>
                        <td> </td>
                        <td align=\'center\'><b>Segunda</b></td>
                        <td align=\'center\'><b>Terca</b></td>
                        <td align=\'center\'><b>Quarta</b></td>
                        <td align=\'center\'><b>Quinta</b></td>
                        <td align=\'center\'><b>Sexta</b></td>
                     </tr>';

                    foreach ($horario['Segunda'] as $nome_hora => $hora) {
                        print '<tr><td><b>' . $nome_hora . '</b></td>';
                        foreach ($horario as $nome_dia => $dia) {
                            $local = $dia[$nome_hora];
                            //print 'Analisando: '.$nome_dia.'('.$nome_hora.')<br/>';
                            print '<td>' . ($local == null ?
                                            '<i>(vazio)</i>' :
                                            '<a href=' . $this->link_espaco($local->nome) . '>' . $local->nome . '</a>') . '</td>';
                        }
                        print '</tr>';
                    }
                    print '</table>';
                    print '</div>';
                }

                function mostrar_horario() {
                    $cod_curso = substr($this->id, 0, 4);
                    $periodo = substr($this->id, 4, 1);

                    $horario = $this->UFLA->obter_Horario_Aluno($cod_curso, $periodo);

                    print '<h1>HORARIO</h1>';
                    print '<b>Curso</b>: ' . $cod_curso . '<br/>
                   <b>Periodo</b>: ' . $periodo . '<br/>';

                    print '<table border=\'1\' bordercolor=green>
                     <tr>
                        <td> </td>
                        <td align=\'center\'><b>Segunda</b></td>
                        <td align=\'center\'><b>Terca</b></td>
                        <td align=\'center\'><b>Quarta</b></td>
                        <td align=\'center\'><b>Quinta</b></td>
                        <td align=\'center\'><b>Sexta</b></td>
                     </tr>';


                    foreach ($horario['Segunda'] as $nome_hora => $hora) {
                        print '<tr><td><b>' . $nome_hora . '</b></td>';
                        foreach ($horario as $nome_dia => $dia) {
                            $slot = $dia[$nome_hora];

                            print '<td>';
                            if ($slot == null) {
                                print '<i>(vazio)</i>';
                            } else {
                                foreach ($slot as $compromisso) {
                                    $oferta = $compromisso[0];
                                    $local = $compromisso[1];
                                    //print 'Analisando: '.$nome_dia.'('.$nome_hora.')<br/>';

                                    print '<a href=' . $this->link_oferta($oferta) . '>'
                                            . $oferta->cod_disc_relacionada . '
                                  (' . ($oferta->pratica ? 'P' : 'T') . ')
                                   - <i>' . implode(' ', $oferta->turmas) .
                                            '</i></a> ';
                                    print '(<a href=' . $this->link_espaco($local->nome) . '>' . $local->nome . '</a>)<br/>';
                                }
                            }
                            print '</td>';
                        }
                        print '</tr>';
                    }
                    print '</table>';
                }

                function mostrar_pavilhao() {
                    $nome_pavilhao = $this->id;
                    foreach ($this->UFLA->pavilhoes as $pavilhao) {
                        if ($pavilhao->nome == $nome_pavilhao) {
                            break;
                        }
                    }
                    print '<div align=\'center\'>';
                    print '<h2>' . $pavilhao->nome . '</h2>';

                    print '<b>Cursos Preferenciais: </b>';
                    if ($pavilhao->cursos_preferenciais == null) {
                        print '<i>(nenhum)</i>';
                    } else {
                        foreach ($pavilhao->cursos_preferenciais as $curso) {
                            print '<a href=' . $this->link_curso($curso->codigo) . '>' . $curso->nome . '</a>&nbsp&nbsp';
                        }
                    }


                    print '<br/><b>Isolado: </b>' . ($pavilhao->isolado ? 'Sim' : 'Não');

                    print '<br/>
                   <table border="1"><tr><td colspan="2" align="center"><b>Salas</b></td></tr>';
                    foreach ($pavilhao->salas as $sala) {
                        print '<tr>
                        <td><a href=' . $this->link_espaco($sala->nome) . '>' . $sala->nome . '</a></td><td>' . $sala->capacidade . '</td>
                       </tr>';
                    }

                    print '</table>
                   </div>';
                }

                function mostrar_local() {
                    $local = $this->id;
                    $sala = tools::buscar_Local($local, $this->UFLA->pavilhoes);
                    print '<div align=\'center\'>';
                    if ($sala != null) {
                        print '<h2>' . $sala->nome . '</h2>';
                        $ocupacao = $sala->obter_Ocupacao();

                        print '<table border=\'1\'>
                     <tr>
                        <td> </td>
                        <td align=\'center\'><b>Segunda</b></td>
                        <td align=\'center\'><b>Terca</b></td>
                        <td align=\'center\'><b>Quarta</b></td>
                        <td align=\'center\'><b>Quinta</b></td>
                        <td align=\'center\'><b>Sexta</b></td>
                     </tr>';


                        foreach ($ocupacao['Segunda'] as $nome_hora => $hora) {
                            print '<tr><td><b>' . $nome_hora . '</b></td>';
                            foreach ($ocupacao as $nome_dia => $dia) {
                                $slot = $dia[$nome_hora];

                                print '<td>';
                                if ($slot == null) {
                                    print '<i>(vazio)</i>';
                                } else {
                                    foreach ($slot as $oferta) {
                                        print '<a href=' . $this->link_oferta($oferta) . '>'
                                                . $oferta->cod_disc_relacionada . '
                                  (' . ($oferta->pratica ? 'P' : 'T') . ')
                                  - <i>' . implode(' ', $oferta->turmas) .
                                                '</i></a><br/>';
                                    }
                                }
                                print '</td>';
                            }
                            print '</tr>';
                        }
                    } else {
                        print '<h2>Sala não encontrada.</h2>';
                    }
                    print '</div>';
                }

                function mostrar_choques_espaco() {
                    $ocorrencias = $this->choques_espaco;
                    $qtd = 0;
                    
                    foreach($ocorrencias as $ocupacoes){
                        foreach($ocupacoes as $horas){
                            foreach($horas as $oferas){
                                $qtd += (count($ocorrencias)>1 ? count($ocorrencias) : 0);
                            }
                        }
                    }

                    //print_r($ocorrencias);
                    print '<h2>Choques de Espaço</h2>';
                    print 'Foram detectados <b>' . $qtd . '</b> choques de salas de aula.<br/><br/>';

                    foreach ($ocorrencias as $local => $ocupacoes) {
                        print '<b><a href=\'http://localhost/timetable/view.php?secao=local&id=' . $local . '\'>' . $local . '</a></b><br/>';
                        print '<table border=\'1\'>';
                        foreach ($ocupacoes as $dia => $horas) {
                            print '<tr>
                              <td>' . $dia . '</td>
                              <td>';
                            foreach ($horas as $hora => $ofertas) {
                                print '<b>' . $hora . ': </b>';
                                foreach ($ofertas as $oferta) {
                                    $turmas = $oferta->turmas;
                                    $target_URL = $oferta->cod_disc_relacionada . ($oferta->pratica ? 'P' : 'T') . implode('', $turmas);

                                    print '<a href=\'http://localhost/timetable/view.php?secao=oferta&id=' . $target_URL . '\'>';
                                    print $oferta->cod_disc_relacionada . ' - ' . ($oferta->pratica ? 'P' : 'T') . ' (' . implode(' ', $turmas) . ')';
                                    print '</a>&nbsp&nbsp&nbsp&nbsp';
                                }
                                print '<br/>';
                            }
                            print '</td></tr>';
                        }
                        print '</table><br/>';
                    }
                }

                function mostrar_choques_horario() {
                    $ocorrencias = $this->choques_horario;  //[Curso][Periodo][Dia][Hora] = array of {oferta.php}
                    
                    $qtd = 0;
                    foreach($ocorrencias as $curso=>$periodos){
                        foreach($periodos as $periodo=>$dias){
                            foreach($dias as $dia=>$horas){
                                foreach($horas as $hora=>$ocorrencias){
                                    $qtd += (count($ocorrencias)>1 ? count($ocorrencias) : 0);
                                }
                            }
                        }
                    }
                    
                    $ocorrencias = $this->choques_horario;  //@todo PQ???
                    //print_r($ocorrencias);
                    print '<h2>Choques de Horário</h2>';
                    print 'Foram detectados <b>' . $qtd . '</b> choques de horário.<br/><br/>';
                    foreach ($ocorrencias as $curso => $periodos) {
                        print '<h3>Curso: ' . $curso . '</h3>';
                        print '<table border=\'1\' align=\'center\'>';
                        foreach ($periodos as $periodo => $dias) {
                            print '<tr><td colspan=\'2\' align=\'center\'>
                             <a href=\'http://localhost/timetable/view.php?secao=horario&id=' . $curso . $periodo . (substr($curso, 2, 2)) . 'A\'>
                               <b>' . $periodo . 'º Período</b>
                             </a>
                           </td></tr>';
                            foreach ($dias as $dia => $horas) {
                                print '<tr><td valign=\'center\'>' . $dia . '</td>
                               <td>';
                                foreach ($horas as $hora => $ofertas) {
                                    print '<b>' . $hora . '</b>: ';
                                    foreach ($ofertas as $oferta) {
                                        print '<a href=' . $this->link_oferta($oferta) . '>'
                                                . $oferta->cod_disc_relacionada . ' (' . ($oferta->pratica ? 'P' : 'T') . ')' .
                                                '</a>&nbsp&nbsp';
                                    }
                                    print '<br/>';
                                }
                                print '</td></tr>';
                            }
                        }
                        print '</table>';
                    }
                }

                function mostrar_distribuicao() {
                    $info = $this->espacamento;
                    print '<h2>Distribuição de Horário</h2>';
                    foreach ($info as $cod_curso => $periodos) {
                        print '<h3><a href=\'' . $this->link_curso($cod_curso) . '\'>' . $cod_curso . '</a></h3>';

                        print '<table align="center"> <tr>';
                        foreach ($periodos as $periodo => $distribuicao) {
                            print '<td  valign="top">
                        <table border=1><tr><td colspan=3 align="center">
                            <b><a href=' . $this->link_horario($cod_curso, $periodo) . '>' . $periodo . 'º período</a></b>
                          </td></tr>';

                            //analise de espacamentos

                            print '<tr><td>Espacamentos</td><td>***Janelas***</td><td>Alternancia</td></tr>
                           <tr><td>';
                            $espacamentos = $this->espacamento;
                            if($espacamentos[$cod_curso][$periodo] != null){
                                foreach ($espacamentos[$cod_curso][$periodo] as $oferta) {
                                    print '<a href=' . $this->link_oferta($oferta) . '>'
                                            . $oferta->cod_disc_relacionada . ' (' . ($oferta->pratica ? 'P' : 'T') . ')' .
                                            '</a><br/>';
                                }
                            }
                            else{
                                print '<i>(nenhum)</i>';
                            }
                            print '</td><td>';

                            //analise de janelas
                            $janelas = $this->janelas;
                            foreach ($janelas[$cod_curso][$periodo] as $oferta) {
                                print '<a href=' . $this->link_oferta($oferta) . '>'
                                        . $oferta->cod_disc_relacionada . ' (' . ($oferta->pratica ? 'P' : 'T') . ')' .
                                        '</a><br/>';
                            }
                            print '</td><td>';

                            //analise de alternancia
                            $alternancias = $this->alternancia;
                            foreach ($alternancias[$cod_curso][$periodo] as $oferta) {
                                print '<a href=' . $this->link_oferta($oferta) . '>'
                                        . $oferta->cod_disc_relacionada . ' (' . ($oferta->pratica ? 'P' : 'T') . ')' .
                                        '</a><br/>';
                            }
                            print '</tr></table></td>';
                        }
                        print '</tr></table><br/>';
                    }
                }

                function mostrar_adequamento() {
                    print '<h2>Distribuição de Horário</h2>';

                    print '<table border="1"><tr>
                    <td></td>
                    <td align="center"><b>Laboratório</b></td>
                    <td align="center"><b>Turno</b></td>
                    <td align="center"><b>Isolamento</b></td>
                    <td align="center"><b>Proximidade</b></td>
                    <td align="center"><b>Exclusividade</b></td>
                    <td align="center"><b>Capacidade</b></td>
                   </tr>';
                    
                    
                    foreach ($info as $nome_dpto => $fatores) {
                        print '<tr><td align="center"><b>' . $nome_dpto . '</b></td>';
                        
                        if(array_key_exists($nome_dpto, $this->lab)) {
                            print '<td align="center">';
                            if ($ocorrencias == null) {
                                print '</td>';
                            }

                            foreach ($ocorrencias as $ocorrencia) {
                                $oferta = $ocorrencia[0];
                                $dia = substr($ocorrencia[1], 0, 3);
                                $hora = $ocorrencia[2];
                                print '<i>(' . $dia . ' ' . $hora . ')</i>
                              <a href=' . $this->link_oferta($oferta) . '>'
                                        . $oferta->cod_disc_relacionada . ' (' . ($oferta->pratica ? 'P' : 'T') . ')' .
                                        '</a><br/>';
                            }
                            print '</td>';
                        }
                        print '</tr>';
                    }
                }

                function debugging($UFLA) {

                    $it = 0;
                    $info = $this->UFLA->choques_Espaco();
                    $qtd_ant = $info[0];
                    print 'Iteração ' . $it . ': ' . $qtd_ant . ' choque(s) de salas de aula<br/>';
                    do {
                        $it++;
                        $resultado = $this->UFLA->avaliar();
                        $qtd = $resultado[0];
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
                        
                        if ($qtd < $qtd_ant)
                            print 'Iteração ' . $it . ': ' . $qtd . ' choque(s) de salas de aula<br/>';
                        $qtd_ant = $qtd;
                    }while ($qtd > 10);
                }

                function link_pavilhao($nome) {
                    $versao = (isset($_GET["versao"]) ? ($_GET["versao"]) : null);
                    $link = "http://localhost/timetable/view.php?versao=" . $versao . "&";
                    return '\'' . $link . 'secao=pavilhao&id=' . $nome . '\'';
                }

                function link_espaco($nome) {
                    $versao = (isset($_GET["versao"]) ? ($_GET["versao"]) : null);
                    $link = "http://localhost/timetable/view.php?versao=" . $versao . "&";
                    return '\'' . $link . 'secao=local&id=' . $nome . '\'';
                }

                function link_oferta($oferta) {
                    $versao = (isset($_GET["versao"]) ? ($_GET["versao"]) : null);
                    $link = "http://localhost/timetable/view.php?versao=" . $versao . "&";
                    $target_URL = $oferta->cod_disc_relacionada . ($oferta->pratica ? 'P' : 'T') . implode('', $oferta->turmas);
                    return '\'' . $link . 'secao=oferta&id=' . $target_URL . '\'';
                }

                function link_disc($cod) {
                    $versao = (isset($_GET["versao"]) ? ($_GET["versao"]) : null);
                    $link = "http://localhost/timetable/view.php?versao=" . $versao . "&";
                    return '\'' . $link . 'secao=disc&id=' . $cod . '\'';
                }

                function link_dpto($cod) {
                    $versao = (isset($_GET["versao"]) ? ($_GET["versao"]) : null);
                    $link = "http://localhost/timetable/view.php?versao=" . $versao . "&";
                    return '\'' . $link . 'secao=dpto&id=' . $cod . '\'';
                }

                function link_matriz($matriz) {
                    $versao = (isset($_GET["versao"]) ? ($_GET["versao"]) : null);
                    $link = "http://localhost/timetable/view.php?versao=" . $versao . "&";
                    return '\'' . $link . 'secao=matriz&id=' . $matriz->curso . '' . $matriz->nome . '\'';
                }

                function link_horario($cod_curso, $periodo) {
                    $versao = (isset($_GET["versao"]) ? ($_GET["versao"]) : null);
                    $link = "http://localhost/timetable/view.php?versao=" . $versao . "&";
                    return '\'' . $link . 'secao=horario&id=' . $cod_curso . $periodo . '\'';
                }

                function link_curso($cod) {
                    $versao = (isset($_GET["versao"]) ? ($_GET["versao"]) : null);
                    $link = "http://localhost/timetable/view.php?versao=" . $versao . "&";
                    return '\'' . $link . 'secao=curso&id=' . $cod . '\'';
                }

            }
            ?>
        </div></body>
</html>
