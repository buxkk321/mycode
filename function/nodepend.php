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
function get_unicode($str){
	return substr(json_encode($str),1,-1);
}
function unicode2utf8($str){
    return json_decode('"'.$str.'"',true);
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
 * 计算单条记录在指定结果集中的父级深度,结束id可选
 * @param number $start 开始数据主键值
 * @param number $end 结束数据主键值
 * @param array $data 原始结果集
 * @param array $data_index 主键到原始结果集数字索引的映射,如果原始结果集已经排序成由主键作为键名的数组,则该参数应该留空
 * @return number
 */
function count_parent($data,$data_index=array(),$start=1,$end=0,$parent='pid'){
    $re=0;
    if(empty($data_index)){
        $start=$data[$start][$parent];
        while(isset($data[$start]) && $start!=$end){
            $start=$data[$start][$parent];
            $re++;
        }
    }else{
        $start=$data[$data_index[$start]][$parent];
        while(isset($data[$start]) && $start!=$end){
            $start=$data[$data_index[$start]][$parent];
            $re++;
        }
    }
    return $re;
}
/**
 * 将输入数组重排成以唯一id或标识作为键名的数组
 * @param array $data 输入数据
 * @param int $plan 处理方式,数值说明:
 *  0:只排序不返回索引
 *  1:只返回索引不排序
 *  2:排序并返回索引
 * @param string $uk 标识值单元的键名,默认为'id'
 */
function set_index(&$data,$plan=0,$uk='id'){
	$temp=array();
	switch ($plan){
		case 0:
			foreach($data as $k=>$v){
				$temp[$v[$uk]]=$v;
			}
			$data=$temp;
			$temp=0;
			break;
		case 1:
			foreach($data as $k=>$v){
				$temp[$v[$uk]]=$k;
			}
			break;
		case 2:
			$temp2=array();
			foreach($data as $k=>$v){
				$temp2[$v[$uk]]=$v;
				$temp[$v[$uk]]=$k;
			}
			$data=$temp2;
			unset($temp2);
			break;
	}
	unset($v);
	return $temp;
}
/**
 * 按发散的分类树结构排序,返回根据分类层级排序过的一维数组,键名为主键值
 * @param unknown $data
 * @param unknown $data_index
 * @return array
 */
function tree_sort($data,$data_index=array(),$pk='id',$pid='pid',$sort='sort',$multicount=''){
    $tree_index='';
    $re=array();
    foreach($data as $v){
        if($tree_index==''){
            $tree_index='_'.$v[$pid].'_'.$v[$pk].'_';
        }else{
            $pos_pid=strpos($tree_index,'_'.$v[$pid].'_');/*找到父项id的位置*/
            $pos_id=strpos($tree_index,'_'.$v[$pk].'_');/*找到自己id的位置*/
            if($pos_pid===false){
                if($pos_id===false){/*1,既没找到父项id也没找到自己id,则将信息追加到索引字符串前面*/
                    $tree_index.=$v[$pid].'_'.$v[$pk].'_';
                }else{/*2,没找到父项id但找到自己的id,追加父项id到字符串*/
                    $tree_index=substr($tree_index,0,$pos_id).'_'.$v[$pid].substr($tree_index,$pos_id);
                }
            }else{
                $fix=strlen('_'.$v[$pid]);
                if($pos_id===false){/*3,找到父项id但没找到自己的id,在父项id之后插入'_自己id_'*/
                    $tree_index=substr($tree_index,0,$pos_pid+$fix).'_'.$v[$pk].substr($tree_index,$pos_pid+$fix);/**/
                }else{/*4,自己id和父项id同时找到,则保留最左边的父项id*/
                    if($pos_id<$pos_pid){
                        $tree_index=str_replace('_'.$v[$pid].'_','',$tree_index);/*去掉不正确的pid*/
                        $tree_index=substr($tree_index,0,$pos_id).'_'.$v[$pid].substr($tree_index,$pos_id);/*同步骤2*/
                    }
                }
            }
        }
    }

    $tree_index=explode('_', substr($tree_index,1,-1));
    foreach($tree_index as $vv){
        @$kp=empty($data_index)?$vv:$data_index[$vv];
        if(isset($data[$kp])){
            $re[$vv]=$data[$kp];
            $re[$vv]['deep']=count_parent($data,$data_index,$vv);
        }
    }

    return $re;
}
/**
 * 无限级分类,返回分好组的多维数组
 * @param array $data 原始数据
 * @param array $data_index 主键到原始数据数字索引的映射
 * @return array
 */
function build_tree($data,$data_index=array(),$parent='pid',$child='child',$end='0',$multicount='') {
	$tree=array();
	$parent=(string)$parent;
	$child=(string)$child;
	$multicount=(string)$multicount;
	$end=(string)$end;

	$s=empty($data_index);
	foreach ($data as $ke => $vo) {
		$kp=$s?$vo[$parent]:$data_index[$vo[$parent]];
		if($vo[$parent]==$end){
			$tree['_tree'][]=& $data[$ke];
		}elseif(isset($data[$kp])){
			$data[$kp][$child][] =& $data[$ke];
			if($multicount!=''){
				if(!isset($data[$ke][$multicount])) $data[$ke][$multicount]=1;
				$data[$kp][$multicount]+=$data[$ke][$multicount];
			}
		}else{
			$tree['_noparent'][]=& $data[$ke];
		}
	}

	return $tree;
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
function file_is_uploaded($filename){
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
 * php获取当前访问的完整url地址
 * @return string
 */
function GetCurUrl() {
    $url = 'http://';
    if (isset ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] == 'on') {
        $url = 'https://';
    }
    if ($_SERVER ['SERVER_PORT'] != '80') {
        $url .= $_SERVER ['HTTP_HOST'] . ':' . $_SERVER ['SERVER_PORT'] . $_SERVER ['REQUEST_URI'];
    } else {
        $url .= $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];
    }
    // 兼容后面的参数组装
    if (stripos ( $url, '?' ) === false) {
        $url .= '?t=' . $_SERVER ['REQUEST_TIME'];
    }
    return $url;
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

/**
 * 取得输出数据的表头
 * @param $cols_info
 * @param array $cols
 * @param int $except
 * @return array
 */
function getGrids($cols_info,$cols=array(),$except=0){
    $re=array();
    is_string($cols) && $cols=explode(',', $cols);
    if(empty($cols)){
        foreach ($cols_info as $kk=>$vv){
            $re[$vv['field']]=parseComment($vv['comment']);
            $re[$vv['field']]['col_type']=$vv['type'];
        }
    }else{
        $cols=array_flip($cols);
        switch ($except){
            case 1://排除给定字段的值
                foreach ($cols_info as $kk=>$vv){
                    if(!isset($cols[$vv['field']])){
                        $re[$vv['field']]=parseComment($vv['comment']);
                        $re[$vv['field']]['col_type']=$vv['type'];
                    }
                }
                break;
            case 2://将存在字段,才提取出来
                $inte=array();
                foreach ($cols_info as $kk=>$vv){
                    if(isset($cols[$vv['field']])){
                        $cols[$vv['field']]=parseComment($vv['comment']);
                        $cols[$vv['field']]['col_type']=$vv['type'];
                        $inte[$vv['field']]=1;
                    }
                }
                $re=array_intersect_key($cols, $inte);
                break;
            default://不管给定字段是否存在,都进行创建
                foreach ($cols as $kk=>&$vv){
                    $vv=parseComment($cols_info[$kk]['comment']);
                    $vv['col_type']=$cols_info[$kk]['type'];
                }
                $re=$cols;
        }
    }
    return $re;
}
/**
 * 解析字段的comment
 * @param $comment
 * @return array|mixed
 */
function parseComment($comment){
    $col_info=array('title'=>'');
    if($comment==''){
    }elseif(strpos($comment,'{')===false || strpos($comment,'}')===false){
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
function wp_file_get_contents($url,$option=null) {
    $context = stream_context_create ( array (
        'http' => array (
            'timeout' => 30
        )
    ) );// 超时时间，单位为秒

    return file_get_contents ( $url, 0, $context );
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
 * 实时输出数据功能初始化
 */
function opr_init($charset='utf-8'){
	$charset && header("Content-type: text/html; charset=utf-8");
	error_reporting(E_ALL);
	set_time_limit(0);
	ob_end_clean ();
	ob_start ();
	echo 
		'<script>
			var custom_scroll=0;
			window.onscroll = function(){
				console.log(this.scrollTop);
				console.log(this.scrollHeight);
				if(this.scrollHeight - this.scrollTop > 30){
					custom_scroll=1;
				}
			}
			function scrollBottom(){
				if(!custom_scroll){
					window.scrollTo(0,document.body.scrollHeight);
				}
			}
		</script>';
}
/**
 * 实时输出数据,可选择记录时间
 * @param unknown_type $data
 */
function opr($data,$type=null,$showtime=false,$restore=''){
	static $start=0;
	!$type && $type='var_dump';
	
	if($type=='echo'){
		echo $data;
	}else{
		$type($data);
	}
	if($showtime){
		!$start && $start=microtime(true);
		echo '####脚本已运行####'.(microtime(true)-$start).'秒';
	}
	if($restore){

	}
	echo '<br/>';
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
	exit(json_encode($response));
}

function success_response($sucinfo){
    if(is_string($sucinfo)){
        $sucinfo=array('msg'=>$sucinfo,'result'=>'');
    }
	$success=array(
			'status'=>1,
			'msg'=>$sucinfo['msg']?:'成功',
			'success_response'=>$sucinfo['result']
	);
	exit(json_encode($success));
}