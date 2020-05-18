<?php
namespace GatewayChat\Contract;
interface OnWebSocketConnectInterface
{
    public function onWebSocketConnect($client_id,$data,$db,\Redis $redis);
}