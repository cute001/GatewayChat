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
            $receive=$data['receive'];
            $send=$data['send'];
            $data['time']=time();
            $responser=['type'=>'recordConversation','data'=>$data];
            /*$message_id=$redis->lPop('');
            if($message_id)
            $data['message_id']=$message_id;*/
            $str=json_encode($responser,true);

            $key=Chat::$private_list.$receive.'_'.$send;
            if(!$redis->exists($key)){
                $key=Chat::$private_list.$send.'_'.$receive;
            }
            $redis->lPush($key,$str);

            Gateway::sendToUid($receive,$str);
            if($data['receive'] !== $data['send']){
                Gateway::sendToUid($send,$str);
            }

        }
    }
}