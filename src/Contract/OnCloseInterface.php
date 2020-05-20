<?php
namespace GatewayChat\Contract;
use Workerman\MySQL\Connection;
interface OnCloseInterface
{
    public function onClose($client_id,Connection $db,\Redis $redis);
}