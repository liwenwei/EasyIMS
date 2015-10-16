<?php

define('MOUDLE_ACTION_ADD', 'ADD');
define('MOUDLE_ACTION_UPDATE', 'UPDATE');
define('MOUDLE_ACTION_DELETE', 'DELETE');
define('MOUDLE_ACTION_IMPORT', 'IMPORT');
define('MOUDLE_ACTION_EXPORT', 'EXPORT');
define('MOUDLE_ACTION_MUTI_UPLOAD', 'MUTI_UPLOAD');

/**
 * 支持的数据类型
 */
return array(
	MOUDLE_ACTION_ADD            => 0,  // 新增数据
	MOUDLE_ACTION_UPDATE         => 1,  // 更新数据
	MOUDLE_ACTION_DELETE         => 2,  // 删除数据
	MOUDLE_ACTION_IMPORT         => 3,  // 导入数据
	MOUDLE_ACTION_EXPORT         => 4,  // 导出数据
	MOUDLE_ACTION_MUTI_UPLOAD    => 5   // 批量上传图片
);