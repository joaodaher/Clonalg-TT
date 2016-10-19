<?php
/**
 * Description of vars
 *
 * @author João Daher
 */
class vars {
    static private $xml_path = "var.xml";
    /**
     * Retorna informacoes basicas de todos os cursos
     * @return [array of 'course info']: array['CODIGO'] = ['NOME', 'DPTO', 'NOTURNO', array['PERIODO']='NOME MATRIZ']
     * ex: array['G010']=['CIENCIA DA COMPUTACAO', 'DCC', false]
     */
    static function getCourses() {
        $var = simplexml_load_file(self::$xml_path);
        $courses = array();
        foreach($var->courses as $course){
            $info = array();
            foreach($course->grid as $matriz){
                $semestre = (int)$matriz['semester'];
                $nome_matriz = (string)$matriz['name'];

                $info[$semestre] = $nome_matriz;
            }
            $cod_curso = (string)$course['code'];
            $nome_curso = (string)$course['name'];
            $dpto = (string)$course['dpt'];
            $noturno = ((string)$course['shift']=='daytime'?false:true);

            $courses[$cod_curso] = array($nome_curso, $dpto, $noturno, $info);
        }
        return $courses;
    }

    static function getDepartments(){
        $var = simplexml_load_file(self::$xml_path);
        $dptos = array();
        foreach($var->departments as $dpto){
            $nome = (string)$dpto['name'];
            $caminho = (string)$dpto['offer_path'];
            //print 'Criando departamento '.$nome.' ('.$caminho.')<br/>';
            $dptos[$nome] = $caminho;
        }
        return $dptos;
    }

    static function getDayLenght(){
        $var = simplexml_load_file(self::$xml_path);
        return (int)$var->time->day_lenght;
    }

    static function getWeekLenght(){
        $var = simplexml_load_file(self::$xml_path);
        return (int)$var->time->week_lenght;
    }

    static function getSemesterLenght(){
        $var = simplexml_load_file(self::$xml_path);
        return (int)$var->time->semester_lenght;
    }

    static function getEveningTime(){
        $var = simplexml_load_file(self::$xml_path);
        return (string)$var->time->evening;
    }

    static function getNightTime(){
        $var = simplexml_load_file(self::$xml_path);
        return (string)$var->time->night;
    }

    static function getMorningTime(){
        $var = simplexml_load_file(self::$xml_path);
        return (string)$var->time->morning;
    }

    static function getLunchTime(){
        $var = simplexml_load_file(self::$xml_path);
        return (string)$var->time->lunch_time;
    }

    static function getSnackTime(){
        $var = simplexml_load_file(self::$xml_path);
        return (string)$var->time->snack_time;
    }

    static function getAfternoonTime(){
        $var = simplexml_load_file(self::$xml_path);
        return (string)$var->time->afternoon;
    }

    static function getStokingGap(){
        $var = simplexml_load_file(self::$xml_path);
        return (float)$var->evaluation->stoking_gap;
    }

    static function getPntChqHorario(){
        $var = simplexml_load_file(self::$xml_path);
        return (float)$var->evaluation->chq_horario;
    }
    
    static function getPntChqLocal(){
        $var = simplexml_load_file(self::$xml_path);
        return (float)$var->evaluation->chq_local;
    }
    
    static function getPntEspacamento(){
        $var = simplexml_load_file(self::$xml_path);
        return (float)$var->evaluation->espacamento;
    }

    static function getPntJanelas(){
        $var = simplexml_load_file(self::$xml_path);
        return (float)$var->evaluation->janelas;
    }

    static function getPntAlternancia(){
        $var = simplexml_load_file(self::$xml_path);
        return (float)$var->evaluation->alternancia;
    }

    static function getPntLab(){
        $var = simplexml_load_file(self::$xml_path);
        return (float)$var->evaluation->lab;
    }

    static function getPntTurno(){
        $var = simplexml_load_file(self::$xml_path);
        return (float)$var->evaluation->turno;
    }

    static function getPntIsolamento(){
        $var = simplexml_load_file(self::$xml_path);
        return (float)$var->evaluation->isolamento;
    }

    static function getPntProximidade(){
        $var = simplexml_load_file(self::$xml_path);
        return (float)$var->evaluation->proximidade;
    }

    static function getPntExclusividade(){
        $var = simplexml_load_file(self::$xml_path);
        return (float)$var->evaluation->exclusividade;
    }

    static function getPntCapacidade(){
        $var = simplexml_load_file(self::$xml_path);
        return (float)$var->evaluation->capacidade;
    }

    static function getRoomPath(){
        $var = simplexml_load_file(self::$xml_path);
        return (string)$var->rooms['path'];
    }

    static function getGenericGrid(){
        $var = simplexml_load_file(self::$xml_path);
        $days_of_the_week = $var->time->days_of_the_week;
        $time_of_the_day = $var->time->time_of_the_day;

        foreach($time_of_the_day as $hour){
            $full_day[(string)$hour] = null;
        }
        foreach($days_of_the_week as $day){
            $grid[(string)$day] = $full_day;
        }

        return $grid;
    }


    //CLONALG
    static function getPopSize(){
        $var = simplexml_load_file(self::$xml_path);
        return (int)$var->clonalg->pop_size;
    }

    static function getCloningAmt(){
        $var = simplexml_load_file(self::$xml_path);
        return (float)$var->clonalg->cloning_amt;
    }

    static function getCloningTax(){
        $var = simplexml_load_file(self::$xml_path);
        return (float)$var->clonalg->cloning_tax;
    }

    static function getMutationTax(){
        $var = simplexml_load_file(self::$xml_path);
        return (float)$var->clonalg->mutation_tax;
    }

    static function getRanking(){
        $var = simplexml_load_file(self::$xml_path);
        return (int)$var->clonalg->ranking;
    }

    static function getMaxGenerations(){
        $var = simplexml_load_file(self::$xml_path);
        return (int)$var->clonalg->max_generations;
    }

    static function getDiversificationAmt(){
        $var = simplexml_load_file(self::$xml_path);
        return (int)$var->clonalg->diversification_amt;
    }
    
    static function getMaxTime(){
        $var = simplexml_load_file(self::$xml_path);
        return (int)$var->clonalg->max_time;
    }
}

?>
