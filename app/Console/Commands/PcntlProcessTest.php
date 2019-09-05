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
    protected $signature = 'pcntl-process:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $workerCount = 10;

    protected $processChildIds = 10;

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
        $this->processChildIds = [];
        for ($i = 0; $i < $this->workerCount; $i++) {
            $this->processChildIds[$i] = pcntl_fork();
            switch ($this->processChildIds[$i]) {
                case -1 :
                    echo "fork failed : {$i} \r\n";
                    exit;
                case 0 :
                    $this->doWork($i);
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

    protected function doWork($i)
    {
        sleep(rand(3, 10));
        Log::debug('finish', ['$i' => $i, 'pid' => posix_getpid()]);
    }
}
