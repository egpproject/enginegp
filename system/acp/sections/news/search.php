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

$text = isset($_POST['text']) ? trim($_POST['text']) : '';

$mkey = md5($text . $id);

$cache = $mcache->get($mkey);

$nmch = null;

if (is_array($cache)) {
    if ($go) {
        sys::outjs($cache, $nmch);
    }

    sys::outjs($cache);
}

if (!isset($text[2])) {
    if ($go) {
        sys::outjs(['e' => 'Для выполнения поиска, необходимо больше данных'], $nmch);
    }

    sys::outjs(['e' => '']);
}

if ($text[0] == 'i' and $text[1] == 'd') {
    $sql->query('SELECT `id`, `name`, `tags`, `views`, `date` FROM `news` WHERE `id`="' . sys::int($text) . '" LIMIT 1');
} else {
    $like = '`id` LIKE FROM_BASE64(\'' . base64_encode('%' . str_replace('_', '\_', $text) . '%') . '\') OR'
        . '`name` LIKE FROM_BASE64(\'' . base64_encode('%' . str_replace('_', '\_', $text) . '%') . '\') OR'
        . '`text` LIKE FROM_BASE64(\'' . base64_encode('%' . str_replace('_', '\_', $text) . '%') . '\') OR'
        . '`tags` LIKE FROM_BASE64(\'' . base64_encode('%' . str_replace('_', '\_', $text) . '%') . '\') OR'
        . '`full_text` LIKE FROM_BASE64(\'' . base64_encode('%' . str_replace('_', '\_', $text) . '%') . '\')';

    $sql->query('SELECT `id`, `name`, `tags`, `views`, `date` FROM `news` WHERE ' . $like . ' ORDER BY `id` ASC LIMIT 20');
}

if (!$sql->num()) {
    if ($go) {
        sys::outjs(['e' => 'По вашему запросу ничего не найдено'], $nmch);
    }

    sys::outjs(['e' => 'По вашему запросу ничего не найдено']);
}

$list = '';

while ($news = $sql->get()) {
    $list .= '<tr>';
    $list .= '<td>' . $news['id'] . '</td>';
    $list .= '<td>' . $news['name'] . '</td>';
    $list .= '<td>' . $news['tags'] . '</td>';
    $list .= '<td class="text-center">' . $news['views'] . '</td>';
    $list .= '<td class="text-center">' . date('d.m.Y - H:i:s', $news['date']) . '</td>';
    $list .= '<td class="text-center"><a href="#" onclick="return news_delete(\'' . $news['id'] . '\')" class="text-red">Удалить</a></td>';
    $list .= '</tr>';
}

$mcache->set($mkey, ['s' => $list], false, 15);

sys::outjs(['s' => $list]);
