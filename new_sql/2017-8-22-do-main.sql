DROP TABLE IF EXISTS `db_domain_name`;
CREATE TABLE `db_domain_name` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '����',
  `domain_name` varchar(50) DEFAULT NULL COMMENT '����',
  `Start_Time` datetime DEFAULT NULL COMMENT '��ʼʱ��',
  `End_Time` datetime DEFAULT NULL COMMENT '����ʱ��',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;