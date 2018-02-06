# StmpSender::__construct

创建一个SMTP发送

> *文件信息* suda\mail\sender\StmpSender.php: 33~276

## 所属类 

[StmpSender](../StmpSender.md)

## 可见性

 public 

## 说明




## 参数


| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| server |  string | 无 |  SMTP邮件服务器 |
| port |  int | 无 |  端口号 |
| timeout |  int | 无 |  设置发送超时 |
| name |  string | 无 |  邮箱用户名 |
| password |  string | 无 |  邮箱密码 |
| isSecurity |  bool | 1 |  是否使用SSL，需要开启 OpenSSL 模块 |



## 返回值

返回值类型不定


## 例子

```php
$sender=new StmpSender('smtp.163.com', 465, 500, 'dxkite@163.com', 'password', true);
$this->json($sender->send(
     (new Message('我的邮件', '测试发送邮件'))
     ->setFrom('dxkite@163.com')
     ->setTo('dxkite@qq.com')));
```