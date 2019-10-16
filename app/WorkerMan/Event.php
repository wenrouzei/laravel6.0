<?php
/**
 * Created by PhpStorm.
 * User: wenrouzei
 * Date: 2019/8/12
 * Time: 23:13
 */

namespace App\WorkerMan;


use App\User;
use GatewayWorker\BusinessWorker;
use GatewayWorker\Lib\Gateway;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Workerman\Lib\Timer;

class Event
{
    /**
     * @param $businessWorker
     */
    public static function onWorkerStart(BusinessWorker $businessWorker)
    {
        Log::channel('workerman')->debug('worker-id：' . $businessWorker->workerId);
    }

    public static function onConnect($clientId)
    {
        Log::channel('workerman')->info('connect：' . $clientId);
    }

    public static function onWebSocketConnect($clientId, $data)
    {
        Log::channel('workerman')->info('ws connect：' . $clientId, ['$data' => $data]);
    }

    public static function onMessage($clientId, $message)
    {
        Log::channel('workerman')->info($clientId . ' message：' . $message);
    }

    public static function onClose($clientId)
    {
        Log::channel('workerman')->debug('close：' . $clientId);
    }
}
