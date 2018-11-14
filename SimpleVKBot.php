<?php


$access_token = 'ваш токен';

function getLongPollData()

{
    global $access_token;
// получаем адрес LongPoll сервера

// параметры для отправки в ВК
    $parameters = [
        'access_token' => $access_token,
        'need_pts' => '0',
        'lp_version' => '2',
        'v' => '5.69'
    ];

// передаем запрос в ВК
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.getLongPollServer');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// сохраняем ответ
    $response = curl_exec($ch);
// закрываем соединение
    curl_close($ch);

// декодируем json ответ
    $response = json_decode($response);

//записываем полученные данные
    global $key, $server, $ts;
    $key = $response->response->key;
    $server = $response->response->server;
    $ts = $response->response->ts;
};

function sendMessage($which, $forward)
{
    global $access_token;
    $parameters = [
        'access_token' => $access_token,
        'v' => '5.56',
        'chat_id' => 'айди',
        'message' => $which,
        'forward_messages' => $forward
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

}

;

function getWeather()
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://api.openweathermap.org/data/2.5/weather?id=498817&appid=3c6464a2f6bcbeecf2f55441edb741ce');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// сохраняем ответ
    $weather = curl_exec($ch);
// закрываем соединение
    curl_close($ch);
// декодируем json
    $weather = json_decode($weather);

    $answer = 'Текущая температура ' . ($weather->main->temp - 273.15) . '˚C давление ' . $weather->main->pressure . ' гПА влажность ' . $weather->main->humidity . '%';
    return $answer;
}

;

getLongPollData();

while (true) {
//производим подключение к longpoll серверу

// параметры для отправки в ВК
    $parameters = [
        'act' => 'a_check',
        'key' => $key,
        'ts' => $ts,
        'wait' => '25',
        'mode' => '2',
        'version' => '2'
    ];

// передаем запрос в ВК
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://' . $server);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// сохраняем ответ
    $longpoll = curl_exec($ch);
// закрываем соединение
    curl_close($ch);
// декодируем json
    $longpoll = json_decode($longpoll);
    $ts = $longpoll->ts;


    if (array_key_exists('Failed', (array)$longpoll)) {
        getLongPollData();
    };

    //ищем новые сообщения

    foreach ($longpoll->updates as $event) {
        if ($event[0] == 4 && $event[3] == 2000000003) { //ID беседы!!
            echo $event[5] . PHP_EOL;
            echo 'from' . $event[6]->from . PHP_EOL;
            echo $event[1] . PHP_EOL;//id msg
            if (stristr(mb_strtolower($event[5], 'UTF-8'), "погода")) {
                $readyMessage = getWeather();
                sendMessage($readyMessage, $event[1]);
            }
        }
    }
}


?>
