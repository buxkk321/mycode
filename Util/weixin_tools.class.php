<?php
namespace Common\My;
class weixin_tools {
	protected static $appid='wxbc9ec2d95e7e274b';
	protected static $secret='8519af6ac4a12d218dfcaf7a33689b37';
	public static $last_errmsg='';
	/**
	 * 使用curl请求数据
	 * @param unknown_type $ch
	 * @param unknown_type $c_url
	 * @param unknown_type $post
	 * @return mixed
	 */
	public static function docurl($ch,$c_url,$post=array()){
		curl_setopt($ch, CURLOPT_URL, $c_url); // 要访问的地址
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
		//    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
		//    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
		curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 设置超时限制防止死循环
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
		curl_setopt($ch, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
		$tmpInfo = curl_exec($ch); // 执行操作
	
		return $tmpInfo; // 返回数据
	}
	/**
	 * 获取access_token 带缓存
	 * @param unknown_type $refresh
	 * @param  $cache 说明 ：该缓存类必须实现 exists、get、save三个静态方法,此处的读写都需要进行json编码
	 * @return Ambigous <number, string, mixed>
	 */
	public static function get_access_token($refresh=false,$cache){
		$cacheKey='weixin.data';
		if(!$refresh && $cache::exists($cacheKey)){
			$data=$cache::get($cacheKey);
			if($data['time_out']<$_SERVER['REQUEST_TIME']+10){
				$refresh=true;
			}
		}else{
			$refresh=true;
		}
		if($refresh){
			$url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.(self::$appid).'&secret='.(self::$secret);
			// 			dump($url);
			$ch = curl_init(); // 启动一个CURL会话
			$data = json_decode(self::docurl($ch,$url),true);
			$data['time_out']=$_SERVER['REQUEST_TIME']+$data['expires_in'];
			curl_close($ch);
			$cache::save($cacheKey,$data);
		}
	
		return $data;
	}
	/**
	 *  通过code获取openid
	 * @param string $code
	 * @return array
	 */
	public static function get_openid($code=''){
		$url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.(self::$appid).'&secret='.(self::$secret).'&code='.$code.'&grant_type=authorization_code';
		$ch = curl_init(); // 启动一个CURL会话
		$output = self::docurl($ch,$url);
		if($output===false){
			self::$last_errmsg=curl_error($ch);
			return false;
		}else{
			$output=json_decode($output,true);
		}
	
		curl_close($ch);
	
		if($output['openid']==''){
			self::$last_errmsg='errcode '.$output['errcode'].':'.$output['errmsg'];
			return false;
		}else{
			return $output;
		}
	}
	public static function get_user_info($token,$openid){
		$url='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$token.'&openid='.$openid.'&lang=zh_CN';
		$ch = curl_init();
		$output = weixin_tools::docurl($ch,$url);
		if($output===false){
			self::$last_errmsg=curl_error($ch);
		}else{
			$output=json_decode($output,true);
		}
		curl_close($ch);
		
		return $output;
	}
	
	public static function check_weixin_access(){
		$re=stripos($_SERVER['HTTP_USER_AGENT'],"MicroMessenger ");
		return !$re;
	}
	
}