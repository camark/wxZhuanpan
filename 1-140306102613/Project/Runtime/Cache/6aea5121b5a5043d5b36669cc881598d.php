<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>转盘抽奖</title>
<style type="text/css">
.demo{width:417px; height:417px; position:relative; margin:50px auto}
#disk{width:417px; height:417px; background:url(__PUBLIC__/images/disk.jpg) no-repeat}
#start{width:163px; height:320px; position:absolute; top:46px; left:130px; cursor:pointer;}
#start img{cursor:pointer}
</style>
<script type="text/javascript" src="__PUBLIC__/js/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/jQueryRotate.2.2.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/jquery.easing.min.js"></script>
<script type="text/javascript">
$(function(){
        lottery();
});
function lottery(){
	$("#startbtn").click(function(){
		$.ajax({
			type: 'POST',
			url: '__URL__/run',
			dataType: 'json',
			cache: false,
			error: function(){
				alert('出错了！');
				return false;
			},
			success:function(json){
				var a = parseInt(json.angle); //角度
				var p = json.praisename;//奖项
				var n = json.num;//剩余抽奖次数
				if(p!="" && a!=0){
					$("#startbtn").rotate({
						duration:3000, //转动时间
						angle: 0, //默认角度
						animateTo:3600+a, //转动角度
						easing: $.easing.easeOutSine,
						callback: function(){
							var con = confirm('恭喜你，中得'+p+'您还有'+n+'次抽奖次数，还要再来一次吗？');
							$("#startbtn").rotate({angle:0});
							$("#startbtn").css("cursor","pointer");
							if(!con){
								$("#startbtn").unbind('click').css("cursor","default");
							}
						}
					});
				}else{
					alert("您已经没有抽奖次数了！");
				}
			}
		})
	})
}
</script>
</head>

<body>

<div class="demo">
        <div id="disk"></div>
        <div id="start"><img src="__PUBLIC__/images/start.png" id="startbtn"></div>
   </div>
</body>
</html>