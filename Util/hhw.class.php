<?php
namespace Common\My;
class hhw {
	public static $tn_ac='hhw_addr_code';
	public static $tn_area='hhw_area';
	public static $tn_pc='hhw_pack_category';
	public static $tn_pack='hhw_pack_monthly';
	public static $tn_sundry='hhw_sundry';
	public static $tn_mn='hhw_mobile_num';
	public static $tn_mnr='hhw_mobile_num_rule';
	public static $tn_mnf='hhw_mobile_num_flow';
	public static $tn_mnl='hhw_mobile_num_level';
	public static $admin_log='hhw_admin_log';
	
	public static $company2name=array('yidong'=>'移动','liantong'=>'联通','dianxin'=>'电信');
	public static $company2code=array('yidong'=>1,'liantong'=>2,'dianxin'=>3);
	public static $code2company=array(1=>'yidong',2=>'liantong',3=>'dianxin');
	public static $code2name=array(1=>'移动',2=>'联通',3=>'电信');
	
	public static $support=array('eq'=>1,'enum'=>1,'reg'=>1,'php'=>1);//支持的靓号规则解析模式
	
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
	public static function get_post_code_info($refresh=false,$range=null,$insure=false){
		$cacheKey=self::$tn_area.'.data';
		if (!fc::exists($cacheKey) || $refresh) {
			$addr['list']=M()->table(self::$tn_area)->field('post_code,title')->select();
			data_tree::set_index($addr['list'],0,'post_code');
			$addr['tree']=array();
			foreach ($addr['list'] as $ke => $vo) {
				$kp=str_split($ke,2);
				$addr['tree'][$kp[0].'0000'][$kp[0].$kp[1].'00']=1;
			}
			fc::save($cacheKey,$addr);
		}else{
			$addr=fc::get($cacheKey);
		}
		if(!isset($addr['tree']) && !$insure){
			return self::get_post_code_info(1,$range,true);
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
	/**
	 * 获取手机号归属地信息
	 * @param unknown_type $num
	 * @param unknown_type $_ch
	 * @param unknown_type $delay 延迟时间,单位微秒
	 * @return Ambigous <string, multitype:>
	 */
	public static function get_num_addr($num,$_ch=null,$delay=0){
		// 		$url='http://www.showji.com/search.htm?m='.$num;
		// 		$url='http://www.ip138.com:8080/search.asp?action=mobile&mobile='.$num;
		$time=strstr(microtime(true)*1000,'.',true);
		$callback='';
		$url='http://api.showji.com/locating/showji.com1118.aspx?m='.$num.'&output=json&timestamp='.$time.'&callback='.$callback;
		if($_ch){//如果有手动指定的cURL handle,则使用手动指定的,否则自动创建一个
			$ch=$_ch;
		}else{
			$ch=curl_init();
		}
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
			$rt=json_decode($str,true);
			if($rt){
				$re['City']=$rt['City'];
				$re['num']=$rt['Mobile'];
				$re['post_code']=$rt['PostCode'];
			}else{
				preg_match_all('/mobile\D*?([0-9]{11})\D/is',$str,$catch);//抓取手机号
				$re['num']=$catch[1][0];
				preg_match_all('/postcode\D*?([0-9]{6})\D/is',$str,$catch);//抓取邮编
				$re['post_code']=$catch[1][0];
				preg_match_all('/city\D*?[\'\"]([\p{Han}]+?)[\'\"]\D/is',$str,$catch);//抓取邮编
				$re['City']=$catch[1][0];
			}
			
			if($re['num'] && $re['post_code'] && $re['City']){
				$re['msg']='归属地城市:'.$re['City'].',邮编:'.$re['post_code'];
				$where=array('post_code'=>$re['post_code']);
				$addr_info=M()->table(self::$tn_ac)->where($where)->order('xzqh_code')->find();
				$re['xzqh_code']=$addr_info['xzqh_code'];
				$re['status']=1;
			}elseif($re['num'] && !$re['post_code']){
				$re['msg']='号码['.$re['num'].']没有找到归属地信息,请手动设置归属地或联系管理员';
				$re['status']=2;
			}else{
				$re['msg']='调用过于频繁或接口已更新,如有疑问请联系管理员:'.addslashes(htmlspecialchars($str));
			}
		}
		if(!$_ch) curl_close($ch);//如果没有手动指定curl则关闭刚才创建的curl
		if($delay>0) usleep($delay);
		return $re;
	}
	public static function check_empty($tn,$where){
		$a=M()->table($tn)->where($where)->limit(1)->find();
		return empty($a);
	}
	

	
	public static function lv_encode($company,$gen,$level){
		if(isset(self::$company2code[$company])) $company=self::$company2code[$company];
		return $company.$gen.$level;
	}
	public static function lv_decode($lv_code,&$subject,$type=0){
		$subject['level']=substr($lv_code,2);
		switch ($type){
			case 0:
				$subject['company']=self::$code2company[substr($lv_code,0,1)];
				$subject['gen']=substr($lv_code,1,1);
				break;
			case 1:
				$subject['company']=self::$code2name[substr($lv_code,0,1)];
				$subject['gen']=substr($lv_code,1,1).G;
				break;
		}
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
}