<?php
/**
 * 数据XML编码
 * @param mixed  $data 数据
 * @param string $item 数字索引时的节点名称
 * @param string $id   数字索引key转换为的属性名
 * @return string
 */
function data_to_xml($data, $item='item', $id='id') {
	$xml = $attr = '';
	foreach ($data as $key => $val) {
		if(is_numeric($key)){
			$id && $attr = " {$id}=\"{$key}\"";
			$key  = $item;
		}
		$xml    .=  "<{$key}{$attr}>";
		$xml    .=  (is_array($val) || is_object($val)) ? data_to_xml($val, $item, $id) : $val;
		$xml    .=  "</{$key}>";
	}
	return $xml;
}
/**
 * XML编码
 * @param mixed $data 数据
 * @param string $root 根节点名
 * @param string $item 数字索引的子节点名
 * @param string $attr 根节点属性
 * @param string $id   数字索引子节点key转换的属性名
 * @param string $encoding 数据编码
 * @return string
 */
function xml_encode($data, $root='think', $item='item', $attr='', $id='id', $encoding='utf-8') {
	if(is_array($attr)){
		$_attr = array();
		foreach ($attr as $key => $value) {
			$_attr[] = "{$key}=\"{$value}\"";
		}
		$attr = implode(' ', $_attr);
	}
	$attr   = trim($attr);
	$attr   = empty($attr) ? '' : " {$attr}";
	$xml    = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
	$xml   .= "<{$root}{$attr}>";
	$xml   .= data_to_xml($data, $item, $id);
	$xml   .= "</{$root}>";
	return $xml;
}

/**
 * Ajax方式返回数据到客户端
 * @access protected
 * @param mixed $data 要返回的数据
 * @param String $type AJAX返回数据格式
 * @return void
 */
function ajax_return($data,$type='JSON') {
	
	switch (strtoupper($type)){
		case 'JSON' :
			// 返回JSON数据格式到客户端 包含状态信息
			header('Content-Type:application/json; charset=utf-8');
			exit(json_encode($data));
		case 'XML'  :
			// 返回xml格式数据
			header('Content-Type:text/xml; charset=utf-8');
			exit(xml_encode($data));
// 		case 'JSONP':
// 			// 返回JSON数据格式到客户端 包含状态信息
// 			header('Content-Type:application/json; charset=utf-8');
// 			$handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
// 			exit($handler.'('.json_encode($data).');');
// 		case 'EVAL' :
// 			// 返回可执行的js脚本
// 			header('Content-Type:text/html; charset=utf-8');
// 			exit($data);
// 		default     :
// 			// 用于扩展其他返回格式数据
// 			Hook::listen('ajax_return',$data);
	}
}
/**
 * 获取指定类的方法列表
 * @param mixed $str
 * @return multitype:
 */
function getMethods($str){
	$class  = new  \ReflectionClass ($str);
	return $class -> getMethods ();
}
/**
 * 获取指定类的属性列表
 * @param mixed $str
 * @return multitype:
 */
function getProperties($str,$filter=\ReflectionProperty::IS_PUBLIC){
	$class  = new  \ReflectionClass ($str);
	$p=$class -> getProperties($filter);
	if(is_object($str)){
		foreach ($p as $kk=>&$vv){
			$name=$vv->name;
			@$vv->value=$str->$name;
		}
	}
	return $p;
}

/**
 * 浏览器友好的变量输出
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为true
 * @return void|string
 */
function dump($var='', $echo=true, $label=null, $strict=0) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    switch ($strict){
    	case 0:
    		if (ini_get('html_errors')) {
    			$output = print_r($var, true);
    			$output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
    		} else {
    			$output = $label . print_r($var, true);
    		}
    		break;
    	case 1:
    		ob_start();
    		var_dump($var);
    		$output = ob_get_clean();
    		if (!extension_loaded('xdebug')) {
    			$output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
    			$output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
    		}
    		break;
    	case 2:
    		ob_start();
    		var_export($var);
    		$output = ob_get_clean();
    		if (!extension_loaded('xdebug')) {
    			$output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
    		}
    		break;
    }
    if ($echo) {
        echo($output);
        return null;
    }else
        return $output;
}


/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'...' : $slice;
}


/**************自定义的**************/
/**
 * 文本文件内容初始化
 * @param string $path 文件路径
 * @param string $str 初始内容
 * @param bool $over 是否强制初始化 默认false,即文件不存在才初始化
 */
function file_init($path,$str,$over=false){
	if($over===false){
		if(!file_exists($path)){
			goto action_1;
		}
	}else{
		goto action_1;
	}
	return;
	action_1:
	$handle = fopen($path,'w');
	fwrite($handle,$str);
	fclose($handle);
}
/**
 * 把配置数据写到php文件中
 * @param string $filepath	文件路径
 * @param array $data	待写入的数据,若为false,则不读取数据直接return
 * @param int $replace 替换模式
 * 0:不替换 直接追加
 * 1:替换掉指定键名的单元
 * 2:全部替换
 * @return bool
 */
function setConfig($filepath,$data=array(),$replace=1){
	if(!file_exists($filepath)){
		$handle = fopen($filepath,'w');
		fwrite($handle,'<?php
	return array();');
		fclose($handle);
		return false;
	}
	if($data===false){
		return 0;
	}
	$cfg=include $filepath;
	switch ($replace){
		case 0:
			$cfg=array_merge_recursive($cfg,$data);
			break;
		case 1:
			foreach($data as $vv){
				$index='$cfg';
				$count=count($vv)-1;
				for($i=0;$i<$count;$i++){
					$index.='["'.$vv[$i].'"]';
				}
				$index.='=end($vv);';
				eval($index);
			}
			break;
		case 2:
			$cfg=$data;
			break;
	}
	$cfg='<?php
	return '.var_export($cfg,true).';';
	$re=file_put_contents($filepath,$cfg);
	return $re;
}
/**
 * 将任意php数据输出成字符串
 * @param mixed $data
 */
function php_encode($data){
	$str='<?php
	return '.var_export($data,true).';';
	return $str;
}
/**
 * uniqid加强版
 */
function uniqid_ex($key=''){
	static $i=0;
	return uniqid($key.(++$i));
}
/**
 * 保证输入的数据变成数组
 */
function check_array($mixed,$delimiter=','){
	if(strpos($mixed,$delimiter)===false){
		if(empty($mixed)){
			$mixed=array();
		}else{
			$mixed=array_unique((array)$mixed);
		}
	}else{
		$mixed=explode($delimiter, $mixed);
	}
	return $mixed;
}

/**
 * 计算未达标的个数
 * @param unknown $now 本次完成的数量
 * @param unknown $all 之前完成的总数
 * @param unknown $target 每次需要完成的目标数量
 */
function count_lack($now,$all,$target){
	static $t=1;
	$re['all']=$all+$now;
	$re['disp']=$target*$t-$re['all'];
	$t++;
	return $re;
}

/**
 * 判断是否为null
 */
function decide_null($data,$def=''){
	is_null($data) && $data=$def;
	return $data;
}
/**
 * 文件上传检测
 * @param unknown_type $filename
 * @return boolean
 */
function check_file($filename){
	return !(
			$_FILES[$filename]==null || 
			$_FILES[$filename]['error']==4 || 
			(count($_FILES[$filename]['error'])===1 && $_FILES[$filename]['error'][0]===4)
			);
}
/**
* 数组批量取值
* @param mixed $keys 指定的键名
* @param mixed $arr 搜索的数组,或者回调函数
* @param mixed $key_pre 返回数组的键名前缀
* @return multitype:NULL string mixed unknown
*/
function array_batch($keys,$arr,$key_pre=''){
	$data=array();
	is_string($keys) && $keys=explode(',', $keys);
	switch (true){
		case (is_array($arr)):
			$tag=true;
// 			$def='';
// 			switch(true){
// 				case ($key_pre===false):
// 					$key_pre='';
// 					$tag=false;
// 					break;
// 				case ($key_pre=='(int)'):
// 					$key_pre='';
// 					$def=0;
// 					break;
// 				default:
// 					break;
// 			}
			if($key_pre===false){
				$key_pre='';
				$tag=false;
			}
			foreach($keys as $vv){
				if(isset($arr[$vv])){
					$data[$key_pre.$vv]=$arr[$vv];
				}else{
					$tag && $data[$key_pre.$vv]='';
				}
			}
			break;
		case (is_callable($arr)):
			$num=func_num_args();
			if($num>3){
				$args=array_slice(func_get_args(), 3);
				$data=call_user_func_array($arr, array_merge(array($keys,$key_pre),$args));
			}else{
				$data=$arr($keys,$key_pre);
			}
			break;
	}
	return $data;
}
/**
 * 数组键值加字符串前缀
 * @param array $arr
 * @param string $prefix
 * @param mixed $index 若提供了index参数 则只有指定键名的单元会被替换
 * @return array
 */
function value_add_prefix($arr,$prefix,$index=array()){
	if(empty($index)){
		foreach ($arr as $kk=>$vv){
			$vv!=null && $arr[$kk]=$prefix.$vv;
		}
	}else{
		is_string ( $index ) && $index = explode ( ',', $index );
		foreach ($index as $vv){
			$arr[$vv]!=null && $arr[$vv]=$prefix.$arr[$vv];
		}
		
	}
	
	return $arr;
}
/**
 * 获取注释中的文本
 * @param string $str
 * @param string $tag
 * @return unknown|string
 */
function getDocComment($str, $tag = ''){
	if(is_string($str)){
		if (empty($tag)){
			return $str;
		}
		$matches = array();
		preg_match("/{$tag}\s*:(.*)(\r\n|\r|\n)/U", $str, $matches);

		if (isset($matches[1])){
			return trim($matches[1]);
		}else{
			return '';
		}
	}else{
		return '';
	}
}
/**
 * 数组任意位置插入新单元
 * @param array $arr
 * @param int $offset
 * @param mixed $data
 * @return multitype:
 */
function array_insert(&$arr,$offset,$data){
	$arr=array_merge(array_slice($arr, 0,$offset+1),(array)$data,$arr);
}
function think_ucenter($str, $key = 'ThinkUCenter'){
    return '' === $str ? '' : md5(sha1($str) . $key);
}

function C($name=null, $value=null,$default=null) {
	static $_config = array();	
	// 无参数时获取所有
	if (empty($name)) {
		return $_config;
	}

	// 优先执行设置获取或赋值
	if (is_string($name)) {
		if (strpos($name, '.')===false) {
			if ($value===null){
				return isset($_config[$name]) ? $_config[$name] : $default;
			}else{
				$_config[$name] = $value;
				return;
			}
		}else{
			
			$name = explode('.', $name);
			$index='$_config';
			$count=count($name);
			for($i=0;$i<$count;$i++){
				$index.='["'.$name[$i].'"]';
			}
			if ($value===null){
				$index='$re='.$index.';';
				eval($index);
				return $re;
			}else{
				$index.='=$value;';
				eval($index);
				return;
			}
		}
	}
	//TODO 批量设置
	if (is_array($name)){
		$_config = array_merge($_config,$name);
		return;
	}
	return null; // 避免非法参数
}

function M(){
	$model=new \Phalcon\Mvc\Model();

}
