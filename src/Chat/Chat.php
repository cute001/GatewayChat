<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-05-14
 * Time: 12:29
 */
namespace GatewayChat\Chat;
use GatewayChat\Contract\OnCloseInterface;
use GatewayChat\Contract\OnConnectInterface;
use GatewayChat\Contract\OnMessageInterface;
use GatewayChat\Contract\OnWebSocketConnectInterface;
use GatewayWorker\Lib\Gateway;

class Chat implements OnMessageInterface,OnCloseInterface,OnConnectInterface,OnWebSocketConnectInterface
{
    //用户所在组 所有进入聊天室的即加入此组
    public static $group= 'chat';
    /**
     *以下为redis中存放数据的键值 'chat:'开头
     *第一个字表示类型 s字符串，h哈希 l列表 s合集 z有序合集
     */
    //所有用户信息（哈希）[ uid1=>userinfo,uid2=>userinfo]
    public static $user_info='chat:h_user_info';
    //在线用户（合集）[uid1,uid2,uid4]
    public static $user_online='chat:s_user_online';
    //用户关系表/好友列表（有序合合集） [前缀 +uid]
    //根据用户发送信息先后排序，即每个用户将会有一个表
    public static $user_relation='chat:z_user_relation_';
    //用户关系 请求列表（哈希）  [前缀 +uid] 一个用户对应一个表
    //一个请求用户ID对应一个field，请求容对应value
    public static $user_request='chat:h_user_request_';
    //用户系统消息表前缀(列表) [前缀 +uid]
    public static $user_system='chat:l_user_system_';
    //私聊消息记录(列表) [前缀 +uid ] send_id + receive_id
    public static $private_group_list='chat:l_private_chat_';
    //群聊房间信息列表
    public static $chat_group_room='';
    //群聊消息（合集）[msg1,msg2,msg3]
    public static $group_chat='';
    //聊天室信息（哈希）？？？
    public static $chat_room='';
    public function onMessage($client_id, $message, $db, \Redis $redis)
    {
        // TODO: Implement onMessage() method.
        $data=json_decode($message,true);
        if(isset($data['event']) && isset($data['method'])){

        };//method
    }

    public function onClose($client_id, $db,\Redis $redis)
    {
        // TODO: Implement onClose() method.
        if($_SESSION['id']){
            $redis->sRem(self::$user_online,$_SESSION['id']);
        }

    }

    public function onConnect($client_id, $db,\Redis $redis)
    {
        // TODO: Implement onConnect() method.
    }

    public function onWebSocketConnect($client_id, $data, $db,\Redis $redis)
    {
        // TODO: Implement onWebSocketConnect() method.
        if(empty($data['get'])){
            return false;
        }
        //此处放置鉴权，失败则return;
        Gateway::joinGroup($client_id,self::$group);
        if(!empty($data['get']['id'])){
            Gateway::bindUid($client_id,$data['get']['id']);
            $_SESSION['id']=$data['get']['id'];
            $redis->sAdd(self::$user_online,$data['get']['id']);
        }
    }
}