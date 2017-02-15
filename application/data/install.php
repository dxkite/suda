<?php
/* ------------------------------------------------------ *\
   ------------------------------------------------------
   PHP Simple Library Database Backup File
        Create On: 2017-02-15 09:04:28
        SQL Server version: 10.1.10-MariaDB
        Host: localhost   
        Database: test_api
        Tables: 4
   ------------------------------------------------------
\* ------------------------------------------------------ */

try {
/** Open Transaction To Avoid Error **/
Query::beginTransaction();


$effect=($create=new Query('CREATE DATABASE IF NOT EXISTS '.\Config::get('database.name').';'))->exec();
if ($create->erron()==0){
        echo 'Create Database '.\Config::get('database.name').' Ok,effect '.$effect.' rows'."\r\n";
    }
    else{
        die('Database '.\Config::get('database.name').'create filed!');   
    }
 (new Query('DROP TABLE IF EXISTS #{client}'))->exec();

        $effect=($query_client=new Query('CREATE TABLE `#{client}` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT \'客户端ID\',
  `ip` bigint(32) NOT NULL COMMENT \'客户端IP\',
  `machine` varchar(32) NOT NULL COMMENT \'机器码MD5\',
  `black` tinyint(1) NOT NULL COMMENT \'黑名单\',
  `offset` bigint(20) NOT NULL COMMENT \'最后便偏移\',
  `length` bigint(20) NOT NULL COMMENT \'长度\',
  `crc32` bigint(20) NOT NULL COMMENT \'crc32值\',
  `last` int(11) NOT NULL COMMENT \'最后活动时间\',
  `online` tinyint(1) NOT NULL COMMENT \'是否在线\',
  PRIMARY KEY (`id`),
  KEY `black` (`black`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8'))->exec();
        if ($query_client->erron()==0){
            echo 'Create Table:'.\Config::get('database.prefix').'client Ok,effect '.$effect.' rows'."\r\n";
        }
        else{
             echo 'Create Table:'.\Config::get('database.prefix').'client Error!,effect '.$effect.' rows'."\r\n";   
        } (new Query('DROP TABLE IF EXISTS #{key}'))->exec();

        $effect=($query_key=new Query('CREATE TABLE `#{key}` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT \'关键字ID\',
  `type` int(1) NOT NULL COMMENT \'关键字类型\',
  `key` varchar(255) NOT NULL COMMENT \'关键字\',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8'))->exec();
        if ($query_key->erron()==0){
            echo 'Create Table:'.\Config::get('database.prefix').'key Ok,effect '.$effect.' rows'."\r\n";
        }
        else{
             echo 'Create Table:'.\Config::get('database.prefix').'key Error!,effect '.$effect.' rows'."\r\n";   
        } (new Query('DROP TABLE IF EXISTS #{keywords}'))->exec();

        $effect=($query_keywords=new Query('CREATE TABLE `#{keywords}` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT \'关键字ID\',
  `type` int(1) NOT NULL COMMENT \'关键字类型\',
  `key` varchar(255) NOT NULL COMMENT \'关键字\',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8'))->exec();
        if ($query_keywords->erron()==0){
            echo 'Create Table:'.\Config::get('database.prefix').'keywords Ok,effect '.$effect.' rows'."\r\n";
        }
        else{
             echo 'Create Table:'.\Config::get('database.prefix').'keywords Error!,effect '.$effect.' rows'."\r\n";   
        } (new Query('DROP TABLE IF EXISTS #{user}'))->exec();

        $effect=($query_user=new Query('CREATE TABLE `#{user}` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT \'用户ID\',
  `name` varchar(13) NOT NULL COMMENT \'用户名\',
  `password` varchar(60) NOT NULL COMMENT \'密码HASH\',
  `group` bigint(20) NOT NULL DEFAULT \'0\' COMMENT \'分组ID\',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8'))->exec();
        if ($query_user->erron()==0){
            echo 'Create Table:'.\Config::get('database.prefix').'user Ok,effect '.$effect.' rows'."\r\n";
        }
        else{
             echo 'Create Table:'.\Config::get('database.prefix').'user Error!,effect '.$effect.' rows'."\r\n";   
        }/** End Querys **/
Query::commit();
return true;
} 
catch (Exception $e)
{
    Query::rollBack();
   return false;
}