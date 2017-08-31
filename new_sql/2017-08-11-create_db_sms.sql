/*
SQLyog Ultimate v11.26 (32 bit)
MySQL - 5.5.53 : Database - sq_test
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`sq_test` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `sq_test`;

/*Table structure for table `db_sms_check` */

DROP TABLE IF EXISTS `db_sms_check`;

CREATE TABLE `db_sms_check` (
  `id` int(3) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `check_title` varchar(50) NOT NULL COMMENT '验证的名称',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态:0-不验证,1-验证',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='短信验证列表';

/*Data for the table `db_sms_check` */

insert  into `db_sms_check`(`id`,`check_title`,`status`) values (1,'开启短信',2),(2,'登录验证',1),(3,'重置密码验证',1),(4,'发货提示',1),(5,'余额支付提示',1);

USE `sq_test`;

/*Table structure for table `db_sms_template` */

DROP TABLE IF EXISTS `db_sms_template`;

CREATE TABLE `db_sms_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `check_id` int(11) NOT NULL COMMENT 'db_sms_check表的id',
  `sms_id` int(3) NOT NULL COMMENT 'sms_type表的id',
  `template` varchar(800) NOT NULL COMMENT '模板内容',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COMMENT='短信模板列表';

/*Data for the table `db_sms_template` */

insert  into `db_sms_template`(`id`,`check_id`,`sms_id`,`template`) values (65,2,1,'a:3:{i:0;s:13:\"登录签名1\";i:1;s:14:\"登录内容11\";i:2;s:14:\"登录变量11\";}'),(66,3,1,'a:3:{i:0;s:20:\"重置密码-签名1\";i:1;s:19:\"重置密码-内容\";i:2;s:20:\"重置密码-变量1\";}'),(67,4,1,'a:3:{i:0;s:20:\"发货提示-签名1\";i:1;s:19:\"发货提示-内容\";i:2;s:19:\"发货提示-变量\";}'),(68,5,1,'a:3:{i:0;s:19:\"余额支付-签名\";i:1;s:19:\"余额支付-内容\";i:2;s:19:\"余额支付-变量\";}'),(69,2,2,'123123'),(70,3,2,'123123'),(71,4,2,'123213'),(72,5,2,'213123');

DROP TABLE IF EXISTS `db_sms_type`;

CREATE TABLE `db_sms_type` (
  `id` int(3) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `sms_title` varchar(50) NOT NULL COMMENT '短信的名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='短信类型列表';

/*Data for the table `db_sms_type` */

insert  into `db_sms_type`(`id`,`sms_title`) values (1,'华信'),(2,'阿里大于');