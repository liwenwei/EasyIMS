<?php

namespace Admin\Controller;

use Think\Controller;

/**
 * @author 李文伟
 *
 */
class ModelController extends Controller {
	
	/**
	 * @var array
	 * 支持的数据类型
	 */
	protected $DATA_TYPE = null;
	
	/**
	 * @var array
	 * 改模块支持的操作，例如：增、删、改等
	 */
	protected $MOUDLE_ACTION = null;
	
	public function __construct(){
		parent::__construct();
		$this->DATA_TYPE = include dirname(__FILE__).'/../../Common/datatype.php';
		$this->MOUDLE_ACTION = include dirname(__FILE__).'/../../Common/MoudleAction.php';
	}
	
	/**
	 * 默认控制器执行方法
	 */
	public function index() {
		$modelInfo = $this->getModelinfo (); // 获取模块配置信息
		$begin = $_REQUEST ["begintime"];
		$end   = $_REQUEST ["endtime"];
		$page = null;
		$list = null;
		if (! empty ( $begin ) && ! empty ( $end )) { // 搜索
			$begin = $begin . " 00:00:00";
			$end = $end . " 23:59:59";
			$moreTable = $this->checkMoreTable ( $modelInfo ["modelcolumsguanliantable"], $modelInfo ["modelcolumsguanlianziduan"] );
			if (empty ( $moreTable )) { // 单表查询
				$map ['ctime'] = array ( array ('egt',$begin), array ('elt', $end) );
				$count = M ( "my_" . $modelInfo ["modelname"] )->where ( $map )->count (); // 查询满足要求的总记录数
				$Page = new \Think\Page ( $count, 10 ); // 实例化分页类 传入总记录数和每页显
				$list = M ( "my_" . $modelInfo ["modelname"] )->where ( $map )->order ( "my_" . $modelInfo ["modelname"] . ".id" )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
			} else {
				// 第一步获取符合条件的单表数据
				$map ['ctime'] = array ( array ('egt', $begin),array ('elt', $end) );
				$count = M ( "my_" . $modelInfo ["modelname"] )->where ( $map )->count (); // 查询满足要求的总记录数
				$Page = new \Think\Page ( $count, 10 ); // 实例化分页类 传入总记录数和每页显
				$list = M ( "my_" . $modelInfo ["modelname"] )->where ( $map )->order ( "my_" . $modelInfo ["modelname"] . ".id" )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
				// 第二步遍历数据集合的每一列
				foreach ( $list as $dl => $dll ) {
					$ffg = 0;
					foreach ( $dll as $llk => $llkk ) {
						if (! empty ( $moreTable [$ffg] )) {
							$name = $moreTable [$ffg] ["glb"]; // 连接表名
							$showname = $moreTable [$ffg] ["glzd"]; // 显示字段
							$gdata = M ( "my_" . $name )->where ( "id=" . $llkk )->find ();
							$list [$dl] [$llk] = $gdata [$showname];
						}
						$ffg ++;
					}
				}
			}
		} else {
			$moreTable = $this->checkMoreTable ( $modelInfo ["modelcolumsguanliantable"], $modelInfo ["modelcolumsguanlianziduan"] );
			if (empty ( $moreTable )) { // 单表查询
				$count = M ( "my_" . $modelInfo ["modelname"] )->count (); // 查询满足要求的总记录数
				$Page = new \Think\Page ( $count, 10 ); // 实例化分页类 传入总记录数和每页显
				$list = M ( "my_" . $modelInfo ["modelname"] )->order ( "my_" . $modelInfo ["modelname"] . ".id" )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
			} else {
				$count = M ( "my_" . $modelInfo ["modelname"] )->count (); // 查询满足要求的总记录数
				$Page = new \Think\Page ( $count, 10 ); // 实例化分页类 传入总记录数和每页显
				$list = M ( "my_" . $modelInfo ["modelname"] )->order ( "my_" . $modelInfo ["modelname"] . ".id" )->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
				// 第二步遍历数据集合的每一列
				foreach ( $list as $dl => $dll ) {
					$ffg = 0;
					foreach ( $dll as $llk => $llkk ) {
						if (! empty ( $moreTable [$ffg] )) {
							$name = $moreTable [$ffg] ["glb"]; // 连接表名
							$showname = $moreTable [$ffg] ["glzd"]; // 显示字段
							$gdata = M ( "my_" . $name )->where ( "id=" . $llkk )->find ();
							$list [$dl] [$llk] = $gdata [$showname];
						}
						$ffg ++;
					}
				}
			}
		}
		$show = $Page->show (); // 分页显示输出
		$dataContent = "";
		foreach ( $list as $li => $va ) {
			$fllg = 0;
			$dataContent .= "<tr>";
			foreach ( $va as $v => $vv ) {
				if ($modelInfo ["modelcolumsisshow"] [$fllg] == 1) {
					if ($modelInfo ["modelcolumstype"] [$fllg] == 8) { // 图片类型
						$dataContent .= "<td><a href='" . $vv . "' target='block'><img style='width:50px;heigth:50px;' src='" . $vv . "'/></a></td>";
					} else {
						if ($modelInfo ["modelcolumstype"] [$fllg] == 9) {
							if (empty ( $vv )) {
								$dataContent .= "<td style='color:red'>暂无附图</td>";
							} else {
								$dataContent .= "<td>有附图</td>";
							}
						} else {
							$dataContent .= "<td>" . $this->csubstr ( strip_tags ( $vv ), 0, 10, "utf-8", true ) . "</td>";
						}
					}
				}
				$fllg ++;
			}
			$dataContent .= "<td>";
			#region update
			$dataContent .= "<a style='cursor:pointer' onclick=\"updateinfo(" . $va ["id"] . ")\">修改</a>&nbsp;&nbsp;";
			#endregion
			
			#region delete
			$dataContent .= "<a style='cursor:pointer' onclick=\"delinfo(" . $va ["id"] . ")\">删除</a>&nbsp;&nbsp;";
			#endregion
			
			#region muti_upload
			if ($this->getmorepics ()) {
				$dataContent .= "<a style='cursor:pointer;color:#036CB4' onclick=\"uploadfu(" . $va ["id"] . ")\">上传附图</a>&nbsp;&nbsp;";
				$dataContent .= "<a style='cursor:pointer;color:#036CB4' onclick=\"seefu(" . $va ["id"] . ")\">查看附图</a>";
			}
			#endregion
			$dataContent .= "</td></tr>";
		}
		$modelshowcolums = $this->getShowColums ( $modelInfo ["modelcolumsisshow"], $modelInfo ["modelcolumschinaname"] );
		$modelfuncs = $this->getmodelfuncs ( $modelInfo ["modelfuncs"] );
		$this->assign ( "modelname", ucfirst ( $modelInfo ["modelname"] ) );
		$this->assign ( "modelchinaname", $modelInfo ["modelchinaname"] );
		$this->assign ( "modelsubchinaname", $modelInfo ["modelsubchinaname"] );
		$this->assign ( "modelfuncs", $modelfuncs );
		$this->assign ( "modelcolumschinaname", $modelshowcolums );
		$this->assign ( "modelcolumscontext", $dataContent );
		$this->assign ( "page", $show );
		$this->display ();
	}
	
	#region import
	public function importexcel() {
		$modelInfo = $this->getModelinfo (); // 获取模块配置信息
		$this->assign ( "modelname", ucfirst ( $modelInfo ["modelname"] ) );
		$this->assign ( "modelchinaname", $modelInfo ["modelchinaname"] );
		$this->display ();
	}
	
	public function saveexcel() {
		// 上传
		$upload = new \Think\Upload (); // 实例化上传类
		$upload->maxSize = 3145728;
		$upload->rootPath = './upload/';
		$upload->savePath = '';
		$upload->saveName = array (
				'uniqid',
				'' 
		); // uniqid函数生成一个唯一的字符串序列。
		$upload->exts = array (
				'xlsx',
				'xls' 
		);
		$upload->autoSub = true;
		$upload->subName = 'excel';
		$info = $upload->upload ();
		if ($info) {
			// 解析excel
			$modelInfo = $this->getModelinfo (); // 获取模块配置信息
			$modelcolums = $modelInfo ["modelcolums"];
			$modelcolumstype = $modelInfo ["modelcolumstype"];
			$ABCArr = array (
					"A",
					"B",
					"C",
					"D",
					"E",
					"F",
					"G",
					"H",
					"I",
					"J",
					"K",
					"L",
					"M",
					"N",
					"O",
					"P",
					"Q",
					"R",
					"S",
					"T",
					"U",
					"V",
					"W",
					"X",
					"Y",
					"Z" 
			);
			vendor ( "PHPExcel.PHPExcel" );
			$file_name = getcwd () . "/upload/excel/" . $info ["file_upload"] ['savename'];
			if ($_FILES ["myfile"] ["type"] == "application/vnd.ms-excel") {
				$objReader = \PHPExcel_IOFactory::createReader ( 'Excel5' );
			} else {
				$objReader = \PHPExcel_IOFactory::createReader ( 'Excel2007' );
			}
			$objPHPExcel = $objReader->load ( $file_name, $encode = 'utf-8' );
			$sheet = $objPHPExcel->getSheet ( 0 );
			$highestRow = $sheet->getHighestRow (); // 取得总行数
			$highestColumn = $sheet->getHighestColumn (); // 取得总列数
			for($i = 2; $i <= $highestRow; $i ++) {
				$saveData = null;
				foreach ( $modelcolums as $ms => $mss ) {
					if ($mss == "id" | $mss == "ctime" | $mss == "mtime") {
						continue;
					}
					if ($modelcolumstype [$ms] == $this->DATA_TYPE['IMAGE']) {
						$saveData [$mss] = "/upload/morepic/" . $objPHPExcel->getActiveSheet ()->getCell ( $ABCArr [$ms] . $i )->getValue ();
					} else {
						$saveData [$mss] = $objPHPExcel->getActiveSheet ()->getCell ( $ABCArr [$ms] . $i )->getValue ();
					}
				}
				$saveData ["ctime"] = date ( 'Y-m-d H:i:s', time () );
				$saveData ["mtime"] = date ( 'Y-m-d H:i:s', time () );
				M ( "my_" . $modelInfo ["modelname"] )->data ( $saveData )->add ();
			}
			$retMSG = array (
					"data" => $_POST,
					"info" => "success",
					"status" => "1" 
			);
			echo json_encode ( $retMSG );
		} else {
			$retMSG = array (
					"data" => $_POST,
					"info" => "error",
					"status" => "1" 
			);
			echo json_encode ( $retMSG );
		}
	}
	
	public function import() {
		$modelInfo = $this->getModelinfo (); // 获取模块配置信息
		$this->assign ( "modelname", ucfirst ( $modelInfo ["modelname"] ) );
		$this->assign ( "modelchinaname", $modelInfo ["modelchinaname"] );
		$this->display ();
	}
	#endregion
	
	#region muti_upload
	
	
	/**
	 * 上传附图
	 */
	public function insertmopic(){
		$id=$_POST["id"];
		$where["id"]=$id;
		$subimages=$_POST["subimages"];
		$data["%SubImagesField%"]=$subimages;
		M("%TableName%")->where($where)->data($data)->save();
	}
	
	/**
	 * 查看附图
	 */
	public function seefu() {
		$id = $_REQUEST ["id"];
		$modelInfo = $this->getModelinfo (); // 获取模块配置信息
		$obj = M ( "my_" . $modelInfo ["modelname"] )->where ( "id='" . $id . "'" )->find ();
		$fu = $obj ["%SubImagesField%"];
		$showhtml = "";
		if (! empty ( $fu )) {
			$fuarr = explode ( ",", $fu );
			foreach ( $fuarr as $key => $value ) {
				if (! empty ( $value )) {
					$showhtml .= "<li>";
					$showhtml .= "<img src=\"/upload/morepic/" . $value . "\"/>";
					$showhtml .= "<span id=\"" . $id . "\" name=\"" . $value . "\" style='cursor:pointer' onclick='delimginfo(this)'>删除</span>";
					$showhtml .= "</li>";
				}
			}
		}
		$this->assign ( "showhtml", $showhtml );
		$this->assign ( "modelname", ucfirst ( $modelInfo ["modelname"] ) );
		$this->display ();
	}
	
	public function savemorepic() {
		// 上传
		$upload = new \Think\Upload (); // 实例化上传类
		$upload->maxSize = 0;
		$upload->rootPath = './upload/';
		$upload->savePath = '';
		$upload->saveName = array (
				'uniqid',
				'' 
		); // uniqid函数生成一个唯一的字符串序列。
		$upload->exts = array (
				'jpg',
				'gif',
				'png',
				'jpeg' 
		);
		$upload->autoSub = true;
		$upload->subName = 'morepic';
		$info = $upload->upload ();
		$retMSG = array (
				"data" => "",
				"info" => "success",
				"status" => "1" 
		);
		if (! $info) { // 上传错误提示错误信息
			$retMSG ["data"] = $this->error ( $upload->getError () );
			$retMSG ["info"] = "error";
		} else { // 上传成功 获取上传文件信息
			foreach ( $info as $file ) {
				$retMSG ["data"] = $file ['savename'];
			}
		}
		echo json_encode ( $retMSG );
	}
	
	
	/**
	 * 删除附图，可以是单个，也可以是多个
	 */
	public function delimage() {
		$modelInfo = $this->getModelinfo (); // 获取模块配置信息
		$id = $_REQUEST ["id"];
		$imagename = $_REQUEST ["imagename"];
		// TODO: 将数据库表名前缀'my_'提取出来，变为全局常量。-李文伟
		$obj = M ( "my_" . $modelInfo ["modelname"] )->where ( "id='" . $id . "'" )->find ();
		$fu = $obj ["%SubImagesField%"];
		$showhtml = "";
		if (! empty ( $fu )) {
			$fuarr = explode ( ",", $fu );
			foreach ( $fuarr as $key => $value ) {
				if ($value != $imagename) {
					$showhtml .= $value . ",";
				}
			}
		}
		$showhtml = substr ( $showhtml, 0, strlen ( $showhtml ) - 1 );
		$data ["%SubImagesField%"] = $showhtml;
		if (M ( "my_" . $modelInfo ["modelname"] )->where ( "id='" . $id . "'" )->save ( $data )) {
			$retMSG = array (
					"data" => "",
					"info" => "success",
					"status" => "1"
			);
			echo json_encode ( $retMSG );
		} else {
			$retMSG = array (
					"data" => "",
					"info" => "error",
					"status" => "1"
			);
			echo json_encode ( $retMSG );
		}
	}
	#endregion
	
	#region add
	
	public function add() {
		$modelInfo = $this->getModelinfo (); // 获取模块配置信息
		$modelcolums = $modelInfo ["modelcolums"]; // 模块字段列表
		$modelcolumstype = $modelInfo ["modelcolumstype"]; // 获取字段类型列表
		$modelcolumschinaname = $modelInfo ["modelcolumschinaname"]; // 模块字段中文名称
		$moreTable = $this->checkMoreTable ( $modelInfo ["modelcolumsguanliantable"], $modelInfo ["modelcolumsguanlianziduan"] );
		$modelcolumssearchstr = $modelInfo ["modelcolumssearchstr"];
		$savehtml = "";
		$editorhtml = "";
		$sync = "";
		foreach ( $modelcolums as $m => $mm ) {
			if ($mm == "id" | $mm == "ctime" | $mm == "mtime" | $mm == "subimages") {
				continue;
			}
			if ($modelcolumstype [$m] == $this->DATA_TYPE['IMAGE']) {
				$savehtml .= "<tr>";
				$savehtml .= "<td class=\"tdl\">" . $modelcolumschinaname [$m] . "</td>";
				$savehtml .= "<td>";
				$savehtml .= "<input name=\"" . $mm . "\" type=\"file\" class=\"ipt\" style=\"width:400px;\" />";
				$savehtml.="</td>";
				$savehtml.="</tr>";
				continue;
			}
			if($modelcolumstype[$m]==$this->DATA_TYPE['DATETIME']){
				$savehtml.="<tr>";
				$savehtml.="<td class=\"tdl\">".$modelcolumschinaname[$m]."</td>";
				$savehtml.="<td>";
				$savehtml.="<input name=\"".$mm."\" type=\"text\" class=\"ipt\" onclick=\"WdatePicker({ dateFmt: 'yyyy-MM-dd ' })\" style=\"width:400px;\" />";
				$savehtml .= "</td>";
				$savehtml .= "</tr>";
				continue;
			}
			if ($modelcolumstype [$m] == $this->DATA_TYPE['TEXT']) {
				$sync .= $mm . ".sync();";
				$editorhtml .= "var " . $mm . "; ";
				$editorhtml .= "KindEditor.ready(function (K) { ";
				$editorhtml .= $mm . " = K.create('textarea[name=\"" . $mm . "]', { ";
				$editorhtml .= "resizeType: 1,";
				$editorhtml .= "allowPreviewEmoticons: false,";
				$editorhtml .= "allowImageUpload: true,";
				$editorhtml .= "items: [";
				$editorhtml .= "'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',";
				$editorhtml .= "'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',";
				$editorhtml .= "'insertunorderedlist', '|', 'emoticons', 'image', 'link']";
				$editorhtml .= "});";
				$editorhtml .= "});";
				$savehtml .= "<tr>";
				$savehtml .= "<td class=\"tdl\">" . $modelcolumschinaname [$m] . "</td>";
				$savehtml .= "<td>";
				$savehtml .= " <textarea name=\"" . $mm . "\" style=\"width:800px;height:200px;visibility:hidden;\"></textarea>";
				$savehtml .= "</td>";
				$savehtml .= "</tr>";
				continue;
			}
			if (! empty ( $moreTable )) { // 关联字段
				if ($moreTable [$m] != 0) {
					$obj = M ( "my_" . $moreTable [$m] ["glb"] )->where ( $modelcolumssearchstr [$m] )->select ();
					$optionhtml = "";
					foreach ( $obj as $o => $oo ) {
						$optionhtml .= "<option value=\"" . $oo ["id"] . "\">" . $oo [$moreTable [$m] ["glzd"]] . "</option>";
					}
					$savehtml .= "<tr>";
					$savehtml .= "<td class=\"tdl\">" . $modelcolumschinaname [$m] . "</td>";
					$savehtml .= "<td>";
					$savehtml .= "<select  class=\"ipt\" size=\"1\" name=\"" . $mm . "\" style=\"width:400px;\">";
					$savehtml .= $optionhtml;
					$savehtml .= "</select>";
					$savehtml .= "</td>";
					$savehtml .= "</tr>";
					continue;
				}
			}
			$savehtml .= "<tr>";
			$savehtml .= "<td class=\"tdl\">" . $modelcolumschinaname [$m] . "</td>";
			$savehtml .= "<td>";
			$savehtml .= "<input name=\"" . $mm . "\" type=\"text\" class=\"ipt\" style=\"width:400px;\" />";
			$savehtml .= "</td>";
			$savehtml .= "</tr>";
		}
		$this->assign ( "sync", $sync );
		$this->assign ( "editorstr", $editorhtml );
		$this->assign ( "savehtml", $savehtml );
		$this->assign ( "modelchinaname", $modelInfo ["modelchinaname"] );
		$this->assign ( "modelname", ucfirst ( $modelInfo ["modelname"] ) );
		$this->display ();
	}
	
	public function saveadd() {
		$modelInfo = $this->getModelinfo (); // 获取模块配置信息
		// 上传
		$upload = new \Think\Upload (); // 实例化上传类
		$upload->maxSize = 3145728;
		$upload->rootPath = './upload/';
		$upload->savePath = '';
		$upload->saveName = array (
				'uniqid',
				''
		); // uniqid函数生成一个唯一的字符串序列。
		$upload->exts = array (
				'jpg',
				'gif',
				'png',
				'jpeg'
		);
		$upload->autoSub = true;
		$upload->subName = array (
				'date',
				'Ymd'
		);
		$info = $upload->upload ();
		$modelfilecolums = $this->getmodelfilecolums ( $modelInfo );
		if (! empty ( $modelfilecolums )) {
			foreach ( $modelfilecolums as $ms => $mss ) {
				if ($info [$mss]) {
					$_POST [$mss] = '/upload/' . $info [$mss] ['savepath'] . $info [$mss] ['savename'];
				}
			}
		}
		$_POST ['ctime'] = date ( 'Y-m-d H:i:s', time () );
		$_POST ['mtime'] = date ( 'Y-m-d H:i:s', time () );
		$User = M ( "my_" . $modelInfo ["modelname"] );
		// 根据表单提交的POST数据创建数据对象
		if ($User->create ()) {
			$result = $User->add (); // 写入数据到数据库
			if ($result) {
				$retMSG = array (
						"data" => "",
						"info" => "success",
						"status" => "1"
				);
				echo json_encode ( $retMSG );
			} else {
				$retMSG = array (
						"data" => "",
						"info" => "error",
						"status" => "1"
				);
				echo json_encode ( $retMSG );
			}
		}
	}
	#endregion
	
	#region update
	public function update() {
		$modelInfo = $this->getModelinfo (); // 获取模块配置信息
		$id = $_REQUEST ["id"];
		// 获取记录信息
		$objinfo = M ( "my_" . $modelInfo ["modelname"] )->where ( "id='" . $id . "'" )->find ();
		$modelcolums = $modelInfo ["modelcolums"]; // 模块字段列表
		$modelcolumstype = $modelInfo ["modelcolumstype"]; // 获取字段类型列表
		$modelcolumschinaname = $modelInfo ["modelcolumschinaname"]; // 模块字段中文名称
		$moreTable = $this->checkMoreTable ( $modelInfo ["modelcolumsguanliantable"], $modelInfo ["modelcolumsguanlianziduan"] );
		$modelcolumssearchstr = $modelInfo ["modelcolumssearchstr"];
		$savehtml = "";
		$editorhtml = "";
		$sync = "";
		foreach ( $modelcolums as $m => $mm ) {
			if ($mm == "id" | $mm == "ctime" | $mm == "mtime" | $mm == "subimages") {
				continue;
			}
			if ($modelcolumstype [$m] == $this->DATA_TYPE['IMAGE']) {
				$savehtml .= "<tr>";
				$savehtml .= "<td class=\"tdl\">" . $modelcolumschinaname [$m] . "</td>";
				$savehtml .= "<td>";
				$savehtml .= "<input name=\"" . $mm . "\" type=\"file\" class=\"ipt\" style=\"width:400px;\" />";
				$savehtml .= "</td>";
				$savehtml .= "</tr>";
				continue;
			}
			if($modelcolumstype[$m]==$this->DATA_TYPE['DATETIME']){
				$savehtml.="<tr>";
				$savehtml.="<td class=\"tdl\">".$modelcolumschinaname[$m]."</td>";
				$savehtml.="<td>";
				$savehtml.="<input name=\"".$mm."\" value=\"".$objinfo[$mm]."\" type=\"text\" class=\"ipt\" onclick=\"WdatePicker({ dateFmt: 'yyyy-MM-dd ' })\" style=\"width:400px;\" />";
				$savehtml.="</td>";
				$savehtml.="</tr>";
				continue;
			}
			if ($modelcolumstype [$m] == $this->DATA_TYPE['TEXT']) {
				$sync .= $mm . ".sync();";
				$editorhtml .= "var " . $mm . "; ";
				$editorhtml .= "KindEditor.ready(function (K) { ";
				$editorhtml .= $mm . " = K.create('textarea[name=\"" . $mm . "]', { ";
				$editorhtml .= "resizeType: 1,";
				$editorhtml .= "allowPreviewEmoticons: false,";
				$editorhtml .= "allowImageUpload: true,";
				$editorhtml .= "items: [";
				$editorhtml .= "'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',";
				$editorhtml .= "'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',";
				$editorhtml .= "'insertunorderedlist', '|', 'emoticons', 'image', 'link']";
				$editorhtml .= "});";
				$editorhtml .= "});";
				$savehtml .= "<tr>";
				$savehtml .= "<td class=\"tdl\">" . $modelcolumschinaname [$m] . "</td>";
				$savehtml .= "<td>";
				$savehtml .= " <textarea name=\"" . $mm . "\" style=\"width:800px;height:200px;visibility:hidden;\">" . $objinfo [$mm] . "</textarea>";
				$savehtml .= "</td>";
				$savehtml .= "</tr>";
				continue;
			}
			if (! empty ( $moreTable )) { // 关联字段
				if ($moreTable [$m] != 0) {
					$obj = M ( "my_" . $moreTable [$m] ["glb"] )->where ( $modelcolumssearchstr [$m] )->select ();
					$optionhtml = "";
					foreach ( $obj as $o => $oo ) {
						if ($oo ["id"] == $objinfo [$mm]) {
							$optionhtml .= "<option selected value=\"" . $oo ["id"] . "\">" . $oo [$moreTable [$m] ["glzd"]] . "</option>";
						} else {
							$optionhtml .= "<option value=\"" . $oo ["id"] . "\">" . $oo [$moreTable [$m] ["glzd"]] . "</option>";
						}
					}
					$savehtml .= "<tr>";
					$savehtml .= "<td class=\"tdl\">" . $modelcolumschinaname [$m] . "</td>";
					$savehtml .= "<td>";
					$savehtml .= "<select  class=\"ipt\" size=\"1\" name=\"" . $mm . "\" style=\"width:400px;\">";
					$savehtml .= $optionhtml;
					$savehtml .= "</select>";
					$savehtml .= "</td>";
					$savehtml .= "</tr>";
					continue;
				}
			}
			$savehtml .= "<tr>";
			$savehtml .= "<td class=\"tdl\">" . $modelcolumschinaname [$m] . "</td>";
			$savehtml .= "<td>";
			$savehtml .= "<input name=\"" . $mm . "\" type=\"text\" class=\"ipt\" style=\"width:400px;\" value=\"" . $objinfo [$mm] . "\" />";
			$savehtml .= "</td>";
			$savehtml .= "</tr>";
		}
		$savehtml .= "<input name=\"id\" type=\"hidden\" class=\"ipt\" style=\"width:400px;\" value=\"" . $objinfo ["id"] . "\" />";
		$this->assign ( "sync", $sync );
		$this->assign ( "editorstr", $editorhtml );
		$this->assign ( "savehtml", $savehtml );
		$this->assign ( "modelchinaname", $modelInfo ["modelchinaname"] );
		$this->assign ( "modelname", ucfirst ( $modelInfo ["modelname"] ) );
		$this->display ();
	}
	
	public function saveupdate() {
		$modelInfo = $this->getModelinfo (); // 获取模块配置信息
		                                  // 上传
		$upload = new \Think\Upload (); // 实例化上传类
		$upload->maxSize = 3145728;
		$upload->rootPath = './upload/';
		$upload->savePath = '';
		$upload->saveName = array (
				'uniqid',
				'' 
		); // uniqid函数生成一个唯一的字符串序列。
		$upload->exts = array (
				'jpg',
				'gif',
				'png',
				'jpeg' 
		);
		$upload->autoSub = true;
		$upload->subName = array (
				'date',
				'Ymd' 
		);
		$info = $upload->upload ();
		$modelfilecolums = $this->getmodelfilecolums ( $modelInfo );
		if (! empty ( $modelfilecolums )) {
			foreach ( $modelfilecolums as $ms => $mss ) {
				if ($info [$mss]) {
					$_POST [$mss] = '/upload/' . $info [$mss] ['savepath'] . $info [$mss] ['savename'];
				}
			}
		}
		$saveData = null;
		$_POST ['ctime'] = date ( 'Y-m-d H:i:s', time () );
		$_POST ['mtime'] = date ( 'Y-m-d H:i:s', time () );
		$id = $_POST ["id"];
		foreach ( $modelInfo ["modelcolums"] as $mc => $mcc ) {
			if ($mcc == "id") {
				continue;
			}
			if ($modelInfo ["modelcolumstype"] [$mc] == 8) {
				if (! empty ( $_POST [$mcc] )) {
					$saveData [$mcc] = $_POST [$mcc];
				}
			} else {
				if ($modelInfo ["modelcolumstype"] [$mc] == 9) {
					continue;
				} else {
					$saveData [$mcc] = $_POST [$mcc];
				}
			}
		}
		if (M ( "my_" . $modelInfo ["modelname"] )->where ( "id='" . $id . "'" )->save ( $saveData )) {
			$retMSG = array (
					"data" => $_POST,
					"info" => "success",
					"status" => "1" 
			);
			echo json_encode ( $retMSG );
		} else {
			$retMSG = array (
					"data" => $_POST,
					"info" => "error",
					"status" => "1" 
			);
			echo json_encode ( $retMSG );
		}
	}
	
	public function updateinfo() {
	}
	#endregion
	#region delete
	public function delinfo() {
		$modelInfo = $this->getModelinfo (); // 获取模块配置信息
		$id = $_REQUEST ["id"];
		$where ["id"] = $id;
		if (M ( "my_" . $modelInfo ["modelname"] )->where ( $where )->delete ()) {
			$retMSG = array (
					"data" => "",
					"info" => "success",
					"status" => "1" 
			);
			echo json_encode ( $retMSG );
		} else {
			$retMSG = array (
					"data" => "",
					"info" => "error",
					"status" => "1" 
			);
			echo json_encode ( $retMSG );
		}
	}
	#endregion

	
	/**
	 * 获取模块配置信息
	 */
	public function getModelinfo() {
		// TODO: 还有没有更好的办法实现该功能，感觉将modelid写在这不是很妥当。-李文伟
		$where ["id"] = "%MoudleId%";
		$obj = M ( "my_sysmodel" )->where ( $where )->find ();
		$modelArr ["modelname"] = $obj ["tablename"]; // 模块英文名称--小写例如 constant
		$modelArr ["modelchinaname"] = $obj ["modelname"]; // 模块中文名称例如 常量管理
		$modelArr ["modelsubchinaname"] = $obj ["subnames"]; // 模块操作名称例如 常量列表
		$modelArr ["modelfuncs"] = $this->convertStr2Arr ( $obj ["subnamefuncs"] ); // 模块功能列表例如 导入导出图库
		$modelArr ["modelcolums"] = $this->convertStr2Arr ( $obj ["tablecolums"] ); // 模块字段列表
		$modelArr ["modelcolumstype"] = $this->convertStr2Arr ( $obj ["tablecolumstype"] ); // 模块字段类型列表
		$modelArr ["modelcolumsisshow"] = $this->convertStr2Arr ( $obj ["tablecolumsishow"] ); // 模块字段是否显示
		$modelArr ["modelcolumschinaname"] = $this->convertStr2Arr ( $obj ["pageshowname"] ); // 模块字段中文名称
		$modelArr ["modelcolumsguanliantable"] = $this->convertStr2Arr ( $obj ["guanliantable"] ); // 模块字段关联表
		$modelArr ["modelcolumsguanlianziduan"] = $this->convertStr2Arr ( $obj ["guanlianziduan"] ); // 模块字段关联表字段名称
		$modelArr ["modelcolumssearchstr"] = $this->convertStr2Arr ( $obj ["searchstr"] ); // 模块字段关联表查询字段
		return $modelArr;
	}
	
	/**
	 * 检测是否包含附图
	 */
	public function getmorepics() {
		$str = false;
		$modelinfo = $this->getModelinfo ();
		$modelcolumstype = $modelinfo ["modelcolumstype"];
		foreach ( $modelcolumstype as $key => $value ) {
			if ($value == $this->DATA_TYPE['SUBIMAGES']) {
				$str = true;
				break;
			}
		}
		return $str;
	}
	
	/**
	 * 检测是否多表联合查询并返回表名和表字段数组
	 */
	public function checkMoreTable($glb, $glzd) {
		$moreTable = null;
		foreach ( $glb as $k => $kk ) {
			if ($kk != 0) {
				$obj = M ( "my_sysmodel" )->where ( "id=" . $kk )->find ();
				$moreTable [$k] ["glb"] = $obj ["tablename"];
				$col = $this->convertStr2Arr ( $obj ["tablecolums"] );
				$moreTable [$k] ["glzd"] = $col [$glzd [$k]];
			}
		}
		return $moreTable;
	}
	
	/**
	 * 获取模块字段显示字段
	 */
	public function getShowColums($columsshow, $columsname) {
		$columsTitles = "";
		$flag = 0;
		foreach ( $columsshow as $c => $cc ) {
			if ($cc != 0) {
				$columsTitles .= "<th>" . $columsname [$flag] . " </th>";
			}
			$flag ++;
		}
		$columsTitles .= "<th>操作</th>";
		return $columsTitles;
	}
	
	/**
	 * 获取模块功能列表
	 * @param array 模块支持的数据操作数组
	 */
	public function getmodelfuncs($funcs) {
		foreach ( $funcs as $f => $ff ) {
			if ($ff == $this->MOUDLE_ACTION['ADD']) {
				$fun .= "<input class=\"btn\" id=\"find\" type=\"button\" onclick=\"goadd()\" value=\"新增数据\" />";
			}
			if ($ff == $this->MOUDLE_ACTION['IMPORT']) {
				$fun .= "<input class=\"btn\" id=\"find\" type=\"button\" onclick=\"importData()\" value=\"导入数据\" />";
			}
			if ($ff == $this->MOUDLE_ACTION['EXPORT']) {
				$fun .= "<input class=\"btn\" id=\"find\" type=\"button\" onclick=\"exportData()\" value=\"导出数据\" />";
			}
			// TODO: 移除seebank()方法。
			/* if ($ff == $this->MOUDLE_ACTION['MUTI_UPLOAD']) {
				$fun .= "<input class=\"btn\" id=\"find\" type=\"button\" onclick=\"seebank()\" value=\"批量上传图片\" />";
			} */
		}
		return $fun;
	}
	
	/**
	 * 将字符串转为数组
	 */
	public function convertStr2Arr($str) {
		$strArr = explode ( ",", $str );
		return $strArr;
	}
	
	/**
	 * 获取模块文件列
	 */
	public function getmodelfilecolums($modelinfo) {
		$filecolums = null;
		foreach ( $modelinfo ["modelcolumstype"] as $m => $mm ) {
			if ($mm == 8) {
				$filecolums [] = $modelinfo ["modelcolums"] [$m];
			}
		}
		return $filecolums;
	}
	
	/**
	 * 截取中文字符串
	 */
	public function csubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) 

	{
		if (function_exists ( "mb_substr" )) 

		{
			
			if (mb_strlen ( $str, $charset ) <= $length)
				return $str;
			
			$slice = mb_substr ( $str, $start, $length, $charset );
		} 

		else 

		{
			
			$re ['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
			
			$re ['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
			
			$re ['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
			
			$re ['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
			
			preg_match_all ( $re [$charset], $str, $match );
			
			if (count ( $match [0] ) <= $length)
				return $str;
			
			$slice = join ( "", array_slice ( $match [0], $start, $length ) );
		}
		
		if ($suffix)
			return $slice . "…";
		
		return $slice;
	}
}