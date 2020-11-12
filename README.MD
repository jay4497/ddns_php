# 基于 DNS_POD 家接口的动态域名解析

主要通过定时任务来执行脚本，以此实现动态域名解析。

### 使用方法

##### 要求

PHP >= 5.6

##### 拉取本程序到本地

```
git clone https://github.com/jay4497/ddns_php.git
```

##### 配置文件

复制 `config.example.php` 文件并重命名为 `config.php`，然后按实际情况进行配置。

##### 运行

Linux：

```
crontab -e
// then insert
5 * * * * /path/to/php -f /path/to/App.php
```

Windows：

使用 **任务计划程序** 创建任务，操作项设置脚本填写 `/path/to/php -f /path/to/App.php`，其它项请自行配置，不赘述。

### 更新日志

2020-11-12

新增 `ipv6` 地址的解析。

更换获取本地 IP 的远端服务（[ip.sb](https://ip.sb/api/)）地址。
