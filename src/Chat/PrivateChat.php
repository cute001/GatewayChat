<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-05-29
 * Time: 13:27
 */
namespace GatewayChat\Chat;
use GatewayWorker\Lib\Gateway;
use Workerman\MySQL\Connection;
class PrivateChat
{
    public function send($client_id, $data,Connection $db,\Redis $redis)
    {
        if(isset($data['receive']) && isset($data['send'])){
            $data['time']=time();
            /*$message_id=$redis->lPop('');
            if($message_id)
            $data['message_id']=$message_id;*/
            $str=json_encode($data,true);
            Gateway::sendToUid($data['receive'],$str);
            Gateway::sendToUid($data['send'],$str);
        }
    }
}