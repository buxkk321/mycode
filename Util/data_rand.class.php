<?php
namespace Common\My;
class data_rand {
	/**
	 * 生成一个存在(或不存在)于指定列表中的随机数
	 * @param array $fliparr 数字列表,键名为待匹配的数字,键值不能为空
	 * @param int $min
	 * @param int $max
	 * @param bool $inarray true:返回存在于列表中的随机数,false:返回不在列表中的随机数
	 * @return int
	 */
	public static function num_in_array($fliparr,$min,$max,$inarray=true){
		$num=mt_rand($min, $max);
	
		isset($fliparr[$num])!=$inarray && $num=self::num_in_array($fliparr,$min,$max,$inarray);
		
		return $num;
	}
	/**
	 * 生成多个随机数,可选则是否有重复
	 * @param int $min
	 * @param int $max
	 * @param int $num 不能小于2,否则返回false
	 * @param bool $duplicate 是否允许重复值
	 * @return array 如果给定的num比min,max的差值还大,则返回false
	 */
	public static function multi_num($min,$max,$num=2,$duplicate=false){
		$re=array();
		if($num<2){return false;}
		if($duplicate){
			for($i=0;$i<$num;$i++){
				$re[$i]=mt_rand($min, $max);
			}
		}else{
			if($num>$max-$min+1){
				return false;
			}
			for($i=0;$i<$num;$i++){
				$re[self::num_in_array($re,$min,$max,false)]=1;
			}
			$re=array_keys($re);
		}
	
		return $re;
	}
	
	public static function string($mate,$length){
		$str='';
		$max=strlen($mate)-1;
		for($i=0;$i<$length;$i++){
			$str.=$mate[mt_rand(0,$max)];
		}
		return $str;
	}
}