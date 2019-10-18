<?php
/**
 * Created by PhpStorm.
 * User: wenrouzei
 * Date: 2019/8/12
 * Time: 23:13
 */

namespace App\WorkerMan;


use App\Jobs\ChatMessage;
use GatewayWorker\BusinessWorker;
use Illuminate\Support\Facades\Log;

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
        try {
            $data = json_decode($message, true);
            if ($data && isset($data['type']) && $data['type'] == 'chat') {
                dispatch((new ChatMessage([
                    'message' => $data['content'] ?? '',
                    'where' => $data['where'] ?? '',
                    'client_id' => $clientId
                ]))->onQueue('default'));
            }
        } catch (\Throwable $throwable) {
            try {
                Log::channel('workerman')->info($clientId . ' message：' . $message, ['$throwable' => $throwable]);
            } catch (\Throwable $throwable) {

            }
        }
    }

    public static function onClose($clientId)
    {
        Log::channel('workerman')->debug('close：' . $clientId);
    }
}
