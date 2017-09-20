# blog

说明 因为框架采用了命名空间等方式、支持的php版本要大于5.5以上才可使用

使用比较简单

1、直接下载此框架到你的坏境目录下 地址 https://github.com/Evenboy/blog.git 可以使用 git clone 进行拉取

2、由于依赖于 composer管理工具 需先安装composer 安装比较简单 官网 https://getcomposer.org/download/

3、composer 安装好 之后 请在项目根目录执行  composer install

4、访问方式是 根目录下的index.php为入口

5、此框架可以隐藏index.php .htaccess文件已经存在 不过你的apache要开启rewrite 重写 nginx也需要配置

6、路由方式 你的服务器地址 例如youserver出现了 welcome 默认访问的是Index控制器下的index方法

7、由于采用blade模版组件、会生成缓存文件、涉及到文件的读写。所以在linux系统下需要文件可读可写的权限(windows请忽略) 具体参考文档


Note that because the framework USES namespaces and other means, the PHP version supported is more than 5.5 to be used

Easy to use

1. download this framework directly to your bad border directory Address https://github.com/Evenboy/blog.git you can use the git clone to pull

2. because depends on the composer management tools Need to install composer installation is fairly simple The website https://getcomposer.org/download/

3. Please execute composer install in the root of the project after the composer is installed

4. The access method is index.php in the root directory

5. This framework can hide the index.php.htaccess file but your apache has to start rewrite and nginx needs to be configured

6. The routing method your server address, such as youserver, shows welcome the default access is the Index method under the Index controller

7. Since the blade template component is used, the cache file is generated and the file is read and written.So under Linux, you need files to read writable permissions (Windows please ignore) for specific reference documentation
