<?php
namespace Common\My;
use Common;
/**
 * ...
 * @author Administrator
 */
class math{

	/**
	 * 开始匹配
	 * @param int|string $num 号码
	 * @param int|string $generation 第几代
	 * @param array $rules $匹配规则
	 * @return unknown
	 */
	public static function entry($num,$generation){
		$input['status']=1;
		$input['generation']=$generation.'';//第几代
		$input['num']=$num.'';//号码
		$input['prefix']=substr($num,0,3);//前三位
		$prefix_info=self::$prefix[$input['prefix']];//根据号码前三位找到对应的信息
		if($prefix_info==null){
			$input['status']=3;
			$input['msg']='该号码没有匹配的运营商,请检查程序配置是否正确';
			dump($input['msg']);
		}else{
			$input['company']=$prefix_info[0];//运营商
			$input['correct']=$prefix_info[1];//修正值
			call_user_func_array ( 'self::match_type_'.$input['company'].$input['generation'] , array(&$input ));
		}

		return $input;
	}
	
	
	public static function get_reg_arithmetic_progression($length=2,$cd=1){
		for($j=0,$reg='(';$j<$cd;$j++){
			for($i=0;$i<10;$i++){
				$reg.=$i.'(?='.($i+$cd).')';
			}
		}
		
		return '(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)|9(?=0)){6}[0-9]';
	}
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
	//电信3G靓号规则组
	public static function match_type_33($input,$test){
		$str=substr($input['num'],-8);
		if($test=='reg'){
			$re=preg_match('/'.self::$reg['ABABABAB'].'|'.self::$reg['AAA[^4689]'].'/',$str);
		}else{
			eval('$re=('.self::$php_rule['ABABABAB'].') || ('.self::$php_rule['AAA[^4689'].');');
		}
		if($re){
			$input['match_type']='ABABABAB';
		}else{
			
		}
		return $re?9:0;
	}
	//电信4G靓号规则组
	public static function match_type_34($input){
	
	
	
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