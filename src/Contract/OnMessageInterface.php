<?php
namespace GatewayChat\Contract;
interface OnMessageInterface
{
    public function onMessage($client_id,$message,$db,\Redis $redis);
}