<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body><div align="center">
        <?php
        session_start();
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');
        ini_set('display_errors',1);

        include 'estrutura\matriz_curricular.php';
        include 'estrutura\data.php';
        include 'estrutura\sistema.php';
        include 'estrutura\disciplina.php';
        include 'estrutura\departamento.php';
        include 'estrutura\pavilhao.php';
        include 'estrutura\sala.php';
        include 'estrutura\tools.php';
        include 'clonalg\clonalg.php';
        include 'vars.php';

        $timetable = new clonalg();
        $timetable->executar();
        $_SESSION['ufla'] = serialize($timetable->obter_Melhor()->UFLA);
        $_SESSION['ufla2'] = serialize($timetable->obter_Pior()->UFLA);

        print '<h2><a href=\'http://localhost/timetable/view.php?versao=ufla\'>INICIAR!</a></h2>';


        ?>
        </div></body>
</html>
