# Performance - test


## Information

- test python script: [script/test_request.py](../script/test_request.py)
- Test ：echo 'hello world' in single page
- Environment
    - Windows
        - Windows 10 [1803] 17134.288
        - Apache 2.4
        - PHP 7.2.1
    - Linux
        - CentOS Linux 7.4.1708 (Core)　 
        - Nginx -Tengine 2.2
        - PHP 7.2.4

## Test Graphic

![](imgs/runtimes.png)

### Linux & Nginx & Suda 2.0.2

```
analyzed php.test.atd3.cn.log in 10000 requests: max 0.002, min 0.000, avg 0.000200
analyzed suda.test.atd3.cn.log in 10000 requests: max 0.004, min 0.000, avg 0.000741
analyzed think.test.atd3.cn.log in 10000 requests: max 0.025, min 0.012, avg 0.012979
analyzed laravel.test.atd3.cn.log in 10000 requests: max 0.400, min 0.046, avg 0.052295
```

| Target | Average (s/req) | Max (s/req) | Min (s/req)|
|---------|---------|---------|---------|
| PHP | 0.000200 |  0.002  | 0.000 |
| Suda    | 0.000741 | 0.004 | 0.000 |
| ThinkPHP| 0.012979 | 0.025 | 0.012 |
| Laravel | 0.052295 | 0.400 | 0.046 |



### Windows & Apache & Suda 2.0.1

```
analyzed suda.atd3.org in 3103 requests : max 1742339, min  432841, avg 498551.638737
analyzed echo.atd3.org in 3103 requests : max 703120, min  402891, avg 409689.134386
analyzed think.atd3.org in 3101 requests : max 384002807, min  827799, avg 1119370.529829
analyzed laravel.atd3.org in 3099 requests : max 2703628, min  922579, avg 969710.239109
```


| Target | Average (ms/req) | Max (ms/req) | Min (ms/req)|
|---------|---------|---------|---------|
| PHP | 409689.134386 |  703120  | 402891 |
| Suda    | 498551.638737 | 1742339 | 432841 |
| ThinkPHP| 1119370.529829 | 384002807 | 827799 |
| Laravel | 969710.239109 | 2703628 | 922579 |
