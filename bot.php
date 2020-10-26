<?php

require __DIR__ . '/vendor/autoload.php';

use Philip\Philip;
use Philip\IRC\Response;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$config = [
    'server' => 'irc.chat.twitch.tv',
    'port' => 6697,
    'ssl' => true,
    'username' => $_ENV['USERNAME'],
    'realname' => $_ENV['USERNAME'],
    'nick' => $_ENV['USERNAME'],
    'connection_password' => $_ENV['PASSWORD'],
    'channels' => explode(',', $_ENV['CHANNEL']),
    'unflood' => 500,
    'admins' => explode(',', $_ENV['ADMINS']),
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

$bot->onChannel('/^.*$/', function ($event) use (&$db) {
    $request = $event->getRequest();
    $user = $request->getSendingUser();

    $stmt = $db->prepare('INSERT OR IGNORE INTO onchat (date, username) VALUES (date(), :username)');
    $stmt->execute([':username' => $user]);
    if ($stmt->rowCount() != 0) {
        $stmt = $db->prepare('SELECT 1 FROM streamers WHERE username = :username');
        $stmt->execute([':username' => $user]);
        if ($stmt->fetch(PDO::FETCH_ASSOC) !== false) {
            $event->addResponse(Response::msg(
                $request->getSource(),
                "!sh-so ${user}",
            ));
        }
    }
});

$bot->run();
