<?php
namespace Common\My;
class hhw_tptools {
	public static $tn_ac='hhw_area_code';
	public static $tn_area='hhw_area';
	public static $tn_pc='hhw_pack_category';
	public static $tn_sundry='hhw_sundry';
	public static $tn_mn='hhw_mobile_num';
	public static $tn_mnr='hhw_mobile_num_rule';
	public static $tn_mnf='hhw_mobile_num_flow';
	public static $tn_mnl='hhw_mobile_num_level';
	public static $admin_log='hhw_admin_log';
	public static $default_company=array(
				'yidong'=>'移动',
				'liantong'=>'联通',
				'dianxin'=>'电信'
			);
	public static $code2company=array(1=>'yidong',2=>'liantong',3=>'dianxin');
	
	private static $pre_info=array();
	private static $flow=array();
	
	public static function get_area_code($refresh=false,$range=null,$insure=false){
		$cacheKey=self::$tn_ac.'.data';
		if (!fc::exists($cacheKey) || $refresh) {
			$addr['list']=M()->table(self::$tn_ac)->field('code,title')->select();
			data_tree::set_index($addr['list'],0,'code');
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
	
			fc::save($cacheKey,$addr);
		}else{
			$addr=fc::get($cacheKey);
		}
		if(!isset($addr['tree']) && !$insure){
			return self::get_area_code(1,$range,true);
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
	
	public static function get_columns($tn,$refresh=false,$insure=false){
		$cacheKey = $tn.'.cache';
		if (!fc::exists($cacheKey) || $refresh) {
			$info=M()->query('SHOW FULL COLUMNS FROM '.$tn);
			$cols_info=array();
			foreach($info as $vv){
				$cols_info[$vv['field']]=array_change_key_case($vv);
			}
			fc::save($cacheKey, $cols_info);
		}else{
			$cols_info=fc::get($cacheKey);
		}
		if(!$cols_info && !$insure){
			$cols_info=self::get_columns($tn,true,true);
		}
		return $cols_info;
	}
	/**
	 * 套餐分类
	 */
	public static function get_pack_category($refresh=false,$company=null,$insure=false){
		$cacheKey=self::$tn_pc.'.data';
		if (!fc::exists($cacheKey) || $refresh) {
			$category['data']=M()->query('select * from '.self::$tn_pc);
			$category['map']=data_tree::build_map($category['data'],'company');
			$category['index']=data_tree::set_index($category['data'],1);
			fc::save($cacheKey,$category);
		}else{
			$category=fc::get($cacheKey);
		}
		if(!isset($category['index']) && !$insure){
			$category=self::get_pack_category(true,null,true);
		}
		$c=array('yidong'=>1,'liantong'=>1,'dianxin'=>1);
		if(isset($c[$company])){
			$temp=array();
			foreach((array)$category['map'][$company] as $k=>$v){
				$temp[]=$category['data'][$v];
			}
			unset($category['map']);
			$category=array('data'=>$temp);
		}
		return $category;
	}
	/**
	 * 杂项信息
	 * @param bool $refresh
	 * @param string $name
	 * @param bool $insure
	 * @return string
	 */
	public static function get_sundry_content($refresh=false,$name='',$insure=false){
		$cacheKey=self::$tn_sundry.'.'.$name.'.data';
		fc::$enctype=1;
		if (!fc::exists($cacheKey) || $refresh) {
			$data=M()->table(self::$tn_sundry)->where('name="'.$name.'"')->getField('content');
			fc::save($cacheKey,$data);
		}else{
			$data=fc::get($cacheKey);
		}
		if(($data=='' || !is_string($data)) && !$insure){
			$data=self::get_sundry_content(true,$name,true);
		}
		return $data;
	}
	public static function get_num_addr($num){
		// 		$url='http://www.showji.com/search.htm?m='.$num;
		// 		$url='http://www.ip138.com:8080/search.asp?action=mobile&mobile='.$num;
		$time=strstr(microtime(true)*1000,'.',true);
		$url='http://api.showji.com/locating/showji.com1118.aspx?m='.$num.'&output=json&callback=querycallback&timestamp='.$time;
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $url); // 要访问的地址
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
		curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 设置超时限制防止死循环
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
		curl_setopt($ch, CURLOPT_HEADER, 0); // 不显示返回的Header区域内容
		$str=curl_exec($ch);// 执行操作
		$re=$catch=array();
		if ($str===false) {
			$re['msg']=curl_error($ch);
		}else{
			preg_match_all('/mobile.*?([0-9]{11})/is',$str,$catch);//抓取手机号
			$re['num']=$catch[1][0];
			preg_match_all('/postcode.*?([0-9]{6})/is',$str,$catch);//抓取邮编
			$re['post_code']=$catch[1][0];
			if($re['num'] && $re['post_code']){
				preg_match_all('/postcode.*?([0-9]{6})/is',$str,$catch);//抓取邮编
				$ainfo=M()->table(self::$tn_area)->where('post_code='.$re['post_code']);
				if($ainfo==null){
					$ainfo=M()->table(self::$tn_area)->where();
				}
	
	
				$re['status']=1;
			}else{
				$re['msg']='获取手机号归属地的接口已更新,请联系开发人员升级接口解析程序';
			}
		}
		curl_close($ch);
	
		return $re;
	}
	public static function check_empty($tn,$where){
		$a=M()->table($tn)->where($where)->limit(1)->find();
		return empty($a);
	}
	
	/**
	 * 后台管理员的操作的日志
	 * @param string $table
	 * @param mixed $record_id
	 * @param int $user_id
	 * @return array
	 */
	public static function admin_log($table,$record_id,$user_id){
		$re=array('status'=>0);
		//参数检查
		if(empty($table) || empty($record_id) || empty($user_id)){
			$re['msg']='参数不能为空';
			return $re;
		}
		if(strpos($record_id,',')!==false) $record_id=implode(',',$record_id);
		if(!is_array($record_id)) $record_id=array($record_id);
		foreach($record_id as $v){
			//插入行为日志
			$data=array(
					'user_id'=>$user_id,
					'action_ip'=>ip2long(get_client_ip()),
					'table'=>$table,
					'record_id'=>$v,
					'create_time'=>$_SERVER['REQUEST_TIME'],
					'action_url'=>$_SERVER['REQUEST_URI']
			);
			$id=M()->table(self::$admin_log)->add($data);
			$re['status']=(bool)$id;
			if(!$id){
				$re['msg']=M()->getDbError();
				break;
			}
		}
	
		return $re;
	}
	/**
	 * 计算靓号等级模块初始化
	 * @param int|string $num 号码
	 * @param int|string $gen 第几代
	 * @param array $assist_data 其他必要数据
	 * @return unknown
	 */
	public static function num_match_init(){
		$cinfo=(array)json_decode(self::get_sundry_content(0,'company_config'),true);
		$company2code=array_flip(self::$code2company);
		foreach($cinfo as $k=>$v){
			foreach($v['pre'] as $kk=>$vv){
				self::$pre_info[$kk]=$company2code[$k].$vv;
			}
		}
		$join=array(
				'left join '.self::$tn_mnl.' mnl on mnl.id=mnf.level_id',
				'left join '.self::$tn_mnr.' mnr on mnr.id=mnf.rule_id'
			);
		$field=array(
				'mnf.sort',
				'mnr.parse',
				'mnr.rule',
				'mnr.param',
				'mnr.name',
				'mnr.title'
			);
		$finfo=M()->table(self::$tn_mnf.' mnf')->join($join)->field($field)->select();
		dump($finfo);
	}
	public static function num_match($num,$gen){
		$input=array(
				'status'=>1,
				'gen'=>$gen.'',//第几代
				'num'=>$num.'',//号码
				'prefix'=>substr($num,0,3),//前三位
		
		);
		$prefix_info=self::$pre_info[$input['prefix']];//根据号码前三位找到对应的信息
		if($gen<2){
			$input['status']=2;
			$input['msg']='请选择电话网络的代数';
		}elseif($prefix_info==null){
			$input['status']=3;
			$input['msg']='手机号['.$num.']没有匹配的运营商';
		}else{
			$input['company']=$prefix_info[0];//运营商
			$input['correct']=$prefix_info[1];//修正值
			self::do_match($input);
			$input['company']=self::$code2company[$prefix_info[0]];
		}
		return $input;
	}
	private static function do_match(&$input){
		$input['level']=0;
		$code=$input['company'].$input['gen'];
		switch ($code){//流程修正
			case 33://电信3G
				if($input['correct']>0){
					$flow=array_slice(self::$flow[$code],(int)$input['correct']);
				}else{
					$flow=self::$flow[$code];
				}
				$input['level']=7;
				break;
			case 22://联通2G
				$flow=self::$flow[23];
				unset($flow[7]['php'],$flow[7]['reg']['AABB'],$flow[7]['reg']['ABAB']);
				break;
			default:
				$flow=self::$flow[$code];
		}
		if(empty($flow)){
			$input['status']=4;
			$input['msg']='目前没有设置['.(self::$company_name[$input['company']]).$input['gen'].'G]靓号规则';
			return ;
		}
	
		// 		if($test=='reg'){
		// 			$flow[8]['reg'][]='[+1]{6}';
		// 			$flow[8]['reg'][]='[-1]{6}';
		// 		}else{
		// 			$flow[8]['php'][]=array('ap_positive','6');
		// 			$flow[8]['php'][]=array('ap_negative','6');
		// 		}
		// 		dump($flow);
	
		foreach ($flow as $k=>$v){
			if(isset($v['eq'])){
				$len=strlen($v['eq']);
				if(substr($input['num'],-$len)==$v['eq']){
					$input['first_match']='eq{'.$len.'}';
					$input['level']+=$k;
					break;
				}
			}
			// 			if(isset($v['neq'])){
			// 				if(substr($input['num'],-strlen($v['neq']))!=$v['neq']){
			// 					$input['first_match']='neq['.$v['neq'].']';
			// 					$input['level']=$k+$fix;
			// 					break;
			// 				}
			// 			}
			if(isset($v['enum'])){
				foreach ($v['enum'] as $k2=>$v2){
					if(isset($v2[substr($input['num'],-($k2))])){
						$input['first_match']='eq{'.$k2.'}';
						$input['level']+=$k;
						break 2;
					}
				}
			}
			if(isset($v['reg'])){
				foreach ($v['reg'] as $v3){
					if(preg_match('/'.self::$reg[$v3].'$/',$input['num'])){
						$input['first_match']=$v3;
						$input['level']+=$k;
						break 2;
					}
				}
			}
			if(isset($v['php'])){
				foreach ($v['php'] as $v4){
					if (isset($v4[1])) {
						//为了效率优化做以下约定:第二个单元为截取尾号的长度,第三个单元为指定的数字,第四个单元为$input数组的键名
						$param[]=substr($input['num'],-($v4[1]));
						isset($v4[2]) && $param[]=$v4[2];
						isset($v4[3]) && $param[]=$input[$v4[3]];
					}else{
						$param[]=$input;
					}
					if(call_user_func_array('self::'.$v4[0],$param)){
						$input['first_match']=$v4[0].($v4[1]>0?'_'.$v4[1]:'');
						$input['level']+=$k;
						break 2;
					}
				}
			}
		}
	
		switch ($code){//结束操作
			case 34://电信4G
	
			default:
				if(!$input['first_match']){
					$input['level']=0;
				}
		}
	
		self::extra_match($input);
	}
	public static function extra_match(&$input){
		$month=substr($input['num'],-4,2);
		$day=substr($input['num'],-2);
		if(($month=='02' && $day>0 && $day<30) || checkdate($month,$day,'2000')){
			$input['month_match']=$month;
		}
	}
}