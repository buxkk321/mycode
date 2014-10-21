<?php
namespace Common\My;
/**
 * 数据库查询相关功能
 * @author Administrator
 */
class db_query{
	private static $db;
	private static $db_type;
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
	 * @param $page 当前页数
	 * @param $query 在url中传递的查询条件编码后的字符串
	 * @param array $condition 查询条件,格式说明:
	 *  ['join']
	 *  ['where']
	 *  ['group']
	 *  ['having']
	 *  ['order']
	 *  ['page_size']:每页显示条数
	 * @return array 返回值说明
	 * 
	 * 
	 */
	public static function parseCondition($page_now,&$query,&$condition=array()){
		$re=array('sql_main'=>'','sql_right'=>'');
		//设置查询参数
		if($query==null){
			$query=base64_encode(json_encode($condition));
		}else{
			$condition=json_decode(base64_decode($query),true);
		}
		//拼中间部分的sql语句
		isset($condition['join']) && $re['sql_main'].=$condition['join'].' ';
		isset($condition['where']) && $re['sql_main'].=self::parseWhere($condition['where']).' ';
		if(isset($condition['group'])){
			$re['sql_main'].='group by '.$condition['group'].' ' ;
			isset($condition['having']) && $re['sql_main'].='having '.$condition['having'].' ';
		}
		//order条件
		isset($condition['order']) && $re['sql_right'].=$condition['order'].' ';
		//limit条件
		$page_now<1 && $page_now=1;//当前页码
		!isset($condition['page_size']) && $condition['page_size']=7;
		if($condition['page_size']>0){
			@$offset=((int)$page_now-1)*((int)$condition['page_size']);
			$re['sql_right'].="limit $offset,{$condition['page_size']}";
		}
		
		return $re;
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
		$default=array(
				'base_url'=>'',
				'page_now'=>1,
				'page_size'=>6,
				'visible_page'=>7,
				'total_rows'=>0,
				'%prev%'=>'上一页',
				'%next%'=>'下一页',
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
		$result['_page'].='<a href="'.$config['base_url'].'" class="jump_ok">确定</a>';
	
	}
	/**
	 * 生成列表表头或表单
	 * @param unknown_type $input
	 * @param unknown_type $cols
	 * @param unknown_type $except
	 * @param unknown_type $key_prefix
	 * @return multitype:
	 */
	public static function getGrids($cols_info,$cols=array(),$except=false,$key_prefix=''){
		$re=array();
		is_string($cols) && $cols=explode(',', $cols);
	
		if(empty($cols)){
			foreach ($cols_info as $kk=>$vv){
				$vv=array_change_key_case($vv);
				$re[$key_prefix.$vv['field']]['title']=$vv['comment'];
			}
		}else{
			$cols=array_flip($cols);
			foreach($cols_info as $vv){
				$vv=array_change_key_case($vv);
				isset($cols[$vv['field']])!=$except && $re[$key_prefix.$vv['field']]['title']=$vv['comment'];
			}
		}
		unset($vv);
	
		return $re;
	}
	
	public static function setdb($db,$db_type='tp',$cache_type='file'){
		self::$db=$db;
		self::$db_type=$db_type;
		return self;
	}
	
/******************以下方法需要在setdb后调用******************/
	
	private static function query($sql,$current=false){
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
	private static function insert($table,$data=array()){
		is_array($table) && $table = implode(',', $table);
		switch (strtolower(self::$db_type)){
			case 'tp':
				$re=self::$db->table($table)->add($data);
				break;
			case 'phalcon';
				$re=self::$db->insert($table,array_values($data),array_keys($data));
				$re!==false && $re=self::$db->lastInsertId();
				break;
			default:/*默认是原生pdo*/
// 				$cols = implode(',',array_keys($data));
// 				$re=self::$db->exec("insert {$table}({$cols}) values()");
		}
		return $re;
	}
	private static function update($table,$data=array(),$where){
		is_array($table) && $table = implode(',', $table);
		switch (strtolower(self::$db_type)){
			case 'tp':
				$re=self::$db->table($table)->where($where)->save($data);
				break;
			case 'phalcon';
				$re=self::$db->update($table,array_keys($data),array_values($data),$where);
				break;
			default:/*默认是原生pdo*/
// 				$set='';
// 				foreach($data as $k=>$v){
// 					$set[]=$k.'='.$v;
// 				}
// 				$set = implode(',',$set);
// 				$re=self::$db->exec("update $table set $set where $where");
		}
		return $re;
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
				'field'=>'*',
				'count_col'=>'*',
				'sql_main'=>'',
				'sql_right'=>'',
				'page'=>true
			);
		$config=(array)$config+$default;
		$table=is_string($config['table'])?$config['table']:'';
		$field=is_array($config['field'])?implode(',',$config['field']):$config['field'];
		
		//最终的查询
		$result['_list']=self::query("select $field from $table {$config['sql_main']} {$config['sql_right']}");
		$config['page'] && $result['_total_rows']=self::getTotalRows($table,$config['sql_main'],$config['count_col']);
	}
	public static function getTotalRows($table,$sql_main,$count_col='*'){
		return current(self::query("select count($count_col) ttr from $table $sql_main",true));
	}

	
	public static function getColumns($tn,$refresh){
		$cacheKey = $tn.'.cache';
		if (!$this->adminCache->exists($cacheKey) || $refresh) {
			$cols_info=self::query('SHOW FULL COLUMNS FROM '.$tn);
			$this->adminCache->save($cacheKey, $cols_info);
		}else{
			$cols_info=$this->adminCache->get($cacheKey);
		}
		return $cols_info;
	}
	
	/**
	 * post请求带文件上传的添加或修改数据的处理函数
	 * @param string $table 完整的表名
	 * @param array $field 要更新的字段信息,格式说明:
	 *  ['pk'] 主键名
	 *  ['filename'] 保存上传文件路劲的字段名
	 *  ['fileconfig'] 文件上传的配置数据
	 *  ['cols'] 直接从$_POST中获取的字段名,多个字段用英文逗号分隔，也可以是数组
	 * @param function $before_edit 在执行数据库操作前的处理(验证)函数,可选,参数说明:
	 * 	$result:上一步操作的结果数据,引用传值,不需要返回值,格式同最后的return,出错时需要设置 $result['status']=3;
	 * @param function $after_edit update操作成功之后时使用的自定义处理函数,可选,格式说明:
	 *  $result:之前的处理结果数据,引用传值
	 * @return array $result 操作结果
	 * 返回值说明:
	 *  ['data'] 待操作的数据
	 *  ['id']影响数据的主键值
	 *	['status'] 状态:
	 * 	 0:操作失败
	 *   1:操作成功
	 *   2:文件上传错误
	 *   3:执行数据库操作前的处理函数出错
	 *  ['msg'] 错误信息
	 */
	public static function post_edit($table,$field,$before_edit=null,$after_edit=null){
		$pk=isset($field['pk'])?$field['pk']:'id';
		$result=array(
			'status'=>1,
			'data'=>array_batch($field['cols'],$_POST,false)
		);
	
// 		if(!empty($field['filename'])){
// 			$info=Plugin\Upload::dealUploadFiles($field['filename'],$field['fileconfig']);
// 			if($info===false){
// 				$result['msg']=Plugin\Upload::$_upload_error;
// 				$result['status']=2;
// 			}elseif(!empty($info)){
// 				foreach($info as $kk=>$vv){
// 					if(is_numeric($kk)){
// 						//TODO 多文件上传
	
// 					}else{
// 						$result['data'][$kk]=$vv['savepath'].$vv['savename'];
// 					}
// 				}
// 			}
// 		}
	
		is_callable($before_edit) && $before_edit($result);
	
		if($result['status']==1){
			try {
				if($_POST[$pk]>0){
					self::update($table,$result['data'],$pk.'='.$_POST[$pk]);
					$result['id']=$_POST[$pk];
				}else{
					$result['id']=self::insert($table,$result['data']);
				}
				is_callable($after_edit) && $after_edit($result);
			} catch (\Exception $e) {
				$result['status']=0;
				if($_POST[$pk]>0){
					$result['msg']='更新出错:'.$e->__toString();
				}else{
					$result['msg']='添加出错:'.$e->__toString();
				}
			}
		}
	
		return $result;
	}
}