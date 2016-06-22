<?php
function isMobile($mobile) {
	return preg_match("/^(?:13\d|14\d|15\d|18[0123456789])-?\d{5}(\d{3}|\*{3})$/", $mobile);
}

function isEmail($email) {
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}

/**
 * 发送HTTP请求方法，目前只支持CURL发送请求
 * @param  string $url    请求URL
 * @param  array  $params 请求参数
 * @param  string $method 请求方法GET/POST
 * @return array  $data   响应数据
 */
function http($url, $params, $method = 'GET', $header = array(), $multi = false){
	$opts = array(
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_HTTPHEADER     => $header
	);

	/* 根据请求类型设置特定参数 */
	switch(strtoupper($method)){
		case 'GET':
			$opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
			break;
		case 'POST':
			//判断是否传输文件
			//$params = $multi ? $params : http_build_query($params);
			$opts[CURLOPT_URL] = $url;
			$opts[CURLOPT_POST] = 1;
			$opts[CURLOPT_POSTFIELDS] = $params;
			break;
		default:
			throw new Exception('不支持的请求方式！');
	}

	/* 初始化并执行curl请求 */
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data  = curl_exec($ch);
	$error = curl_error($ch);
	curl_close($ch);
	if($error) throw new Exception('请求发生错误：' . $error);
	return  $data;
}

/**
 * 不转义中文字符和\/的 json 编码方法
 * @param array $arr 待编码数组
 * @return string
 */
function jsencode($arr) {
	$str = str_replace ( "\\/", "/", json_encode ( $arr ) );
	$search = "#\\\u([0-9a-f]+)#ie";
	
	if (strpos ( strtoupper(PHP_OS), 'WIN' ) === false) {
		$replace = "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))";//LINUX
	} else {
		$replace = "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))";//WINDOWS
	}
	
	return preg_replace ( $search, $replace, $str );
}

// 数据保存到文件
function data2file($filename, $arr=''){
	if(is_array($arr)){
		$con = var_export($arr,true);
		$con = "<?php\nreturn $con;\n?>";
	} else{
		$con = $arr;
		$con = "<?php\n $con;\n?>";
	}
	write_file($filename, $con);
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 单位 秒
 * @return string
 * @author winky
 */
function encrypt($data, $key = '', $expire = 0) {
    $key  = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    $str = sprintf('%010d', $expire ? $expire + time():0);

    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256);
    }
    return str_replace(array('+','/','='),array('-','_',''),base64_encode($str));
}

/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是encrypt方法加密的字符串）
 * @param  string $key  加密密钥
 * @return string
 * @author winky
 */
function decrypt($data, $key = ''){
    $key    = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
    $data   = str_replace(array('-','_'),array('+','/'),$data);
    $mod4   = strlen($data) % 4;
    if ($mod4) {
       $data .= substr('====', $mod4);
    }
    $data   = base64_decode($data);
    $expire = substr($data,0,10);
    $data   = substr($data,10);

    if($expire > 0 && $expire < time()) {
        return '';
    }
    $x      = 0;
    $len    = strlen($data);
    $l      = strlen($key);
    $char   = $str = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1))<ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }else{
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

function getTaskStatusStr($status = 0,$type = 'apply' , $company = ''){
	if ($type == 'comment') return '尚未作出评价';
	switch ($status) {
		case 0:
			return $type == 'apply' ? '已发出任务申请' : $company.'对你发出了任务邀请';
		break;
		case 1:
			return $type == 'apply' ? '企业已通过申请' : '已同意企业的邀请';
		break;		
		case 2:
			return $type == 'apply' ? '企业已忽略你的申请' : '你已经忽略企业的邀请';
		break;
		case 3:
			return $type == 'apply' ? '你已完成该任务' : '该任务已经完成';
		break;		
		default:
			return '未知的状态';
		break;
	}
}


function getArea($cache = true){
	$area = S ( 'S_Area' );
	if (empty ( $area ) || ! $cache) {
		// 缓存不存在，或者参数读取缓存。
		$areaModel = D('Area');
		$area = $areaModel -> where ('status = 3')->order ( 'sort,itemid' )->getField('itemid,title,pid,arrparentid,child');
		//把市的省拚出来
		foreach ($area as $k=>$v){
			//如果是顶级
			if ($v['pid']==0){
				$areaArr[$v['itemid']]['itemid'] = $v['itemid'];
				$areaArr[$v['itemid']]['title'] = $v['title'];
				$areaArr[$v['itemid']]['pid'] = $v['pid'];
				$areaArr[$v['itemid']]['arrparentid'] = $v['arrparentid'];
				$areaArr[$v['itemid']]['child'] = $v['child'];
				//上级
				$areaArr[$v['itemid']]['upitemid'] = $v['itemid'];
				$areaArr[$v['itemid']]['uptitle'] = $v['title'];
			}
			//查出上级的名称和ID
			else {
				$areaArr[$v['itemid']]['itemid'] = $v['itemid'];
				$areaArr[$v['itemid']]['title'] = $v['title'];
				$areaArr[$v['itemid']]['pid'] = $v['pid'];
				$areaArr[$v['itemid']]['arrparentid'] = $v['arrparentid'];
				$areaArr[$v['itemid']]['child'] = $v['child'];
				//上级
				$areaArr[$v['itemid']]['upitemid'] = $area[$v['pid']]['itemid'];
				$areaArr[$v['itemid']]['uptitle'] = $area[$v['pid']]['title'];
			}
		}
		$area = $areaArr;
		S ( 'S_Area' , $area );
	}
	return $area;
}

?>