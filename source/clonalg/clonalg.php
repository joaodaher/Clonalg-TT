<?php

/**
 * Sistema Imune baseado no CLONALG
 * @author João Daher
 */

include 'clonalg\arraySort.php';
include 'clonalg\celula.php';

class clonalg {
    private $historico;
    private $top3;
    
    private $max_geracoes;
    private $max_time;
    private $tam_pop;
    private $max_clones;
    private $taxa_clonagem; //beta
    private $taxa_mutacao; //p : fator multiplicativo para definir intensidade da mutacao (MAIOR: mutacao maxima ; MENOR: mutacao minima)
    private $ranking; //'n' melhores selecionados
    private $diversification_amt;
    public $pop = array(); //vetor de cell.php

    /**
     * Cria um novo sistema imune, baseado nas configuracoes de var.xml
     * @param boolean $carregar Se a populacao de celulas sera carregada de arquivos
     */
    function __construct($carregar=false) {
        $this->tam_pop = vars::getPopSize();
        $this->max_clones = vars::getCloningAmt();
        $this->taxa_clonagem = vars::getCloningTax();
        $this->taxa_mutacao = vars::getMutationTax();
        $this->ranking = vars::getRanking();
        $this->diversification_amt = vars::getDiversificationAmt();

        $this->max_geracoes = vars::getMaxGenerations();
        $this->max_time = vars::getMaxTime();
        
        $t0 = time();
        //gera populacao inicial
        print "****Fase de criacao****";
        if(!$carregar){
            for ($i = 0; $i < $this->tam_pop; $i++) {
                print '.';
                $this->pop[$i] = new celula();
            }
        }
        else{
            $this->carregar_populacao();
        }
        arraySort::sort($this->pop);
        $t1 = time();
        print " ->".number_format(($t1-$t0),6)."s\n";
    }
    
    /**
     * Inicia o processo evolutivo do sistema imune
     */
    public function executar() {
        $t1 = time();
        //Para cada geracao...
        $gen = 0;
        do{
            $gen++;
            $this->evoluir_Geracao();
            $this->update_Info($gen);

            $melhor_celula = $this->obter_Melhor();
            $pior_celula = $this->obter_Pior();
            $fitness_Medio = $this->fitness_Medio();

            print "\n\n=======GERACAO ".$gen."=======\n";
            print "FITNESS MAXIMO = ".$melhor_celula->fitness." (".($melhor_celula->fitness-$fitness_Medio)*100/$fitness_Medio."%) : ".$melhor_celula->id."\n";
            print "FITNESS MINIMO = ".$pior_celula->fitness." (".($pior_celula->fitness-$fitness_Medio)*100/$fitness_Medio."%) : ".$pior_celula->id."\n";
            print "FITNESS MEDIO = ".$fitness_Medio."\n";
            print "ERROS = ".$melhor_celula->erros_graves."\n";
            print "==============================\n\n";
            
            
        }while(!$this->criterio_Parada($melhor_celula, $gen, (time()-$t1)));
        $this->write_Info();

    }

    /**
     * Informa a celula com maior fitness
     * @return celula.php Celula de maior fitness 
     */
    public function obter_Melhor(){
       return $this->pop[0];
    }

    /**
     * Informa a celula com menor fitness
     * @return celula.php Celula de menor fitness 
     */
    public function obter_Pior(){
       return $this->pop[$this->tam_pop-1];
    }

    /**
     * Calcula a meida dos fitness de todas as celulas do sistema
     * @return float Fitness Medio 
     */
    private function fitness_Medio(){
        $fitness = 0;
        foreach($this->pop as $celula){
            $fitness += $celula->fitness;
        }
        return (float)$fitness/(count($this->pop));
    }

    /**
     * Verifica se o sistema atingiu algum criterio de parada
     * @param celula.php $celula Melhor celula do sistema
     * @param int $gen Geracao atual em que o sistema se encontra
     * @param time $time Tempo de execucao do algoritmo
     * @return boolean TRUE, se algum criterio de parada foi atingido 
     */
    private function criterio_Parada($celula, $gen, $time){
        $this->max_geracoes = vars::getMaxGenerations();
        print "Executado ".$time."s de ".$this->max_time."s\n\n";
        return ($celula->erros_graves == 0 || $gen >= $this->max_geracoes || $time >= $this->max_time);
    }

    /**
     * Evoluir a populacao uma unica geracao, passando pelas fases:
     * - Selecao
     * - Clonagem
     * - Mutacao
     * - Selecao / Inclusao
     * - Controle Populacional
     * - Diversificacao
     */
    private function evoluir_Geracao(){
        $t1 = time();

        //Selecionar: separar os “Melhores”
        print("****Fase de selecao de melhores****");
        $escolhidos = $this->selecionar_Celulas($this->pop, $this->ranking);
        $t2 = time();
        print " ->".number_format(($t2-$t1),6)."s\n";
        
        //Clonagem: fazer copias dos melhores
        print("****Fase de clonagem****");
        $clones = $this->clonar_Celulas($escolhidos);
        $t3 = time();
        print " ->".number_format(($t3-$t2),6)."s\n";
        $this->mostrar_fitness($clones);

        //Hipermutar: mutar os clones
        print("****Fase de mutacao de clones****");
        $clones = $this->mutar_Celulas($clones);
        $t4 = time();
        print " ->".number_format(($t4-$t3),6)."s\n";
        $this->mostrar_fitness($clones);

         //Selecionar: separar os “Melhores Clones”
        print("****Fase de selecao de melhores clones****");
        $escolhidos = $this->selecionar_Celulas($clones, $this->ranking);
        $t5 = time();
        print " ->".number_format(($t5-$t4),6)."s\n";

        //Inserir: acrescentar os clones na populacao
        print("****Fase de inclusao de clones****");
        $this->incluir_na_Populacao($clones);
        $t7 = time();
        print " ->".number_format(($t7-$t5),6)."s\n";

        //Gerar: criar novos individuos aleatoriamente (evitar maximo local)
        //Substituir: matar os ''Piores” anticorpos
        print("****Fase de matanca****");
        $this->matar_Excesso_Celulas();
        $t8 = time();
        print " ->".number_format(($t8-$t7),6)."s\n";

        
        //Inserir: acrescentar os clones na populacao
        print("****Fase de diversificacao****");
        $this->diversificar_Celulas();
        $t9 = time();
        print " ->".number_format(($t9-$t8),6)."s\n";
        
        //$this->mostrar_fitness($this->pop);
        print "\nTEMPO TOTAL: ".($t9-$t1)."s";
    }

    /**
     * Seleciona N melhores celulas dado um conjunto de celulas ordenadas por fitness
     * @param array of celula.php $conjunto Vetor de celulas ordenadas por fitness
     * @param int $n_melhores Quantidade de N melhores
     * @return array of celula.php Celulas selecionadas 
     */
    private function selecionar_Celulas($conjunto, $n_melhores) {
        print "\nSelecionando ".$n_melhores." melhores dentre ".count($conjunto)." celulas.\n";
        $escolhidos = $conjunto;
        $fitness_escolhidos = array();
        $escolhidos = array_slice($escolhidos, 0, $n_melhores);

        return $escolhidos;
    }

    /**
     * Realiza a clonagem de cada uma das celulas de um dado conjunto ordenado por fitness
     * A clonagem eh feita proporcionalmente a sua posicao no conjunto:
     * Primeiro, mais clones. Ultimo, menos clones.
     * @param array of {celula.php} $celulas Vetor de celulas ordenado por fitness
     * @return array of {celula.php} Vetor de clones gerados 
     */
    private function clonar_Celulas($celulas) {
        $clones = array();
        print "\nClonando ".count($celulas)." celulas\n";
        $melhor_fitness = $celulas[0]->fitness;
        foreach($celulas as $i=>$celula){
            $prob = (float) (rand(0, 100) / 100);
            print '.';
            if($prob < $this->taxa_clonagem){
                //$qtd_clones = floor(($celula->fitness/$melhor_fitness)*$this->max_clones);
                $qtd_clones = floor($this->max_clones/($i+1));
                print '+'.$qtd_clones.' ';
                for($i=0; $i<$qtd_clones; $i++){
                    ;
                    $clones[] = $celula->clonar();
                }
            }
        }
        print "\n".count($clones)." clones criados.\n";
        return $clones;
    }

    /**
     * Efetua a hipermutacao das celulas clone de acordo com a probabilidade de mutação
     * $prob_mutacao = exp(-$taxa_mutacao * D) , onde D eh a afinidade
     * @param [array of {celula.php}] $clones Celulas clone a serem hipermutadas
     * @return [array of {celula.php}] Celulas clone, ja mutados
     */
    private function mutar_Celulas($celulas) {
        print "\nMutando ".count($celulas)." celulas\n";        
        foreach($celulas as $celula) {
            print '.';
            $celula->mutar($this->taxa_mutacao);
        }
        return $celulas;
    }

    /**
     * Acrescenta um conjunto de celulas à populacao
     * Realiza a ordenacao da populacao de acordo com o fitness
     * @param [array of {celula.php}] $novos Conjunto de celulas novas
     */
    private function incluir_na_Populacao($novos) {
        print "\nIncluindo ".count($novos)." celulas\n";
        foreach($novos as $celula){
            print '.';
            $this->pop[] = $celula;
        }
        arraySort::sort($this->pop);
    }

    /**
     * Cria novas celulas aleatorias e inclui na populacao
     */
    private function diversificar_Celulas(){
        $novos = array();
        for($i=0; $i<$this->diversification_amt; $i++){
            print '.';
            $novos[] = new celula();
        }
        print '  ';
        print "\nDiversificando ".count($novos)." novas celulas.\n";
        $this->incluir_na_Populacao($novos);
        
    }

    /**
     * Remove-se as celulas com pior fitness, a fim de manter o tamanho da populacao fixo
     */
    private function matar_Excesso_Celulas() {
        print "\nMatando ".(count($this->pop)-$this->tam_pop)." celulas\n";
        $this->pop = array_slice($this->pop, 0, $this->tam_pop);
    }


    /**
     * Exibe o fitness de todas as celulas de um dado conjunto
     * @param array of {celula.php} $celulas Vetor de celulas 
     */
    private function mostrar_fitness($celulas){
        print "\n";
        foreach($celulas as $celula){
            print " ".$celula->fitness;
        }
        print "\n";
    }

    /**
     * Atualiza o historico celular de toda a populacao,
     * informando o fitness atual da celula
     * @param int $gen Geracao atual da populacao
     */
    private function update_Info($gen){
        foreach($this->pop as $celula){
            $this->historico[$celula->id][$gen] = $celula->fitness;
        }
        $this->top3[$gen]['melhor'] = $this->obter_Melhor()->fitness;
        $this->top3[$gen]['pior'] = $this->obter_Pior()->fitness;
    }
    
    /**
     * Escreve no arquivo "historico.txt" o historico de fitness celular
     * de todas as celulas da populacao
     */
    public function write_Info(){
        $fp = fopen("historico.txt", "w+");
        
        $message = "";
        foreach($this->historico as $id=>$gens){
            $message = $message.(string)$id.";";
            
            for($i=1; $i<=$this->max_geracoes; $i++){
                if(array_key_exists($i, $gens)){
                    $message = $message.(string)$gens[$i].";";
                }
                else{
                    $message = $message.";";
                }
            }
            $message = $message."\n";
        }
        print $message;
        fwrite($fp, $message);
        fclose($fp);
    }
    
    /**
     * Salva todas as celulas da populacao dentro do diretorio "populacao".
     * Cada celula tem seu conteudo salvo em um arquivo {ID}.cel
     */
    public function salvar_populacao(){
        print "\n\nSALVANDO POPULACAO";
        foreach($this->pop as $celula){
            $fp = fopen("populacao\\".$celula->id.".cel", "w+");
            print ".";
            fwrite($fp, serialize($celula));
            fclose($fp);
        }
    }
    
    /**
     * Carrega todas as celulas da populacao que constam no diretorio "populacao"
     * e as inclui na populacao
     */
    private function carregar_populacao(){
        $ponteiro  = opendir(getcwd()."\\populacao");
        // monta os vetores com os itens encontrados na pasta
        while ($nome_itens = readdir($ponteiro)) {
            if(substr($nome_itens,-3) == "cel"){
                $dir[] = $nome_itens;
                print $nome_itens."\n";
            }
        }
        $this->pop = array();
        foreach($dir as $file){
            print "!\n";
            $this->pop[] = unserialize(file_get_contents("populacao\\".$file));
        }
    }

}

?>
