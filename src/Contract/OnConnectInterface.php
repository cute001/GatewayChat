<?php
namespace GatewayChat\Contract;
interface OnConnectInterface
{
    public function onConnect($client_id,$db,\Redis $redis);
}