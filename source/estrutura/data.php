<?php
/**
 * Classe estatica que efetua a manipulação de dados externos e cria cada modulo da estrutura
 * @author joaodaher
 */
include 'curso.php';
include 'oferta.php';

class data {


    public static function criar_Dptos(){
        $departamentos = array();
        foreach(vars::getDepartments() as $nome=>$caminho){
            //print 'Verificando Departamento '.$nome.' ('.$caminho.')<br/>';
            if(@file($caminho)){
                //print 'Encontrado! Criando disciplinas...<br/>';
                $disciplinas = self::criar_Disciplinas_Dpto($caminho);
                $dpto = new departamento($nome, $disciplinas);
                $departamentos[] = $dpto;
            }
        }
        return $departamentos;
    }

    /*
     * Formato CVS
     * {nome},{codigo},{ch_t},{ch_p},{turma},{vagas},[OPCIONAL: {tam_aula}, {repetente}]
     */
    private static function criar_Disciplinas_Dpto($fileName){
        $fd = fopen ($fileName, "r");
        $lines = array();
        while (!feof ($fd)){
            $buffer = fgets($fd, 4096);
            $lines[] = $buffer;
        }
        fclose ($fd);

        $disciplinas = array();
        for($i=0; $i<count($lines); $i++){
            $conteudo = explode(";", $lines[$i]);

            $nome = $conteudo[0];
            $codigo = $conteudo[1];
            $ch_t = (int)$conteudo[2];
            $ch_p = (int)$conteudo[3];
            $turma = explode(" ", $conteudo[4]);
            $vagas = (int)$conteudo[5];



            //------------REMOVER PARA DAR LIBERDADE-inicio-------------------------
            
            $irrelevante = true;
            foreach(array('10A','10B','10C','14A','14B','14C') as $turma_para_mater){
                if(tools::search_array($turma_para_mater, $turma)){
                    $irrelevante = false;
                }
            }
            if($irrelevante){
                //print 'turma irrelevante<br/>';
                continue;
            }
            
            //------------REMOVER PARA DAR LIBERDADE-fim----------------------------

            //analisar o 7o parametro: tamanho maximo de aula
            if(count($conteudo) == 7){
                $tam_aula = (int)$conteudo[6];
            }
            else{
                $creditos = ($ch_t+$ch_p)/7;
                $tam_aula = ($creditos==3 ? 3 : 2);
            }
            
            //print 'Analisando a disciplina '.$codigo.'<br/>';
            $indice = self::procurar_Disciplina($disciplinas, $codigo);//verifica se a disciplina ja foi criada

            if($indice == null){ //se a disciplina nao foi criada
                //print 'NOVA! Foi criada!<br/>';
                $disciplina = new disciplina($nome, $codigo, $ch_t, $ch_p);
                $disciplinas[] = $disciplina;
            }
            else{
                $disciplina = $disciplinas[$indice];
            }

            if($ch_p != 0){
                //print 'Criando oferta pratica ('.  implode(' ', $turma).')...<br/>';
                $disciplina->criar_Oferta($turma, $vagas, $ch_p, true, $tam_aula); //criando variacao PRATICA da oferta
            }

            if($ch_t != 0){
                //print 'Criando oferta teorica ('.  implode(' ', $turma).')...<br/>';
                $disciplina->criar_Oferta($turma, $vagas, $ch_t, false, $tam_aula); //criando variacao TEORICA da oferta
            }

            //print '<b>'.count($disciplinas).' disciplinas ate agora...</b><br/>';
        }
        return $disciplinas;
    }

    //retorna o INDICE da disciplina
    private static function procurar_Disciplina($vet_disc, $codigo){
        foreach($vet_disc as $i=>$disc){
            if($disc->codigo == $codigo){
                return $i;
            }
        }
        return null;
    }


    public static function criar_Cursos($departamentos){
        $cursos = array();

        $cursos_info = vars::getCourses();
        foreach ($cursos_info as $cod => $info) { //para cada curso...
            $nome = $info[0];
            $dpto = $info[1];
            $noturno = $info[2];
            //print 'Criando o curso de '.$nome.' ('.$cod.') ligado ao '.$dpto.'<br/>';
            $curso = new curso($nome, $cod, $dpto, $noturno); //...criar o curso...

            foreach($info[3] as $periodo=>$nome_matriz){ //...e pra cada matriz do curso...
                $caminho = 'data\\matriz\\'.$cod.$nome_matriz.'.cvs';
                //print 'Preparando pra buscar a matriz em '.$caminho.'<br/>';
                if(@file($caminho)){
                    $matriz = self::criar_Matriz($caminho, $departamentos, $periodo); //...criar a matriz!
                    $curso->adicionar_Periodo($periodo, $matriz);
                }
            }
            $cursos[] = $curso;
        }

        return $cursos;
    }

    /*
     * Formato CVS
     * {nome},{codigo},{obrigatoria?},{pre_req1 pre-req2},{periodo},{ch_t},{ch_p}
     */
    private static function criar_Matriz($fileName, $departamentos, $periodo_alvo){
        //print '<br/><b>Analisando '.$fileName.' ('.$periodo_alvo.'o periodo)</b><br/>';
        $fd = fopen ($fileName, "r");
        $lines = array();
        while (!feof ($fd)){
            $buffer = fgets($fd, 4096);
            $lines[] = $buffer;
        }
        fclose ($fd);

        //print 'Criando uma matriz...<br/>';
        $conteudo = array();
        for($i=0; $i<count($lines); $i++){
            $conteudo = explode(";", $lines[$i]);

            //$nome = $conteudo[0]; //irrelevante, a busca da disciplina eh feita pelo codigo
            $codigo = $conteudo[1];
            $obrigatoria = ($conteudo[2]=='s'?true:false);
            $pre_requisitos = ($conteudo[3]=="" ? null : explode(" ", $conteudo[3]));
            $periodo = (int)$conteudo[4];
            //$ch_t = (int)$conteudo[5]; //irrelevante, as CH sao controladas pela proprias disciplinas/ofertas
            //$ch_p = (int)$conteudo[6];
            //print '#'.($i+1).' ADICIONANDO: '.$nome.'('.$codigo.')<br/>';

            if($periodo == $periodo_alvo){
                $disciplina = tools::buscar_Disciplina($codigo, $departamentos);
                //print 'Buscada a disciplina '.$disciplina->codigo.'<br/>';
                $entradas[] = array($disciplina, $obrigatoria, $periodo, $pre_requisitos);
            }
        }

        $path = explode("\\", $fileName);
        $fileName = $path[count($path)-1];
        $cod_curso = substr($fileName, 0, 4);
        $nome = substr($fileName, 4, 6);
        return new matriz_curricular($nome, $cod_curso, $entradas);
    }



    /*
     * Formato CVS
     * {nome},{laboratorio?},{capacidade},{cursos_pref1 cursos_pref2},{disciplinas_exclusivas},{isolado?}
     */
    public static function criar_Espacos($cursos){
        $fileName = vars::getRoomPath();
        $fd = fopen ($fileName, "r");
        while (!feof ($fd)){
            $buffer = fgets($fd, 4096);
            $lines[] = $buffer;
        }
        fclose ($fd);

        $espacos = array();
        foreach($lines as $line){
            $conteudo = explode(",", $line);

            $nome_sala = $conteudo[0];
            $nome_pavilhao = self::nome_Pavilhao($nome_sala);
            $laboratorio = ($conteudo[1]=='s'?true:false);
            $isolado = ($conteudo[5]=='s'?true:false);
            $capacidade = $conteudo[2];

            if($conteudo[3] == ''){
                $cursos_preferenciais = null;
            }
            else{
                $cod_cursos = explode(' ', $conteudo[3]);
                foreach($cod_cursos as $cod_curso){
                    //print 'Acrescentando curso preferencial...<br/>';
                    $cursos_preferenciais[] = tools::buscar_Curso_por_Cod($cursos, $cod_curso);
                }

            }


            $disciplinas_exclusivas = explode(" ", $conteudo[4]);

            $pavilhao = self::procurar_Pavilhao($espacos, $nome_pavilhao);
            if($pavilhao == null){
                $pavilhao = new pavilhao($nome_pavilhao, $cursos_preferenciais, $isolado);
                $espacos[] = $pavilhao;
            }
            $pavilhao->adicionar_Sala($nome_sala, $laboratorio, $capacidade, $disciplinas_exclusivas);

        }
        //print_r($espacos);
        return $espacos;
    }

    private static function procurar_Pavilhao($pavilhoes, $nome_pavilhao){
        foreach($pavilhoes as $pavilhao){
            if($pavilhao->nome == $nome_pavilhao) return $pavilhao;
        }
        return null;
    }

    private static function nome_Pavilhao($nome_sala){
        if(substr($nome_sala, 0, 2) == "PV"){
            $nome = "Pavilhão ".substr($nome_sala, 2, 1);
        }
        else if(substr($nome_sala, 0, 1) == "D"){
            $nome = "Salas do ".substr($nome_sala, 0, 3);
        }
        return $nome;
    }

    public static function definir_Turnos_Ofertas($dptos, $cursos){
        foreach($dptos as $dpto){
            foreach($dpto->disciplinas as $disc){
                foreach($disc->ofertas as $oferta){
                    $oferta->definir_turno(tools::verificar_Oferta_Noturna($cursos, $dptos, $oferta));
                }
            }
        }
    }

}

?>
