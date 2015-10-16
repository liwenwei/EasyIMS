<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller {
	public function index(){
		$this->display();
	}
public function checkLogin(){
		$username=$_POST["username"];
		$userpwd=$_POST["userpwd"];
		if($username=="admin"&&md5($userpwd)==C("SYSTEMPASSWORLD")){
			$_SESSION["user"]="admin";
			$this->ajaxReturn("success");
		}else{//验证权限
			$where["account"]=$username;
			$where["pwd"]=md5($userpwd);
			$where["_logic"]="AND";
			$obj=M("my_admingl")->where($where)->find();
			if(!empty($obj)){
				$_SESSION["user"]=$obj["id"];
				$this->ajaxReturn("success");
			}
			else{
				$this->ajaxReturn("error");
			}
		}
	}
	public function layout(){
		if(isset($_SESSION["user"])) {
			unset($_SESSION["user"]);
			unset($_SESSION);
			session_destroy();
			$this->ajaxReturn("success");
		}else {
			$this->ajaxReturn("success");
		}
	}
}