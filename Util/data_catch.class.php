<?php
namespace Common\My;
class data_catch {
	
	/**
	 * 手机号归属地
	 * @param unknown_type $num
	 * @param unknown_type $_ch
	 * @param unknown_type $delay
	 * @return Ambigous <number, string, multitype:>
	 */
	public static function mobile_addr($num,$_ch=null,$delay=0){
		// 		$url='http://www.showji.com/search.htm?m='.$num;
		// 		$url='http://www.ip138.com:8080/search.asp?action=mobile&mobile='.$num;
		$url='http://api.showji.com/locating/showji.com1118.aspx?m='.$num.'&output=json&callback=querycallback&timestamp='.$time;

		
		$time=strstr(microtime(true)*1000,'.',true);
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
			preg_match_all('/mobile\D*?([0-9]{11})\D/is',$str,$catch);//抓取手机号
			$re['num']=$catch[1][0];
			preg_match_all('/postcode\D*?([0-9]{6})\D/is',$str,$catch);//抓取邮编
			$re['post_code']=$catch[1][0];
			if($re['num'] && $re['post_code']){
				preg_match_all('/postcode.*?([0-9]{6})/is',$str,$catch);//抓取邮编
				$check=self::check_empty(self::$tn_area, 'post_code='.$re['post_code']);
				if($check){
					M()->table(self::$tn_area)->add(array('post_code'=>$re['post_code']));
				}
				$re['status']=1;
			}elseif($re['num'] && !$re['post_code']){
				$re['msg']='号码['.$re['num'].']没有找到归属地信息,请手动设置归属地或联系管理员';
			}else{
				$re['msg']='被第三方接口屏蔽或接口已更新,请联系开发人员升级接口解析程序:'.$str;
			}
		}
		if(!$_ch) curl_close($ch);//如果没有手动指定curl则关闭刚才创建的curl
		if($delay>0){
			usleep($delay);
		}
		return $re;
	}
	/**
	 * 查询邮编
	 * @param unknown_type $num
	 * @param unknown_type $_ch
	 * @param unknown_type $delay
	 * @return Ambigous <number, string, multitype:>
	 */
	public static function post_code($name,$_ch=null,$delay=0){
		$name_url=urlencode(iconv('UTF-8','GB2312',$name));
		$url='www.ip138.com/post/search.asp?area='.$name_url.'&action=area2zip';
		$time=strstr(microtime(true)*1000,'.',true);
		if($_ch){//如果有手动指定的cURL handle,则使用手动指定的,否则自动创建一个
			$ch=$_ch;
		}else{
			$ch=curl_init();
		}
		curl_setopt($ch, CURLOPT_URL, $url); // 要访问的地址
// 		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。
// 		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
		curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 设置超时限制防止死循环
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
		curl_setopt($ch, CURLOPT_HEADER, 0); // 不显示返回的Header区域内容
		$str=curl_exec($ch);// 执行操作
		$re=$catch=array();
		if ($str===false) {
			$re['msg']=curl_error($ch);
		}else{
			$str=iconv('gb2312','UTF-8', $str);
			$reg='/'.$name.'\D*?邮编\D*?([0-9]{6})\D*?区号\D*?([0-9]{3,4})/is';
// 			$reg='/\u67e5\u8be2\u7ed3\u679c\D*?'.$name.'\D*?\u90ae\u7f16\D*?([0-9]{6})\D/is';
// 			dump($reg);
			preg_match_all($reg,$str,$catch);//抓取
// 			dump($catch);
			$count_post_code=count($catch[1]);
			if($count_post_code>1){
				$c=array_count_values($catch[1]);
				if(!$c[$catch[1][0]]==$count_post_code){//如果所有结果都一样 就算通过
					$re['data']['post_code']=0;
				}
			}else{
				$re['data']['post_code']=$catch[1][0];
			}
			$re['data']['area_code']=(int)$catch[2][0];
// 			dump($re);	
		}
		if(!$_ch) curl_close($ch);//如果没有手动指定curl则关闭刚才创建的curl
		if($delay>0){
			usleep($delay);
		}
		return $re;
	}
}