# 性能测试


## 脚本新能分析

- 测试脚本：[script/test_request.py](../script/test_request.py)
- 测试内容：页面显示 Hello World 字样
- 测试环境：
    - Windows
        - Windows 10 [1803] 17134.288
        - Apache 2.4
        - PHP 7.2.1
    - Linux
        - CentOS Linux 7.4.1708 (Core)　 
        - Nginx -Tengine 2.2
        - PHP 7.2.4

## 测试报告

![](imgs/runtimes.png)

### Linux & Nginx & Suda 2.0.2

```
analyzed php.test.atd3.cn.log in 10000 requests: max 0.002, min 0.000, avg 0.000200
analyzed suda.test.atd3.cn.log in 10000 requests: max 0.004, min 0.000, avg 0.000741
analyzed think.test.atd3.cn.log in 10000 requests: max 0.025, min 0.012, avg 0.012979
analyzed laravel.test.atd3.cn.log in 10000 requests: max 0.400, min 0.046, avg 0.052295
```

| 网站程序 | 平均耗时 | 最大耗时 | 最小耗时 |
|---------|---------|---------|---------|
| PHP原始 | 0.000200 |  0.002  | 0.000 |
| Suda    | 0.000741 | 0.004 | 0.000 |
| ThinkPHP| 0.012979 | 0.025 | 0.012 |
| Laravel | 0.052295 | 0.400 | 0.046 |

*单位：秒*

### Windows & Apache & Suda 2.0.1

```
analyzed suda.atd3.org in 3103 requests : max 1742339, min  432841, avg 498551.638737
analyzed echo.atd3.org in 3103 requests : max 703120, min  402891, avg 409689.134386
analyzed think.atd3.org in 3101 requests : max 384002807, min  827799, avg 1119370.529829
analyzed laravel.atd3.org in 3099 requests : max 2703628, min  922579, avg 969710.239109
```

| 网站程序 | 平均耗时 | 最大耗时 | 最小耗时 |
|---------|---------|---------|---------|
| PHP原始 | 409689.134386 |  703120  | 402891 |
| Suda    | 498551.638737 | 1742339 | 432841 |
| ThinkPHP| 1119370.529829 | 384002807 | 827799 |
| Laravel | 969710.239109 | 2703628 | 922579 |

*单位：微秒*
