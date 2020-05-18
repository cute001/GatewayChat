<?php
namespace GatewayChat\Contract;
interface OnCloseInterface
{
    public function onClose($client_id,$db,\Redis $redis);
}