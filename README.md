# suda v3 [refactored for suda](https://github.com/dxkite/suda)

高性能、轻量化Web框架

## TODO

- [x] ORM   
    - [x] 读写分离
    - [x] 语句处理
    - [ ] 数据源
        - [x] MySQL
        - [ ] SQLite
- [ ] 基础框架功能
    - [x] 错误处理
    - [x] 路由解析
    - [x] 事件机制
    - [x] 加载器
    - [x] 环境封装
    - [ ] 缓存控制
        - [ ] 缓存支持
            - [ ] 文件缓存
            - [ ] Redis
            - [ ] Memcache
        - [ ] 缓存
        - [ ] 缓存代理

- [ ] 模板支持
    - [ ] 模板引擎

- [x] 框架应用支持
    - [x] 应用
    - [x] 应用模块支持
    - [ ] 缓存优化
    
- [ ] 2.x 兼容模块


## CS Fixer Rules

```
@PSR2,dir_constant,final_internal_class,is_null,line_ending,lowercase_static_reference,no_empty_statement,no_multiline_whitespace_around_double_arrow,no_unset_cast,single_quote,binary_operator_spaces
```