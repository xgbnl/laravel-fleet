## laravel-fleet

> 帮助开发者快速开发👏

#### 环境及扩展要求

- `^php8.2`
- `redis`
- `ext-redis`
- `ext-http`

#### 安装包及发布

执行以上操作后，在会`Controllers`目录中生成`BaseController`,你后面生成的控制器也应该继承它,因为它为你提供了许多开箱即用的功能

```shell
composer require xgbnl/laravel-fleet

php artisan fleet:publish 
```
#### 配置缓存层

Cacheable将使用`redis`管理你的缓存,所以你要为你的`.env`进行配置

```dotenv
CACHEABLE=cache
```

编辑 `config/database.php`，添加新的键值`cache`

```php 
'redis' => [

    // add 
        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '1'),
        ],
] 
```
