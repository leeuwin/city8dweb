/*
 Navicat Premium Data Transfer

 Source Server         : localhost-mysql
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : localhost:3306
 Source Schema         : snake

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 27/03/2020 20:38:14
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin_login_log
-- ----------------------------
DROP TABLE IF EXISTS `admin_login_log`;
CREATE TABLE `admin_login_log`  (
  `log_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '日志id',
  `login_user` varchar(55) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '登录用户',
  `login_ip` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '登录ip',
  `login_area` varchar(55) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '登录地区',
  `login_user_agent` varchar(155) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '登录设备头',
  `login_time` datetime(0) NULL DEFAULT NULL COMMENT '登录时间',
  `login_status` tinyint(1) NULL DEFAULT 1 COMMENT '登录状态 1 成功 2 失败',
  PRIMARY KEY (`log_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 25 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for admin_operate_log
-- ----------------------------
DROP TABLE IF EXISTS `admin_operate_log`;
CREATE TABLE `admin_operate_log`  (
  `log_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '操作日志id',
  `operator` varchar(55) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作用户',
  `operator_ip` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作者ip',
  `operate_method` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作方法',
  `operate_desc` varchar(155) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作简述',
  `operate_time` datetime(0) NOT NULL COMMENT '操作时间',
  PRIMARY KEY (`log_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 84 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for admin_user
-- ----------------------------
DROP TABLE IF EXISTS `admin_user`;
CREATE TABLE `admin_user`  (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '管理员id',
  `admin_name` varchar(55) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '管理员名字',
  `admin_password` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '管理员密码',
  `role_id` int(11) NULL DEFAULT NULL COMMENT '所属角色',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 禁用 1 启用',
  `add_time` datetime(0) NOT NULL COMMENT '添加时间',
  `last_login_time` datetime(0) NULL DEFAULT NULL COMMENT '上次登录时间',
  `update_time` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`admin_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_user
-- ----------------------------
INSERT INTO `admin_user` VALUES (1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 1, 1, '2019-09-03 13:31:20', '2020-03-27 16:40:57', NULL);

-- ----------------------------
-- Table structure for node
-- ----------------------------
DROP TABLE IF EXISTS `node`;
CREATE TABLE `node`  (
  `node_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '角色id',
  `node_name` varchar(55) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '节点名称',
  `node_path` varchar(55) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '节点路径',
  `node_pid` int(11) NOT NULL COMMENT '所属节点',
  `node_icon` varchar(55) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '节点图标',
  `is_menu` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否是菜单项 1 不是 2 是',
  `sort` int(11) NULL DEFAULT NULL COMMENT '排序',
  `add_time` datetime(0) NULL DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`node_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 26 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of node
-- ----------------------------
INSERT INTO `node` VALUES (1, '主页', '#', 0, 'layui-icon layui-icon-home', 2, 1, '2019-09-03 14:17:38');
INSERT INTO `node` VALUES (2, '后台首页', 'index/index', 1, '', 1, 0, '2019-09-03 14:18:24');
INSERT INTO `node` VALUES (3, '修改密码', 'index/editpwd', 1, '', 1, 0, '2019-09-03 14:19:03');
INSERT INTO `node` VALUES (4, '权限管理', '#', 0, 'fa fa-lock', 2, 10, '2019-09-03 14:19:34');
INSERT INTO `node` VALUES (5, '管理员管理', 'manager/index', 4, 'layui-icon layui-icon-group', 2, 1, '2019-09-03 14:27:42');
INSERT INTO `node` VALUES (6, '添加管理员', 'manager/addadmin', 5, '', 1, NULL, '2019-09-03 14:28:26');
INSERT INTO `node` VALUES (7, '编辑管理员', 'manager/editadmin', 5, '', 1, NULL, '2019-09-03 14:28:43');
INSERT INTO `node` VALUES (8, '删除管理员', 'manager/deladmin', 5, '', 1, NULL, '2019-09-03 14:29:14');
INSERT INTO `node` VALUES (9, '日志管理', '#', 0, 'fa fa-folder', 2, 20, '2019-10-08 16:07:36');
INSERT INTO `node` VALUES (10, '系统日志', 'log/system', 9, 'fa fa-file-archive-o', 2, 1, '2019-10-08 16:24:55');
INSERT INTO `node` VALUES (11, '登录日志', 'log/login', 9, 'fa fa-file-word-o', 2, 5, '2019-10-08 16:26:27');
INSERT INTO `node` VALUES (12, '操作日志', 'log/operate', 9, 'fa fa-file-excel-o', 2, 10, '2019-10-08 17:02:10');
INSERT INTO `node` VALUES (13, '角色管理', 'role/index', 4, 'fa fa-user-secret', 2, 5, '2019-10-09 21:35:54');
INSERT INTO `node` VALUES (14, '添加角色', 'role/add', 13, '', 1, NULL, '2019-10-09 21:40:06');
INSERT INTO `node` VALUES (15, '编辑角色', 'role/edit', 13, '', 1, NULL, '2019-10-09 21:40:53');
INSERT INTO `node` VALUES (16, '删除角色', 'role/delete', 13, '', 1, NULL, '2019-10-09 21:41:07');
INSERT INTO `node` VALUES (17, '权限分配', 'role/assignauthority', 13, '', 1, NULL, '2019-10-09 21:41:38');
INSERT INTO `node` VALUES (18, '节点管理', 'node/index', 4, 'layui-icon layui-icon-more', 2, 10, '2019-10-09 21:42:06');
INSERT INTO `node` VALUES (19, '添加节点', 'node/add', 18, '', 1, NULL, '2019-10-09 21:42:51');
INSERT INTO `node` VALUES (20, '编辑节点', 'node/edit', 18, '', 1, NULL, '2019-10-09 21:43:29');
INSERT INTO `node` VALUES (21, '删除节点', 'node/delete', 18, '', 1, NULL, '2019-10-09 21:43:44');
INSERT INTO `node` VALUES (22, '查看管理员', 'manager/index', 5, '', 1, NULL, '2020-03-23 16:07:47');
INSERT INTO `node` VALUES (23, '查看节点', 'node/index', 18, '', 1, NULL, '2020-03-23 16:09:46');
INSERT INTO `node` VALUES (24, '系统配置', '#', 0, 'fa fa-th-large', 2, 5, '2020-03-26 14:52:51');
INSERT INTO `node` VALUES (25, '网站配置', 'system_config/index', 24, 'fa fa-apple', 2, 1, '2020-03-26 15:25:32');

-- ----------------------------
-- Table structure for role
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role`  (
  `role_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '角色id',
  `role_name` varchar(55) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '角色名称',
  `role_node` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '角色拥有的权限节点',
  `role_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '角色状态 1 启用 2 禁用',
  PRIMARY KEY (`role_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of role
-- ----------------------------
INSERT INTO `role` VALUES (1, '超级管理员', '#', 1);

-- ----------------------------
-- Table structure for system_config
-- ----------------------------
DROP TABLE IF EXISTS `system_config`;
CREATE TABLE `system_config`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '配置编码',
  `value` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '配置值',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `index_system_config_name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 323 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '系统参数配置' ROW_FORMAT = Compact;

-- ----------------------------
-- Records of system_config
-- ----------------------------
INSERT INTO `system_config` VALUES (315, 'website_name', '后台管理');
INSERT INTO `system_config` VALUES (316, 'site_name', '后台管理');
INSERT INTO `system_config` VALUES (317, 'login_expire_time', '180');
INSERT INTO `system_config` VALUES (318, 'max_upload_file', '2048');
INSERT INTO `system_config` VALUES (319, 'upload_file_type', 'png|gif|jpg|jpeg|xlsx|ico');
INSERT INTO `system_config` VALUES (320, 'browser_icon', '\\uploads\\20200327\\f8706cc6b2a2a69f31064415a193420c.ico');
INSERT INTO `system_config` VALUES (321, 'copyright', '© 2020 admin.cn MIT license');

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '/static/index/images/avatar.png' COMMENT '头像',
  `username` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `mobile` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `user_level_id` int(10) NOT NULL DEFAULT 1 COMMENT '用户等级',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'JDJ5JDEwJHRneXhvRW9tYjREa0g1TGtZVmNQRmVQSkZNZkhDZm1iN0hEMWRST1J1eHNBNzE1UGRLUkFt' COMMENT '密码',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用',
  `create_time` int(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (1, '/static/index/images/avatar.png', 'ceshi', '测试账号', '15674589856', 1, 'JDJ5JDEwJG10VmkyVjJRYXpaZEdzbzhITDF0V3V6U2h6WHZSSWJRbmhXZDN4MnVNYnlSVTVBQ2l2WWZt', 1, 1585125144, 1585125144, 0);

-- ----------------------------
-- Table structure for user_level
-- ----------------------------
DROP TABLE IF EXISTS `user_level`;
CREATE TABLE `user_level`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `description` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '简介',
  `img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '/static/index/images/user_level_default.png' COMMENT '图片',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用',
  `create_time` int(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int(10) NULL DEFAULT 0 COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户等级' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_level
-- ----------------------------
INSERT INTO `user_level` VALUES (1, '普通用户', '普通用户', '/uploads/attachment/20190822/65e4ad92ece9fdb7f3822ba4fc322bf6.png', 1, 1585120012, 1585120012, NULL);
INSERT INTO `user_level` VALUES (2, '青铜会员', '青铜会员', '/uploads/attachment/20190822/d0b153352b15ea7097403c563e9c3be4.png', 1, 1585120012, 1585120012, NULL);
INSERT INTO `user_level` VALUES (3, '白银会员', '白银会员', '/uploads/attachment/20190822/72031bafedeba534d1e862b8d717f8db.png', 1, 1585120012, 1585120012, NULL);
INSERT INTO `user_level` VALUES (4, '黄金会员', '黄金会员', '/uploads/attachment/20190822/6dcc15ea1701c449e63e6856f0931e2a.png', 1, 1585120012, 1585120012, NULL);

-- ----------------------------
-- Table structure for user_token
-- ----------------------------
DROP TABLE IF EXISTS `user_token`;
CREATE TABLE `user_token`  (
  `token` varchar(350) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Token',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员ID',
  `token_time` int(10) NULL DEFAULT NULL COMMENT '生成token时间',
  PRIMARY KEY (`token`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '会员Token表' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
