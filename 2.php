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

echo "Execute tests? \n";
	$ans = trim(fgets(STDIN));
	if($ans == "yes"){
            echo "Execute convertString('aabbccaabbcc', 'ab')\n";
            echo "Result = ".convertString('aabbccaabbcc', 'ab');
        }

