<?php
namespace Common\My;
/**
 * 缓存
 * @author Administrator
 */
class cache{
	private static $cache_type='file';
	private static $root='./Data/';
	public static $test=1;
	public static function exists($key){
		return file_exists(self::$root.$key);
	}
	public static function get($key){
		$date=file_get_contents(self::$root.$key);
		switch (self::$test){
			case 1:
				$data   =   unserialize($date);
				break;
			case 2:
				$data   =   json_decode($date,true);
				break;
		}
		return $data;
	}	
	public static function save($key,$value){
		switch (self::$test){
			case 1:
				$data   =   serialize($value);
				break;
			case 2:
				$data   =   json_encode($value);
				break;
		}
		$handle = fopen(self::$root.$key,'w');
		fwrite($handle,$data);
		fclose($handle);
	}

}