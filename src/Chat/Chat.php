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
use GatewayChat\Events;
use GatewayChat\JwtAuth;
use GatewayWorker\Lib\Gateway;
use Workerman\MySQL\Connection;
class Chat //implements OnMessageInterface,OnCloseInterface,OnConnectInterface,OnWebSocketConnectInterface
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

    /* ----------好友关系----------*/
    //用户关系表/好友列表（有序合合集） [前缀 +uid]  根据用户发送信息先后排序，即每个用户将会有一个表
    public static $user_relation='chat:relation:z_user_';
    //用户关系 请求列表（哈希）  [前缀 +uid] 一个用户对应一个表 一个请求用户ID对应一个field，请求容对应value
    public static $user_request='chat:request:h_user_';
    //用户系统消息表前缀(列表) [前缀 +uid]
    public static $user_system='chat:system:l_user_';

    /* ----------私聊相关---------- */
    //私聊消息记录(列表) [前缀 +uid ] send_id + receive_id
    public static $private_list='chat:private:l_';
    // 私聊消息ID列表
    public static $private_id_list='chat:l_private_id';
    // 私聊消息记录起始娄
    public static $private_id_start='chat:s_private_start';

    /* ----------群聊相关---------- */
    //群聊房间信息列表
    public static $chat_group_room='';
    //群聊消息（合集）[msg1,msg2,msg3]
    public static $group_chat='';
    //聊天室信息（哈希）？？？
    public static $chat_room='';

    public static function onMessage($client_id, $message, Connection $db, \Redis $redis)
    {
        // TODO: Implement onMessage() method.
        $data=json_decode($message,true);
        if(isset($data['controller']) && isset($data['method'])){
            $controller=__NAMESPACE__.'\\'.$data['controller'];
            $method=$data['method'];
            $data=isset($data['data'])?$data['data']:null;
            Events::callback($controller,$method,[$client_id, $data,$db, $redis]);
        };
    }

    public function onClose($client_id,Connection  $db,\Redis $redis)
    {
        // TODO: Implement onClose() method.
        if(isset( $_SESSION['id']) && !Gateway::isUidOnline($_SESSION['id']) ){
            $redis->sRem(self::$user_online,$_SESSION['id']);
        }

    }

    public static function onConnect($client_id, Connection $db,\Redis $redis)
    {
        // TODO: Implement onConnect() method.
    }

    public static function onWebSocketConnect($client_id, $data,Connection $db,\Redis $redis)
    {
        // TODO: Implement onWebSocketConnect() method.
        if(empty($data['get']) || empty($data['get']['token'] ) || empty($data['get']['id'] ) ){
            Gateway::closeClient($client_id);
            return null;
        }
        $chat=$db->select('token')->from('chat_user')->where('id= :id')->bindValues(array('id'=>$data['get']['id']))->row();
        if($chat && isset($chat['token'])){
            $chat =JwtAuth::verifyToken($data['get']['token'],$chat['token']);
            if( is_array($chat) ){
                $redis->sAdd(static::$user_online,$chat['id']);
                return User::login($client_id,self::$group,$chat['id']);
            }
        }
        Gateway::closeClient($client_id);

    }
}