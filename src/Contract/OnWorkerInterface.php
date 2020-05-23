<?php
namespace GatewayChat\Contract;
use GatewayWorker\BusinessWorker;
interface OnWorkerInterface
{
    public function onWorkerStart(BusinessWorker $worker);

    public function onWorkerStop(BusinessWorker $worker);
}