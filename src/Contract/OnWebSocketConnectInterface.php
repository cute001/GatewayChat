<?php
namespace GatewayChat\Contract;
use Workerman\MySQL\Connection;
interface OnWebSocketConnectInterface
{
    public function onWebSocketConnect($client_id,$data,Connection $db,\Redis $redis);
}