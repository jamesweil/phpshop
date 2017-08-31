DROP TABLE IF EXISTS `db_domain_name`;
CREATE TABLE `db_domain_name` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '名字',
  `domain_name` varchar(50) DEFAULT NULL COMMENT '域名',
  `Start_Time` datetime DEFAULT NULL COMMENT '开始时间',
  `End_Time` datetime DEFAULT NULL COMMENT '结束时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;