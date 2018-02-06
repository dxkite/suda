# StmpSender::send

发送信息

> *文件信息* suda\mail\sender\StmpSender.php: 33~276

## 所属类 

[StmpSender](../StmpSender.md)

## 可见性

 public 

## 说明




## 参数


| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| message |  suda\mail\message\Message | 无 |  信息体 |



## 返回值

类型：boolean

无



## 例子

```php
$sender=new StmpSender('smtp.163.com', 465, 500, 'dxkite@163.com', 'password', true);
$this->json($sender->send(
     (new Message('我的邮件', '测试发送邮件'))
     ->setFrom('dxkite@163.com')
     ->setTo('dxkite@qq.com')));
```