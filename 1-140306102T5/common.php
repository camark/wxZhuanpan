<?php
function isMobile($mobile) {
	return preg_match("/^(?:13\d|14\d|15\d|18[0123456789])-?\d{5}(\d{3}|\*{3})$/", $mobile);
}

function isEmail($email) {
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}

/**
 * ����HTTP���󷽷���Ŀǰֻ֧��CURL��������
 * @param  string $url    ����URL
 * @param  array  $params �������
 * @param  string $method ���󷽷�GET/POST
 * @return array  $data   ��Ӧ����
 */
function http($url, $params, $method = 'GET', $header = array(), $multi = false){
	$opts = array(
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_HTTPHEADER     => $header
	);

	/* �����������������ض����� */
	switch(strtoupper($method)){
		case 'GET':
			$opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
			break;
		case 'POST':
			//�ж��Ƿ����ļ�
			//$params = $multi ? $params : http_build_query($params);
			$opts[CURLOPT_URL] = $url;
			$opts[CURLOPT_POST] = 1;
			$opts[CURLOPT_POSTFIELDS] = $params;
			break;
		default:
			throw new Exception('��֧�ֵ�����ʽ��');
	}

	/* ��ʼ����ִ��curl���� */
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data  = curl_exec($ch);
	$error = curl_error($ch);
	curl_close($ch);
	if($error) throw new Exception('����������' . $error);
	return  $data;
}

/**
 * ��ת�������ַ���\/�� json ���뷽��
 * @param array $arr ����������
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

// ���ݱ��浽�ļ�
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
 * ϵͳ���ܷ���
 * @param string $data Ҫ���ܵ��ַ���
 * @param string $key  ������Կ
 * @param int $expire  ����ʱ�� ��λ ��
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
 * ϵͳ���ܷ���
 * @param  string $data Ҫ���ܵ��ַ��� ��������encrypt�������ܵ��ַ�����
 * @param  string $key  ������Կ
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
	if ($type == 'comment') return '��δ��������';
	switch ($status) {
		case 0:
			return $type == 'apply' ? '�ѷ�����������' : $company.'���㷢������������';
		break;
		case 1:
			return $type == 'apply' ? '��ҵ��ͨ������' : '��ͬ����ҵ������';
		break;		
		case 2:
			return $type == 'apply' ? '��ҵ�Ѻ����������' : '���Ѿ�������ҵ������';
		break;
		case 3:
			return $type == 'apply' ? '������ɸ�����' : '�������Ѿ����';
		break;		
		default:
			return 'δ֪��״̬';
		break;
	}
}


function getArea($cache = true){
	$area = S ( 'S_Area' );
	if (empty ( $area ) || ! $cache) {
		// ���治���ڣ����߲�����ȡ���档
		$areaModel = D('Area');
		$area = $areaModel -> where ('status = 3')->order ( 'sort,itemid' )->getField('itemid,title,pid,arrparentid,child');
		//���е�ʡ�ճ���
		foreach ($area as $k=>$v){
			//����Ƕ���
			if ($v['pid']==0){
				$areaArr[$v['itemid']]['itemid'] = $v['itemid'];
				$areaArr[$v['itemid']]['title'] = $v['title'];
				$areaArr[$v['itemid']]['pid'] = $v['pid'];
				$areaArr[$v['itemid']]['arrparentid'] = $v['arrparentid'];
				$areaArr[$v['itemid']]['child'] = $v['child'];
				//�ϼ�
				$areaArr[$v['itemid']]['upitemid'] = $v['itemid'];
				$areaArr[$v['itemid']]['uptitle'] = $v['title'];
			}
			//����ϼ������ƺ�ID
			else {
				$areaArr[$v['itemid']]['itemid'] = $v['itemid'];
				$areaArr[$v['itemid']]['title'] = $v['title'];
				$areaArr[$v['itemid']]['pid'] = $v['pid'];
				$areaArr[$v['itemid']]['arrparentid'] = $v['arrparentid'];
				$areaArr[$v['itemid']]['child'] = $v['child'];
				//�ϼ�
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