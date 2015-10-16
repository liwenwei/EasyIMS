<?php
namespace Common\Controller;
use Think\Controller;
/**
 * 前后台公用基类
 * modify author : 张雪锋
 * modify time : 2015-2-5
 */
class BaseController extends Controller{
	//判定是否登录系统
	function _initialize() {
		if(empty($_SESSION["user"])){
			echo "<script LANGUAGE='JavaScript'>
				  window.top.location='/Admin/Login/index'; 
				  </script>";
		}
		//判定用户是否从浏览器地址中直接进入
	    if(empty($_SERVER["HTTP_REFERER"])){
			echo "<script LANGUAGE='JavaScript'>
				  window.top.location='/Admin/Error/errorPage.html'; 
				  </script>";
		} 
	}
}