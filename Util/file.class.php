<?php
namespace Common\My;
/**
 * 文件读写
 * @author Administrator
 */
class file{
	private static $root='./Data/';
	public static function r($key,$test=1){
		$date=file_get_contents(self::$root.$key);
		switch ($test){
			case 1:
				$data   =   unserialize($date);
				break;
			case 2:
				$data   =   json_decode($date,true);
				break;
			default:
				eval('$data='.$date.';');
				break;
		}
		return $data;
	}	
	public static function w($key,$value,$test=1){
		switch ($test){
			case 1:
				$data   =   serialize($value);
				break;
			case 2:
				$data   =   json_encode($value);
				break;
			default:
				$data   =   var_export($value);
				break;
		}
		$handle = fopen(self::$root.$key,'w');
		fwrite($handle,$data);
		fclose($handle);
	}
}