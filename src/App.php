<?php
namespace GatewayChat;
ini_set('display_errors', 'on');


use GatewayWorker\Register;
use GatewayWorker\Gateway;
use Workerman\Worker;
class App
{
    //配置数组
    public $config;
    const APP_GATEWAY='AppGateway';
    const BUSINESS_WORKER='BusinessWorker';

    public function __construct($rout_path=null,$config_path=null)
    {
        self::extension();
        Route::load($rout_path);
        Config::load($config_path);
        $this->config=new Config();
        return $this;
    }

    /**
     * 环境检测
     * @param null
     */
    public static function extension()
    {
        if(strpos(strtolower(PHP_OS), 'win') === 0)
        {
            exit("start.php not support windows, please use start_for_win.bat\n");
        }
        // 检查扩展
        if(!extension_loaded('pcntl'))
        {
            exit("Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
        }

        if(!extension_loaded('posix'))
        {
            exit("Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
        }
    }

    /**
     * 构建work
     * @param null
     */
    public function make()
    {
        $config=$this->config;
        $servers=$config->get('websocket.servers');
        Worker::$logFile = __DIR__.'/workerman.log';
        Worker::$stdoutFile =__DIR__. '/stdout.log';
        if(is_array($servers)){
            foreach ($servers as $item ){
                $serve=new Config($item);
                $this->makeRegister($serve);
                $this->makeGateway($serve);
                $this->makeBusinessworker($serve);
                unset($c);
            }
        }
        return $this;
    }

    private function   makeRegister( Config $serve)
    {
        if($serve->get('bus_port') ){
            $register=new Register('text://0.0.0.0:'.$serve->get('bus_port'));
            $register->name=$serve->get('name').'Register';
        }
    }

    private function makeGateway( Config $serve)
    {

        $port= $serve->get('port');
        $bus_port=$serve->get('bus_port');
        $name=$serve->get('name');
        $count=$serve->get('processes');
        $lan_ip=$serve->get('lan_ip');
        $start_port=$serve->get('start_port');
        $ping_interval=$serve->get('ping_interval',55);
        $ping_limit=$serve->get('ping_limit',3);
        $ping_data=$serve->get('ping_data');
        // gateway 进程，这里使用Text协议，可以用telnet测试，多端口必改
        $gateway = new Gateway("websocket://0.0.0.0:".$port);

        // gateway名称，status方便查看，多端口必改
        $gateway->name  = $name.self::APP_GATEWAY;

        // gateway进程数
        $gateway->count = $count;

        // 本机ip，分布式部署时使用内网ip
        $gateway->lanIp = $lan_ip;

        // 内部通讯起始端口
        $gateway->startPort = $start_port;

        // 服务注册地址 同start_businessworker.php 这是内部通信端口。
        $register_address= $lan_ip.':'.$bus_port;
        $gateway->registerAddress = $register_address;

        //ping 检测时间
        $gateway->pingInterval = $ping_interval;

        //ping 检测 重试次数
        $gateway->pingNotResponseLimit = $ping_limit;

        //ping 发送数据
        if($ping_data){
            $gateway->pingData = $ping_data;
        }
    }

    private function makeBusinessworker( Config $serve)
    {
        $bus_port=$serve->get('bus_port');
        $count=$serve->get('worker_count');
        $name=$serve->get('name');
        $lan_ip=$serve->get('lan_ip');

        // bussinessWorker 进程
        $worker = new BusinessWorker();
        // worker名称，多端口必改
        $name=(string)($name.self::BUSINESS_WORKER);
        $worker->name =$name;
        // bussinessWorker进程数量 CPU核心数
        $worker->count = $count;
        // 服务注册地址 ，多端口必改
        $worker->registerAddress = $lan_ip.':'.$bus_port;
        //回调类
        $worker->eventHandler= new \GatewayChat\Events($serve);
    }

    public function run()
    {
        Worker::runAll();

    }
}