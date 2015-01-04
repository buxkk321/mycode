<?php
namespace Common\My;
/**
 * ...
 * @author Administrator
 */
class math{

    public static $prime_list=array();
	/**
	 * 正则规则
	 * @var unknown
	 */
	public static $reg=array(
			'ABABABAB'=>'(.)(?!\1)(.)(\1\2){3}',
			'ABCDABCD'=>'(.)(?!\1)(.)(?!\1|\2)(.)(?!\1|\2|\3)(.)\1\2\3\4',
			'AABBCCDD'=>'(.)\1(?!\1)(.)\2(?!\1|\2)(.)\3(?!\1|\2|\3)(.)\4',
			'[+]{7}'=>'(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)|9(?=0)){6}[0-9]',
			'[-]{7}'=>'(9(?=8)|8(?=7)|7(?=6)|6(?=5)|5(?=4)|4(?=3)|3(?=2)|2(?=1)|1(?=0)|0(?=9)){6}[0-9]',
			'AABBCC'=>'(.)\1(?!\1)(.)\2(?!\1|\2)(.)\3',
			'AAAA[^4]'=>'',
			'AAA[^4689]'=>'([^4689])\1\1',
			'ABABAB'=>'(.)(?!\1)(.)\1\2\1\2',
			'AABB[A^4][B^48]'=>'([^4])\1([^48])\2',
			'ABC[C89]'=>'([^89])(?!\1)[^89][89]',
			'180*180'=>'^180.+180$',
			'189*189'=>'^189.+189$',
			'A8A8[A^14]'=>'([^14]8)\1',
			'AAA8[A^14569]'=>'([^14569])\1{2}8',
				
				
			'*WWXXYYZZ'
	);
	/**
	 * 明确指定的数字
	 * @var unknown
	*/
	public static $stable=array(
			3=>array(158,518,168,118,668,998),
			4=>array(1588,1688,5188,1818,1118,5558,6668,9998),
			5=>array(44444)
	);
// 	public static $99big=array(
// 			22=>4,23,24,25,26,27,28,29,32,33,34,35,36,37,38,39
			
// 			)						
// 1	1	1	1	1	1	1	1	1
// 2	4	8	16	32	64	128	256	512
// 3	9	27	81	243	729	2187	6561	19683
// 4	16	64	256	1024	4096	16384	65536	262144
// 5	25	125	625	3125	15625	78125	390625	1953125
// 6	36	216	1296	7776	46656	279936	1679616	10077696
// 7	49	343	2401	16807	117649	823543	5764801	40353607
// 8	64	512	4096	32768	262144	2097152	16777216	134217728
// 9	81	729	6561	59049	531441	4782969	43046721	387420489


    /**
     * 求质数
     * @param int $start
     * @param int $end
     */
    public static function get_prime($f=1,$start_len=null,$end_len=null){
        !$start_len && $start_len=5;
        !$end_len && $end_len=9;
        $re=array();
        $gmp=gmp_nextprime (str_repeat('9',$start_len-1));
        $str=gmp_strval($gmp);
        $len=strlen($str);
        $re[$len][]=$str;
        $time=microtime(true);
        if($f){
            while(true){
                if($len>$end_len){
                    break;
                }else{
                    $gmp=gmp_nextprime ($str);
                    $str=gmp_strval($gmp);
                    $len=strlen($str);
                    $re[$len][]=$str;
                }
            }
        }else{
            while($len<=$end_len){
               //TODO:判断质数
            }
        }
        echo microtime(true)-$time;
        return $re;
    }

	//测试代码
	public static function test($input){
		$o='0123456789';
		$c=1111;
		$time=microtime(true);
		for($i=0;$i<$c;$i++){
			$arr[$i]='180'.(Common\My\data_rand::string($o,8));
		}
		dump('生成用时:'.(microtime(true)-$time));
		
		$test=self::$reg['AAA[^4689]'];
		echo "<br/>正则的测试";
		$time=microtime(true);
		dump('正则规则:/'.self::$reg[$test].'/');
		for ($i=0,$j=0;$i<$c;$i++){
			$str=substr($arr[$i],-3);
			$re=preg_match('/'.self::$reg[$test].'/', $str);
			$re && $j++;
		}
		dump($re);
		dump('命中:'.$j.'个,用时:'.(microtime(true)-$time));
		
		echo "<br/>php的测试";
		$time=microtime(true);
		
		dump('php代码:$re='.self::$php_rule[$test].';');
		for ($i=0,$j=0;$i<$c;$i++){
			$str=substr($arr[$i],-3);
			eval('$re='.self::$php_rule[$test].';');
			$re && $j++;
		}
		dump($re);
		dump('命中:'.$j.'个,用时:'.(microtime(true)-$time));
	}
}