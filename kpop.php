<?php

class Parameters{
    const FILE_NAME = 'product.txt';
    const COLUMNS = ['item', 'price'];
    const POPULATION_SIZE = 10;
    const BUDGET = 29000;
    const STOPPING_VALUE = 10000;
    const CROSOVER_RATE = 0.8;
}
class Catalogue
{
    function createProductColumn($listOfRawProduct){
        foreach (array_keys($listOfRawProduct) as $listOfRawProductKey){
            $listOfRawProduct[Parameters::COLUMNS[$listOfRawProductKey]] = $listOfRawProduct[$listOfRawProductKey];
            unset($listOfRawProduct[$listOfRawProductKey]);
        }
        return $listOfRawProduct;
}

    function product(){
        $collectionOfListProduct = [];

        $raw_data = file(Parameters::FILE_NAME);
        foreach ($raw_data as $listOfRawProduct){
            $collectionOfListProduct[] = $this->createProductColumn(Parameters::COLUMNS,explode(",",$listOfRawProduct));
        }
        return $collectionOfListProduct;
    }
}

class Individu
{
    function countNumberOfGen()
    {
        $catalogue = new Catalogue;
        return count($catalogue->product());
    }
    function createRandomIndividu()
    {
        for ($i = 0; $i <= $this->countNumberOfGen()-1; $i++){
            $ret[] = rand(0,1);
        }
        return $ret;
    }
}


class population
{
    function createRandomPopulation()
    {
        $individu = new Individu;
        for($i = 0; $i <= Parameters::POPULATION_SIZE-1; $i++)
        {
            $ret[] = $individu->createRandomIndividu();
        }
        return $ret;
    }
}

class Fitness
{
    function SelectingItem($individu)
    {
        $catalogue = new Catalogue;
        foreach($individu as $individuKey => $binaryGen){
            if ($binaryGen === 1){
                $ret[] = [
                    'selectedKey' => $individuKey,
                    'selectedprice' => $catalogue->product()[$individuKey]['price']
                ];
            }
        }
        return $ret;
    }

    function calculatefitnessvalue($individu)
    {
       return array_sum(array_column($this->selectingItem($individu), 'selectedPrice'));
    }

    function countSelectedItem($individu)
    {
        return count($this->selectingItem($individu));
    }
    
    function searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem)
    {
        if ($numberOfIndividuHasMaxItem == 1){
            $index = array_search($maxItem, array_column($fits, 'numberOfSelected'));
            //echo '<br>';
            //print_r($fits[$index]);
            //echo '<p>';
        }else{
            foreach ($fits as $key => $val){
                if ($val['numberOfSelectedItem'] === $maxItem){
                    echo $key. ' '.$val['fitnessValue'].'<br>';
                    $ret[] = [
                        'individuKey' => $key,
                        'fitnessValue' => $val['fitnessValue']
                    ];
                }
            }
            if (count(array_unique(array_column($ret, 'fitnessValue'))) === 1){
                $index = rand(0, count($ret) - 1);
            } else {
                $max = max(array_column($ret, 'fitnessValue'));
                $index = array_search($max, array_column($ret, 'fitnessValue'));
            }
            echo 'hasil';
            return $ret[$index];
        }
    }
        

    function isFound($fits)
    {
        $countedMaxItems = array_count_values(array_column($fits, 'numberOfSelectedItem'));
        print_r($countedMaxItems);
        echo '<br>';
        $maxItem = max(array_keys($countedMaxItems));
        echo $maxItem;
        echo '<br>';
        echo $countedMaxItems[$maxItem];
        $numberOfIndividuHasMaxItem = $countedMaxItems[$maxItem];

        $bestFitnessValue = $this->searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem)['fitnessValue'];
        echo '<br>';
        echo '<br> Best fitness Value: '.$bestFitnessValue;

        $residual = Parameters::BUDGET - $bestFitnessValue;
        echo 'Residual: '. $residual;

        if ($residual <= Parameters::STOPPING_VALUE && $residual > 0){
            return TRUE;
        }
    }

    function isFit($finessValue)
    {
        if ($finessValue <= Parameters::BUDGET){
            return TRUE;
        }
    }

    function fitnessEvaluation($population)
    {
        $catalogue = new Catalogue;
        foreach ($population as $lisOfIndividuKey => $lisOfIndividu){
            echo 'Individu-'. $lisOfIndividuKey.'<br>';
            foreach ($lisOfIndividu as $individuKey => $binaryGen){
                echo $binaryGen.'&nbsp;&nbsp;';
                print_r($catalogue->product()[$individuKey]);
                echo '<br>';
            }
            $finessValue = $this->calculateFitnessValue($lisOfIndividu);
            $numberOfSelectedItem = $this->countSelectedItem($lisOfIndividu);
            echo 'Max. Item: '. $numberOfSelectedItem;
            echo ' Fitness value: '.$finessValue;
            if ($this->isFit($finessValue)){
               echo ' (Fit)';
               $fits[] = [
                   'selectedIndividuKey' => $lisOfIndividuKey,
                   'numberOfSelectedItem' => $numberOfSelectedItem,
                   'fitnessValue' => $finessValue
               ];
               print_r($fits);
            }else{
                echo ' (Not Fit)';
            }
            echo '<p>';
        }
        if ($this->isFound($fits)){
            echo 'Not founf';
        }else{
            echo ' >> Next Generation';
        }
        
    }
}

class Crossover
{
    public $populations;

    function __construct($populations)
    {
        $this->populations = $populations;
    }

    function randomZeroToOne()
    {
        return (float) rand() / (float) getrandmax();
    }

    function generateCrossover()
    {
        for ($i = 0; $i <= Parameters::POPULATION_SIZE-1; $i++){
            $randomZeroToOne = $this->randomZeroToOne();
           if ($randomZeroToOne < Parameters::CROSOVER_RATE){
               $parents[$i] = $randomZeroToOne;
           }
        }
        foreach (array_keys($parents)as $key){
            foreach (array_keys($parents) as $subkey){
                if ($key !== $subkey){
                    $ret[] = [$key, $subkey];
                }
            }
            array_shift($parents);
        }
        return $ret;
    }

    function offspring($parent1, $parent2, $cutPointIndex, $offspring)
    {
        $lengthOfGen = new Individu;
        if ($offspring === 2){
            for ($i = 0; $i <= $lengthOfGen->countNumberOfGen()-1; $i++){
                if ($i <= $cutPointIndex){
                $ret[] = $parent2[$i];
            }
            if ($i > $cutPointIndex){
                $ret[] = $parent1[$i];
            }

        }
        return $ret;
     }


        // if ($offspring === 2){
        //     for ($i = 0; $i <= $lengthOfGen->countNumberOfGen()-1; $i++){
        //         if ($i <= $cutPointIndex){
        //         $ret[] = $parent2[$i];
        //     }
        //     if ($i > $cutPointIndex){
        //         $ret[] = $parent2[$i];
        //     }
        //     if ($i > $cutPointIndex){
        //         $ret[] = $parent1[$i];
        //     }
        // }
    }

    function cutPointRandom()
    {
        $lengthOfGen = new Individu;
        return rand(0, $lengthOfGen->countNumberOfGen()-1);

    }

    function crossover()
    {
        $cutPointIndex = $this->cutPointRandom();
       foreach ($this->generateCrossover() as $listOfCrossover){
           $parent1 = $this->populations[$listOfCrossover[0]];
           $parent2 = $this->populations[$listOfCrossover[1]];
           echo 'Parents :<br>';
           foreach ($parent1 as $gen){
               echo $gen;
           }
           echo ' >< ';
           foreach ($parent2 as $gen){
               echo $gen;
           }
           echo '<br>';

           echo 'offspring<br>';
           $offspring1 = $this->offspring($parent1, $parent2, $cutPointIndex, 1);
           $offspring2 = $this->offspring($parent1, $parent2, $cutPointIndex, 2);
           print_r($offspring1);
           echo '<br>';
           print_r($offspring2);
       }
    }
}

$initialPopulation = new Population;
$Population = $initialPopulation->createRandomPopulation();

$fitness = new Fitness;
$fitness->fitnessEvaluation($Population);

$crossover = new Crossover($Population);
$crossover->crossover();

//$individu = new Individu;
//print_r($individu->createRandomIndividu());
