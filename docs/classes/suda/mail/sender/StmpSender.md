#  StmpSender 

> *文件信息* suda\mail\sender\StmpSender.php: 33~292


SMTP邮件发送器


## 描述




 
## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
 | protected    | userName | | 
 | protected    | password | | 
 | protected    | server | | 
 | protected    | port | | 
 | protected    | isSecurity | | 
 | protected    | timeout | | 
 | protected    | socket | | 
 | protected    | error | | 
 | protected    | message | | 
## 方法

 
| 可见性 | 方法名 | 说明 |
|--------|-------|------|
 |  public  |[__construct](StmpSender/__construct.md) | 创建一个SMTP发送 |
 |  public  |[send](StmpSender/send.md) | 发送信息 |
 |  protected  |[getCommand](StmpSender/getCommand.md) |  |
 |  protected  |[getData](StmpSender/getData.md) |  |
 |  protected  |[openSocket](StmpSender/openSocket.md) |  |
 |  protected  |[openSocketSecurity](StmpSender/openSocketSecurity.md) |  |
 |  protected  |[close](StmpSender/close.md) |  |
 |  protected  |[closeSecutity](StmpSender/closeSecutity.md) |  |
 |  protected  |[sendCommand](StmpSender/sendCommand.md) |  |
 |  protected  |[sendCommandSecurity](StmpSender/sendCommandSecurity.md) |  |
 |  public  |[getError](StmpSender/getError.md) |  |
 |  public  |[_errorHandler](StmpSender/_errorHandler.md) |  |
 |  protected  |[setError](StmpSender/setError.md) |  |
 |  protected  |[log](StmpSender/log.md) |  |
## 例子

```php
$sender=new StmpSender('smtp.163.com', 465, 500, 'dxkite@163.com', 'password', true);
$this->json($sender->send(
     (new Message('我的邮件', '测试发送邮件'))
     ->setFrom('dxkite@163.com')
     ->setTo('dxkite@qq.com')));
```