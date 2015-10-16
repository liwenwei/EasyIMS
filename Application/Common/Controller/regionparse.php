<?php

define('REGION_IDENTIFIER','#');
define('REGION_IDENTIFIER_WORD_BEGIN', 'region');
define('REGION_IDENTIFIER_WORD_END', 'endregion');


/**
 * 根据需要的页面功能，生成controller类。
 * @param string $input 源文件路径
 * @param string $output 生成文件路径
 * @param array $actions 页面支持的功能（增删改等）
 */
function parse_region($input, $output, $actions){
	
	$support_action_arr = array();
	foreach ($actions as $key => $action){
		$action_tag_begin = strtolower($action);
		array_push($support_action_arr, $action_tag_begin);
	}
	
	// 创建新的controller类文件
	$new_controller_handle = fopen($output, "w") or die("Unable to open file!");
	
	// 读取原始controller类文件
	$base_controller_handle = fopen($input, "r");
	if($base_controller_handle){
		$write_line = true;
		$is_region_tags = false;
		$i=1;
		while (($line = fgets($base_controller_handle)) !== false){
			/*************************************************************
			 * 识别region区域目前不支持嵌套的region，只支持一层，例如
			 * #region
			 * content
			 * #endregion
			 * 
			* ***********************************************************/
			if (is_begin_region_tag($line, $region)){
				if (in_array(trim($region), $support_action_arr)){
					$write_line = true;
				} else {
					$write_line = false;
				}
				$is_region_tags = true;
			} else if (is_end_region_tag($line)){
				$write_line = true;
				$is_region_tags = true;
			} else {
				$is_region_tags = false;
			}
			if ($write_line == true && $is_region_tags == false){
				// 写入新的文件
				fwrite($new_controller_handle, $line);
			}
			$i++;
		}
	
		// close the file handles
		fclose($base_controller_handle);
		fclose($new_controller_handle);
	} else {
		die("Unable to open file!");
	}
}

/**
 * 是否为开始区域标识（#region），如果是，$action得到该区域的名称
 * @param string $str
 * @param string $action 区域名称
 * @return boolean
 */
function is_begin_region_tag($str, &$action){
	
	if(IsNullOrEmptyString($str)){
		return false;
	}
	
	$str = strtolower(trim($str));
	$tag_len = strlen(REGION_IDENTIFIER) + strlen(REGION_IDENTIFIER_WORD_BEGIN);
	$tag_str = REGION_IDENTIFIER.REGION_IDENTIFIER_WORD_BEGIN;
	
	
	if(strlen($str) < $tag_len){
		return false;
	}
	
	if($str[0]!=REGION_IDENTIFIER){
		return false;
	}
	if(substr($str, 0, $tag_len) == $tag_str){
		// 获取区域名称， 例如 #region add 得到的是add
		$action = strtolower(substr($str, $tag_len, strlen($str)));
		return true;
	} else {
		return false;
	}
	
}

/**
 * 是否为结束区域标识（#endregion）
 * @param string $str
 * @return boolean
 */
function is_end_region_tag($str){
	if(IsNullOrEmptyString($str)){
		return false;
	}
	
	$str = strtolower(trim($str));
	$tag_len = strlen(REGION_IDENTIFIER) + strlen(REGION_IDENTIFIER_WORD_END);
	$tag_str = REGION_IDENTIFIER.REGION_IDENTIFIER_WORD_END;
	
	if(strlen($str) < $tag_len){
		return false;
	}
	
	if($str[0]!=REGION_IDENTIFIER){
		return false;
	}
	
	if(substr($str, 0, $tag_len) != $tag_str){
		return false;
	}
	
	return true;
}

/**
 * 判断字符串是否为空
 * @param string $str
 * @return boolean
 */
function IsNullOrEmptyString($str){
	return (!isset($str) || trim($str)=== '');
}