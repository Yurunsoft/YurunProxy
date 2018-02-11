# YurunProxy
基于 Swoole 的内网穿透，支持本地微信开发、Web开发，让外网能够访问到！

刚开始学 Swoole 做的一个山寨简易版 ngrok。

服务端需要 Swoole 支持，客户端为了兼容 Windows 系统使用了 Sockets 扩展，后续计划增加 Swoole 版的异步客户端。

第一版没有用自定义协议，传输数据使用的 JSON 格式，通过 AES 加密、GZip 压缩后传输。

正在不断开发完善，第一次开发这种服务器应用，欢迎吐槽！

## 部署说明

首先你要安装好 PHP + Swoole 环境，这个就不多说了，详情看：https://wiki.swoole.com/wiki/page/6.html

然后将代码从 Git 下载下来。

### 服务端

在服务器上配置 `config/server.php` 文件

`web` 是访问服务器上网页的端口设置，`listen` 是客户端连接服务器的端口设置。

`domain` 是配置要支持代理转发的域名。

`key` 是长度为 32 的字符串，是数据加密密钥，必须和客户端设置一致。

启动服务器端 `php YurunProxy/runServer.php`

### 客户端

在开发机上配置 `config/config.php` 文件

`server` 是客户端连接服务器的端口设置。

`domain` 是配置要支持代理转发的域名，必须和服务器上设置对应。

`key` 是长度为 32 的字符串，是数据加密密钥，必须和服务器设置一致。

启动客户端，监听所有域名的请求 `php YurunProxy/runClient.php`

启动客户端，只监听某个域名的请求 `php YurunProxy/runClient.php -domain www.proxy.com`