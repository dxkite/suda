# Application::getModuleConfig
获取模块的配置信息
> *文件信息* suda\core\Application.php: 30~759
## 所属类 

[Application](../Application.md)

## 可见性

  public  
## 说明


包含了应用的各种处理方式，可以用快捷函数 app() 来使用本类


## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| module |  string | 无 | 无 |
| configName |  string | null | 无 |

## 返回值
类型：array|null
无

## 例子


获取模块信息 (`module.json` 文件的内容)

```php
app()->getModuleConfig(模块名);
```

获取配置信息（`module/resource/config/文件名.json` 文件的内容）

```php
app()->getModuleConfig(模块名,文件名);
```