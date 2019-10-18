<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>websocket</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    <div class="content">
        <div class="title m-b-md">
            websocket
        </div>
        <div class="links">
            <span id="time"></span>
        </div>
    </div>
</div>
</body>
</html>
<script>
    let ws = new WebSocket('ws://192.168.10.10:2346');
    let intervalId;
    let chatIntervalId;
    ws.addEventListener('open', function (event) {
        console.log('已连接！');
        intervalId = setInterval(function () {
            console.log(intervalId + ' ping');
            var time = new Date;
            //获取年月日，时分秒
            var y = time.getFullYear();
            var m = time.getMonth() + 1;
            var d = time.getDate();
            var h = time.getHours();
            var i = time.getMinutes();
            var s = time.getSeconds();
            var weekDay = time.getDay();
            document.getElementById('time').innerHTML = (y + "年" + m + "月" + d + "日" + "   " + h + ":" + i + ":" + s + " " + "星期" + "日一二三四五六".charAt(weekDay));
            ws.send('{"type":"pong"}');
        }, 10000);
        chatIntervalId = setInterval(function () {
            console.log(intervalId + ' chat');
            ws.send('{"type":"chat","content":"测试内容","where":1}');
        }, 2000);
    });
    ws.addEventListener('message', onMessage)
    ws.addEventListener('close', event => {
        console.log("连接关闭，定时重连");
        clearInterval(intervalId);
        clearInterval(chatIntervalId);
        console.log('关闭定时器:' + intervalId);
        console.log('关闭聊天定时器:' + chatIntervalId);
    });
    ws.addEventListener('error', function (event) {
        console.log("出现错误");
    });

    function onMessage(event) {
        console.log(event.data);
        let data = JSON.parse(event.data)
        switch (data.type) {
            case 'ping': // 服务端ping客户端
                ws.send('{"type":"pong"}')
                break
            case 'init': // 初始化绑定客户端id
                client_id = data.client_id
                ws.send('{"openid":"test10000001","role_id":"1111","name":"bA35kOAI3H","game_id":"10000001","zone_id":"1","zone_name":"\u53cc\u7ebf\u4e00\u533a","vip":"1","pay":"0","level":"0","channel":"0","client_id":"5MysqNAwuZ80yaYOyfhZ","timestamp":1525749379,"type":"login","sign":"3746dc3d13194f3748de44c5fbdb262d"}')
                break
            case 'fail':
                console.log(event.data);
                break
            case 'success':
                console.log(event.data)
                break
        }
    }
</script>
