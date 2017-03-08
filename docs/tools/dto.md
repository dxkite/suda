# 数据表扩展 (Data Table Object,DTO)
数据表对象的作用是自动生成数据表，默认路径在 resource/dto,在该目录下编写数据包对象文件
可以自动生成数据表,简化了数据表创建操作。
如下DTO文件，在运行命令 `php app/console --db -g -m default` 后会生成一个数据表创建语句。其中default是模块名。

**user.dto**
```
; 用户表
id bigint(20) auto primary  comment="用户ID" 
name varchar(13) unique comment="用户名"
email varchar(50) unique comment="邮箱"
password varchar(60) comment="密码HASH"
group bigint(20) key default=0 comment="分组ID"
available int(1) key default=0 comment="邮箱验证"
avatar varchar(255) comment="头像URL"
ip  varchar(32) comment="注册IP"
```

**sql**
```sql
CREATE TABLE `user` (
	`id` BIGINT(20) NOT NULL  AUTO_INCREMENT COMMENT '用户ID',
	`name` VARCHAR(13) NOT NULL   COMMENT '用户名',
	`email` VARCHAR(50) NOT NULL   COMMENT '邮箱',
	`password` VARCHAR(60) NOT NULL   COMMENT '密码HASH',
	`group` BIGINT(20) NOT NULL DEFAULT '0'  COMMENT '分组ID',
	`available` INT(1) NOT NULL DEFAULT '0'  COMMENT '邮箱验证',
	`avatar` VARCHAR(255) NOT NULL   COMMENT '头像URL',
	`ip` VARCHAR(32) NOT NULL   COMMENT '注册IP',
	PRIMARY KEY (`id`),
	UNIQUE KEY `name` (`name`),
	UNIQUE KEY `email` (`email`),
	KEY `group` (`group`),
	KEY `available` (`available`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
```

