
CREATE TABLE IF NOT EXISTS `mon_auth_access` (
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `group_id` int(10) unsigned NOT NULL COMMENT '组别ID',
  `update_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `create_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='组别用户关联表';

CREATE TABLE IF NOT EXISTS `mon_auth_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级ID',
  `name` varchar(50) NOT NULL COMMENT '组名',
  `rules` text NOT NULL COMMENT '规则ID',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1:有效,2:无效',
  `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='权限规则组表';

CREATE TABLE IF NOT EXISTS `mon_auth_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级ID',
  `mark` varchar(200) NOT NULL COMMENT '规则标志',
  `name` varchar(50) NOT NULL COMMENT '规则名称',
  `description` varchar(250) NOT NULL DEFAULT '' COMMENT '扩展描述信息',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '权重排序',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1:有效,0:无效',
  `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='权限规则表';