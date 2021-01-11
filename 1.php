<?php
	
	// вывод простых чисел от a до b
	// алгоритм Решето Эратосфена из статьи https://habr.com/ru/post/133037/
	function findSimple ($a, $b) {
		
		$sqrt_limit = floor(sqrt($b));
		
		$allNums = str_repeat("\1", $b + 1);
		
		
		for($i = 2; $i <= $sqrt_limit; $i++){
			if($allNums[$i]==="\1"){
				for($j = $i*$i; $j <= $b; $j += $i){
					$allNums[$j]="\0";
				}
			}
		}
		// формируем выходной массив простых чисел
		$simpleNums = [];
		for($i = 2; $i <= $b; $i++){
			if($i >= $a and $allNums[$i] === "\1"){
				$simpleNums[] = $i;
			}
		}				
		return $simpleNums;
	}
	
	function createTrapeze($a){
		$arrTrap = [];
		for($i = 0; $i < count($a); $i += 3){
			$arrTrap[] = [
				"a" => $a[$i],
				"b" => $a[$i + 1],
				"c" => $a[$i + 2]
			];
		}
		return $arrTrap;
	}
	
	function squareTrapeze($a){
		for($i = 0; $i < count($a); $i++){
			$a[$i]["s"] = 0.5 * ($a[$i]["a"] + $a[$i]["b"]) * $a[$i]["c"];
		}
		return $a;
	}
	
	function getSizeForLimit($a, $b){
		for($i = 0; $i < count($a); $i++){
			if($a[$i]["s"] > $b){
				unset($a[$i]);
			}
		}
		return $a;
	}
	
	function getMin($a){
		$minVal = $a[0];
		for($i = 1; $i < count($a); $i++){
			if($minVal > $a[$i]){
				$minVal = $a[$i];
			}
		}
		return $minVal;
	}
	
	function printTrapeze($a){
		foreach($a as $tropez){
			
			if($tropez["s"] % 2 == 0){
				printf("%'#10d   %'#10d  %'#10d  %'#10.2f\n", $tropez['a'], $tropez['b'], $tropez['c'], $tropez['s']);
			} else{
				printf("%10d   %10d  %10d  %10.2f\n", $tropez['a'], $tropez['b'], $tropez['c'], $tropez['s']);
			}
		}
	}
	
	abstract class BaseMath{
		
		public function exp1($a, $b, $c){
			return $a * ($b**$c);
		}
		
		public function exp2($a, $b, $c){
			return ($a / $b)**$c;
		}
		//реализуется классом наследником
		abstract public function getValue();
	}
	
	class F1 extends BaseMath{
		protected $a;
		protected $b;
		protected $c;
		
		function __construct($a, $b, $c) {
			$this->a = $a;
			$this->b = $b;
			$this->c = $c;
		}
		//определяем абстрактный метод
		public function getValue(){
			//если напишем ($this->exp1($a,$b,$c) + ($this->exp2($a,$c,$b)%3)**min($this->a,$this->b,$this->c)
			// то будет неочевидным образом при переопределении одного из методов exp1 или exp2 меняться вывод getValue()
			return $this->a*($this->b**$this->c)+((($this->a/$this->c)**$this->b)%3)**min($this->a,$this->b,$this->c);
		}
	}
	
	echo "Execute tests? \n";
	$ans = trim(fgets(STDIN));
	if($ans == "yes"){
		echo "Execute findSimple(78,521)\n\n";
		echo "Function return:\n";
		print_r(findSimple(78, 521));
		echo "\n\n";
		
		echo "Execute createTrapeze([1, 2, 3, 4, 5, 6])\n";
		$resCreateTrapeze = createTrapeze(array(1, 2, 3, 4, 5, 6));
		print_r($resCreateTrapeze);
		echo "\n\n";
		
		echo "Execute squareTrapeze()\n";
		$resSquareTrapeze = squareTrapeze($resCreateTrapeze);
		print_r($resSquareTrapeze);
		
		echo "\n\n Execute getSizeForLimit(resSquareTrapeze,5)\n";
		$resGetSizeForLimit = getSizeForLimit($resSquareTrapeze, 5);
		print_r($resGetSizeForLimit);
		
		printf("\n\n Execute getMin([5,3,7,8,1]) =  %d\n\n", getMin([5,3,7,8,1]));
		
		echo "\n Execute printTrapeze(squareTrapeze(createTrapeze([4,2,3,5,5,6,7,8,9,2,2,2,2,3,2]))\n\n";
		$inPrintTrapeze = squareTrapeze(createTrapeze([4,2,3,5,5,6,7,8,9,2,2,2,2,3,2]));
		printTrapeze($inPrintTrapeze);
		
		echo "\n Realize abstract class BaseMath, create object F1 f1 = new F1(1,2,3)\n";
		$f1 = new F1(1,2,3);
		printf("Object f1->exp1(4,5,6) = %.2f \n", $f1->exp1(4,5,6));
		printf("Object f1->exp2(7,8,9) = %.2f \n", $f1->exp2(7,8,9));
		printf("Object f1->getValue() = %.2f \n", $f1->getValue());
	}