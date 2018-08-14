# StmpSender::sendCommand

> *文件信息* suda\mail\sender\StmpSender.php: 33~292
## 所属类 

[StmpSender](../StmpSender.md)

## 可见性

  protected  
## 说明



## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| command |  string | 无 | 无 |
| stateReturn |  int | null | 无 |

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