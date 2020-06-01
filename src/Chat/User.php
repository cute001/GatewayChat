<?php
/**
 * 用户处理类
 * User: Administrator
 * Date: 2020-05-20
 * Time: 14:45
 */
namespace GatewayChat\Chat;
use GatewayWorker\Lib\Gateway;
use Workerman\MySQL\Connection;
class User
{
    /**
     * 登录成功
     * @param string $client_id 请求ID
     * @param string $group 加入聊天组名
     * @param int $id 用户chat_user id
     * @return null
     */
    public static function login($client_id,$group,$id)
    {
        $_SESSION['id']=$id;
        Gateway::joinGroup($client_id,$group);
        Gateway::bindUid($client_id,$id);
        $data=['type'=>'userLogin','id'=>$id];
        $str=json_encode($data,320);
        Gateway::sendToGroup($group,$str);
    }
    /**
     * 更新用户
     * @param string $client_id 请求ID
     * @param string $group 加入聊天组名
     * @param int $id 用户chat_user id
     * @return null
     */
    public static function updateInfo($client_id, $data, Connection $db,  \Redis $redis)
    {
        if(!empty($data['id']) ){
            $redis->hSet(Chat::$user_info, $data['id'],json_encode($data,320));
            $data=['userInfo'=>$data,'type'=>'updateInfo'];
            $str=json_encode($data,320);
            Gateway::sendToGroup(Chat::$group,$str);
        }
    }

    public function getUserList($client_id, $data, Connection $db,  \Redis $redis)
    {
        $online=isset($data['online']) ? $data['online'] : $redis->sMembers(Chat::$user_online);
        if(empty($online)){
            return false;
        }
        $list=$redis->hMGet(Chat::$user_info,$online);
        $data=['type'=>'recordUserList','data'=>$list];
        $str=json_encode($data,320);
        Gateway::sendToClient($client_id,$str);
    }

}