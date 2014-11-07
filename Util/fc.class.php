<?php
namespace Common\My;
use Admin\Controller\PublicController;

/**
 * 文件缓存
 * @author Administrator
 */
class fc{
	private static $root='./Runtime/Data/';
	public static $enctype=2;//编码类型
	public static $compress=0;//压缩开关
	public static function exists($key){
		return is_file(self::$root.$key);
	}
	public static function get($key){
		$data=file_get_contents(self::$root.$key);
		self::$compress && $data=gzuncompress($data);
		switch (self::$enctype){
			case 1:
				$data   =   json_decode($data,true);
				break;
			case 2:
				$data   =   unserialize($data);
				break;
		}
		return $data;
	}	
	public static function save($key,$value){
		switch (self::$enctype){
			case 1:
				$data   =   json_encode($value);
				break;
			case 2:
				$data   =   serialize($value);
				break;
		}
		self::$compress && $data=gzcompress($data,1);
		$handle = fopen(self::$root.$key,'w');
		fwrite($handle,$data);
		fclose($handle);
	}
	
	public static function test(){
		$o='0123456789';
		$arr1=array(180=>1,181=>1,189=>1,133=>1,153=>1,177=>1);
		for($i=0;$i<115;$i++){
			$arr[$i]=array_rand($arr1).(\Common\My\data_rand::string($o,8));
		}
		
		$c=11111;
		
		
		echo "<br/>json的测试";
		self::$enctype=1;
		$time=microtime(true);
		for ($i=0,$j=0;$i<$c;$i++){
			$re=self::save('test1.data',$arr);
		}
		dump('写:'.(microtime(true)-$time));
		
		$time=microtime(true);
		for ($i=0,$j=0;$i<$c;$i++){
			$re=self::get('test1.data');
		}
		dump('读:'.(microtime(true)-$time));
		
		
		echo "<br/>serialize的测试";
		self::$enctype=2;
		$time=microtime(true);
		for ($i=0,$j=0;$i<$c;$i++){
			$re=self::save('test2.data',$arr);
		}
		dump('写:'.(microtime(true)-$time));
		
		$time=microtime(true);
		for ($i=0,$j=0;$i<$c;$i++){
			$re=self::get('test2.data');
		}
		dump('读:'.(microtime(true)-$time));
	}
}