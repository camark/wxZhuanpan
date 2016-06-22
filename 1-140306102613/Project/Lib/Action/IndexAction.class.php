<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends Action {
    public function index(){
    	if(!$this->checkCanDo()){
			$this->error('您还没有抽奖权限，请登录',__URL__.'/login');
    	}else{
    		$this->display();
    	}
    }
    private function checkCanDo(){
    	$username=session(PHPOK_SESSIONNAME.'_USERNAME');
    	$password=session(PHPOK_SESSIONNAME.'_PASSWORD');
    	if($username=='' or $password==''){
    		$this->error('您还没有抽奖权限，请登录',__URL__.'/login');
    	}else{
    		$m=M('User');
    		$data=array('username'=>$username,'password'=>$password,_logic=>'and');
    		$row=$m->where($data)->select();
    		if($row){
    			return true;
    		}else{
    			return false;
    		}
    	}

    }
    public function login(){
    	$this->display();
    }
    public function checkLogin(){
    	$username=trim($_POST['username']);
    	$password=md5($_POST['password']);
    	if(!isset($username) || empty($username) || !isset($password) || empty($password)){
    		$this->error('用户名密码不能为空！',__URL__.'/login');
    	}else{
    		$m=new Model('User');
    		if($m->autoCheckToken($_POST)){
				$resoult=$m->where(array('username'=>$username,'password'=>$password,_logic=>'and'))->select();
				if($resoult){
					session(PHPOK_SESSIONNAME.'_USERNAME',$username);
					session(PHPOK_SESSIONNAME.'_PASSWORD',$password);
					$this->success('登录成功，正在跳转到抽奖页面',__URL__.'/index');
				}else{
					$this->error('对不起，帐号或者密码不正确！',__URL__.'/login');
				}
    		}
    	}
    }
    public function run(){
		if(!$this->checkCanDo()){
			$this->error('您还没有抽奖权限，请登录',__URL__.'/login');
    	}else{
    		$prize_arr=array();
    		$m=new Model('Config');
    		$arr=$m->select();
    		foreach($arr as $key=>$val){
				$min=explode(",",$val['min']);
				$max=explode(",",$val['max']);
				if(count($min)>1){
					$val['min']=$min;
				}
				if(count($max)>1){
					$val['max']=$max;
				}
					$prize_arr[$key]=$val;
			}
			echo $this->getResult($prize_arr);
    	}
    }
    private function getResult($priearr){
		$arr=array();
		$count=array();
		foreach ($priearr as $key => $val) {
    		$arr[$val['id']] = $val['chance'];
    		$count[$val['id']] = $val['praisenumber'];
		}
		$rid = $this->getRand($arr,$count); //根据概率获取奖项id
		$res = $priearr[$rid-1]; //中奖项
		$username=session(PHPOK_SESSIONNAME."_USERNAME");
		$password=session(PHPOK_SESSIONNAME."_PASSWORD");
		$m=M('User as u');
		$row=$m->field('u.id,n.number')->join('inner join magic_useraddnumber as n on u.id=n.aid')->where(array('u.username'=>$username,'u.password'=>$password))->find();
		if($row){
			if($row['number']==0){
				$num=0;
				$result['praisename'] =null;
				$result['angle']=0;
			}else{
				$num=$row['number']-1;
				$min = $res['min'];
				$max = $res['max'];
				if(is_array($min)){ //多等奖的时候
    				$i = mt_rand(0,count($min)-1);
    				$result['angle'] = mt_rand($min[$i],$max[$i]);
				}else{
    				$result['angle'] = mt_rand($min,$max); //随机生成一个角度
				}
				$result['praisename'] = $res['praisename'];
			}
			//用户抽奖次数减1
			$cjModel=M('Useraddnumber');
			$cjModel->where(array('aid'=>$row['id']))->save(array('number'=>$num));
			$result['num']=$num;
			//用户抽取的那个奖项减1
			$mode=M('Config');
			$row=$mode->field(praisenumber)->where(array('id'=>$res['id']))->find();
			if($row['praisenumber']==-1){
				$num=-1;
			}else if($row['praisenumber']==0){
				$num=0;
			}else{
				$num=$row['praisenumber']-1;
			}
			$mode->where(array('id'=>$res['id']))->save(array('praisenumber'=>$num));

			return $this->json($result);
		}
    }
    private function getRand($proArr,$proCount){
    	$result = '';
    	$proSum=0;
    	//概率数组的总概率精度  获取库存不为0的
    	foreach($proCount as $key=>$val){
    		if($val==0){
    			continue;
    		}else{
    			$proSum=$proSum+$proArr[$key];
    		}
    	}
    	//概率数组循环 �
    	foreach ($proArr as $key => $proCur) {
    		if($proCount[$key]==0){
    			continue;
    		}else{
    			$randNum = mt_rand(1, $proSum);//关键
        		if ($randNum <= $proCur) {
        			$result = $key;
           	 		break;
        		}else{
            		$proSum -= $proCur;
        		}
    		}

    	}
    	unset ($proArr);
    	return $result;
    }
    private function json($array){
    	$this->arrayRecursive($array, 'urlencode', true);
		$json = json_encode($array);
		return urldecode($json);
    }
    //对数组中所有元素做处理
	private function arrayRecursive(&$array, $function, $apply_to_keys_also = false){
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				arrayRecursive($array[$key], $function, $apply_to_keys_also);
			}else{
				$array[$key] = $function($value);
			}
			if ($apply_to_keys_also && is_string($key)){
				$new_key = $function($key);
				if ($new_key != $key){
					$array[$new_key] = $array[$key];
					unset($array[$key]);
				}
			}
		}
	}
}