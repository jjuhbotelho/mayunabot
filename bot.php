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

$db = new PDO('sqlite:db.sqlite3');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec(file_get_contents('schema.sql'));

$bot->onChannel('/^!so-add (.*)$/', function ($event) use (&$bot, &$db) {
    $request = $event->getRequest();
    $user = $request->getSendingUser();
    if ($bot->isAdmin($user)) {
        $matches = $event->getMatches();
        $streamer = ltrim($matches[0], '@');

        $stmt = $db->prepare('INSERT OR IGNORE INTO streamers (username) VALUES (:username)');
        $stmt->execute([':username' => $streamer]);

        $event->addResponse(Response::msg(
            $request->getSource(),
            "${user} ${streamer} adicionado",
        ));
    } else {
        $event->addResponse(Response::msg(
            $request->getSource(),
            "${user} Ihhhh... Vai rolar não, otário!",
        ));
    }
});

$bot->onChannel('/^!so-remove (.*)$/', function ($event) use (&$bot, &$db) {
    $request = $event->getRequest();
    $user = $request->getSendingUser();
    if ($bot->isAdmin($user)) {
        $matches = $event->getMatches();
        $streamer = ltrim($matches[0], '@');

        $stmt = $db->prepare('DELETE FROM streamers WHERE username = :username');
        $stmt->execute([':username' => $streamer]);

        $event->addResponse(Response::msg(
            $request->getSource(),
            "${user} ${streamer} removido",
        ));
    } else {
        $event->addResponse(Response::msg(
            $request->getSource(),
            "${user} Ihhhh... Vai rolar não, otário!",
        ));
    }
});

$bot->onChannel('/^!echo (.*)$/', function ($event) {
    $matches = $event->getMatches();
    $event->addResponse(Response::msg($event->getRequest()->getSource(), trim($matches[0])));
});

$bot->run();
