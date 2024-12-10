<?php

/*
 * EngineGP   (https://enginegp.ru or https://enginegp.com)
 *
 * @copyright Copyright (c) 2018-present Solovev Sergei <inbox@seansolovev.ru>
 *
 * @link      https://github.com/EngineGPDev/EngineGP for the canonical source repository
 *
 * @license   https://github.com/EngineGPDev/EngineGP/blob/main/LICENSE MIT License
 */

if (!defined('EGP')) {
    exit(header('Refresh: 0; URL=http://' . $_SERVER['HTTP_HOST'] . '/404'));
}

// Закрытие / Удаление вопроса
if (isset($url['action']) and in_array($url['action'], ['close', 'delete'])) {
    include(SEC . 'help/action/' . $url['action'] . '.php');
}

// Массив статусов вопроса
$status = [
    0 => 'Есть ответ',
    1 => 'Ожидается ответ',
    2 => 'Прочитан',
];

if (in_array($user['group'], ['admin', 'support'])) {
    $sql->query('SELECT `id` FROM `help` WHERE `close`="0"');
} else {
    $sql->query('SELECT `id` FROM `help` WHERE `user`="' . $user['id'] . '" AND `close`="0"');
}

$aPage = sys::page($page, $sql->num(), 20);

sys::page_gen($aPage['ceil'], $page, $aPage['page'], 'help/section/open');

if (in_array($user['group'], ['admin', 'support'])) {
    $helps = $sql->query('SELECT `id`, `user`, `type`, `service`, `status`, `date`, `time`, `title` FROM `help` WHERE `close`="0" ORDER BY `id` DESC LIMIT ' . $aPage['num'] . ', 20');
} else {
    $helps = $sql->query('SELECT `id`, `type`, `service`, `status`, `date`, `time`, `title` FROM `help` WHERE `user`="' . $user['id'] . '" AND `close`="0" ORDER BY `id` DESC LIMIT ' . $aPage['num'] . ', 20');
}

// Массив пользователей
$uArr = [];

while ($help = $sql->get($helps)) {
    // Создатель вопроса
    if (in_array($user['group'], ['admin', 'support']) and !isset($uArr[$help['user']])) {
        $sql->query('SELECT `login` FROM `users` WHERE `id`="' . $help['user'] . '" LIMIT 1');

        if (!$sql->num()) {
            $uArr[$help['user']] = 'Пользователь удален';
        } else {
            $us = $sql->get();
            $uArr[$help['user']] = $us['login'];
        }
    }

    // Краткая информация вопроса
    switch ($help['type']) {
        case 'server':
            $sql->query('SELECT `address` FROM `servers` WHERE `id`="' . $help['service'] . '" LIMIT 1');
            if (!$sql->num()) {
                $name = 'Игровой сервер: #' . $help['service'] . ' (не найден)';
            } else {
                $ser = $sql->get();
                $name = 'Игровой сервер: #' . $help['service'] . ' ' . $ser['address'];
            }

            break;

        case 'hosting':
            $name = 'Виртуальных хостинг: #' . $help['service'];

            break;

        default:
            $name = 'Вопрос без определенной услуги';
    }

    if (!empty($help['title'])) {
        $name = $help['title'];
    }

    $html->get('question', 'sections/help/open');

    $html->set('id', $help['id']);

    if (array_key_exists('user', $help)) {
        $html->set('uid', $help['user']);
        $html->set('login', $uArr[$help['user']]);
    }

    $html->set('name', $name);
    $html->set('status', $status[$help['status']]);
    $html->set('date', sys::today($help['date']));
    $html->set('time', sys::today($help['time']));

    $html->pack('question');
}

$html->get('open', 'sections/help');

$html->set('question', $html->arr['question'] ?? '');

$html->set('pages', $html->arr['pages'] ?? '');

$html->pack('main');

if (!in_array($user['group'], ['admin', 'support'])) {
    $html->unitall('user', 'main', 1);
    $html->unitall('support', 'main');
} else {
    $html->unitall('user', 'main');
    $html->unitall('support', 'main', 1);
}

if ($user['group'] == 'admin') {
    $html->unitall('admin', 'main', 1);
} else {
    $html->unitall('admin', 'main');
}
