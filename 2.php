<?php

/**
 * Ищет второе вхождение подстроки $b в строке $a. Если
 * такое вхождение есть, то заменить его на инвертированную строку $b.
 * Возвращает измененную строку, или просто $a, если вхождений нет.
 * 
 * @param type $a   строка дл поиска
 * @param type $b   подстрока
 */
function convertString($a, $b){
    $funcRes = $a;
    $searchRes = strpos($a, $b);
    if($searchRes !== false){
        $searchRes = strpos($a, $b, $searchRes + strlen($b));
        if($searchRes !== false){
            $funcRes = substr_replace($a, strrev($b), $searchRes, strlen($b));
            strrev($b);
        }
    }
    
    return $funcRes;
}
/**
 * Сортерует двумерный массив $a по возрастанию ключа, переданного 
 * аргументом $b.
 * Возвращает true в случае успешного завершения или false в случае возникновения ошибки.
 * 
 * @param type $a   двумерный массив вида [['a'=>2,'b'=>1],['a'=>1,'b'=>3]]
 * @param type $b   ключ вложенного массива
 */
function mySortForKey(&$a, $b){
    //проверяем данные перед обработкой
    for($i=0; $i < count($a); $i++){
        if(!array_key_exists($b, $a[$i])){
            
            throw new Exception("Key '".$b."' is not exist in  arrey index '"
                                .$i."'\n");
        }
    }
    
    $compare = function($elem1, $elem2) use ($b){
         if ($elem1[$b] == $elem2[$b]) {
            return 0;
        }
        return ($elem1[$b] < $elem2[$b]) ? -1 : 1;
    };
    return uasort($a, $compare);
}

echo "Execute tests? \n";
	$ans = trim(fgets(STDIN));
	if($ans == "yes"){
            echo "Execute convertString('aabbccaabbcc', 'ab')\n";
            echo "Result = ".convertString('aabbccaabbcc', 'ab');
            echo "\n";
            echo "Run with no 'b' key in element 3\n"
                ."mySortForKey (['a'=>2,'b'=>1],['a'=>1,'b'=>3] \n"
                ."             ['a'=>2,'b'=>2],['a'=>1,'f'=>7], 'b') \n";
            $arrToSortErr = [['a'=>2,'b'=>1],['a'=>1,'b'=>3],['a'=>2,'b'=>2],['a'=>1,'f'=>7]];
            try{
               mySortForKey($arrToSortErr, 'b');
            } catch (Exception $ex) {
                echo "Catch exception. Message is: ".$ex->getMessage()."\n";
            }
            echo "Run with correct keys\n"
                ."mySortForKey (['a'=>2,'b'=>1],['a'=>1,'b'=>3] \n"
                ."             ['a'=>2,'b'=>2],['a'=>1,'b'=>7], 'b') \n";
            $arrToSort = [['a'=>2,'b'=>1],['a'=>1,'b'=>3],['a'=>2,'b'=>2],['a'=>1,'b'=>7]];
            echo "Arrey before sorting:\n";
            print_r($arrToSort);
            echo "\n";
            mySortForKey($arrToSort, 'b');
            echo "Arrey after sorting:\n";
            print_r($arrToSort);
        }

