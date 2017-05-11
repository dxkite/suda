## WEB控制台
- 数据备份时数据库不存在

## 辅助功能
- 模块多版本共存
- 引用 `module#version@author:name`

# 开发者IP限制
    define('DEBUG_ACCESS','127.0.0.1,::1');

# ETAG生成有时会出现问题BUG

# 插件机制
加载-卸载-激活-禁用
```json
{
    "mount":"",
    "umount":"",
    "active":"",
    "dactive":""
}
```