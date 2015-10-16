<?php

namespace Admin\Controller;

use Think\Controller;

/**
 * @author 李文伟
 * 模块开发控制器
 *
 */
class DevelopController extends Controller {
	
	/**
	 * @var array
	 * 改模块支持的操作，例如：增、删、改等
	 */
	protected $MOUDLE_ACTION = null;
	
	public function __construct(){
		parent::__construct();
		$this->MOUDLE_ACTION = include dirname(__FILE__).'/../../Common/MoudleAction.php';
		include dirname(__FILE__).'/../../Common/datatype.php';
	}
	
	/**
	 * 获取系统模块列表
	 */
	public function index() {
		
		$this->getAllMoudles();
		
		$this->display ();
	}
	
	/**
	 * 新建模块
	 */
	public function add() {
		
		// 获取关联表名称
		$sql = "select id,tablename, count(distinct tablename) from my_sysmodel group by tablename";
		$list = M ()->query ( $sql );
		$this->assign ( "list", $list );
		
		// 获取支持的数据类型
		$data_type_arr = require dirname(__FILE__).'/../../Common/datatype.php';
		$this->assign('dataTypeList', $data_type_arr);
		
		// 获取页面功能
		$moudle_action = $this->getMoudleAction();
		$this->assign('MoudleAction',$moudle_action);
		
		// 获取所有一级菜单
		$this->getParentMoudle();
		
		$this->display ();
	}
	
	/**
	 * 获取系统模块列表
	 */
	public function getAllMoudles(){
		// 获取系统模块列表
		$list = M ( "v_sysmodels" )->select ();
		foreach ( $list as $key => $value ) {
			// 如果该模块是父模块
			if(!$value['sysmodelid'])
				continue;
			
			// 获取该模块支持的数据操作
			$funcs = explode ( ",", $value ["subnamefuncs"] );
			$funcname = "";
			foreach ( $funcs as $k => $kk ) {
				if ($kk == $this->MOUDLE_ACTION['ADD']) {
					$funcname .= "新增数据,";
				} else if ($kk == $this->MOUDLE_ACTION['UPDATE']) {
					$funcname .= "更新数据,";
				} else if ($kk == $this->MOUDLE_ACTION['DELETE']) {
					$funcname .= "删除数据,";
				} else if ($kk == $this->MOUDLE_ACTION['IMPORT']) {
					$funcname .= "导入,";
				} else if ($kk == $this->MOUDLE_ACTION['EXPORT']) {
					$funcname .= "导出,";
				} else if ($kk == $this->MOUDLE_ACTION['MUTI_UPLOAD']) {
					$funcname .= "图库,";
				}
			}
			$funcname = substr ( $funcname, 0, strlen ( $funcname ) - 1 );
			$list [$key] ["subnamefuncs"] = $funcname;
		}
		$this->assign ( "list", $list );
	}
	
	/**
	 * 获取表中的所有列
	 */
	public function getTableColums() {
		$tid = $_POST ["id"];
		$con ["id"] = $tid;
		$obj = M ( "my_sysmodel" )->where ( $con )->find ();
		if (empty ( $obj )) {
			$retMSG = array (
					"data" => "",
					"info" => "error",
					"status" => "1" 
			);
			echo json_encode ( $retMSG );
		} else {
			$colarr = explode ( ",", $obj ["tablecolums"] );
			$retMSG = array (
					"data" => $colarr,
					"info" => "success",
					"status" => "1" 
			);
			echo json_encode ( $retMSG );
		}
	}

	
	public function createModel() {
		$tablename = $_POST ["tablename"]; // 数据表表名称
		$topshow = $_POST ["topshow"]; // 是否在顶端显示
		$leftshow = $_POST ["leftshow"]; // 是否在左侧显示
		$yijiname = $_POST ["yijiname"]; // 一级模块名称
		$erjiname = $_POST ["erjiname"]; // 二级模块名称
		$funcs = $_POST ["func"]; // 页面功能
		$colname = $_POST ["colname"]; // 字段名称
		$dataType = $_POST ["dataType"]; // 字段类型
		$collength = $_POST ["collength"]; // 字段长度
		$datanull = $_POST ["datanull"]; // 字段是否为空
		$datapr = $_POST ["datapr"]; // 字段是否为主键
		$pageshow = $_POST ["pageshow"]; // 字段是否显示
		$pageshowname = $_POST ["pageshowname"]; // 字段显示名称
		$guanliantable = $_POST ["guanliantable"]; // 字段关联表名称
		$guanlianziduan = $_POST ["guanlianziduan"]; // 关联显示字段
		$searchstr = $_POST ["searchstr"]; // 关联查询字段
		
		$sub_model_id = 0; // 二级模块编号
		
		// @@@@@@@@@@@@@@@@@@@@@@@@@向菜单表my_model中插入菜单项-------开始
		$subData ["modelname"] = $erjiname;
		$subData ["modelurl"] = "/Admin/" . ucfirst ( $tablename ) . "/index";
		$data ["modelname"] = $yijiname;
		$menusobj = M ( "my_model" )->where ( $data )->find ();
		if (empty ( $menusobj )) {
			// 判断是否存在该父模块，如果存在，返回id
			$moudle_id = $this->getParentMoudleIdByName($yijiname);
			// 如果存在
			if($moudle_id){
				$subData ["parentid"] = $moudle_id;	
				if ($sub_model_id = M ( "my_model" )->data ( $subData )->add ()) {
				} else {
					$this->message("", "error", "1");
					return;
				}
			} else {
				// 插入一级模块名称和二级模块名称
				$data ["modelurl"] = "";
				// TODO 设置 parentid
				$data ["parentid"] = 0;
				$data ["istopshow"] = $topshow;
				$data ["isleftshow"] = $leftshow;
				if ($moudleid = M ( "my_model" )->data ( $data )->add ()) {
					$subData ["parentid"] = $moudleid;
					if ($sub_model_id = M ( "my_model" )->data ( $subData )->add ()) {
					} else {
						$this->message("", "error", "1");
						return;
					}
				}
			}
		} else {
			$con ["modelname"] = $erjiname;
			$submens = M ( "my_model" )->where ( $con )->find ();
			if (empty ( $submens )) {
				$subData ["parentid"] = $menusobj ["id"];
				if (M ( "my_model" )->data ( $subData )->add ()) {
				} else {
					$this->message("", "error", "2");
					return;
				}
			} else {
				$this->message("", "error", "3");
				return;
			}
		}
		// @@@@@@@@@@@@@@@@@@@@@@@@@向菜单表my_model中插入菜单项-------结束
		
		// @@@@@@@@@@@@@@@@@@@@@@@@@创建表---开始
		$sqlcon ["tablename"] = $tablename;
		$sqlobj = M ( "my_sysmodel" )->where ( $sqlcon )->find ();
		if (empty ( $sqlobj )) { 
			// 创建数据库表
			$sql = "create table `my_" . $tablename . "`";
			$sql .= "(";
			foreach ( $colname as $c => $cc ) {
				$datatype = $this->getDataType ( $dataType [$c] ); // 获取数据类型
				$isnull = "not null"; // 是否可空
				if ($datanull [$c] == 1) {
					$isnull = "null";
				}
				$isparmarykey = ""; // 是否是主键
				if ($datapr [$c] == 1) {
					$isparmarykey = "primary key AUTO_INCREMENT";
				}
				
				if ($c == count ( $colname ) - 1) {
					if (empty ( $collength [$c] )) {
						$sql .= "`" . $cc . "` " . $datatype . " " . $isnull . " " . $isparmarykey;
					} else {
						$sql .= "`" . $cc . "` " . $datatype . "(" . $collength [$c] . ")" . " " . $isnull . " " . $isparmarykey;
					}
				} else {
					if (empty ( $collength [$c] )) {
						$sql .= "`" . $cc . "` " . $datatype . " " . $isnull . " " . $isparmarykey . ",";
					} else {
						$sql .= "`" . $cc . "` " . $datatype . "(" . $collength [$c] . ")" . " " . $isnull . " " . $isparmarykey . ",";
					}
				}
			}
			$sql .= ")";
			M ()->query ( $sql );
		}
		// @@@@@@@@@@@@@@@@@@@@@@@@@创建表---结束
		
		// @@@@@@@@@@@@@@@@@@@@@@@@@向模块配置表my_sysmodel中插入配置项-------开始
		$syscon ["tablename"] = $tablename;
		$syscon ["subnames"] = $erjiname;
		$syscon ["_logic"] = "AND";
		$sysobj = M ( "my_sysmodel" )->where ( $syscon )->find ();
		if (! empty ( $sysobj )) {
			$this->message("", "error", "4");
			return;
		}
		$sysmodelData ["tablename"] = $tablename;
		$sysmodelData ["modelid"] = $sub_model_id;
		$sysmodelData ["modelname"] = $yijiname;
		$sysmodelData ["subnames"] = $erjiname;
		$sysmodelData ["subnamefuncs"] = $this->convertArray2str ( $funcs );
		$sysmodelData ["tablecolums"] = $this->convertArray2str ( $colname );
		$sysmodelData ["tablecolumstype"] = $this->convertArray2str ( $dataType );
		$sysmodelData ["tablecolumslength"] = $this->convertArray2str ( $collength );
		$sysmodelData ["tablecolumsisnull"] = $this->convertArray2str ( $datanull );
		$sysmodelData ["tablecolumsispramarykey"] = $this->convertArray2str ( $datapr );
		$sysmodelData ["tablecolumsishow"] = $this->convertArray2str ( $pageshow );
		$sysmodelData ["pageshowname"] = $this->convertArray2str ( $pageshowname );
		$sysmodelData ["guanliantable"] = $this->convertArray2str ( $guanliantable );
		$sysmodelData ["guanlianziduan"] = $this->convertArray2str ( $guanlianziduan );
		$sysmodelData ["searchstr"] = $this->convertArray2str ( $searchstr );
		
		// TODO 为什么这个多打印数据，是当时方便调式代码，但是没有删除吗？
		if (! $sysid = M ( "my_sysmodel" )->data ( $sysmodelData )->add ()) {			
			$this->message("", "error", 5);
			return;
		}
		// @@@@@@@@@@@@@@@@@@@@@@@@@向模块配置表my_sysmodel中插入配置项-------结束
		
		// @@@@@@@@@@@@@@@@@@@@@@@@@读取控制器模板-------开始
		$controllpath = getcwd () . "/Application/Common/Controller/ModelControllerTemplate.class.php";
		$returncontrollpath = getcwd () . "/Application/Admin/Controller/";
		$new_controller_path = $returncontrollpath . ucfirst ( $tablename ) . "Controller.class.php";
		
		// 根据该模块支持的功能，去生成相应的controller code（移除模板中多余的代码）
		include getcwd () . "/Application/Common/Controller/regionparse.php";
		$action_arr = array(); // 该模块支持的功能
		foreach ($funcs as $key => $value){
			foreach ($this->MOUDLE_ACTION as $action => $action_key){
				if ($action_key == $value){
					array_push($action_arr, $action);
				}
			}
		}
		parse_region($controllpath, $new_controller_path, $action_arr);
		
		// TODO: 如何在控制器模板中传递参数，例如可以参考bat命令传递参数的形式 %OutDir%
		// 或者 .\build.bat $(SolutionDir)$(Configuration)\ $(Platform)
		// OutDir=%1
		$f = file_get_contents ( $new_controller_path );
		$f = str_replace ( "%MoudleId%", $sysid, $f );                 // 控制器模板中所有操作都根据id来进行
		$f = str_replace ( "%TableName%", 'my_'.$tablename, $f );      // 替换控制器模板中的%TableName%参数
		if(in_array(SYS_DATA_TYPE_SUBIMAGES, $dataType)){
			$field_key = array_search(SYS_DATA_TYPE_SUBIMAGES, $dataType);
			$field_name = $colname[$field_key];
			$f = str_replace ( "%SubImagesField%", $field_name, $f );  // 替换控制器模板中的%TableName%参数
		}
		
		$f = str_replace ( "ModelController", ucfirst ( $tablename ) . "Controller", $f );
		file_put_contents ( $new_controller_path, $f );
		
		// @@@@@@@@@@@@@@@@@@@@@@@@@读取控制器模板-------结束
		
		// @@@@@@@@@@@@@@@@@@@@@@@@@读取视图模板-------开始
		$viewpath = getcwd () . "/Application/Common/View/listviewmodel.html";
		$returnviewpath = getcwd () . "/Application/Admin/view/";
		
		if(!file_exists($returnviewpath . ucfirst ($tablename))){
			if (!mkdir($returnviewpath . ucfirst ($tablename))) {
				$this->message("", "error", 1);
				return;
			}
		}
		
		$v = file_get_contents ( $viewpath );
		file_put_contents ( $returnviewpath . ucfirst ( $tablename ) . "/index.html", $v );
		
		if(in_array(MOUDLE_ACTION_ADD, $action_arr)){
			$viewpath = getcwd () . "/Application/Common/View/addviewmodel.html";
			$v = file_get_contents ( $viewpath );
			file_put_contents ( $returnviewpath . ucfirst ( $tablename ) . "/add.html", $v );
		}
		
		if(in_array(MOUDLE_ACTION_UPDATE, $action_arr)){
			$viewpath = getcwd () . "/Application/Common/View/updateviewmodel.html";
			$v = file_get_contents ( $viewpath );
			file_put_contents ( $returnviewpath . ucfirst ( $tablename ) . "/update.html", $v );
		}
		
		if(in_array(MOUDLE_ACTION_MUTI_UPLOAD, $action_arr)){			
			$viewpath = getcwd () . "/Application/Common/View/uploadviewmodel.html";
			$v = file_get_contents ( $viewpath );
			// TODO: 使用另外一种方式实现该功能。-李文伟
			
			//----------------------------------
			// TODO: [需要讨论]是用%paramater%替换页面中的变量，还是使用PHP MVC {$parameter}实现绑定
			//
			// 这个问题涉及到该系统的一个根本要素，是生成硬编码的code，还是生成{$parameter}PHP MVC灵活的绑定。
			// 1. 生成硬编码的code，例如 /Admin/Shop/export.html
			// 这种方式普通用户不需要了解系统的具体运行方式，因为生成的就是具体的code，而不是绑定相关的信息，所以便于用户进行下一步开发，
			// 而不要了解系统本身
			// 2. 使用PHP MVC {$parameter}实现绑定
			// 灵活
			//
			// 本人比较倾向于第一种方式
			// 标注人：李文伟
			// 日     期：20150930
			//----------------------------------
			$v = str_replace ( "%MoudleNameByChinese%", $erjiname, $v );
			$v = str_replace ( "%MoudleName%", ucfirst($tablename), $v );
			file_put_contents ( $returnviewpath . ucfirst ( $tablename ) . "/upload.html", $v );
			
			// TODO: 将seefu.html文件改为viewUploadedFiles.html
			$viewpath = getcwd () . "/Application/Common/View/seefuviewmodel.html";
			$v = file_get_contents ( $viewpath );
			file_put_contents ( $returnviewpath . ucfirst ( $tablename ) . "/seefu.html", $v );
		}
		
		if(in_array(MOUDLE_ACTION_IMPORT, $action_arr)){
			$viewpath = getcwd () . "/Application/Common/View/importexcelviewmodel.html";
			$v = file_get_contents ( $viewpath );
			file_put_contents ( $returnviewpath . ucfirst ( $tablename ) . "/importexcel.html", $v );
		}
		
		if(in_array(MOUDLE_ACTION_EXPORT, $action_arr)){
			// TODO: 实现数据导出功能。
		}
		
		// @@@@@@@@@@@@@@@@@@@@@@@@@读取视图模板-------结束
		
		$this->message("The moudle has created successfully", "success", 0);
	}

	
	/**
	 * 移除模板，删除相应数据库表、删除数据库中相关记录的数据、PHP Controller、PHP View等文件
	 * 
	 * 有两个功能：
	 * 1. 如果删除子模板，仅删除该模板
	 * 2. 如果删除父模板，相应应该删除所有子模板
	 */
	public function removeModel(){
		
		$model_id=$_REQUEST["id"];
		
		// 获取该模块对应的表名称
		$table_name ="";

		// TODO ThinkPHP存在不能一次执行多个（或多次）存储过程，因此考虑自定义PDO自己实现。
		
		// 删除相应的数据和该模块对应的表，获取该模块对应的表名称
		$drop_moudle_sql="call proc_DropMoudle(".$model_id.")";
		$tablenames_arr=M("")->query($drop_moudle_sql);
		
		$view_path = getcwd ()."/Application/Admin/View/";
		$controller_path = getcwd()."/Application/Admin/Controller/";
		
		foreach ($tablenames_arr as $key=>$value){
			// 删除视图
			$detail_view_path = $view_path . ucfirst ($value['tablename']);
			$this->deleteDir($detail_view_path);
			
			// 删除控制器
			$detail_controller_path = $controller_path . ucfirst ($value['tablename'])."Controller.class.php";
			if(file_exists($detail_controller_path)){
				unlink($detail_controller_path);
			}
		}
		
		$this->message("", "success", "1");
	}
	
	// TODO 为什么不把这些方法封装到一个公共类库里面。
	/**
	 * 删除目录，以及该目录下所有的文件
	 * @param string $dirPath
	 * @throws InvalidArgumentException
	 */
	private static function deleteDir($dirPath){
		if(!is_dir($dirPath)){
			//throw new InvalidArgumentException("$dirPath must be a directory");
			return ;
		}
		
		$files = glob($dirPath . '*', GLOB_MARK);
		foreach ($files as $file) {
			if (is_dir($file)) {
				self::deleteDir($file);
			} else {
				unlink($file);
			}
		}
		rmdir($dirPath);
	}
	
	private function message($data, $info, $status){
		$retMSG = array (
				"data"   => $data,
				"info"   => $info,
				"status" => $status
		);
		echo json_encode ( $retMSG );
	}
	
	/**
	 * 检查是否多表
	 * @param int $id 编号
	 */
	public function checkMoreTable($id) {
		$where ["id"] = $id;
		$obj = M ( "my_sysmodel" )->where ( $where )->find ();
		$guanlianbiao = $obj ["guanliantable"];
		$garr = explode ( ",", $guanlianbiao );
		$re = 0;
		foreach ( $garr as $key => $val ) {
			if ($val != 0) {
				$re = 1;
				break;
			}
		}
		return $re;
	}
	
	public function getsqlcolums($arr) {
		$str = "";
		foreach ( $arr as $key => $value ) {
			if ($key == count ( $arr ) - 1) {
				$str .= "`" . $value . "`";
			} else {
				$str .= "`" . $value . "`" . ",";
			}
		}
		return $str;
	}
	
	public function getthcolumnames($arr, $showlist, $pageshow) {
		$str = "";
		foreach ( $arr as $key => $value ) {
			if ($key == count ( $arr ) - 1) {
				if ($pageshow [$key]) {
					$str .= $showlist [$key];
				}
			} else {
				if ($pageshow [$key]) {
					$str .= $showlist [$key] . ",";
				}
			}
		}
		return $str;
	}
	
	
	/**
	 * 获取所有一级菜单, 不包含“模块开发”。
	 * 此方法主要是为了在模块开发页面，生成一级模块，以供选择
	 */
	public function getParentMoudle(){
		
		$where ["parentid"] = 0;
		$where ["isleftshow"] = 1;
		$where ["modelname"] = array("neq","模块开发"); // !="模块开发"
		$where ["_logic"] = "AND";
		
		$parent_moudles = M("my_model")->where($where)->select();
		
		$this->assign('parentMoudleList',$parent_moudles);
	}
	
	
	/**
	 * 根据父菜单名字，获取父菜单id
	 * 
	 * @param string $name 父菜单名称
	 * 
	 * @return 父菜单编号，id
	 */
	private function getParentMoudleIdByName($name){
		
		$where ["parentid"] = 0;
		$where ["isleftshow"] = 1;
		$where ["modelname"] = array("neq","模块开发"); // !="模块开发"
		$where ["modelname"] = $name;
		$where ["_logic"] = "AND";
		
		$parent_moudle = M("my_model")->where($where)->select();
		if(count($parent_moudle)!=1){
			return false;
		}
		
		return $parent_moudle[0]['id'];
		
	}
	
	
	/**
	 * 获取和显示页面功能列表
	 */
	private function getMoudleAction(){
		
		$moudle_action_arr = require dirname(__FILE__).'/../../Common/MoudleAction.php';
		
		$moudle_action_html = "";
		foreach ($moudle_action_arr as $action=> $value){
			if ($action == 'ADD'){
				$moudle_action_html .= "<input type=\"checkbox\" name=\"func[]\" value=\"$value\" checked=\"checked\"/> 新增数据&nbsp;&nbsp;";
			} else if ($action == 'UPDATE'){
				$moudle_action_html .= "<input type=\"checkbox\" name=\"func[]\" value=\"$value\" checked=\"checked\"/> 修改数据&nbsp;&nbsp;";
			} else if ($action == 'DELETE'){
				$moudle_action_html .= "<input type=\"checkbox\" name=\"func[]\" value=\"$value\" checked=\"checked\"/> 删除数据&nbsp;&nbsp;";
			} else if ($action == 'IMPORT'){
				$moudle_action_html .= "<input type=\"checkbox\" name=\"func[]\" value=\"$value\"/> 导入数据&nbsp;&nbsp;";
			} else if ($action == 'EXPORT'){
				$moudle_action_html .= "<input type=\"checkbox\" name=\"func[]\" value=\"$value\"/> 导出数据&nbsp;&nbsp;";
			} else if ($action == 'MUTI_UPLOAD'){
				$moudle_action_html .= "<input type=\"checkbox\" name=\"func[]\" value=\"$value\"/> 批量上传文件&nbsp;&nbsp;";
			}
		}
		
		return $moudle_action_html;
	} 
	
	/**
	 * 获取模块及子模块名称
	 * @param string $modelname 模块名称
	 */
	public function getsubnames($modelname) {
		$where ["modelname"] = $modelname;
		$obj = M ( "my_model" )->where ( $where )->find ();
		$con ["parentid"] = $obj ["id"];
		$sublist = M ( "my_model" )->where ( $con )->select ();
		$subnames = "";
		foreach ( $sublist as $key => $value ) {
			if ($key == count ( $sublist ) - 1) {
				$subnames .= $value ["modelname"];
			} else {
				$subnames .= $value ["modelname"] . ",";
			}
		}
		return $subnames;
	}

	
	/**
	 * 将数组转为字符串
	 * @param array $arr 数组？
	 */
	private function convertArray2str($arr) {
		$str = "";
		foreach ( $arr as $key => $value ) {
			if ($key == count ( $arr ) - 1) {
				$str .= $value;
			} else {
				$str .= $value . ",";
			}
		}
		return $str;
	}
	
	
	/**
	 * 根据系统自定义的获取数据类型对应到MySQL数据库中的数据类型
	 * 
	 * 例如：image -> varchar
	 * @param int $key 键值
	 */
	public function getDataType($key) {
		// TODO 将key定义为常量
		if ($key == 0) {
			return "int";
		}
		if ($key == 1) {
			return "float";
		}
		if ($key == 2) {
			return "double";
		}
		if ($key == 3) {
			return "varchar";
		}
		if ($key == 4) {
			return "date";
		}
		if ($key == 5) {
			return "datetime";
		}
		if ($key == 6) {
			return "timestamp";
		}
		if ($key == 7) {
			return "text";
		}
		if ($key == 8) {
			return "varchar";
		}
		if ($key == 9) {
			return "varchar";
		}
	}
}