<?php

namespace Admin\Controller;

use Common\Controller\BaseController;

class IndexController extends BaseController {
	
	// TODO 为什么所有模块的Model会自动生成在$EasyIMS/Application/Runtime/Data/_fields里面，给这方面添加相关的注释和说明，方便维护。
	public function index() {
		if (empty ( $_SESSION ["user"] )) {
			$this->redirect ( "Admin/Login/index" );
		} else {
			if ($_SESSION ["user"] == "admin") {
				// 生成顶部菜单
				$where ["parentid"] = 0;
				$where ["istopshow"] = 1;
				$where ["_logic"] = "AND";
				$topobj = M ( "my_model" )->where ( $where )->select ();
				$topArray = null;
				foreach ( $topobj as $key => $value ) {
					$topArray [$key] ["name"] = $value ["modelname"];
					$condition ["parentid"] = $value ["id"];
					$sublist = M ( "my_model" )->where ( $condition )->select ();
					foreach ( $sublist as $a => $b ) {
						$topArray [$key] ["li"] [$a] ["name"] = $b ["modelname"];
						$topArray [$key] ["li"] [$a] ["url"] = $b ["modelurl"];
					}
				}
				
				// 生成左侧菜单
				$whereleft ["parentid"] = 0;
				$whereleft ["isleftshow"] = 1;
				$whereleft ["_logic"] = "AND";
				$topobj = M ( "my_model" )->where ( $whereleft )->select ();
				$leftArray = null;
				foreach ( $topobj as $key => $value ) {
					$leftArray [$key] ["name"] = $value ["modelname"];
					$condition ["parentid"] = $value ["id"];
					$sublist = M ( "my_model" )->where ( $condition )->select ();
					foreach ( $sublist as $a => $b ) {
						$leftArray [$key] ["li"] [$a] ["name"] = $b ["modelname"];
						$leftArray [$key] ["li"] [$a] ["url"] = $b ["modelurl"];
					}
				}
				$this->assign ( "TOPLIST", $topArray );
				$this->assign ( "LEFTLIST", $leftArray );
				$this->display ();
			} else {
				// 生成顶部菜单
				$id = $_SESSION ["user"];
				$rwhere ["id"] = $id;
				$one = M ( "my_admingl" )->where ( $rwhere )->find ();
				$rlemap ["type"] = $one ["type"];
				$roleobj = M ( "my_role" )->where ( $rlemap )->find ();
				$rolelist = explode ( ",", $roleobj ["names"] );
				$where ["parentid"] = 0;
				$where ["istopshow"] = 1;
				$where ["_logic"] = "AND";
				$topobj = M ( "my_model" )->where ( $where )->select ();
				$topArray = null;
				foreach ( $topobj as $key => $value ) {
					if (in_array ( $value ["modelurl"], $rolelist )) {
						$topArray [$key] ["name"] = $value ["modelname"];
						$condition ["parentid"] = $value ["id"];
						$sublist = M ( "my_model" )->where ( $condition )->select ();
						foreach ( $sublist as $a => $b ) {
							$topArray [$key] ["li"] [$a] ["name"] = $b ["modelname"];
							$topArray [$key] ["li"] [$a] ["url"] = $b ["modelurl"];
						}
					}
				}
				
				// 生成左侧菜单
				$whereleft ["parentid"] = 0;
				$whereleft ["isleftshow"] = 1;
				$whereleft ["_logic"] = "AND";
				$topobj = M ( "my_model" )->where ( $whereleft )->select ();
				$leftArray = null;
				foreach ( $topobj as $key => $value ) {
					if (in_array ( $value ["modelurl"], $rolelist )) {
						$leftArray [$key] ["name"] = $value ["modelname"];
						$condition ["parentid"] = $value ["id"];
						$sublist = M ( "my_model" )->where ( $condition )->select ();
						foreach ( $sublist as $a => $b ) {
							$leftArray [$key] ["li"] [$a] ["name"] = $b ["modelname"];
							$leftArray [$key] ["li"] [$a] ["url"] = $b ["modelurl"];
						}
					}
				}
				
				$this->assign ( "TOPLIST", $topArray );
				
				$this->assign ( "LEFTLIST", $leftArray );
				
				$this->display ();
			}
		}
	}
}