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
	
	public static function get_data($config=array()){
		$default=array(
			'refresh'=>false,
			'table'=>'',
			'keyfix'=>'',
			'sql_right'=>'',
			'current'=>true
		);
		$config+=$default;
		if($config['keyfix']){
			$cacheKey=$config['table'].'.'.$config['keyfix'].'.data';
		}else{
			$cacheKey=$config['table'].'.data';
		}
		$cache=self::$cache;
		$db=self::$db;
		if (!$cache::exists($cacheKey) || $config['refresh']) {
			$data=$db::query('select * from '.$config['table'].' '.$config['sql_right']);
			if($data===false) return false;
			if($config['current'] && is_array($data)) $data=current($data);
			$cache::save($cacheKey,$data);
		}else{
			$data=$cache::get($cacheKey);
		}
		return $data;
	}

	
	/**
	 * 分类信息缓存
	 */
	public static function get_category($config=array()){
		$default=array(
			'refresh'=>false,
			'table'=>'',
			'keyfix'=>'',
			'sql_right'=>'',
			'uk'=>'id',
			'parent'=>'pid'
		);
		$config+=$default;
		$cacheKey=$config['table'].'.data';
		$cache=self::$cache;
		$db=self::$db;
		if (!$cache::exists($cacheKey) || $config['refresh']) {
			$data=array(
				'list'=>$db::query('select * from '.$config['table'].' '.$config['sql_right']),
				'index'=>array(),
				'tree'=>array()
			);
			if($data['list']===false) return false;
			$temp=array();
			foreach($data['list'] as $k=>$v){
				$data['index'][$v[$config['uk']]]=$k;
				$temp[$v[$config['parent']]][$v[$config['uk']]]=array();
			}
			foreach($temp as $k=>$v){
				$ppid=$data['list'][$data['index'][$k]][$config['parent']];
				if(isset($temp[$ppid])){
					$temp[$ppid][$k]=&$v;
				}else{
					$data['tree'][$k]=&$v;
				}
			}
			$cache::save($cacheKey,$data);
		}else{
			$data=$cache::get($cacheKey);
		}
		return $data;
	}
	
	/**
	 *
	 */
	public static function get_area_code($tn,$refresh=false,$range=null){
		$cacheKey=tn.'.data';
		$cache=self::$cache;
		$db=self::$db;
		if (!$cache::exists($cacheKey) || $refresh) {
			$addr['list']=$db::query('select code,title from '.$tn);
			foreach($addr['list'] as $k=>$v){
				$addr['index'][$v['code']]=$k;
			}
			$addr['tree']=array();
			foreach ($addr['list'] as $ke => $vo) {
				$kp=str_split($ke,2);
				if($kp[1].$kp[2]=='0000'){
					$addr['tree'][$ke]=array();
				}elseif($kp[2]=='00'){
					$addr['tree'][$kp[0].'0000'][$ke]=array();
				}else{
					$addr['tree'][$kp[0].'0000'][$kp[0].$kp[1].'00'][$ke]=1;
				}
			}
				
			$cache::save($cacheKey,$addr);
		}else{
			$addr=$cache::get($cacheKey);
		}
		
		if(is_numeric($range)){
			$p=str_pad($range,6,'0',STR_PAD_RIGHT);
			$addr['tree']=array($p=>$addr['tree'][$p]);
			foreach ($addr['list'] as $k=>$v){
				if(substr($k,0,2)!=$range){
					unset($addr['list'][$k]);
				}
			}
		}elseif(is_string($range)){
		}elseif(is_array($range)){
			$temp=array();
			foreach ($range as $v){
				$p=str_pad($v,6,'0',STR_PAD_RIGHT);
				$temp[$p]=$addr[$p];
			}
			$addr=$temp;
		}
		return $addr;
	}

	public static function get_columns($tn,$refresh=false){
		$cacheKey = $tn.'.cols';
		$cache=self::$cache;
		$db=self::$db;
		if (!$cache::exists($cacheKey) || $refresh) {
			$info=$db::query('SHOW FULL COLUMNS FROM '.$tn);
			$cols_info=array();
			foreach($info as $vv){
				$vv=array_change_key_case($vv);
				$cols_info[$vv['field']]=$vv;
			}
			$cache::save($cacheKey, $cols_info);
		}else{
			$cols_info=$cache::get($cacheKey);
		}
		return $cols_info;
	}
	public static function get_table_info($db,$tn,$refresh){
		$cacheKey = $tn.'.table';
		$cache=self::$cache;
		$db=self::$db;
		if (!$cache::exists($cacheKey) || $refresh) {
			$table_info=$db::query('select * from information_schema.TABLES where TABLE_SCHEMA="'.$db.'" and TABLE_NAME="'.$tn.'"');
			$cache::save($cacheKey, $table_info);
		}else{
			$table_info=$cache::get($cacheKey);
		}
		return $table_info;
	}
}