<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PcntlProcessTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pcntl-process:test {--daemon}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $workerCount = 10;

    protected $processChildIds = [];

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
        if ($this->option('daemon')) $this->daemon();

        for ($i = 0; $i < $this->workerCount; $i++) {
            $this->processChildIds[$i] = pcntl_fork();
            switch ($this->processChildIds[$i]) {
                case -1 :
                    echo "fork failed : {$i} \r\n";
                    exit;
                case 0 :
                    $this->handleTask($i);
                    exit;
                default :
                    break;
            }
        }

        //子进程完成之后要退出
        while (count($this->processChildIds) > 0) {
            $childPid = pcntl_waitpid(-1, $status, WNOHANG);
            foreach ($this->processChildIds as $key => $pid) {
                if ($childPid == $pid || $childPid == -1) {
                    unset($this->processChildIds[$key]);
                }
            }
        }
    }

    /**
     * 开启守护进程
     */
    protected function daemon()
    {
        $pid = pcntl_fork();
        if (-1 === $pid) {
            die('fork fail');
        } elseif ($pid > 0) {
            exit(0);
        }
        if (-1 === posix_setsid()) {
            die("setsid fail");
        }
        // Fork again avoid SVR4 system regain the control of terminal.
        $pid = pcntl_fork();
        if (-1 === $pid) {
            die("fork fail");
        } elseif (0 !== $pid) {
            exit(0);
        }
    }

    /**
     * 业务
     *
     * @param $i
     */
    protected function handleTask($i)
    {
        sleep(rand(3, 10));
        Log::debug('finish', ['$i' => $i, 'pid' => posix_getpid()]);
    }
}
