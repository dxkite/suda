## 管理面板
- [x] 数据备份时数据库不存在
- [ ] 路由名称不合理的需要报错

## 辅助功能
- [x] 模块多版本共存
    - [ ] 类库加载优先顺序
- [x] 引用 `module/name:version`

# 开发者IP限制
- [x] define('DEBUG_ACCESS','127.0.0.1,::1');

# ETAG生成有时会出现问题BUG
# 部分服务器 X-Framework 不支持(`500`)

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


## 固定 rewrite 反向引用模式
## 日志即时记录
- [x] 即时记录
- [x] 日打包
- [ ] 月打包

## 序列化方式设置为 php_serialize
