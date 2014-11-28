<?php
namespace Common\My;
use Admin\Controller\PublicController;

/**
 * 文件缓存
 * @author Administrator
 */
class fc{
	private static $path=array('./Runtime/Data/',
								'./Data/');
	public static $enctype=2;//编码类型
	public static $compress=0;//压缩开关
	public function __construct($enctype){
		self::$enctype=$enctype;
	}
	public static function exists($key,$st=0){
		return is_file(self::$path[$st].$key);
	}
	public static function get($key,$st=0){
		$data=file_get_contents(self::$path[$st].$key);
		self::$compress && $data=gzuncompress($data);
		switch (self::$enctype){
			case 1:
				$data   =   json_decode($data,true);
				break;
			case 2:
				$data   =   unserialize($data);
				break;
			case 9:
				break;
		}
		return $data;
	}	
	public static function save($key,$value,$st=0){
		switch (self::$enctype){
			case 1:
				$data   =   json_encode($value);
				break;
			case 2:
				$data   =   serialize($value);
				break;
			case 9:
				$data   =   $value;
				break;
		}
		self::$compress && $data=gzcompress($data,1);
		$handle = fopen(self::$path[$st].$key,'w');
		fwrite($handle,$data);
		fclose($handle);
	}
	public static function countByDate_4bit($return_date=false){
		$date=date ( "Ymd" );
		$file='./Data/Count.txt';
		$arr=array('count'=>0);
		if(is_file($file)){
			$arr=json_decode(file_get_contents($file),true);
			if($arr['date']==$date){
				$arr['count']++;
			}else{
				$arr['date']=$date;
			}
		}
		$handle = fopen($file,'w');
		fwrite($handle,json_encode($arr));
		fclose($handle);
	
		$str=str_pad($arr['count'], 4,'0',STR_PAD_LEFT);
		$return_date && $str=$date.$str;
	
		return $str;
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