# 性能测试


## 脚本新能分析

- 测试脚本：[script/test_request.py](../script/test_request.py)
- 测试内容：页面显示 Hello World 字样
- 测试环境：
    - Windows 10 [1803] 17134.288
    - phpStudy 2018
    - PHP 7.2.1

```
[+] Processing
[-] |##################################################| 100%
[+] Done
[+] Summary
    - request http://echo.atd3.org 2000 times in avg 0.425919s, max 2.954713s, min 0.405524s
    - request http://suda.atd3.org 2000 times in avg 0.511854s, max 1.667579s, min 0.438687s
    - request http://think.atd3.org 2000 times in avg 1.074305s, max 384.008326s, min 0.832934s
    - request http://laravel.atd3.org 2000 times in avg 0.992261s, max 3.219600s, min 0.928798s
```

| 网站程序 | 平均耗时 | 单次请求最大耗时 | 单次请求最小耗时 |
|---------|---------|---------|---------|
| PHP原始 | 0.425919 |  2.954713s  |  0.405524 |
| Suda    | 0.511854 | 1.667579 | 0.438687 |
| ThinkPHP| 1.074305 | 384.008326 | 0.832934 |
| Laravel | 0.992261 | 3.219600 | 0.928798 |

*单位秒*

## 日志新能分析

**注：** 日志内容为以上请求生成

```
dxkite@ubuntu:~/Desktop/Test_Suda$ ./analyse_test_requests.sh vhost-log.log 
analyzed suda.atd3.org in 3103 requests : max 1742339, min  432841, avg 498551.638737
analyzed echo.atd3.org in 3103 requests : max 703120, min  402891, avg 409689.134386
analyzed think.atd3.org in 3101 requests : max 384002807, min  827799, avg 1119370.529829
analyzed laravel.atd3.org in 3099 requests : max 2703628, min  922579, avg 969710.239109
```

| 网站程序 | 平均耗时 | 单次请求最大耗时 | 单次请求最小耗时 |
|---------|---------|---------|---------|
| PHP原始 | 409689.134386 |  703120  | 402891 |
| Suda    | 498551.638737 | 1742339 | 432841 |
| ThinkPHP| 1119370.529829 | 384002807 | 827799 |
| Laravel | 969710.239109 | 2703628 | 922579 |

*单位：微秒*