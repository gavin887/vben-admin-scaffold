-- New empty database only. No application data or administrator password is included.
SET NAMES utf8mb4;

CREATE TABLE `__PREFIX__admin_dept` (
  `admin_dept_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上级部门ID',
  `dept_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '部门名称',
  `sort` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '排序',
  `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`admin_dept_id`),
  KEY `pid` (`pid`),
  KEY `status` (`status`),
  KEY `dept_name` (`dept_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `__PREFIX__admin_menu` (
  `admin_menu_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上级菜单ID',
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路径',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `component` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组件',
  `redirect` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '重定向',
  `meta` json DEFAULT NULL COMMENT 'Meta',
  `sort` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '排序',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`admin_menu_id`),
  KEY `pid` (`pid`),
  KEY `name` (`name`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `__PREFIX__admin_role` (
  `admin_role_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '值',
  `sort` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '排序',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`admin_role_id`),
  KEY `name` (`name`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `__PREFIX__admin_role_menu` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `role_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户角色ID',
  `menu_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '菜单ID',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `__PREFIX__admin_user` (
  `admin_user_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '登录密码',
  `mobile` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户手机号',
  `nick_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户昵称',
  `avatar` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '头像',
  `gender` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邮箱',
  `role_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '角色ID',
  `dept_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '部门ID',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`admin_user_id`),
  UNIQUE KEY `user_name` (`user_name`),
  KEY `dept_id` (`dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `__PREFIX__audit_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `admin_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '操作人(__PREFIX__admin_user.id)',
  `module` varchar(50) NOT NULL DEFAULT '' COMMENT '管理模块',
  `action` varchar(50) NOT NULL DEFAULT '' COMMENT '操作动作',
  `before_json` text COMMENT '操作前数据(JSON)',
  `after_json` text COMMENT '操作后数据(JSON)',
  `ip` varchar(45) NOT NULL DEFAULT '' COMMENT '操作IP',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间(UTC时间戳)',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间(UTC时间戳)',
  PRIMARY KEY (`id`),
  KEY `idx_admin_user` (`admin_user_id`),
  KEY `idx_module_action` (`module`,`action`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `__PREFIX__setting` (
  `key` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '设置项标示',
  `describe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '设置项描述',
  `values` json DEFAULT NULL COMMENT '设置内容(json格式)',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `__PREFIX__upload` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'admin',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `storage` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'local' COMMENT '存储方式',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '物理路径',
  `filename` varchar(63) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '文件名',
  `filetype` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '文件类型',
  `filesize` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小',
  `mimetype` varchar(31) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'mime类型',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `md5` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'MD5',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `filetype` (`filetype`),
  KEY `md5` (`md5`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;

CREATE TABLE `__PREFIX__admin_token` (
 `token_hash` char(64) NOT NULL,
 `admin_user_id` int unsigned NOT NULL,
 `password_hash` char(64) NOT NULL,
 `expires_at` int unsigned NOT NULL,
 PRIMARY KEY (`token_hash`), KEY `user_expiry` (`admin_user_id`,`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `__PREFIX__admin_role` (`admin_role_id`,`name`,`value`,`sort`,`status`,`remark`,`create_time`,`update_time`) VALUES (10001,'超级管理员','supperadmin',1,1,'系统初始化角色',UNIX_TIMESTAMP(),UNIX_TIMESTAMP());
INSERT INTO `__PREFIX__admin_dept` (`admin_dept_id`,`pid`,`dept_name`,`sort`,`status`,`create_time`,`update_time`) VALUES (10001,0,'默认部门',1,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

INSERT INTO `__PREFIX__admin_menu` (`admin_menu_id`,`pid`,`path`,`name`,`component`,`redirect`,`meta`,`sort`,`status`,`create_time`,`update_time`) VALUES (10001,0,'/workspace','工作台','/dashboard/workspace/index','','{"title": "工作台", "icon": "ant-design:dashboard-outlined"}',10,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

INSERT INTO `__PREFIX__admin_menu` (`admin_menu_id`,`pid`,`path`,`name`,`component`,`redirect`,`meta`,`sort`,`status`,`create_time`,`update_time`) VALUES (10003,0,'/system','系统管理','BasicLayout','/system/user','{"title": "系统管理", "icon": "ion:settings-outline"}',900,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

INSERT INTO `__PREFIX__admin_menu` (`admin_menu_id`,`pid`,`path`,`name`,`component`,`redirect`,`meta`,`sort`,`status`,`create_time`,`update_time`) VALUES (10004,10003,'/system/user','账号管理','/system/user/index','','{"title": "账号管理", "icon": "ion:settings-outline"}',901,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

INSERT INTO `__PREFIX__admin_menu` (`admin_menu_id`,`pid`,`path`,`name`,`component`,`redirect`,`meta`,`sort`,`status`,`create_time`,`update_time`) VALUES (10006,10003,'/system/role','角色管理','/system/role/index','','{"title": "角色管理", "icon": "ion:settings-outline"}',902,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

INSERT INTO `__PREFIX__admin_menu` (`admin_menu_id`,`pid`,`path`,`name`,`component`,`redirect`,`meta`,`sort`,`status`,`create_time`,`update_time`) VALUES (10007,10003,'/system/menu','菜单管理','/system/menu/index','','{"title": "菜单管理", "icon": "ion:settings-outline"}',903,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

INSERT INTO `__PREFIX__admin_menu` (`admin_menu_id`,`pid`,`path`,`name`,`component`,`redirect`,`meta`,`sort`,`status`,`create_time`,`update_time`) VALUES (10008,10003,'/system/dept','部门管理','/system/dept/index','','{"title": "部门管理", "icon": "ion:settings-outline"}',904,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

INSERT INTO `__PREFIX__admin_menu` (`admin_menu_id`,`pid`,`path`,`name`,`component`,`redirect`,`meta`,`sort`,`status`,`create_time`,`update_time`) VALUES (10018,10003,'/system/setting','系统设置','/system/setting/index','','{"title": "系统设置", "icon": "ion:settings-outline"}',905,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

INSERT INTO `__PREFIX__admin_menu` (`admin_menu_id`,`pid`,`path`,`name`,`component`,`redirect`,`meta`,`sort`,`status`,`create_time`,`update_time`) VALUES (10009,10003,'/system/profile','个人中心','/system/profile/index','','{"title": "个人中心", "icon": "ion:settings-outline"}',906,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

INSERT INTO `__PREFIX__setting` (`key`,`describe`,`values`,`update_time`) VALUES ('storage','本地上传设置','{"default":"local","engine":{"local":null}}',UNIX_TIMESTAMP());
