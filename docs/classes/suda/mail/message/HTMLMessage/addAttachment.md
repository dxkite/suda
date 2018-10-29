# HTMLMessage::addAttachment
添加附件
> *文件信息* suda\mail\message\HTMLMessage.php: 25~40
## 所属类 

[HTMLMessage](../HTMLMessage.md)

## 可见性

  public  
## 说明



## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| file |  string | 无 |  附件文件路径 |

## 返回值
类型：Message
无

## 例子

···php
$sender=new StmpSender('smtp.163.com', 465, 500, 'dxkite@163.com', 'password', true);
$this->json($sender->send((new Message('我的邮件', '测试发送邮件'))
->setTo('dxkite@qq.com')
->addAttachment(__FILE__)));