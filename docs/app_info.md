## 应用程序目录结构说明
```
├─data
│  ├─cache  缓存目录
│  ├─logs   日志目录
│  ├─temp   零时目录
│  └─views  生成的模板目录
│      └─default    默认模板
├─modules   应用模块目录  
│  ├─default    默认模块
│  │  ├─resource    模块资源目录  
│  │  │  ├─config   模块配置
│  │  │  ├─langs    模块语言
│  │  │  └─template 模块模板
│  │  │      └─default  默认模板
│  │  │          └─static   模板的静态资源
│  │  ├─share   模块共享库（其他模块可访问）
│  │  └─src     模块私有代码
│  └─suda 应用默认模块
├─resource  应用资源
│  └─config 应用配置
└─share 应用共享库
```