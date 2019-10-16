<?php

namespace App\Console\Commands;

use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use Illuminate\Console\Command;
use Workerman\Worker;

class WorkerManBusinessWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'worker-man:business-worker {action} {--daemon}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'worker-man:business-worker
                              {action : 命令}
                              {--daemon : 以守护进程方式启动}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        global $argv;
        $action = $this->argument('action');

        $argv[0] = 'worker-man-gateway';
        $argv[1] = $action;
        $argv[2] = $this->option('daemon') ? '-d' : '';

        $this->init();
    }

    private function init()
    {
        ini_set('display_errors', 'on');
        if (strpos(strtolower(PHP_OS), 'win') === 0) {
            exit("not support windows.\n");
        }

        if (!extension_loaded('pcntl')) {
            exit("Please install pcntl extension.\n");
        }

        if (!extension_loaded('posix')) {
            exit("Please install posix extension.\n");
        }

        Worker::$stdoutFile = storage_path('workerman/stdout.log');
        $unique_prefix = str_replace(DIRECTORY_SEPARATOR, '_', __FILE__);
        Worker::$pidFile = storage_path("workerman/$unique_prefix.pid");
        Worker::$logFile = storage_path('workerman/workerman.log');
        $this->start();
    }

    private function start()
    {
        $this->startGateWay();
        $this->startBusinessWorker();
        $this->startRegister();
        Worker::runAll();
    }

    private function startBusinessWorker()
    {
        $worker = new BusinessWorker();
        $worker->name = 'BusinessWorker';
        $worker->count = 4;
        $worker->registerAddress = '127.0.0.1:1236';
        $worker->eventHandler = \App\Workerman\Event::class;
    }

    private function startGateWay()
    {
        $gateway = new Gateway("websocket://0.0.0.0:2346");
        $gateway->name = 'Gateway';
        $gateway->count = 4;
        $gateway->lanIp = '127.0.0.1';
        $gateway->startPort = 2300;
        $gateway->pingInterval = 55;
        $gateway->pingNotResponseLimit = 1;
        $gateway->pingData = '';
        $gateway->registerAddress = '127.0.0.1:1236';
    }

    private function startRegister()
    {
        new Register('text://0.0.0.0:1236');
    }
}
