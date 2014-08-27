/**
 * uniqid加强版
 */
function uniqid_ex($key=''){
	static $i=0;
	return uniqid($key.(++$i));
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
 * 数组批量取值
 * @param unknown_type $keys
 * @param unknown_type $key_pre
 * @param unknown_type $arr
 * @return multitype:NULL string mixed unknown
 */
function array_batch($keys,$key_pre='',$arr='I'){
	$data=array();
	is_string($keys) && $keys=explode(',', $keys);
	switch (true){
		case (is_string($arr)):
			$num=func_num_args();
			if($num>3){
				$args=array_slice(func_get_args(), 3);
				foreach($keys as $vv){
					$data[$vv]=call_user_func_array($arr, array_merge(array($key_pre.$vv),$args));
				}
			}else{
				foreach($keys as $vv){
					$data[$key_pre.$vv]=$arr($vv);
				}
			}
			break;
		case (is_array($arr)):
			foreach($keys as $vv){
				if(isset($arr[$vv])){
					$data[$key_pre.$vv]=$arr[$vv];
				}else{
					$data[$key_pre.$vv]='';
				}
			}
			break;
	}
	return $data;
}
/**
 * 数组键值加字符串前缀
 * @param unknown_type $arr
 * @param unknown_type $prefix
 * @param unknown_type $index 若提供了index参数 则只有指定键名的单元会被替换
 * @return string
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
 * 数组任意位置插入新单元
 * @param array $arr
 * @param int $offset
 * @param mixed $data
 * @return multitype:
 */
function array_insert(&$arr,$offset,$data){
	$arr=array_merge(array_slice($arr, 0,$offset+1),(array)$data,$arr);
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
