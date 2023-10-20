<?php

namespace app\index\controller;

use think\Db;
use think\Controller;

class Predis extends Controller
{

    private static $handler = null;
    private static $_instance = null;
    private static $options = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '',
        'db' => 0,
        'timeout' => 0,
        'expire' => 0,
        'persistent' => false,
        'prefix' => '',
    ];

    /*
     * 私有构造函数
     */
    public function __construct($options = [])
    {
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('not support: redis');      //判断是否有扩展
        }

        if (!empty($options)) {
            self::$options = array_merge(self::$options, $options);
        }

        $func = self::$options['persistent'] ? 'pconnect' : 'connect';     //长链接

        self::$handler = new \Redis;
        self::$handler->$func(self::$options['host'], self::$options['port'], self::$options['timeout']);

        if ('' != self::$options['password']) {
            self::$handler->auth(self::$options['password']);
        }

        if (0 != self::$options['db']) {
            self::$handler->select(self::$options['db']);
        }

    }

    /**
     * @return Predis|null 对象
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /*
     * 禁止外部克隆
     */
    final public function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }


    public function set($key, $value, $time = 0)
    {
        if (!$key) {
            return '';
        }
        if (is_array($value)) {
            $value = json_encode($value);
        }

        if (!$time) {
            return self::$handler->set($key, $value);
        }

        return self::$handler->setex($key, $time, $value);
    }

    public function get($key)
    {
        if (!$key) {
            return '';
        }

        return self::$handler->get($key);
    }

    public function incr($key)
    {
        if (!$key) {
            return 0;
        }
        return self::$handler->incr($key);
    }

    public function decr($key)
    {
        if (!$key) {
            return 0;
        }
        return self::$handler->decr($key);
	}


    public function __call($name, $args)
    {
        return call_user_func_array([self::$handler, $name], $args);
    }

}