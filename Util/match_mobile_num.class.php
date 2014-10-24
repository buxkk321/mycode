<?php
namespace Common\My;
/**
 * 筛选手机靓号
 * @author Administrator
 */
class match_mobile_num{
	public static $company=array(1=>'yidong',2=>'liantong',3=>'dianxin');//1移动,2联通,3电信
	public static $prefix=array(
		/*移动的*/
		134=>'10',135=>'10',136=>'10',137=>'10',138=>'10',139=>'10',
		150=>'10',151=>'10',152=>'10',157=>'10',158=>'10',159=>'10',
		182=>'10',183=>'10',184=>'10',187=>'10',188=>'10',
		/*联通的*/
		130=>'20',131=>'20',132=>'20',
		155=>'20',156=>'20',
		185=>'20',186=>'20',
		/*电信的*/
		153=>'31',/*第一类*/
		133=>'32',/*第二类*/
		180=>'33',181=>'33',189=>'33',/*第三类*/
		177=>'34',
	);
	/**
	 * 正则规则,主要用于判断尾号
	 * @var unknown
	 */
	public static $reg=array(
			'ABABABAB[A^B]'=>'(.)(?!\1)(.)(\1\2){3}',
			'ABCDABCD[A^B][B^C][C^D]'=>'(.)(?!\1)(.)(?!\1|\2)(.)(?!\1|\2|\3)(.)\1\2\3\4',
			'ABCDABCD'=>'(....)\1',
			'AABBCCDD'=>'(.)\1(?!\1)(.)\2(?!\1|\2)(.)\3(?!\1|\2|\3)(.)\4',
		
			'AAAAAB[A^B][AB^4]'=>'([^4])\1{4}(?!\1)[^4]',
			'ABABAB[A^B]'=>'(.)(?!\1)(.)\1\2\1\2',
			'ABABAB[A^4]'=>'([^4].)\1\1',
			'ABABAB'=>'(..)\1\1',
			
			'AAABBB'=>'(.)\1\1(?!\1)(.)\2\2',
			'AABBCC'=>'(.)\1(?!\1)(.)\2(?!\1|\2)(.)\3',
			'ABCABC'=>'(.)(?!\1)(.)(?!\1|\2)(.)\1\2\3',
			'A88A88[A^4]'=>'([^4]88)\1',
			
			'AAAAB[A^B][AB^4]'=>'([^4])\1{3}(?!\1)[^4]',
			'88AAA[A^4]'=>'88([^4])\1\1',
			
			
			'AABB'=>'(.)\1(.)\2',
			'ABAB'=>'(..)\1',
			'ABAB[AB^4]'=>'([^4]{2})\1',
			'ABBA[AB^4]'=>'([^4])([^4])\2\1',
			'AAAB[AB^4]'=>'([^4])\1\1[^4]',
			'AABB[A^4][B^48]'=>'([^4])\1([^48])\2',
			'AABB[A^B][AB^4]'=>'([^4])\1(?!\1)([^4])\2',
			'AB88[A^B][AB^4]'=>'([^4])(?!\1)[^4]88',
			'A88B[A^B][AB^4]'=>'([^4])88(?!\1)[^4]',
			'AB00[AB^4]'=>'([^4])([^4])00',
			'AB66[AB^4]'=>'([^4])([^4])66',
			'AB99[AB^4]'=>'([^4])([^4])99',
			
			
			'AAAA[689]'=>'([689])\1{3}',
			'AAAA[^4]'=>'([^4])\1{3}',
			'AAAA[^04689]'=>'([^04689])\1{3}',
			'AAA8[A^14569]'=>'([^14569])\1{2}8',
			'AAA8[A^4]'=>'([^4])\1{2}8',
			'A8A8[A^14]'=>'([^14]8)\1',
			'A8A8[A^4]'=>'([^4]8)\1',
			'AA88[A^4]'=>'([^4])\1{1}88',
			'88AA[A^4]'=>'88([^4])\1',
			'8AA8[A^4]'=>'8([^4])\1{1}8',
			'888A[A^4]'=>'888([^4])',
			'A168[A^4]'=>'[^4]168',
			'A158[A^4]'=>'[^4]158',
			'A518[A^4]'=>'[^4]518',
			
			'AAA[0689]'=>'([0689])\1\1',
			'AAA[689]'=>'([689])\1\1',
			'AAA[^689]'=>'([^689])\1\1',
			'AAA[^4689]'=>'([^4689])\1\1',
			'AAA[^4]'=>'([^4])\1\1',
			'ABC[C89]'=>'([^89])(?!\1)[^89][89]',
			'ABC[^4]'=>'[^4]{3}',
			'4BC|A4C|AB4'=>'(4..|.4.|..4)',
			
			'AA[689]'=>'([689])\1',
			'AA[^4]'=>'([^4])\1',
			
			'180%180'=>'^180.+180',
			'189%189'=>'^189.+189',
			//以下规则用php代码匹配更快
// 			'[+1]{7}'=>'(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)|9(?=0)){6}[0-9]',
// 			'[-1]{7}'=>'(9(?=8)|8(?=7)|7(?=6)|6(?=5)|5(?=4)|4(?=3)|3(?=2)|2(?=1)|1(?=0)|0(?=9)){6}[0-9]',
// 			'[+1]{6}'=>'(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)|9(?=0)){5}[0-9]',
// 			'[-1]{6}'=>'(9(?=8)|8(?=7)|7(?=6)|6(?=5)|5(?=4)|4(?=3)|3(?=2)|2(?=1)|1(?=0)|0(?=9)){5}[0-9]',
// 			'[+1]{5}'=>'(0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)|9(?=0)){4}[0-9]',
// 			'[-1]{5}'=>'(9(?=8)|8(?=7)|7(?=6)|6(?=5)|5(?=4)|4(?=3)|3(?=2)|2(?=1)|1(?=0)|0(?=9)){4}[0-9]',
	);
	/**
	 * 程序流程,其中指定的数字只能用来匹配尾号
	 * @var unknown
	 */
	private static $flow=array(
		23=>array(/*联通3g流程*/
			3=>array(
				'reg'=>array('AAAA[689]'),
				'php'=>array(
						array('ap_positive','5'),
						array('ap_negative','5')
				)),
			4=>array(
				'reg'=>array('AAAA[^04689]','ABCDABCD','ABABAB[A^4]'),
			),
			5=>array(
				'reg'=>array('AAA[689]'),
				'php'=>array(
						array('ap_positive','4'),
						array('ap_negative','4')
				)),
			6=>array(
				'reg'=>array('AAA[^689]'),
			),
			7=>array(
				'reg'=>array('AABB','ABAB','AA[689]'),
				'php'=>array(
						array('ap_positive','3'),
						array('ap_negative','3')
				))
		),
		33=>array(/*电信3g流程*/
			array(
				'reg'=>array('ABABABAB[A^B]','AAA[^4689]')
				),
			array(
				'reg'=>array('ABABAB'),
				'enum'=>array(
					4=>array(1588=>1,1688=>1,5188=>1,1818=>1,1118=>1,5558=>1,6668=>1,9998=>1)
				)),
			array(
				'enum'=>array(
					3=>array(158=>1,518=>1,168=>1,118=>1,668=>1,998=>1)
				)),
			array(
				'reg'=>array('AABB[A^4][B^48]','ABC[C89]','180%180','189%189'),
			),
			array(
				'reg'=>array('AAA8[A^14569]','A8A8[A^14]')
				)
		),
		34=>array(/*电信4g流程*/
			8=>array(
				'eq'=>'44444',
				'reg'=>array('ABCDABCD','ABABABAB[A^B]','AABBCCDD','AAAA[^4]'),
				'php'=>array(
					array('ap_positive','7'),
					array('ap_negative','7')
				)),
			7=>array(
				'eq'=>'4444',
				'reg'=>array('AAA[0689]','AABBCC','ABABAB','AAABBB','AA88[A^4]','88AAA[A^4]','A88A88[A^4]'),
				'php'=>array(
					array('ap_positive','6'),
					array('ap_negative','6')
				)),
			6=>array(
				'enum'=>array(
					4=>array(1588=>1,1688=>1)
				),
				'reg'=>array('ABCABC','AAA[^4]','A8A8[A^4]','88AA[A^4]'),
				'php'=>array(
					array('ap_positive','5'),
					array('ap_negative','5')
				)),
			5=>array(
				'eq'=>'444',
				'enum'=>array(
					4=>array('0001'=>1,5678=>1,6789=>1)
				),
				'reg'=>array('AB88[A^B][AB^4]','AABB[A^B][AB^4]','888A[A^4]','AAA8[A^4]','AAAAAB[A^B][AB^4]'),
			),
			4=>array(
				'reg'=>array('A168[A^4]','A158[A^4]','A518[A^4]','8AA8[A^4]','AAAAB[A^B][AB^4]'),
				'php'=>array(
					array('ap_positive','4'),
					array('ap_negative','4')
				)),
			3=>array(
				'reg'=>array('ABBA[AB^4]','AAAB[AB^4]','AB00[AB^4]','AB66[AB^4]','AB99[AB^4]','ABAB[AB^4]')
			),
			2=>array(
				'eq'=>8,
				'reg'=>array('AA[^4]','A88B[A^B][AB^4]'),
				'php'=>array(
					array('ap_positive','3'),
					array('ap_negative','3')
				)),
			1=>array(
				'reg'=>array('ABC[^4]'),
			),
			0=>array(
				'reg'=>array('4BC|A4C|AB4'),
			)
		),
	);
	/*连号*/
	public static function repeat($str){
		return str_repeat($str[0], strlen($str))==$str;
	}
	/*首尾相同*/
	public static function pre_eq_suf($pre,$suf,$asgd=false){
		if($asgd){
			return $pre==$suf && $pre==$asgd;
		}else{
			return $pre==$suf;
		}
	}
	public static function multi_eq(){
		$arr=func_get_args();
		foreach (array_slice($arr,1) as $v){
			if($arr[0]==$v){
				continue;
			}else{
				return false;
			}
		}
		return true;
	}
	/*10位以下顺增*/
	public static function ap_positive($str){
		$list='0123456789012345678';
		if(strpos($list, $str)===false){
			return false;
		}else{
			return true;
		}
	}
	/*10位以下顺减*/
	public static function ap_negative($str){
		$list='9876543210987654321';
		if(strpos($list, $str)===false){
			return false;
		}else{
			return true;
		}
	}
	/*执行匹配*/
	private static function do_match(&$input,$test){
		$input['level']=0;
		$code=$input['company'].$input['generation'];
		switch ($code){//流程修正
			case 33://电信3G
				switch ($input['correct']){
					case 1://153
						$flow=self::$flow[$code];
						break;
					case 2://133
						$flow=array_slice(self::$flow[$code],1);
						break;
					case 3://180,181,189
						$flow=array_slice(self::$flow[$code],2);
						break;
					default:
						$flow=array();
				}
				$input['level']=7;
				break;
			case 22://联通2G
				$flow=self::$flow[23];
				unset($flow[7]['php'],$flow[7]['reg']['AABB'],$flow[7]['reg']['ABAB']);
				break;
			default:
				$flow=self::$flow[$code];
		}
		if(empty($flow)){
			$input['status']=4;
			$input['msg']=$input['num'].'没有对应的'.$input['generation'].'G靓号规则';
			return ;
		}
		
// 		if($test=='reg'){
// 			$flow[8]['reg'][]='[+1]{6}';
// 			$flow[8]['reg'][]='[-1]{6}';
// 		}else{
// 			$flow[8]['php'][]=array('ap_positive','6');
// 			$flow[8]['php'][]=array('ap_negative','6');
// 		}
// 		dump($flow);

		foreach ($flow as $k=>$v){
			if(isset($v['eq'])){
				$len=strlen($v['eq']);
				if(substr($input['num'],-$len)==$v['eq']){
					$input['match_type']='eq{'.$len.'}';
					$input['level']+=$k;
					break;
				}
			}
// 			if(isset($v['neq'])){
// 				if(substr($input['num'],-strlen($v['neq']))!=$v['neq']){
// 					$input['match_type']='neq['.$v['neq'].']';
// 					$input['level']=$k+$fix;
// 					break;
// 				}
// 			}
			if(isset($v['enum'])){
				foreach ($v['enum'] as $k2=>$v2){
					if(isset($v2[substr($input['num'],-($k2))])){
						$input['match_type']='eq{'.$k2.'}';
						$input['level']+=$k;
						break 2;
					}
				}
			}
			if(isset($v['reg'])){
				foreach ($v['reg'] as $v3){
					if(preg_match('/'.self::$reg[$v3].'$/',$input['num'])){
						$input['match_type']=$v3;
						$input['level']+=$k;
						break 2;
					}
				}
			}
			if(isset($v['php'])){
				foreach ($v['php'] as $v4){
					if (isset($v4[1])) {
						//为了效率优化做以下约定:第二个单元为截取尾号的长度,第三个单元为指定的数字,第四个单元为$input数组的键名
						$param[]=substr($input['num'],-($v4[1]));
						isset($v4[2]) && $param[]=$v4[2];
						isset($v4[3]) && $param[]=$input[$v4[3]];
					}else{
						$param[]=$input;
					}
					if(call_user_func_array('self::'.$v4[0],$param)){
						$input['match_type']=$v4[0].($v4[1]>0?'_'.$v4[1]:'');
						$input['level']+=$k;
						break 2;
					}
				}
			}
		}
		
		switch ($code){//结束操作
			case 34://电信4G
				$input['level']>1 && $input['is_nice']=1;
				break;
			default:
				if($input['match_type']){
					$input['is_nice']=1;
				}else{
					$input['level']=0;
				}
		}
		
	}
	/**
	 * 入口
	 * @param int|string $num 号码
	 * @param int|string $generation 第几代
	 * @param array $rules $匹配规则
	 * @return unknown
	 */
	public static function entry($num,$generation,$test){
		$input['status']=1;
		$input['generation']=$generation.'';//第几代
		$input['num']=$num.'';//号码
		$input['prefix']=substr($num,0,3);//前三位
		$prefix_info=self::$prefix[$input['prefix']];//根据号码前三位找到对应的信息
		if($prefix_info==null){
			$input['status']=3;
			$input['msg']=$num.'该号码没有匹配的运营商';
		}else{
			$input['company']=$prefix_info[0];//运营商
			$input['correct']=$prefix_info[1];//修正值
			
			self::do_match($input,$test);
			
		}
		
		return $input;
	}
	
	
	//测试代码
	public static function test(){
		$o='0123456789';
		$c=11111;
		$time=microtime(true);
		$arr1=array(180=>1,181=>1,189=>1,133=>1,153=>1,177=>1);
		for($i=0;$i<$c;$i++){
			$arr[$i]=array_rand($arr1).(\Common\My\data_rand::string($o,8));
		}
		dump('生成用时:'.(microtime(true)-$time));

// 		echo "<br/>正则的测试:";
// 		$time=microtime(true);
// 		for ($i=0,$j=0;$i<$c;$i++){
// 			$re=preg_match('/'.self::$reg['180%180'].'/',$arr[$i]);
// 			if($re){
				
// 			}else{
// 				$re=preg_match('/'.self::$reg['189%189'].'/',$arr[$i]);
// 			}
// 			$re && $j++;
// 		}
// 		dump('命中:'.$j.'个,用时:'.(microtime(true)-$time));
		
// 		echo "<br/>php的测试:";
// 		$time=microtime(true);
// 		for ($i=0,$j=0;$i<$c;$i++){
// 			$prefix=substr($arr[$i],0,3);
// 			$re=$prefix=='180' && $prefix==substr($arr[$i],-3);
// 			if($re){
			
// 			}else{
// 				$re=$prefix=='189' && $prefix==substr($arr[$i],-3);
// 			}
// 			$re && $j++;
// 		}
// 		dump('命中:'.$j.'个,用时:'.(microtime(true)-$time));
// 		exit();
		echo "<br/>正则的测试";
		$time=microtime(true);
		for ($i=0,$j=0;$i<$c;$i++){
			$re=self::entry($arr[$i],4);
			if($re['level']>7){
				$re["match_type"]!='AAAA[^4]' && dump($re);
				$j++;
			}
		}
		dump('命中:'.$j.'个,用时:'.(microtime(true)-$time));
		
// 		echo "<br/>php的测试";
// 		$time=microtime(true);
// 		for ($i=0,$j=0;$i<$c;$i++){
// 			$re=self::entry($arr[$i],4,'php');
// 			if($re['level']>5){
// 				$j++;
// 			}
// 		}
// 		dump('命中:'.$j.'个,用时:'.(microtime(true)-$time));
	}
	public static function test_save(&$input,$test){
		$len=3;
		$str=substr($input['num'],-$len);
		if($test=='reg'){
			preg_match('/'.self::$reg['AAA[^4689]'].'/',$str) && $input['match_type']='AAA[^4689]';
		}else{
			isset(self::$php_stable['AAA[^4689]'][$str]) && $input['match_type']='AAA[^4689]';
		}
		
		if($test=='reg'){
			$re=preg_match("/^{$input['prefix']}.+{$input['prefix']}$/",$input['num']);
		}else{
			$re=$input['prefix']==substr($input['num'], -3);
		}
		$re && $input['match_type']=$input["prefix"].'%'.$input["prefix"];
		
		//测试多个指定数字匹配
		if($test=='reg'){
			$stable=implode('|',self::$stable[4]);
			$re=preg_match("/$stable/",substr($input['num'], -4));
		}else{
			$re=in_array(substr($input['num'],-4),self::$stable[4]);
		}

	}
}