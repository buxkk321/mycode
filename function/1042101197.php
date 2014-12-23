<?php
/**
 * uniqid加强版
 */
function uniqid_ex($key=''){
	static $i=0;
	return uniqid($key.(++$i));
}
/**
 * 使用异或运算交换两个变量的值
 */
function flipvar(&$var1,&$var2){
	$var1=$var1^$var2;
	$var2=$var1^$var2;
	$var1=$var1^$var2;
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
 * 数组任意位置插入新单元,保留键名
 * @param array $arr
 * @param int $offset
 * @param mixed $data
 * @return multitype:
 */
function array_insert(&$arr,$offset,$data,$preserve_keys=null){
	$arr=array_slice($arr, 0,$offset+1,$preserve_keys)+(array)$data+$arr;
}
/**
 * 单个文件上传检测
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
 * 修改一维数组的值
 * @param unknown $keys
 * @param unknown $arr
 * @param string $type
 * @param string $ignore_null
 */
function array_change_value($keys,&$arr,$type='int',$ignore_null=true){
	$func=array(
			'int'=>'intval',
			'float'=>'floatval',
			'string'=>'strval',
			'bool'=>'boolval'
			);
	if(isset($func[$type])){
		$func=$func[$type];
	}else{
		$func='intval';
	}
	if($keys==null){
		foreach($arr as $kk=>$vv){
			$arr[$kk]=$func($arr[$kk]);
		}
	}else{
		is_string($keys) && $keys=explode(',', $keys);
		foreach($keys as $vv){
			isset($arr[$vv]) && $arr[$vv]=$func($arr[$vv]);
		}
	}
}
/** TODO:简化
 * 一维数组批量取值
 * @param mixed $keys 指定的键名
 * @param mixed $arr 搜索的数组,或者回调函数
 * @param mixed $key_pre 返回数组的键名前缀或指定转换的数据类型
 * @return multitype:NULL string mixed unknown
 */
function array_batch($keys,&$arr,$key_pre=false,$unset_unit=false){
	$data=array();
	empty($arr) && $arr=$_POST;
	is_string($keys) && $keys=explode(',', $keys);
	$tag=true;
	$def='';
	switch(true){
		case ($key_pre==false):
			foreach($keys as $vv){
				if(isset($arr[$vv])){
					$data[$vv]=$arr[$vv];
					if($unset_unit) unset($arr[$vv]);
				}
			}
			break;
		case ($key_pre=='(int)'):
			foreach($keys as $vv){
				$data[$vv]=(int)$arr[$vv];
			}
			break;
		case ($key_pre=='(float)'):
			foreach($keys as $vv){
				$data[$vv]=(float)$arr[$vv];
			}
			break;
		default:
			foreach($keys as $vv){
				$data[$key_pre.$vv]=$arr[$vv].'';
			}
			break;
	}
	return $data;
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
 * 根据经纬度求距离
 * @param int $latitudeFrom 经度1
 * @param int $longitudeFrom 纬度1
 * @param int $latitudeTo 经度2
 * @param int $longitudeTo 纬度2
 * @return number
 */
function getDist($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo){
	$t=abs($longitudeFrom-$longitudeTo);
	$lonDelta=deg2rad($t<180?$t:360-$t);
	$latFrom = deg2rad(90-$latitudeFrom);
	$latTo = deg2rad(90-$latitudeTo);
	$angle=acos(cos($latFrom)*cos($latTo)+cos($lonDelta)*sin($latFrom)*sin($latTo));
	return $angle*6371000;
}
function curlGet($ch=null,$url){
	if($ch==null){
		$_ch=curl_init();
	}else{
		$_ch=$ch;
	}
	curl_setopt($_ch, CURLOPT_URL, $url); // 要访问的地址
	curl_setopt($_ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。
	curl_setopt($_ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
	curl_setopt($_ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
	curl_setopt($_ch, CURLOPT_TIMEOUT, 5); // 设置超时限制防止死循环
	curl_setopt($_ch, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
	curl_setopt($_ch, CURLOPT_HEADER, 0); // 不显示返回的Header区域内容
	$re = curl_exec($_ch); // 执行操作
	$ch==null && curl_close($_ch);
	
	return $re; // 返回数据
}
/**
 * 实时输出数据,可选择记录时间
 * @param unknown_type $data
 */
function output_realtime($data,$type='var_dump',$showtime=false,$restore=''){
	static $start=0;
	if($type=='echo'){
		eval($type.' $data;');
	}else{
		eval($type.'($data);');
	}
	if($showtime){
		!$start && $start=microtime(true);
		echo '####脚本已运行####'.(microtime(true)-$start).'秒';
	}
	if($restore){

	}
	ob_flush();
	flush();
}
function ecit($data=null){
	header("Content-type:text/html;charset=utf-8");
	$data=$data?var_export($data,true):time();
	echo $data;exit;
}


function error_response($errinfo){
	$response=array('status'=>0,'msg'=>'');
	if(is_int($errinfo)){
	}elseif(is_string($errinfo)){
		$response['msg']=$errinfo;
	}elseif(is_array($errinfo)){
		$response+=$errinfo;
	}
	echo json_encode($response);
	exit;
}