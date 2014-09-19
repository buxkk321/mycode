SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `maopu` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `maopu` ;

-- -----------------------------------------------------
-- Table `maopu`.`maopu_manager`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `maopu`.`maopu_manager` (
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
-- Table `maopu`.`maopu_ucenter_member`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `maopu`.`maopu_ucenter_member` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `nickname` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '昵称',
  `face` VARCHAR(145) NOT NULL DEFAULT '' COMMENT '头像',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC))
ENGINE = MyISAM
COMMENT = '用户中心';


-- -----------------------------------------------------
-- Table `maopu`.`maopu_article`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `maopu`.`maopu_article` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(145) NOT NULL DEFAULT '' COMMENT '文章标题',
  `cover` VARCHAR(145) NOT NULL DEFAULT '' COMMENT '封面图',
  `cid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '文章分类',
  `uid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建者id',
  `view` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '点击量',
  `comment` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '评论数',
  `create_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  `status` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态',
  `sort` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
  PRIMARY KEY (`id`))
ENGINE = MyISAM
COMMENT = '文章统一信息表';


-- -----------------------------------------------------
-- Table `maopu`.`maopu_category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `maopu`.`maopu_category` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(65) NOT NULL DEFAULT '' COMMENT '唯一英文标识',
  `title` VARCHAR(145) NOT NULL DEFAULT '' COMMENT '标题',
  `icon` VARCHAR(145) NOT NULL DEFAULT '' COMMENT '分类图标',
  `pid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '父类id',
  `sort` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序(同级有效)',
  `meta_title` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'SEO的网页标题',
  `meta_keywords` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'SEO的关键字',
  `meta_description` VARCHAR(150) NOT NULL DEFAULT '' COMMENT 'SEO的描述',
  PRIMARY KEY (`id`))
ENGINE = MyISAM
COMMENT = '分类表';


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;