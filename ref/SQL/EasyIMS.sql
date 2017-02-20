
SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for my_model
-- ----------------------------
DROP TABLE IF EXISTS `my_model`;
CREATE TABLE `my_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modelname` varchar(200) DEFAULT NULL,
  `modelurl` varchar(200) DEFAULT NULL,
  `parentid` int(11) DEFAULT NULL,
  `istopshow` int(11) DEFAULT NULL,
  `isleftshow` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of my_model
-- ----------------------------
INSERT INTO `my_model` VALUES ('1', '模块开发', null, '0', '0', '1');
INSERT INTO `my_model` VALUES ('2', '模块列表', '/Admin/Develop/index', '1', null, null);

-- ----------------------------
-- Table structure for my_options
-- ----------------------------
DROP TABLE IF EXISTS `my_options`;
CREATE TABLE `my_options` (
  `option_id` bigint(20) NOT NULL,
  `option_name` varchar(191) DEFAULT NULL,
  `option_value` longtext NOT NULL,
  `autoload` varchar(20) NOT NULL,
  PRIMARY KEY (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of my_options
-- ----------------------------

-- ----------------------------
-- Table structure for my_role
-- ----------------------------
DROP TABLE IF EXISTS `my_role`;
CREATE TABLE `my_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) DEFAULT NULL,
  `names` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of my_role
-- ----------------------------
INSERT INTO `my_role` VALUES ('11', '20', 'constant,job,contactus,news,case,achievements,company,staff,culture,speech,admingl,role,bander');

-- ----------------------------
-- Table structure for my_sysmodel
-- ----------------------------
DROP TABLE IF EXISTS `my_sysmodel`;
CREATE TABLE `my_sysmodel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tablename` varchar(100) DEFAULT NULL,
  `modelid` int(11) DEFAULT NULL,
  `modelname` varchar(200) DEFAULT NULL,
  `subnames` varchar(300) DEFAULT NULL,
  `subnamefuncs` varchar(300) DEFAULT NULL,
  `tablecolums` varchar(200) DEFAULT NULL,
  `tablecolumstype` varchar(200) DEFAULT NULL,
  `tablecolumslength` varchar(200) DEFAULT NULL,
  `tablecolumsisnull` varchar(200) DEFAULT NULL,
  `tablecolumsispramarykey` varchar(200) DEFAULT NULL,
  `tablecolumsishow` varchar(300) DEFAULT NULL,
  `pageshowname` varchar(300) DEFAULT NULL,
  `guanliantable` varchar(200) DEFAULT NULL,
  `guanlianziduan` varchar(300) DEFAULT NULL,
  `searchstr` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of my_sysmodel
-- ----------------------------

-- ----------------------------
-- View structure for v_sysmodels
-- ----------------------------
DROP VIEW IF EXISTS `v_sysmodels`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `v_sysmodels` AS select `my_sysmodel`.`id` AS `sysmodelid`,`my_model`.`id` AS `modelid`,`my_sysmodel`.`tablename` AS `tablename`,`my_model`.`modelname` AS `subnames`,`my_sysmodel`.`subnamefuncs` AS `subnamefuncs`,`my_sysmodel`.`modelname` AS `modelname` from (`my_model` left join `my_sysmodel` on((`my_model`.`modelname` = `my_sysmodel`.`subnames`))) where ((`my_model`.`modelname` <> '模块开发') and (`my_model`.`modelname` <> '模块列表')) ;

-- ----------------------------
-- Procedure structure for proc_DropMoudle
-- ----------------------------
DROP PROCEDURE IF EXISTS `proc_DropMoudle`;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `proc_DropMoudle`(IN moudleId INT)
proc_label:BEGIN

SET @moudle_name=' ';
SET @parent_moudle_id = -1;
SET @table_name='';
SET @sysmodel_id=0;

START TRANSACTION;

SET @moudle_count = 0;
SET @i = 0;

SELECT modelname, parentid, COUNT(parentid) INTO @moudle_name, @parent_moudle_id, @moudle_count FROM my_model WHERE id=moudleId;

-- 如果没有记录，就返回
IF(@moudle_count=0) THEN
  LEAVE proc_label; -- 或者 LEAVE proc_DropMoudle
END IF;
-- 如果parentid为0，则表示该模块（要删除的模块）为父模块

IF (@parent_moudle_id=0) THEN
  -- 获取子模块个数
  SELECT COUNT(*) INTO @moudle_count FROM my_sysmodel WHERE modelname=@moudle_name;
  SELECT tablename FROM my_sysmodel WHERE modelname=@moudle_name;

  IF(@moudle_count=0) THEN
    -- 如果为空，就将my_model表中的改记录删除
    DELETE FROM my_model WHERE id=moudleId;
  END IF;

  -- 删除所有子模块
  WHILE @i < @moudle_count DO

    SELECT tablename, id INTO @table_name, @sysmodel_id from my_sysmodel WHERE modelname=@moudle_name LIMIT 1;
    -- 删除本条数据记录
    DELETE FROM my_sysmodel WHERE id=@sysmodel_id;
    -- 删除相对应的表
    CALL proc_DropTable(@table_name);
    
    set @i = @i + 1;
  END WHILE;

  -- 删除my_model中的数据
  DELETE FROM my_model WHERE id=moudleId OR parentid=moudleId;

ELSE 
  -- 删除该子模块
  SELECT tablename from my_sysmodel WHERE subnames=@moudle_name;
  SELECT tablename, id INTO @table_name, @sysmodel_id from my_sysmodel WHERE subnames=@moudle_name;
  
  -- 删除本条数据记录
  DELETE FROM my_sysmodel WHERE id=@sysmodel_id;
  -- 删除相对应的表
  CALL proc_DropTable(@table_name);

  -- 删除my_model中的数据
  DELETE FROM my_model WHERE id=moudleId;
END IF;

COMMIT;

END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for proc_DropTable
-- ----------------------------
DROP PROCEDURE IF EXISTS `proc_DropTable`;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `proc_DropTable`(table_name VARCHAR(50))
BEGIN

SET @drop_table_sql = CONCAT('DROP TABLE IF EXISTS my_',table_name);

PREPARE stmt from @drop_table_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for proc_GetTablenames
-- ----------------------------
DROP PROCEDURE IF EXISTS `proc_GetTablenames`;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `proc_GetTablenames`(IN moudleId INT)
proc_label:BEGIN

SET @moudle_name = ' ';
SET @parent_moudle_id = -1;
SET @moudle_count = 0;

SELECT modelname, parentid, COUNT(parentid) INTO @moudle_name, @parent_moudle_id, @moudle_count FROM my_model WHERE id=moudleId;

IF(@moudle_count=0) THEN
  LEAVE proc_label; -- 或者 LEAVE proc_GetTablenames
END IF;

-- 如果parentid为0，则表示该模块为父模块
IF @parent_moudle_id=0 THEN
-- 如果为父模块，就获取该模块下所有的子模块的表名称
SELECT tablename FROM my_sysmodel WHERE modelname=@moudle_name;
ELSE
-- 如果为子模块，只获取当前模块的表名称
SELECT tablename FROM my_sysmodel WHERE subnames=@moudle_name;

END IF;

END
;;
DELIMITER ;
