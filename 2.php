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

/**
 * Импортировать xml файл в базу данных
 * @param type $a   путь к файлу xml
 */
function importXml($a){
    
    //соединяемся с базой данных
    $mysqli = new mysqli("192.168.1.124", "hostuser", "3qQGxY5B", "test_samson");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        return;
    }
    echo $mysqli->host_info . "\n";
    
    $mysqli->set_charset("utf8");
    
    // читаем xml в обьект simpleXML 
    if (!file_exists('products.xml')) {
        exit('Не удалось открыть файл products.xml. '. getcwd());
        return;   
    }
    $xml = simplexml_load_file('products.xml');
    
    //разбираем файл xml
    // подготавливаем запросы для вставки данных
    $insert_product_stmt = $mysqli->prepare("INSERT INTO a_product(product_code, name) VALUES (?, ?);");
    $insert_price_stmt = $mysqli->prepare("INSERT INTO a_price (product_id, price_type, price) VALUES (?, ?, ?);");
    $insert_property_stmt = $mysqli->prepare("INSERT INTO a_property (product_id, property) VALUES (?, ?);");
    $insert_category_stmt = $mysqli->prepare("INSERT INTO a_category (name) VALUES (?);");
    $insert_prod_cat_stmt = $mysqli->prepare("INSERT INTO a_product_categories (product_id, category_id, category_level) VALUES (?, ?, ?);");
    // используем IGNORE чтобы не выызывать ошибок в случае если такая связь категорий уже существует
    $insert_category_conn_stmt = $mysqli->prepare("INSERT IGNORE INTO a_category_connections (id, parent_id) VALUES (?, ?);");
    $insert_cat_conn_null_stmt = $mysqli->prepare("INSERT IGNORE INTO a_category_connections (id) VALUES (?);");
    
    // перебираем все товары
    foreach ($xml as $product) {
        //вставляем продукты и получаем id нового продукта
        // предполагаем, что вставляютс уникальные товары
        $insert_product_stmt->bind_param('ss', $product->attributes()["Код"],
                                               $product->attributes()["Название"]);
        $insert_product_stmt->execute();
        $product_id = $insert_product_stmt->insert_id;
        // перебираем все узлы товара
        foreach($product->children() as $product_param){
            //вставляем цену товара
            if($product_param->getName() == "Цена"){
                $price = (string) $product_param;             
                $insert_price_stmt->bind_param('iss', $product_id,
                                                      $product_param->attributes()["Тип"],
                                                      $price );
                $insert_price_stmt->execute();
            }
            if($product_param->getName() == "Свойства"){
                
                foreach($product_param as $product_property){
                    // собираем название, значение и единицы измерения(если есть) в одну строку
                    $property_name = $product_property->getName()." ".(string) $product_property;
                    if($product_property->attributes()["ЕдИзм"]){
                        $property_name = $property_name.(string)$product_property->attributes()["ЕдИзм"];
                    }
                    $insert_property_stmt->bind_param('is', $product_id,
                                                      $property_name);
                    $insert_property_stmt->execute();
                }
            }
            if($product_param->getName() == "Разделы"){
                // уровень вложенности категории данного товара. Равен количеству категорий товара.
                $category_level = $product_param->count();
                $parent_id = 0;   // id предыдущей категории, для вставки свзи родитель-потомок
                foreach($product_param as $category){
                    $category_name = (string) $category;
                    // если такой элемент есть, то берем его id
                    $result = $mysqli->query("SELECT id FROM a_category WHERE name = '".$category_name."' ;");
                    // если записи с данным id нет, то вставляем новую запись
                    if($result->num_rows == 0){
                        $insert_category_stmt->bind_param('s', $category_name);
                        $insert_category_stmt->execute();
                        $category_id = $insert_category_stmt->insert_id;
                    } else{
                        $category_id = (int) $result->fetch_object()->id;
                    }
                    //записываем связт категории с товаром
                    $insert_prod_cat_stmt->bind_param('iii', $product_id, $category_id, $category_level);
                    $insert_prod_cat_stmt->execute();
                    
                    // если у категории есть родитель, то добавляем связь в таблицу a_category_connections
                    if($parent_id > 0){
                        $insert_category_conn_stmt->bind_param('ii', $category_id, $parent_id);
                        $insert_category_conn_stmt->execute();
                        
                    } else {
                        // если у категории нет родителя, то просто добавляем узел
                        $insert_cat_conn_null_stmt->bind_param('i', $category_id);
                        $insert_cat_conn_null_stmt->execute();
                    }
                    $parent_id = $category_id;
                    $category_level--;
                }
                
            }
            
        }
        
    }
    $mysqli->close();
    
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
            echo "Array after sorting:\n";
            print_r($arrToSort);
            
            echo "Test mysql connection\n";
            importXml("a");
        }

