SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `common` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `common` ;

-- -----------------------------------------------------
-- Table `common`.`tp_manager`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `common`.`tp_manager` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(55) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` VARCHAR(55) NOT NULL DEFAULT '' COMMENT '密码',
  `email` VARCHAR(55) NOT NULL DEFAULT '' COMMENT '邮箱',
  `tel` VARCHAR(55) NOT NULL DEFAULT '' COMMENT '手机号',
  `role` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC))
ENGINE = MyISAM
COMMENT = '管理员表';


-- -----------------------------------------------------
-- Table `common`.`tp_ucenter_member`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `common`.`tp_ucenter_member` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '数据id',
  `username` CHAR(20) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` CHAR(32) NOT NULL DEFAULT '' COMMENT '密码',
  `email` VARCHAR(40) NOT NULL DEFAULT '' COMMENT '邮箱',
  `mobile` VARCHAR(25) NOT NULL DEFAULT '' COMMENT '手机号',
  `reg_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '注册时间',
  `reg_ip` VARCHAR(25) NOT NULL DEFAULT '' COMMENT '注册ip',
  `last_login_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后登录时间',
  `last_login_ip` VARCHAR(25) NOT NULL DEFAULT '' COMMENT '最后登录ip',
  `update_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后更新时间',
  `status` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态',
  `type` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户类型',
  `nickname` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` VARCHAR(145) NOT NULL DEFAULT '' COMMENT '头像',
  `score` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '积分',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC))
ENGINE = MyISAM
COMMENT = '用户中心';


-- -----------------------------------------------------
-- Table `common`.`tp_article`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `common`.`tp_article` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '数据id',
  `tag` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'tag标签列表',
  `title` VARCHAR(145) NOT NULL DEFAULT '' COMMENT '文章标题',
  `cover` VARCHAR(145) NOT NULL DEFAULT '' COMMENT '封面图',
  `cid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '文章分类',
  `uid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建者id',
  `view` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '点击量',
  `comment` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '评论数',
  `markup` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '收藏数',
  `create_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `status` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态',
  `sort` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
  PRIMARY KEY (`id`))
ENGINE = MyISAM
COMMENT = '文章统一信息表';


-- -----------------------------------------------------
-- Table `common`.`tp_article_category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `common`.`tp_article_category` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '数据id',
  `pid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '父类id',
  `sort` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序(同级有效)',
  `deep` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '层级深度',
  `name` VARCHAR(65) NOT NULL DEFAULT '' COMMENT '唯一英文标识',
  `title` VARCHAR(145) NOT NULL DEFAULT '' COMMENT '标题',
  `icon` VARCHAR(145) NOT NULL DEFAULT '' COMMENT '分类图标',
  `meta_title` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'SEO的网页标题',
  `meta_keywords` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'SEO的关键字',
  `meta_description` VARCHAR(150) NOT NULL DEFAULT '' COMMENT 'SEO的描述',
  `x` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'x',
  `y` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'y',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = MyISAM
COMMENT = '分类表';


-- -----------------------------------------------------
-- Table `common`.`tp_article_text`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `common`.`tp_article_text` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '数据id',
  `content` TEXT NULL COMMENT '文章内容',
  `parse` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '内容解析方式',
  PRIMARY KEY (`id`))
ENGINE = MyISAM
COMMENT = '文章详情表';


-- -----------------------------------------------------
-- Table `common`.`tp_tag`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `common`.`tp_tag` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '数据id',
  `name` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'tag唯一标志',
  `lang` CHAR(26) NOT NULL DEFAULT 'en' COMMENT '使用的语言',
  `count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '统计字段',
  PRIMARY KEY (`id`))
ENGINE = MyISAM
COMMENT = 'tag表';


-- -----------------------------------------------------
-- Table `common`.`tp_mylist`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `common`.`tp_mylist` (
  `id` INT NOT NULL,
  `uid` INT NOT NULL DEFAULT 0 COMMENT '用户id',
  `doc_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '文档id',
  PRIMARY KEY (`id`))
ENGINE = MyISAM
COMMENT = '用户收藏信息表';


-- -----------------------------------------------------
-- Table `common`.`tp_menu`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `common`.`tp_menu` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '数据id',
  `pid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '父菜单id',
  `sort` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '同级排序',
  `deep` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '层级深度',
  `title` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '标题',
  `tip` VARCHAR(145) NOT NULL DEFAULT '' COMMENT '提示信息',
  `url` VARCHAR(145) NOT NULL DEFAULT '' COMMENT '跳转地址',
  `group` CHAR(45) NOT NULL DEFAULT '' COMMENT '菜单分组标识符',
  `type` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '菜单类型(0:没有视图的按钮,1:有视图的按钮,2:目录菜单,3:菜单分组)',
  `x` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT '相对画布中心的x偏移',
  `y` FLOAT UNSIGNED NOT NULL DEFAULT 0 COMMENT '相对画布中心的y偏移',
  PRIMARY KEY (`id`))
ENGINE = MyISAM
COMMENT = '后台菜单信息表';


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
