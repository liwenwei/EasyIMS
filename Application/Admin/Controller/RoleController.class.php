<?php
namespace Admin\Controller;
use Common\Controller\BaseController;
class RoleController extends BaseController{
	public function index(){
		$where["desc"]="系统管理员";
		$list=M("my_constant")->where($where)->select();
		$this->assign("list",$list);
		$this->display();
	}
	public function getadminrole(){
		$where["type"]=$_REQUEST["id"];
		$obj=M("my_role")->where($where)->find();
		if(empty($obj["names"])){
			$msg=array("data"=>$obj["names"],"info"=>"error","status"=>1);
			echo json_encode($msg);
		}else{
			$msg=array("data"=>$obj["names"],"info"=>"success","status"=>1);
			echo json_encode($msg);
		}
	}
	public function insertrole(){
		$rolelx=$_POST["rolelx"];
		$where["type"]=$rolelx;
		M("my_role")->where($where)->delete();
		$rolelist=$_POST["roles"];
		$rolw="";
		foreach ($rolelist as $key=>$value){
			$rolw.=$value.",";
		}
		$rolw=substr($rolw, 0,strlen($rolw)-1);
		$data["names"]=$rolw;
		$data["type"]=$rolelx;
		 if(M("my_role")->add($data)){
			$msg=array("data"=>"授权成功","info"=>"success","status"=>1);
			echo json_encode($msg);
		}else{
			$msg=array("data"=>"授权失败","info"=>"error","status"=>1);
			echo json_encode($msg);
		} 
		
	}
}