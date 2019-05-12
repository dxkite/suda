# 事件

在框架中，提供了事件监控功能，通过事件监控，可以在框架运行期间注入自己的代码，比如注入路由、注入环境等操作，事件监控需要模块处于激活状态(`active`)

框架内置了如下事件：

## 内置事件

| 事件名称 | 事件触发时机 | 输入参数列表 |
|---------|----------------|-----------|
| `application:load-config` | 框架启动解析完成配置文件和载入模块之后 |  `\suda\framework\Config`, `\suda\application\Application` |
| `application:load-environment` | 框架加载完成数据库配置之后 |  `\suda\framework\Config`, `\suda\application\Application` |
| `application:load-route` | 框架加载完固定的路由文件之后 | `\suda\framework\Route`, `\suda\application\Application` |
| `application:route:match::after` | 框架匹配完成请求 `URI` 之后 | `\suda\framework\route\MatchResult`, `\suda\framework\Request` |

## 监控事件

模块可以监控事件的发生来为框架添加额外的特性，比如动态添加路由的功能等，事件监控通过模块文件配置 `config/event` 文件来监控，具体映射的文件为

`@resource/config/event`，即为配置文件夹下 `app/modules/welcome/resource/config/event.json` 文件，具体路径视模块而定。

监控环境加载事件代码配置如下，如下配置表示使用类 `\suda\welcome\event\LoadEnvironment` 的静态方法 `handle` 处理事件


```json
{
    "application:load-environment": [
        "suda.welcome.event.LoadEnvironment::handle"
    ]
}
```

实例处理代码如下

```php
<?php
namespace suda\welcome\event;

use suda\application\Application;
use suda\framework\Config;

class LoadEnvironment
{
    public static function handle(Config $config, Application $app)
    {
        $config->set('role', 'admin');
        $app->debug()->info('load environment');
    }
}
```

