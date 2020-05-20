<?php
namespace GatewayChat\Contract;
use Workerman\MySQL\Connection;
interface OnMessageInterface
{
    public function onMessage($client_id,$message,Connection $db,\Redis $redis);
}