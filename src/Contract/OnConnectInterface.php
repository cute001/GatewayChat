<?php
namespace GatewayChat\Contract;
use Workerman\MySQL\Connection;
interface OnConnectInterface
{
    public function onConnect($client_id,Connection $db,\Redis $redis);
}