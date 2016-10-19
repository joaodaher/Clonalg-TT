<?php
/**
 * Classe estatica que realiza a ordenacao de vetores
 * @author João Daher
 */
class arraySort {
    public static function sort(&$array) {
        usort($array, function ($a, $b){
                            if ($a->fitness == $b->fitness) {
                                return 0;
                            }
                            return ($a->fitness > $b->fitness) ? -1 : 1;
                      }
        );
    }
}

?>
