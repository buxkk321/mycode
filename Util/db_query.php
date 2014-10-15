<?php
namespace Common\My;
/**
 * 数据库查询相关功能
 * @author Administrator
 */
class db_query{
	public static $db;
	private static $operates = array('AND'=>1,'OR'=>2,'XOR'=>3);
	/**
	 * 
	 * @var string
	 */
	private static $sql_main='';
	/**
	 *
	 * @var string
	 */
	private static $sql_right='';
	private static function parseThinkWhere($key,$val){
		$whereStr   = '';
		switch($key) {
			case '_string':
				$whereStr = $val;
				break;
			case '_complex':
				$whereStr = substr(self::parseWhere($val),6);
				break;
			case '_query':
				break;
		}
		return '( '.$whereStr.' )';
	}
	/**
	 * @example 
	 *  $key='name'
	 *  $val=array(array(array('like "%asd%"'),array('!="asdasd"'),'_logic'=>'and'),'ccc','_logic'=>'or');
	 * @param unknown $key
	 * @param unknown $val
	 * @return string
	 */
	private static function parseWhereItem($key,$val){
		$whereStr = '';
		if(is_array($val)) {
			@$op=strtoupper((string)$val['_logic']);
			if(isset(self::$operates[$op])){
				$str   =  array();
				unset($val['_logic'],$val['_multi']);
				foreach ($val as $v){
					$str[]   ='('.self::parseWhereItem($key,$v).')';
					$whereStr .= '( '.implode(' '.$op.' ',$str).' )';
				}
			}else{
				$whereStr .=$key.' '.$val[0];
			}
		}elseif(is_numeric($val)){
			$whereStr .=$key.'='.$val;
		}else{
			$whereStr .=$key.'="'.(string)$val.'"';
		}
		return $whereStr;
	}
	/**
	 * 
	 * @example
	 *  $map['id&name&type']=array(array('in(5,7,8,15)'),array('not like "%bb%"'),'ccc','_multi'=>1);
	 * @param unknown $where
	 * @return string
	 */
	public static function parseWhere($where){
		$whereStr = '';
		if(is_string($where)) {
			$whereStr = $where;
		}else{
			@$operate=strtoupper((string)$where['_logic']);
			$operate=' '.(isset(self::$operates[$operate])?$operate:'AND').' '; 
			
			foreach ($where as $key=>$condition){
				if(is_numeric($key)){
					$key  = '_complex';
				}
				if(strpos($key,'_')===0) {
					// 解析特殊条件表达式
					$whereStr   .= self::parseThinkWhere($key,$condition);
				}else{
					// 查询字段的安全过滤
					// if(!preg_match('/^[A-Z_\|\&\-.a-z0-9\(\)\,]+$/',trim($key))){
					//     E(L('_EXPRESS_ERROR_').':'.$key);
					// }
					// 多条件支持
					$key    = trim($key);
					if(strpos($key,'|')) { // 支持使用|或&一次设置多个条件
						$cond_keys =  explode('|',$key);
						$op='OR';
					}elseif(strpos($key,'&')){
						$cond_keys =  explode('&',$key);
						$op='AND';
					}else{
						$op=false;
					}
					if($op){
						$str   =  array();
						$multi  = is_array($condition) && isset($condition['_multi']);
						foreach ($cond_keys as $index=>$cond_key){
							$cond_value =  $multi?$condition[$index]:$condition;
							$str[]   ='('.self::parseWhereItem($cond_key,$cond_value).')';
						}
						$whereStr .= '( '.implode(' '.$op.' ',$str).' )';
					}else{
						$whereStr .= '('.self::parseWhereItem($key,$condition).')';
					}
				}
				$whereStr .= $operate;
			}
			$whereStr = substr($whereStr,0,-strlen($operate));
		}
		return empty($whereStr)?'':' WHERE '.$whereStr;
	}
	/**
	 * 解析各种查询条件
	 * @param array $condition 条件说明
	 *  ['join']
	 *  ['where']
	 *  ['group']
	 *  ['having']
	 *  ['order']
	 *  ['page']:分页选项,格式为array(当前页码,每页显示条数)
	 * @return array 返回值说明
	 *   [0]:从join到having之间的sql
	 *   [1]:order和limit部分sql
	 */
	public static function parseCondition(&$condition=array()){
		//拼中间部分的sql语句
		isset($condition['join']) && $sql_main.=$condition['join'].' ';
		isset($condition['where']) && $sql_main.=self::parseWhere($condition['where']).' ';
		if(isset($condition['group'])){
			$sql_main.='group by '.$condition['group'].' ' ;
			isset($condition['having']) && $sql_main.='having '.$condition['having'].' ';
		}
		//order条件
		isset($condition['order']) && $sql_right.=$condition['order'].' ';
		//limit条件
		$condition['page'][0]<1 && $condition['page'][0]=1;
		!isset($condition['page'][1]) && $condition['page'][1]=7;
		if($condition['page'][1]>0){
			@$offset=((int)$condition['page'][0]-1)*((int)$condition['page'][1]);
			$sql_right.="limit $offset,{$condition['page'][1]}";
		}
		
		return array($sql_main,$sql_right);
	}
	
	public static function start($db,$db_type='tp'){
		switch (strtolower($db_type)){
			case 'tp':
				$re=self::$db->query($sql);
				break;
			case 'phalcon';
			$re=self::$db->fetchAll($sql,\Phalcon\Db::FETCH_ASSOC);
			break;
			default:/*默认是原生pdo*/
				$re=self::$db->query($sql,\PDO::FETCH_ASSOC);
		}
		return self;
	}
	protected static function query($sql,$db,$db_type='tp',$current=false){
		switch (strtolower(self::$db_type)){
			case 'tp':
				$re=self::$db->query($sql);
				break;
			case 'phalcon';
				$re=self::$db->fetchAll($sql,\Phalcon\Db::FETCH_ASSOC);
			break;
			default:/*默认是原生pdo*/
				$re=self::$db->query($sql,\PDO::FETCH_ASSOC);
		}
		if($current && is_array($re)) $re=current($re);
		return $re;
	}
	public static function getTotalRows($table,$sql_main,$count_col='*'){
		return self::query("select count($count_col) from $table $sql_main",true);
	}
	public static function getConfig($str,$varname='query'){
		if($str==null){
			$query=base64_encode(json_encode($condition));
		}else{
			$condition=json_decode(base64_decode($query),true);
		}
	}
	/**
	 * 获取戴分页的数据列表
	 * @param string $table 查询数据表
	 * @param mixed $field 查询字段
	 * @param array $condition 在url中传递的各种查询条件和用户配置值,格式说明:

	 * @param int $current 当前页码
	 * @param string $count_col 统计总记录数时使用的col_name,默认为'*'
	 * @return array 返回值说明:
	 *  ['_list']:数据列表
	 *  ['_total_rows']:总记录数
	 */
	public static function getList(&$result=array(),$config=array()){
		$default=array(
				'table'=>'',
				'field'=>'',
				'condition'=>array(),
				'count_col'=>'*');
		$config=(array)$config+$default;
		$table=is_string($config['table'])?$config['table']:'';
		$field=is_array($config['field'])?implode(',',$config['field']):$config['field'];
		$sql=self::parseCondition($config['condition']);
		
		//最终的查询
		
		$result['_list']=self::query("select $field from $table {$sql[0]} {$sql[1]}");
		
		$config['condition']['page'][1]>0 && $result['_total_rows']=self::getTotalRows($table,$sql[0],$config['count_col']);
		
		return $result;
	}
	/**
	 * 生成分页
	 * @param array $config 配置说明:
	    'base_url':除了页数外的其余部分url
		'page_now':当前页数
		'page_size':每页显示条数
		'visible_page':可见页数
		'total_rows':总记录数
		'%prev%':上一页按钮的文本值
		'%next%':下一页按钮的文本值
	 * @return array 返回值说明
	 * 	['_total_page']:总页数
	 *  ['_current']:当前页数
	 *  ['_page']:分页html代码
	 */
	public static function buildPage(&$result=array(),$config=array()){
		$re=array();
		$default=array(
				'base_url'=>'',
				'page_now'=>1,
				'page_size'=>6,
				'visible_page'=>7,
				'total_rows'=>0,
				'%prev%'=>'上一页',
				'%next%'=>'下一页'
				);
		$config=(array)$config+$default;
		
		$total_page=$config['page_size']>1?ceil($config['total_rows'] / $config['page_size']):1;
		$total_page<1 && $total_page=1;
		$result['_total_page']=$total_page;
	
		$page=$config['page_now']<1?1:$config['page_now'];
		$page>$total_page && $page=$total_page;
		$result['_current']=$page;
	
		$result['_page']='<div id="page_box">';
		$result['_page'].='<a href="'.$config['base_url'].($page-1).'" class="prev">'.$config['%prev%'].'</a>';
		$result['_page'].='<a href="'.$config['base_url'].'1" class="first">1</a>';
	
		if($total_page>$config['visible_page']){
			$i=$page-floor(($config['visible_page']-3)/2);
			$i<2 && $i=2;
			$i+$config['visible_page']-2>$total_page && $i=$total_page-($config['visible_page']-2);
			$i>2 && $result['_page'].='<span class="left">...</span>';
			for($j=0;$j<$config['visible_page']-2;$j++,$i++){
				$result['_page'].='<a href="'.$config['base_url'].$i.'" class="'.($page==$i?'current':'page').'">'.$i.'</a>';
			}
			$i<$total_page && $result['_page'].='<span class="right">...</span>';
		}else{
			for($i=2;$i<$total_page;$i++){
				$result['_page'].='<a href="'.$config['base_url'].$i.'" class="page '.($page==$i?'current':'').'">'.$i.'</a>';
			}
		}
	
		($total_page>1) && $result['_page'].='<a href="'.$config['base_url'].$total_page.'" class="end">'.$total_page.'</a>';
		$result['_page'].='<a href="'.$config['base_url'].($page+1).'" class="next">'.$config['%next%'].'</a>';
		$result['_page'].='<span class="jump">到第<input type="text" class="target_page" />页</span>';
		$result['_page'].='<a href="'.$config['base_url'].'" class="jump_ok"><input type="button" class="ok" value="确定"/></a>';

	}

}