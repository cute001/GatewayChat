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
    //转发私聊
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
            $str=json_encode($data,320);

            $key=Chat::$private_list.$receive.'_'.$send;
            if(!$redis->exists($key)){
                $key=Chat::$private_list.$send.'_'.$receive;
            }
            $redis->lPush($key,$str);

            $str=json_encode($responser,320);
            Gateway::sendToUid($receive,$str);
            if($data['receive'] !== $data['send']){
                Gateway::sendToUid($send,$str);
            }

        }
    }

    //拉取消息
    public function getRecord($client_id, $data,Connection $db,\Redis $redis)
    {
        if(empty($data['receive']) ||  empty($data['send'])){
            return false;
        }
        $receive=$data['receive'];
        $send=$data['send'];
        $key=Chat::$private_list.$receive.'_'.$send;
        if(!$redis->exists($key)){
            $key=Chat::$private_list.$send.'_'.$receive;
        }
        $list=$redis->lRange($key,0,-1);
        if(empty($list)){
            return false;
        }
        $data['list']=$list;
        $arr=['type'=>'recordList','data'=>$data];
        $str=json_encode($arr,320);
        Gateway::sendToUid($send,$str);
    }
}