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
	private static $sql_main='';
	private static $sql_right='';

	public static function parseBetween($str,$delimiter=','){
		$re=array();
		if(isset($str)){
			if(strpos($str,$delimiter)===false){
				$re=$str;
			}else{
				$arr=explode($delimiter,$str);
				$arr[0]=(int)$arr[0];
				$arr[1]=(int)$arr[1];
				if($arr[0]>$arr[1]){
					if(is_numeric($arr[1])){
						$re=array("between {$arr[1]} and {$arr[0]}");
					}else{
						$re=array('>='.$arr[0]);
					}
				}elseif($arr[0]<$arr[1]){
					if(is_numeric($arr[0])){
						$re=array("between {$arr[0]} and {$arr[1]}");
					}else{
						$re=array('<='.$arr[1]);
					}
				}else{
					$re=(int)$arr[0];
				}
			}
		}
		return $re;
	}
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
	 *  result:(name like "%asd%" and name !="asdasd") or name="ccc"
	 * @param unknown $key
	 * @param unknown $val
	 * @return string
	 */
	private static function parseWhereItem($key,$val){
		$whereStr = '';
		if(is_array($val)) {
			if(count($val)==1){
				$whereStr .=$key.' '.$val[0];
			}else{
				@$op=strtoupper((string)$val['_logic']);
				!isset(self::$operates[$op]) && $op='AND';
				unset($val['_logic'],$val['_multi']);
				$str   =  array();
				foreach ($val as $v){
					$str[]   ='('.self::parseWhereItem($key,$v).')';
				}
				$whereStr .= '( '.implode(' '.$op.' ',$str).' )';
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
					if(strpos($key,'|')) { // 支持使用|或&一次对多个字段设置条件
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

	public static function parseComment($comment){
		$col_info=array();
		if(strpos($comment,'{')===false || strpos($comment,'}')===false){
			$po=strpos($comment,'(');$end=strrpos($comment,')');
			if($po!==false && $end!==false && $po<$end){
				$col_info['tip']=substr($comment,$po,$end-$po+1);
				$comment=substr($comment,0,$po).substr($comment,$end+1);
			}
			$po=strpos($comment,'|');
			if($po===false){
				$col_info['title']=$comment;
			}else{
				$col_info['title']=substr($comment,0,$po);
				$col_info['title_suffix']=substr($comment,$po+1);
			}
		}else{
			$col_info=json_decode($comment,true);
		}
		
		return $col_info;
	}
	

	
	/**
	 * 生成列表表头或表单
	 * @param array $input
	 * @param mixed $cols
	 * @param bool $except
	 * @param string $separ
	 * @return multitype:
	 */
	public static function getGrids($cols_info,$cols=array(),$except=false){
		$re=array();
		is_string($cols) && $cols=explode(',', $cols);
	
		if(empty($cols)){
			foreach ($cols_info as $kk=>$vv){
				$re[$vv['field']]=self::parseComment($vv['comment']);
				$re[$vv['field']]['col_type']=$vv['type'];
			}
		}else{
			$cols=array_flip($cols);
			$inte=array();
			foreach ($cols_info as $kk=>$vv){
				if(isset($cols[$vv['field']])!=$except){
					$cols[$vv['field']]=self::parseComment($vv['comment']);
					$cols[$vv['field']]['col_type']=$vv['type'];
					$inte[$vv['field']]=1;
				}
			}
			$re=array_intersect_key($cols, $inte);
		}
		unset($vv);
		return $re;
	}
	/**
	 * 取得需要输出的表头信息(多表)
	 */
	public static function getMultiGrids($cols_info_all,$cols=''){
		$cols_map=$re=array();
		is_string($cols) && $cols=explode(',', $cols);
		foreach ($cols as $vv){
			$pos=strpos($vv,' ');
			if($pos===false){//没有设置别名
				$tt=explode('.',$vv);//取得带表名的字段名
				$key=$tt[1];
			}else{
				$tt=explode('.',substr($vv,0,$pos));//取得带表名的字段名
				$key=trim(substr($vv,$pos));//别名设为映射键名
			}
			$cols_map[$key]=array(
					'tn'=>$tt[0],
					'cn'=>$tt[1],
					);//保存映射关系
		}
		foreach ($cols_map as $kk=>$vv){
			if(isset($cols_info_all[$vv['tn']][$vv['cn']])){
				$re[$kk]=self::parseComment($cols_info_all[$vv['tn']][$vv['cn']]['comment']);
			}
		}
		return $re;
	}
	/**
	 * 获取各种查询条件
	 * @param string $query 在url中传递的查询条件编码后的字符串
	 * @param array $condition 查询条件,格式说明:
	 * @param array $subject 存放sql语句的数组
	 *  ['join']
	 *  ['where']
	 *  ['group']
	 *  ['having']
	 *  ['order']
	 *  ['page_size']:每页显示条数,默认7页,如果设为0,则不解析url中的参数
	 * @return array 返回值说明
	 */
	public static function setCondition(&$subject,&$condition=array(),&$query=''){
		$subject=array('sql_main'=>'','sql_right'=>'');
		!isset($condition['page_size']) && $condition['page_size']=7;
		if($condition['page_size']>0){
			//准备或接受在url中传递的数据
			if($query==null){
				$query=base64_encode(json_encode($condition));
			}else{
				$condition=json_decode(base64_decode($query),true);
			}
		}
	
		//拼中间部分的sql语句
		if(isset($condition['join'])){
			$join=is_array($condition['join'])?implode(' ',$condition['join']):(string)$condition['join'];
			$subject['sql_main'].=$join.' ';
		}
		isset($condition['where']) && $subject['sql_main'].=self::parseWhere($condition['where']).' ';
		if(isset($condition['group'])){
			$subject['sql_main'].='group by '.$condition['group'].' ' ;
			isset($condition['having']) && $subject['sql_main'].='having '.$condition['having'].' ';
		}
		//order条件
		isset($condition['order']) && $subject['sql_right'].='order by '.$condition['order'].' ';
	}
	/**
	 * 自动完成、筛选、转换
	 * @param array $data 输入数据
	 * @param array $rules 完成规则,格式说明:
	 * array(
	 *  '字段名'=>array(
	 *   'when'=>'执行时刻',// 默认为新增的时候自动填充
	 *   'rule'=>'填充内容',
	 *   'parse'=>'内容解析方式',
	 *   'param'=>'额外的参数')
	 * );
	 * @param string $type 执行时刻,只有当该值和规则的第一个键值相等 或等于'both'时,才会执行自动完成
	 */
	public static function auto_pad(&$data,$rules,$type='insert'){
		foreach($rules as $col_name=>$auto){
			if( $type == $auto['when'] || $auto['when'] == 'both') {
				empty($auto['parse']) && $auto['parse'] = 'string';//内容解析方式默认为string
				switch(trim($auto['parse'])) {//根据解析方式执行替换
					case 'function':
						$args = (array)$auto['param'];
						if(isset($data[$col_name])) {
							array_unshift($args,$data[$col_name]);//字段的值作为第一个参数
							if(call_user_func_array($auto['rule'], $args)===false) unset($data[$col_name]);
						}else{
							unset($data[$col_name]);
						}
						break;
					case 'field':    // 用其它字段的值进行填充
						$data[$col_name] = $data[$auto['rule']];
						break;
					case 'ignore': // 忽略指定的值,全等匹配
						if(@$data[$col_name]===$auto['rule']) unset($data[$col_name]);
						break;
					case 'now':
						$data[$col_name]=$_SERVER['REQUEST_TIME'];
						break;
					case 'string':
						$data[$col_name] = (string)$auto['rule'];
						break;
					case 'int':
						$data[$col_name] = (int)$auto['rule'];
						break;
					case 'float':
						$data[$col_name] = (float)$auto['rule'];
						break;
					default://默认不进行解析直接赋值
						$data[$col_name] = $auto['rule'];
				}
			}
		}
	}
	/**
	 * 自动验证
	 * @param array $data 输入数据
	 * @param array $rules 验证规则,格式说明:
	 * array(
	 *  '字段名'=>array(
	 *   'when'=>'验证时刻',
	 *   'rule'=>'规则内容',
	 *   'parse'=>'内容解析方式',
	 *   'param'=>'额外的参数',
	 *   'msg'=>'验证失败时的提示信息',
	 *  )
	 * );
	 * @return boolean
	 */
	public static function auto_validate($data,$rules,$when='both'){
		if(isset($rules)) { // 如果设置了数据自动验证则进行数据验证
			$re['msg']='';
			foreach($rules as $col_name=>$auto) {
				empty($auto['when']) && $auto['parse']='both';
				if($auto['when'] === $when || $auto['when'] == 'both') {
					empty($auto['parse']) && $auto['parse']='reg';
					switch(strtolower(trim($auto['parse']))) {
						case 'eq': // 验证是否等于某个值
							$re['status']=$data[$col_name] == $auto['rule'];
							break;
						case 'neq': // 验证是否不等于某个值
							$re['status']=$data[$col_name] != $auto['rule'];
							break;
						case 'in': // 是否等于多个值中的某一个 逗号分隔字符串或者数组
							$range = array_flip(strpos($auto['rule'],',')===false?((array)$auto['rule']):explode(',',$auto['rule']));
							$re['status']=isset($range[$data[$col_name]]);
							break;
						case 'notin':// 是否不等于所有给定的值
							$range = array_flip(strpos($auto['rule'],',')===false?((array)$auto['rule']):explode(',',$auto['rule']));
							$re['status']=!isset($range[$data[$col_name]]);
							break;
						case 'field': // 验证两个字段是否相同
							$re['status']=$data[$col_name] == $data[$auto['rule']];
							break;
						case 'reg':
							$re['status']=preg_match($auto['rule'],$data[$col_name]);
							break;
						case 'between': // 验证是否在某个范围
							if (strpos($auto['rule'],',')===false){
								$min    =    $auto['rule'][0];
								$max    =    $auto['rule'][1];
							}else{
								list($min,$max)   =  explode(',',$auto['rule']);
							}
							$re['status']=$data[$col_name]>=$min && $data[$col_name]<=$max;
							break;
						case 'notbetween': // 验证是否不在某个范围
							if (strpos($auto['rule'],',')===false){
								$min    =    $auto['rule'][0];
								$max    =    $auto['rule'][1];
							}else{
								list($min,$max)   =  explode(',',$auto['rule']);
							}
							$re['status']=$data[$col_name]<$min || $data[$col_name]>$max;
							break;
						case 'length': // 验证长度
							$length  =  mb_strlen($data[$col_name],'utf-8'); // 当前数据长度
							if(strpos($auto['rule'],',')===false) { // 长度区间
								$re['status']= $length == $auto['rule'];
							}else{// 指定长度
								list($min,$max)   =  explode(',',$auto['rule']);
								$re['status']= $length >= $min && $length <= $max;
							}
							break;
						case 'function':// 使用函数进行验证
							$args = (array)$auto['param'];
							if(isset($data[$col_name])) {
								array_unshift($args,$data[$col_name]);//字段的值作为第一个参数
								$re['status']=(call_user_func_array($auto['rule'], $args)===false);
							}else{
								$re['status']=0;
							}
							break;
						
							// 						case 'ip_allow': // IP 操作许可验证
							// 							return in_array(get_client_ip(),explode(',',$rule));
							// 						case 'ip_deny': // IP 操作禁止验证
							// 							return !in_array(get_client_ip(),explode(',',$rule));
						default:
							$re['msg']='没有可用的验证规则';
							return false;
					}
				}
				if(!$re['status']){
					$re['msg']=$auto['msg'];
					return false;
				}
			}
		}
		return true;
	}
	

	public function __construct($db,$db_type='tp'){
		self::setdb($db,$db_type);
	}
	public static function setdb($db,$db_type='tp'){
		self::$db=$db;
		self::$db_type=$db_type;
	}
	
/******************以下方法需要在setdb后调用******************/
	
	public static function query($sql,$current=false){
		switch (strtolower(self::$db_type)){
			case 'tp':
				$re=self::$db->query($sql);
				break;
			case 'phalcon';
				$re=self::$db->fetchAll($sql,\Phalcon\Db::FETCH_ASSOC);
				$re===false && $re=array();
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
	 * post请求带文件上传的添加或修改数据的处理函数
	 * @param array $config 配置项格式说明:
	 *  ['pk'] 主键名
	 *  ['filename'] 保存上传文件路劲的字段名
	 *  ['fileconfig'] 文件上传的配置数据
	 *  ['cols'] 直接从$_POST中获取的字段名,多个字段用英文逗号分隔，也可以是数组
	 *  ['table'] 完整的表名
	 * @param function $before_edit 在执行数据库操作前的处理(验证)函数,可选,参数说明:
	 * 	$result:上一步操作的结果数据,引用传值,不需要返回值,格式同最后的return,出错时需要设置 $result['status']=3;
	 * @param function $after_edit update操作成功之后时使用的自定义处理函数,可选,格式说明:
	 *  $result:之前的处理结果数据,引用传值
	 * @return array $result 操作结果
	 * 返回值说明:
	 *	['status'] 状态:
	 * 	 0:操作失败
	 *   1:操作成功
	 *   2:文件上传错误
	 *   3:执行数据库操作前的处理函数出错
	 *  ['msg'] 错误信息
	 *  ['id']影响数据的主键值
	 *  ['data'] 待操作的数据
	 */
	public static function post_edit($config,$before_edit=null,$after_edit=null){
		$default=array(
				'pk'=>'id',
				'cols'=>array(),
				'table'=>'',
		);
		$config=(array)$config+$default;
		is_string($config['cols']) && $config['cols']=explode(',', $config['cols']);
		$pk=$config['pk'];
		$result=array('status'=>1);
		foreach($config['cols'] as $vv){
			if(isset($_POST[$vv])){
				$result['data'][$vv]=$_POST[$vv];
			}
		}
		
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
	
		if($result['status']!=1){
			return $result;
		}
		
		try {
			if($_POST[$pk]>0){
				self::update($config['table'],$result['data'],$pk.'='.$_POST[$pk]);
				$result['id']=$_POST[$pk];
			}else{
				$result['id']=self::insert($config['table'],$result['data']);
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
		
		if($result['status']==1 && !empty($result['del_list'])){
			foreach ($result['del_list'] as $vv){
				unlink($vv);
			}
		}
	
		return $result;
	}
	public static function getTotalRows($table,$sql_main,$count_col='*'){
		$sql="select count($count_col) ttr from $table $sql_main";
		return current(self::query($sql,true));
	}

	/**
	 * 生成分页,可以在getList之前执行
	 * @param array $config 配置说明:
	 'base_url':除了页数外的其余部分url
	 'page_now':当前页数
	 'page_size':每页显示条数
	 'visible_page':可见页数
	 'total_rows':总记录数
	 '%prev%':上一页按钮的文本值
	 '%next%':下一页按钮的文本值
	 * @param array $subject 保存sql语句的数组
	 * @return string 分页html代码
	 */
	public static function buildPage(&$subject,&$config=array()){
		$default=array(
				'base_url'=>'',
				'page_size'=>6,
				'visible_page'=>7,
				'%prev%'=>'上一页',
				'%next%'=>'下一页',
				'_total_rows'=>0,
				'_current'=>1,
		);
		$config=(array)$config+$default;
		/* 计算总页数 */
		$total_page=$config['page_size']>1?ceil($config['_total_rows'] / $config['page_size']):1;
		$total_page<1 && $total_page=1;
		$config['_total_page']=$total_page;
		/* 计算出合法的页码 */
		$config['_current']<1 && $config['_current']=1;
		$config['_current']>$total_page && $config['_current']=$total_page;
		$page=$config['_current'];
		/* 设置limit条件 */
		$subject['sql_right']=(string)$subject['sql_right'].' limit '.($page-1)*$config['page_size'].','.$config['page_size'].' ';
		/* 下面开始制作分页html代码 */
		$result='<div id="page_box">';
		$result.='<a href="'.$config['base_url'].($page-1).'" class="prev">'.$config['%prev%'].'</a>';
		$result.='<a href="'.$config['base_url'].'1" class="first">1</a>';
		if($total_page>$config['visible_page']){
			$i=$page-floor(($config['visible_page']-3)/2);
			$i<2 && $i=2;
			$i+$config['visible_page']-2>$total_page && $i=$total_page-($config['visible_page']-2);
			$i>2 && $result.='<span class="left">...</span>';
			for($j=0;$j<$config['visible_page']-2;$j++,$i++){
				$result.='<a href="'.$config['base_url'].$i.'" class="'.($page==$i?'current':'page').'">'.$i.'</a>';
			}
			$i<$total_page && $result.='<span class="right">...</span>';
		}else{
			for($i=2;$i<$total_page;$i++){
				$result.='<a href="'.$config['base_url'].$i.'" class="page '.($page==$i?'current':'').'">'.$i.'</a>';
			}
		}
		($total_page>1) && $result.='<a href="'.$config['base_url'].$total_page.'" class="end">'.$total_page.'</a>';
		$result.='<a href="'.$config['base_url'].($page+1).'" class="next">'.$config['%next%'].'</a>';
		$result.='<span class="jump">到第<input type="text" class="target_page" />页</span>';
		$result.='<a href="'.$config['base_url'].'" class="jump_ok">确定</a>';
	
		return $result;
	}
	/**
	 * 解析limit条件并生成分页所需的参数
	 * @param array $subject 存放sql语句的数组
	 * @param array $config 相关配置,必要格式说明
	 *  ['page_size'] 每页显示条数
	 *  ['_current'] 当前页码
	 *  ['_total_rows'] 总记录数
	 * 函数执行完成后会追加一个:
	 *  ['_total_page'] 总页数
	 */
	public static function setPage(&$subject,&$config){
		//计算总页数
		$total_page=$config['page_size']>1?ceil($config['_total_rows'] / $config['page_size']):1;
		$total_page<1 && $total_page=1;
		$config['_total_page']=$total_page;
		//当前页码
		$config['_current']<1 && $config['_current']=1;
		$config['_current']>$total_page && $config['_current']=$total_page;
		//limit条件
		$subject['sql_right']=(string)$subject['sql_right'].' limit '.($config['_current']-1)*$config['page_size'].','.$config['page_size'].' ';
	}
	/**
	 * 最后一步获取数据列表
	 * @param string $table 查询数据表
	 * @param mixed $field 查询字段
	 * @return array
	 */
	public static function last_get_list($config=array(),$return_sql=false){
		$default=array(
				'table'=>'',
				'field'=>'*',
				'sql_main'=>'',
				'sql_right'=>''
			);
		$config=(array)$config+$default;
		$table=is_string($config['table'])?$config['table']:'';
		$field=is_array($config['field'])?implode(',',$config['field']):$config['field'];
		
		//最终的查询
		$sql='select '.$field.' from '.$table.' '.$config['sql_main'].' '.$config['sql_right'];
		if($return_sql){
			return $sql;
		}else{
			return self::query($sql);
		}
	}
	/**
	 * 获取数据列表
	 * @param array $config 配置项说明:
	 *  'base_url':不带参数的url,
	 *  'query_var':查询条件字符串变量名,默认'query',
	 *  'query_str':当前的查询条件字符串,
	 *  'page_var':分页参数名,默认'p',
	 *  'page_now':当前页数,
	 *  'table':sql语句中from和join之间的内容,
	 *  'field':sql语句中select和from之间的内容,默认'*',
	 *  'condition':查询条件数组,格式参考setCondition方法,
	 * @return array
	 */
	public static function getList($config=array()){
		$re=$sql=array();
		$default=array(
				'base_url'=>'',
				'query_var'=>'query',
				'query_str'=>$_GET['query'],
				'page_var'=>'p',
				'page_now'=>$_GET['p'],
				'table'=>'',
				'field'=>'*',
				'condition'=>array()
		);
		$config+=$default;
		//TODO:table参数的扩展
		!is_string($config['table']) && $config['table']='';
		
		is_array($config['field']) && $config['field']=implode(',',$config['field']);
		
		//分析整理查询条件
		db_query::setCondition($sql,$config['condition'],$config['query_str']);
		$re['_where']=(array)$config['condition']['where'];
		if($config['condition']['page_size']>0){
			//limit条件和分页
			$temp=array(
					'base_url'=>$config['base_url'].'?'.$config['query_var'].'='.$config['query_str'].'&'.$config['page_var'].'=',
					'page_size'=>$config['condition']['page_size'],
					'_current'=>$config['page_now'],
					'_total_rows'=>db_query::getTotalRows($config['table'], $sql['sql_main'])
			);
			$re['_page']=db_query::buildPage($sql,$temp);
			$re['_current']=$temp['_current'];
		}
		//最终的查询
		$re['_sql']='select '.$config['field'].' from '.$config['table'].' '.$sql['sql_main'].' '.$sql['sql_right'];
		$re['_list']=self::query($re['_sql']);
		
		return $re;
	}
}