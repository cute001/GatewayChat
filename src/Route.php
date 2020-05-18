<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-05-13
 * Time: 18:50
 */
namespace GatewayChat;
class Route
{
    public static $routes;
    public static function load($path)
    {
        self::$routes=require_once $path;
    }

    public static function get($name,$path)
    {
        $routes=self::$routes;
        if($name && !empty($routes[$name])){
            return isset($routes[$name][$path])?$routes[$name][$path]:null;
        }
    }
}