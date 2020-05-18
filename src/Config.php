<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-05-13
 * Time: 10:09
 */
namespace GatewayChat;
class Config
{
    private static $config;

    private $data;
    private $is_factory=true;

    public function __construct($config=null)
    {
        if( !empty($config) ){
            $this->data=$config;
            $this->is_factory=false;
        }else{
            $this->data=self::$config;
        }
        return $this;
    }

    public static function load($path)
    {
        if(is_string($path)){
            self::$config=require_once $path;
        }
        if(is_array($path)){
            self::$config=$path;
        }
    }

    public function get($name=null,$default=null)
    {
        $config= $this->is_factory?self::$config:$this->data;
        if(empty($name)){
            return $config;
        }
        $arr= explode(".", $name);
        foreach ($arr as $item){
            if( empty($config[$item]) ){
                return $default ;
            }
            $config =$config[$item];
        }
        return $config;
    }

    public function set($arr=null,$is_factory=false)
    {
        if($arr){
            $this->data=$arr;
        }
        if($is_factory){
            $this->is_factory=$is_factory;
        }
        return $this;
    }
}