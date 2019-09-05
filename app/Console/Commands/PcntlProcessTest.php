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
            $pid = pcntl_fork();
            if ($pid == -1) {
                echo "fork failed : {$i} \r\n";
                exit();
            } elseif ($pid > 0) {
                $this->processChildIds[] = $pid;
            } else {
                $this->handleTask($i);
                exit();
            }
        }

        //子进程完成之后要退出
        while (count($this->processChildIds) > 0) {
            foreach ($this->processChildIds as $key => $pid) {
                $res = pcntl_waitpid($pid, $status, WNOHANG);

                // If the process has already exited
                if ($res == -1 || $res > 0) {
                    Log::debug('子进程退出', ['$pid' => $pid, 'pcntl_wifexited' => pcntl_wifexited($status), 'pcntl_wifsignaled' => pcntl_wifsignaled($status), '$res' => $res]);
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
