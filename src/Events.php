<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace GatewayChat;
/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use GatewayChat\Contract\EventsInterface;
use Workerman\MySQL\Connection;
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events implements EventsInterface
{
    public  $db = null;
    public  $redis=null;
    private $name;
    private $config;

    public function __construct( Config $config)
    {
        $this->config=$config;
    }

    /**
     * 进程启动后初始化数据库连接
     */
    public  function onWorkerStart($worker)
    {
        $name=$worker->name;
        $length=strlen($name)-strlen(App::BUSINESS_WORKER);
        $this->name=substr($name,0,$length);
        $config=new Config();
        $database=$config->get('database');
        $host=$config->get('database.hostname');
        $port=$config->get('database.hostport');
        $usr=$config->get('database.username');
        $password=$config->get('database.password');
        $db_name=$config->get('database.database');

        if(!empty($database)){
            $this->db = new Connection($host, $port, $usr, $password, $db_name);
        }

        $redis_host=$config->get('redis.host','127.0.0.1');
        $redis_port=$config->get('redis.port',6379);
        if(empty($this->redis)){
            $this->redis= new \Redis();
            $this->redis->connect($redis_host,$redis_port);
        }
        $this->callback('?base',__FUNCTION__,[$worker]);
    }

    /**
     * (要求Gateway版本>=3.0.8)
     * 如果业务不需此回调可以删除onConnect
     * @param int $client_id 连接id
     * @param array  $data websocket握手时的http头数据，包含get、server等变量
     */
    public  function onWebSocketConnect($client_id, $data)
    {
        if(isset($data['server']) && isset($data['server']['REQUEST_URI']) ){
            $url=$data['server']['REQUEST_URI'];
            $arr=parse_url($url);
            $path=isset($arr['path'])?$arr['path']:'';
            unset($arr);
            $path= rtrim(ltrim($path,'/') ,'/');
            $controller=Route::get($this->name,$path);
            if(!$controller){
                $path='default';
                $controller=Route::get($this->name,$path);
            }
            $_SESSION['_path']=$path;
            if($controller){
                $this->callback(
                    $path,
                    __FUNCTION__,
                    [
                        $client_id,
                        $data,
                        $this->db,
                        $this->redis
                    ]
                );
            }
        }
    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * @param int $client_id 连接id
     */
    public  function onConnect($client_id)
    {
        $path=isset($_SESSION['_path'])?$_SESSION['_path']:null;
        $this->callback($path,__FUNCTION__,[$client_id,$this->db,$this->redis]);
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public  function onMessage($client_id, $message)
    {
        $path=isset($_SESSION['_path'])?$_SESSION['_path']:null;
        $this->callback($path,__FUNCTION__,[$client_id,$message,$this->db,$this->redis]);
//        $arr=$this->db->select('*')->from('admin_user')->row();
//        Gateway::sendToClient($client_id, json_encode($arr,8));
//        $key=self::$redis->keys('c:*');
//        Gateway::sendToClient($client_id, json_encode($key,8));
    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public  function onClose($client_id)
    {
        $path=isset($_SESSION['_path'])?$_SESSION['_path']:null;
        $this->callback($path,__FUNCTION__,[$client_id,$this->db,$this->redis]);
    }

    public function onWorkerStop($worker)
    {
        $this->callback('?base',__FUNCTION__,[$worker]);
    }

    public function callback($path,$fun,$param_arr)
    {

        if(!$path){
            return false;
        }

        $controller=Route::get($this->name,$path);
        if(!$controller){
            return false;
        }

        if(is_array($controller)){
            foreach ( $controller as $item){
                if(is_callable([$item,$fun])){
                    if( is_string($item) ){
                        $obj=new $item;
                    }
                    call_user_func_array([$obj,$fun],$param_arr);
                }
            }
        }else{
            if(is_callable([$controller,$fun])){
                if( is_string($controller) ){
                    $obj=new $controller;
                }else{
                    $obj=$controller;
                }
                call_user_func_array([$obj,$fun],$param_arr);
            }
        }
    }
}
