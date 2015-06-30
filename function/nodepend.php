<?php
/**
 * uniqid加强版
 */
function uniqid_ex($key=''){
	static $i=0;
	return uniqid($key.(++$i));
}

/**
 * GUID：即Globally Unique Identifier（全球唯一标识符）
 *       也称作 UUID(Universally Unique IDentifier) 。
 * GUID  是一个通过特定算法产生的二进制长度为128位的数字标识符，
 *       用于指示产品的唯一性。GUID 主要用于在拥有多个节点、
 *       多台计算机的网络或系统中，分配必须具有唯一性的标识符。
 *
 * @return string
 */
function create_guid() {

    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    return $charid;
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
 * 使用gzcompress压缩字符串后再进行base64编码
 * @param $str
 * @return string
 */
function gz_base64_encode($str){
    return base64_encode(gzcompress($str));
}

/**
 * base64解码后使用gzuncompress解压
 * @param $str
 * @return string
 */
function gz_base64_decode($str){
    return (string)gzuncompress(base64_decode($str));
}
function get_unicode($str){
    $str=mb_convert_encoding($str,'utf-8',mb_detect_encoding($str));
	return substr(json_encode($str),1,-1);
}
function unicode2utf8($str){
    return json_decode('"'.$str.'"',true);
}


function set_header($param=null,$content_type=1){
    switch ($content_type){
        case 1:
            !$param & $param='utf-8';
            header("Content-type: text/html; charset=".$param);
            break;
        default:

    }
}
function url_pad($url,$pad,$fix=0){
    if(!$url){
        return $fix?:'';
    }
    $parse=explode(':',$url);
    switch ($parse[0]){
        case 'http':
        case 'https':
            break;
        case 'fa':
            break;
        default:
            if($fix>0){
                $url=$pad.substr($url,$fix);
            }else{
                $url=$pad.$url;
            }
    }
    return $url;
}

function countByDate($length=4,$return_date=false,$file='./Data/Count.txt'){
    $date=date ( "Ymd" );
    $arr=array('count'=>0);
    if($length>=0 && is_file($file)){
        $arr=json_decode(file_get_contents($file),true);
        if($arr['date']==$date){
            $arr['date']=$date;
        }
        $arr['count']++;
    }
    $handle = fopen($file,'w');
    fwrite($handle,json_encode($arr));
    fclose($handle);

    $length>=0 && $str=str_pad($arr['count'], $length,'0',STR_PAD_LEFT);
    $return_date && $str=$date.$str;

    return $str;
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

/** TODO:简化
 * 根据给定的键名,从数组中获取一部分单元
 * @param mixed $keys 指定的键名
 * @param mixed $arr 搜索的数组,或者回调函数
 * @param mixed $type 返回数组的键名前缀或指定转换的数据类型
 * @param mixed $unset_unit 是否销毁原数组中取过的单元
 * @return multitype:NULL string mixed unknown
 */
function array_select($keys,&$arr,$type=false,$unset_unit=false){
    $data=array();
    is_string($keys) && $keys=explode(',',$keys);
    if(is_array($type)){/*从$arr寻找数据,覆盖掉$type中对应的值,并返回$type*/
        foreach($keys as $vv){
            $type[$vv]=$arr[$vv];
        }
        $data=$type;
    }else{
        foreach($keys as $vv){
            if($type===false){/*存在字段才读取*/
                $check=isset($arr[$vv]);
            }elseif($type===true){/*不过滤*/
                $check=true;
            }else{/*排除和$type完全相等的值*/
                $check=$arr[$vv]!==$type;
            }

            if($check) $data[$vv]=$arr[$vv];
            if($unset_unit) unset($arr[$vv]);
        }
    }
    return $data;
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
			$temp=null;
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
 * 对数组的一个维度进行排序
 * @param $data
 * @param string $sort
 * @param string $sortby
 * @param bool $multi
 * @return bool
 */
function list_sort(&$data,$sort='',$sortby='asc',$multi=false){
    $sort=is_array($sort)?$sort:explode(',',(string)$sort);
    $sortby=is_array($sortby)?$sortby:explode(',',(string)$sortby);
    if($multi && count($sort)!=count($sortby)) return false;
    usort($data,function($a,$b)use($sort,$sortby,$multi,&$step){
        foreach($sort as $k=>$v){
            if($a[$v]==$b[$v]) continue;
            !$multi && $k=0;/*只取第一个排序方式*/
            $sc=$sortby[$k]=='asc'?1:-1;
            if($a[$v]>$b[$v]){
                return $sc;
            }else{
                return -$sc;
            }
        }
        return 0;
    });
}
/**
 * 无限级分类列表数据转树状结构数组,不排序,返回分好组的多维数组
 * @param array $data 原始数据
 * @param array $data_index 主键到原始数据数字索引的映射
 * @return array
 */
function list2tree($data,$config=array()) {
    $config=(array)$config+array(
            'data_index'=>false,/*用于判断是否排序成以自身唯一标识符为键名的数组*/
            'self'=>'id',
            'parent'=>'pid',
            'parent_end'=>0,/*遍历原始数据时若父项字段值等于该值时则将该节点移到顶级列表末尾*/
            'child_key'=>'_child',/*存档子节点的数组单元键名*/
            'multicount_key'=>'',/*将每一层指定字段的统计数字加起来,每个节点记录所有子节点该字段之和*/
        );
	$tree=array('_tree'=>array());
    $self=(string)$config['self'];
	$parent=(string)$config['parent'];
	$child=(string)$config['child_key'];
	$multicount=(string)$config['multicount_key'];
	$end=(string)$config['parent_end'];
    $data_index=$config['data_index'];
    $id_index=true;
    if($data_index){
        if(is_array($data_index)){/*提供了id到key的映射*/
            $id_index=false;
        }else{/*$data重新排序成以id为键名的数组*/
            $data_index=array();
            foreach($data as $v){
                $data_index[$v[$self]]=$v;
            }
            $data=$data_index;
            $id_index=true;
        }
    }
	foreach ($data as $ke => $vo) {
		$kp=$id_index?$vo[$parent]:$data_index[$vo[$parent]];
		if($vo[$parent]==$end){
			$tree['_tree'][$ke]=&$data[$ke];
		}elseif(isset($data[$kp])){
			if($multicount!=''){
				if(!isset($data[$ke][$multicount])) $data[$ke][$multicount]=1;
                if(!isset($data[$kp][$multicount])) $data[$kp][$multicount]=1;
				$data[$kp][$multicount]+=$data[$ke][$multicount];
			}
            $data[$kp][$child][$ke]=&$data[$ke];
		}else{
			$tree['_noparent'][]=&$data[$ke];
		}
	}
	return $tree;
}
/**
 * 有树状关系的多维数组转换成二维数组
 * @param $arr
 * @param string $child
 * @param bool $unset_child
 * @return array
 */
function tree2list($arr,$config=array(),$parent_value=array()){
    static $cfg;
    if(empty($cfg)){
        $cfg=(array)$config+array(
                'parent'=>'pid',
                'parent_all'=>'pids',
                'child_key'=>'_child',
                'unset_child'=>true,
            );
    }
    $re = array();
    if(!is_array($parent_value)) $parent_value=array();
    foreach($arr as $v){
        if($cfg['parent_all']){
            $v[$cfg['parent_all']]=array_merge($parent_value,array($v[$cfg['parent']]));
        }
        if(isset($v[$cfg['child_key']])){
            $ch=$v[$cfg['child_key']];
            if($cfg['unset_child']) unset($v[$cfg['child_key']]);
            $re[]=$v;
            $re = array_merge($re,tree2list($ch,'',$v[$cfg['parent_all']]));
        }else{
            $re[]=$v;
        }
    }
    return $re;
}

/**
 * 对树状结构的数据进行综合筛选,默认排除没有上级的节点
 * @param $data
 * @param array $config
 * @return array
 */
function tree_filter($data,$config=array()){
    $config=(array)$config+array(
            'self'=>'id',
            'parent'=>'pid',
            'parent_value'=>'',
            'parent_all'=>'pids',
            'level'=>0,
            'sort_key'=>'sort,id',
            'child_key'=>'_child',
            'multicount_key'=>'_subcount',
        );
    $parent_value=(string)$config['parent_value'];
    $multicount_key=(string)$config['multicount_key'];
    $config['data_index']=1;
    /*默认筛选流程*/
    if(!isset($data['_list'])) $data=array('_list'=>$data);
    list_sort($data['_list'],$config['sort_key']);/*根据多个键进行排序*/
    $data+=list2tree($data['_list'],$config);/*生成节点树*/
    $data['_list'] = tree2list($data['_tree'],$config);/*将节点树数组转成二维数组列表*/
//    if($data['_noparent']) $data['_list'] = array_merge($data['_list'],tree2list($data['_noparent'],$config));/*TODO::处理没有上级节点的数据*/
    $data['_map']=set_index($data['_list'],1,$config['self']);/*构建一个id到key的映射*/
    $rebuild_tree=$rebuild_map=0;
    /*进一步的筛选*/
    if($parent_value){/*筛选指定父项下的数据*/
        $temp=$data['_list'][$data['_map'][$parent_value]][$config['parent_all']];
        if($temp[0]==0) unset($temp[0]);
        $temp[]=$data['_list'][$data['_map'][$parent_value]][$config['self']];
        $keys=array();
        foreach($temp as $v){
            $keys[]=$v;
            $keys[]=$config['child_key'];
        }
        $data['_tree']=array_multifind($data['_tree'],$keys);/*从多维数组中找数据*/
        $data['_list']=tree2list($data['_tree'],$config);/*重新将节点树转成列表*/
        $rebuild_map=1;
    }
    if($config['level']>0){/*筛选指定层的数据*/
        foreach ($data['_list'] as $k => $v) {
            if (count($v[$config['parent_all']])>$config['level']) {
                unset($data['_list'][$k]);
                continue;//继续循环
            }
            if($multicount_key) unset($data['_list'][$k][$multicount_key]);
        }
        $rebuild_tree=$rebuild_map=1;
    }
    /*重新生成节点树*/
    if($rebuild_tree){
        if($parent_value) $config['parent_end']=$parent_value;
        $data=array_merge($data,list2tree($data['_list'],$config));
        /*重新排序程二维数组列表*/
        $data['_list'] = tree2list($data['_tree']);
        //    if($data['_noparent'])
        $rebuild_map=1;
    }
    if($rebuild_map) $data['_map']=set_index($data['_list'],1,$config['self']);/*重新构建一个id到key的映射*/
    return $data;
}
/**
 * 提供多个键名,在多维数组中依次查找对应的键值
 */
function array_multifind($org,$keys){
    $re=array();
    $init=0;
    !is_array($keys) && $keys=explode(',',(string)$keys);
    foreach($keys as $v){
        if($init){
            $re=$re[$v];
        }else{
            $re=$org[$v];
            $init=1;
        }
    }
    return $re;
}
/**
 * 检查给定的标识在给定的层级关系中是否会形成一个闭环结构
 * @param $current
 * @param $all
 * @param int $start
 * @param int $step
 * @return bool
 */
function check_tree_cycle($current,$all,$start=1,$step=2){
    $size=count($all);
    for($i=$start;$i<$size;$i+=$step){
        if($current==$all[$i]) return true;
    }
    return false;
}

/**
 * 普通查询结果集根据树状结构关系排序
 * @param $data
 * @param array $config
 * @return array
 */
function tree_sort(&$data,$config=array()){
    $config=(array)$config+array(
            'self'=>'id',
            'parent'=>'pid',
            'sort'=>'sort',
            'final_sort'=>'final_sort',

            'set_index'=>1,
        );
    $self_key=$config['self'];
    $parent_key=$config['parent'];
    $sort_key=$config['sort'];
    $final_sort=$config['final_sort'];
    if($config['set_index']){
        $arr=array();
        foreach($data as $v){
            $arr[$v[$self_key]]=$v;
        }
        $data=$arr;
    }
    foreach($data as $k=>$v){
        $deal_list=array($k);
        $last_sort_key=array();
        $pid_now=$v[$parent_key];
        while(isset($data[$pid_now])){/*如果列表中存在父项数据*/
            if(isset($data[$pid_now][$final_sort])){
                /*如果父项已存在final_sort键,则将该final_sort记录下并退出循环*/
                $last_sort_key=$data[$pid_now][$final_sort];
                break;
            }
            /*父项还未设置final_sort*/
            array_unshift($deal_list,$pid_now);
            $pid_now=$data[$pid_now][$parent_key];/*父项的父项id*/
        }
        foreach($deal_list as $data_key){
            $last_sort_key[]=$data[$data_key][$sort_key];
            $last_sort_key[]=$data[$data_key][$self_key];
            $data[$data_key][$final_sort]=$last_sort_key;
        }
    }
    usort($data,function($a,$b)use($final_sort){
        foreach($a[$final_sort] as $k=>$v){
            if($v==$b[$final_sort][$k]) continue;
            if($v>$b[$final_sort][$k]){
                return 1;
            }else{
                return -1;
            }
        }
        return 0;
    });
}
/**
 * 按发散的分类树结构排序,返回根据分类层级排序过的一维数组,键名为主键值
 * @param unknown $data
 * @param unknown $data_index
 * @return array
 */
function tree_sort2($data,$data_index=array(),$pk='id',$pid='pid',$sort='sort',$count_deep=false){
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
                        $tree_index=str_replace('_'.$v[$pid].'_','_',$tree_index);/*去掉不正确的pid*/
                        $tree_index=substr($tree_index,0,$pos_id).'_'.$v[$pid].substr($tree_index,$pos_id);/*同步骤2*/
                    }
                }
            }
        }
    }

    $tree_index=explode('_', substr($tree_index,1,-1));

    if(!empty($data_index) && is_array($data_index)){
        foreach($tree_index as $vv){
            $kp=$data_index[$vv];
            if(isset($data[$kp])){
                $re[$vv]=$data[$kp];
                $count_deep && $re[$vv]['deep']=count_parent($data,$data_index,$vv);
            }
        }
    }else{
        foreach($tree_index as $vv){
            $kp=$vv;
            if(isset($data[$kp])){
                $re[$vv]=$data[$kp];
                $count_deep && $re[$vv]['deep']=count_parent($data,$data_index,$vv);
            }
        }
    }
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
function get_full_url(){

//    echo $requestUri.'<br />';
    $scheme = empty($_SERVER['HTTPS'])?'':(($_SERVER['HTTPS']=='on')?'s':'');
    $protocol = strstr(strtolower($_SERVER["SERVER_PROTOCOL"]),"/",true).$scheme;
    $port = ($_SERVER['SERVER_PORT'] == '80') ? '' : (':'.$_SERVER['SERVER_PORT']);
    //TODO:: 解决通用问题
    $requestUri = '';
    if (isset($_SERVER['REQUEST_URI'])) { #$_SERVER["REQUEST_URI"] 只有 apache 才支持,
        $requestUri = $_SERVER['REQUEST_URI'];
    } else {
        if (isset($_SERVER['argv'])) {
            $requestUri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
        } else if(isset($_SERVER['QUERY_STRING'])) {
            $requestUri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
        }
    }
    # 获取的完整url
    $_fullUrl = $protocol . '://' . $_SERVER['SERVER_NAME'] . $port . $requestUri;
    return $_fullUrl;
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
function get_grids($cols_info,$cols=array(),$except=0){
    $re=array();
    is_string($cols) && $cols=explode(',', $cols);
    if(empty($cols)){
        foreach ($cols_info as $kk=>$vv){
            $re[$vv['field']]=parseComment($vv['comment']);
            $re[$vv['field']]['col_type']=$vv['type'];
        }
    }else{
        if(empty($cols_info)){
            foreach($cols as $v){
                if(strpos($v,':')){
                    $arr=explode(':',$v);
                    $re[$arr[0]]=array('title'=>$arr[1]);
                }
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
    }
    if(isset($re['id']) && !$re['id']['title']) $re['id']['title']='数据id';
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
function opr_init($time=900,$charset='utf-8'){
	$charset && set_header($charset);
	error_reporting(E_ALL);
	set_time_limit($time);
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

function success_response($sucinfo,$jsonp=false){
    if(is_string($sucinfo)){
        $sucinfo=array('msg'=>$sucinfo,'result'=>'');
    }
    $success=array(
        'status'=>1,
        'msg'=>$sucinfo['msg']?:'成功',
        'success_response'=>$sucinfo['result']
    );
    switch ($jsonp){
        case 1:
            exit($jsonp.'('.json_encode($success).')');
        case 2:
            exit('var '.$jsonp.'='.json_encode($success).';');
        case 3:
            $pattern = '/[\\\<\>\'\"\*]+/';
            exit(preg_replace($pattern,'', $_GET ["jsoncallback"]).'('.json_encode($success).')');
        default:
            exit(json_encode($success));
    }
}

function delDir( $dir ){
   //先删除目录下的所有文件：
    $dh = opendir( $dir );
    while ( $file = readdir( $dh ) ) {
        if ( $file != "." && $file != ".." ) {
            $fullpath = $dir . "/" . $file;
            if ( !is_dir( $fullpath ) ) {
                unlink( $fullpath );
            } else {
                delDir( $fullpath );
            }
        }
    }
    closedir( $dh );
    //删除当前文件夹：
    return rmdir( $dir );
}

function errorLog( $message ){

    return "[" . date('Y-m-d H:i:s', time()) ."][ERROR]" . $message . "\r\n";
}

function xmlToArray($xml) {

    $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $array_data;
}