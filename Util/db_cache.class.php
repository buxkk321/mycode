<?php
namespace Common\My;
/**
 * 结合缓存的数据库操作类
 * @author Administrator
 */
class db_cache{
	protected static $db;
	protected static $cache;
	public static $last_errmsg;
	/**
	 * 
	 * @param unknown_type $db db操作类,需要实现query方法
	 * @param unknown_type $cache 缓存类,需要实现 exists、get、save三个方法
	 */
	public function __construct($db,$cache){
		self::setopt($db, $cache);
	}
	public static function setopt($db,$cache){
		self::$db=$db;
		self::$cache=$cache;
	}
	public static function getSundryInfo($refresh=false,$table,$name){
		$cacheKey=$table.'.'.$name.'.data';
		$cache=self::$cache;
		$db=self::$db;
		if (!$cache::exists($cacheKey) || $refresh) {
			$data=current($db::query('select * from '.$table.' where name="'.$name.'"'));
			if($data===false){
				return false;
			}
			$cache::save($cacheKey,$data);
		}else{
			$data=$cache::get($cacheKey);
		}
		return $data;
	}
}