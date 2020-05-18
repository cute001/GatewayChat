<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-05-13
 * Time: 17:14
 */
namespace GatewayChat;
class BusinessWorker extends \GatewayWorker\BusinessWorker
{
    /**
     * 当进程启动时一些初始化工作
     *
     * @return void
     */
    protected function onWorkerStart()
    {
        if (!class_exists('\Protocols\GatewayProtocol')) {
            class_alias('GatewayWorker\Protocols\GatewayProtocol', 'Protocols\GatewayProtocol');
        }

        if (!is_array($this->registerAddress)) {
            $this->registerAddress = array($this->registerAddress);
        }
        $this->connectToRegister();

        \GatewayWorker\Lib\Gateway::setBusinessWorker($this);
        \GatewayWorker\Lib\Gateway::$secretKey = $this->secretKey;
        if ($this->_onWorkerStart) {
            call_user_func($this->_onWorkerStart, $this);
        }

        if (is_callable([$this->eventHandler, 'onWorkerStart'])) {
            call_user_func([$this->eventHandler, 'onWorkerStart'], $this);
        }

        if (function_exists('pcntl_signal')) {
            // 业务超时信号处理
            pcntl_signal(SIGALRM, array($this, 'timeoutHandler'), false);
        } else {
            $this->processTimeout = 0;
        }

        // 设置回调
        if (is_callable([$this->eventHandler , 'onConnect'])) {
            $this->_eventOnConnect =[$this->eventHandler , 'onConnect'];
        }

        if (is_callable([$this->eventHandler , 'onMessage'])) {
            $this->_eventOnMessage = [$this->eventHandler , 'onMessage'];
        } else {
            echo "Waring: {$this->eventHandler}::onMessage is not callable\n";
        }

        if (is_callable([$this->eventHandler, 'onClose'])) {
            $this->_eventOnClose = [$this->eventHandler, 'onClose'];
        }

        if (is_callable([$this->eventHandler , 'onWebSocketConnect'])) {
            $this->_eventOnWebSocketConnect = [$this->eventHandler , 'onWebSocketConnect'];
        }

    }

    /**
     * 当进程关闭时一些清理工作
     *
     * @return void
     */
    protected function onWorkerStop()
    {
        if ($this->_onWorkerStop) {
            call_user_func($this->_onWorkerStop, $this);
        }
        if (is_callable([$this->eventHandler , 'onWorkerStop'])) {
            call_user_func([$this->eventHandler , 'onWorkerStop'], $this);
        }
    }
}