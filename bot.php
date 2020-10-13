<?php

require __DIR__ . '/vendor/autoload.php';

use Philip\Philip;
use Philip\IRC\Response;

$config = [
    'server' => 'irc.chat.twitch.tv',
    'port' => 6697,
    'ssl' => true,
    'username' => getenv('USERNAME'),
    'realname' => getenv('USERNAME'),
    'nick' => getenv('USERNAME'),
    'connection_password' => getenv('PASSWORD'),
    'channels' => explode(',', getenv('CHANNEL')),
    'unflood' => 500,
    'admins' => explode(',', getenv('ADMINS')),
    'debug' => true,
    'log' => '/dev/stdout',
];

$bot = new Philip($config);

$bot->onChannel('/^!echo (.*)$/', function ($event) {
    $matches = $event->getMatches();
    $event->addResponse(Response::msg($event->getRequest()->getSource(), trim($matches[0])));
});

$bot->run();
