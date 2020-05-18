<?php
namespace GatewayChat\Contract;
interface EventsInterface
{
    public function onWorkerStart($worker);

    public function onWebSocketConnect($client_id, $data);

    public  function onConnect($client_id);

    public  function onMessage($client_id, $message);

    public  function onClose($client_id);

    public function onWorkerStop($worker);
}