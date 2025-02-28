<?php

/*
 * Copyright 2018-2025 Solovev Sergei
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

if (!defined('EGP')) {
    exit(header('Refresh: 0; URL=http://' . $_SERVER['HTTP_HOST'] . '/404'));
}

if (isset($url['active'])) {
    $sql->query('SELECT `active` FROM `privileges` WHERE `server`="' . $id . '" LIMIT 1');
    if ($sql->num()) {
        $privilege = $sql->get();

        if (!$privilege['active']) {
            $sql->query('SELECT `id` FROM `privileges_list` WHERE `server`="' . $id . '" LIMIT 1');
            if (!$sql->num()) {
                sys::outjs(['e' => 'Необходимо настроить привилегии']);
            }

            $sql->query('UPDATE `privileges` set `active`="1" WHERE `server`="' . $id . '" LIMIT 1');
        } else {
            $sql->query('UPDATE `privileges` set `active`="0" WHERE `server`="' . $id . '" LIMIT 1');
        }

        sys::outjs(['s' => 'ok']);
    }

    sys::outjs(['e' => 'Необходимо настроить привилегии']);
}

if (isset($url['delete'])) {
    $sql->query('DELETE FROM `privileges_list` WHERE `id`="' . sys::int($url['delete']) . '" AND `server`="' . $id . '" LIMIT 1');

    $sql->query('SELECT `id` FROM `privileges_list` WHERE `server`="' . $id . '" LIMIT 1');
    if (!$sql->num()) {
        $sql->query('UPDATE `privileges` set `active`="0" WHERE `server`="' . $id . '" LIMIT 1');
    }

    sys::out();
}

if ($go) {
    $sql->query('SELECT `id` FROM `privileges_list` WHERE `server`="' . $id . '" LIMIT 10');
    if ($sql->num() > 9) {
        sys::outjs(['e' => 'Нельзя добавить больше 10-и привилегий'], $name_mcache);
    }

    $aData = [];

    $aData['name'] = isset($_POST['name']) ? trim($_POST['name']) : sys::outjs(['e' => 'Необходимо заполнить все поля'], $name_mcache);
    $aData['flags'] = isset($_POST['flags']) ? trim($_POST['flags']) : sys::outjs(['e' => 'Необходимо заполнить все поля'], $name_mcache);
    $aData['time'] = $_POST['time'] ?? sys::outjs(['e' => 'Необходимо заполнить все поля'], $name_mcache);
    $aData['price'] = $_POST['price'] ?? sys::outjs(['e' => 'Необходимо заполнить все поля'], $name_mcache);

    if (sys::strlen($aData['name']) < 3 || sys::strlen($aData['name']) > 30) {
        sys::outjs(['e' => 'Длина названия должна быть от 3-х до 30-и символов'], $name_mcache);
    }

    if (sys::valid($aData['name'], 'other', '/[А-яA-z0-9]+$/u')) {
        sys::outjs(['e' => 'Неверное указано название, доступны латинские, русские буквы и цифры.'], $name_mcache);
    }

    if (sys::valid($aData['flags'], 'other', '/^[a-z]+$/') || (sys::strlen($aData['flags']) < 1 || sys::strlen($aData['flags']) > 22)) {
        sys::outjs(['e' => 'Неверное указаны флаги AmxModX.'], $name_mcache);
    }

    foreach (count_chars($aData['flags'], 1) as $val) {
        if ($val > 1) {
            sys::outjs(['e' => 'Неверное указаны флаги AmxModX, флаг не должен повторяться дважды.'], $name_mcache);
        }
    }

    if ((!is_array($aData['time']) || !is_array($aData['price'])) || (count($aData['time']) < 1 || count($aData['time']) > 5) || (count($aData['time']) != count($aData['price']))) {
        sys::outjs(['e' => 'Неверное переданы данные.'], $name_mcache);
    }

    $keys = [];
    $data = [];

    foreach ($aData['time'] as $key => $val) {
        $val = intval($val);

        if ($val > 1000) {
            $val = 1000;
        }

        if (in_array($val, $keys)) {
            continue;
        }

        $aData['price'][$key] = intval($aData['price'][$key]);

        if ($aData['price'][$key] < 1) {
            continue;
        }

        $data[$val] = $aData['price'][$key];
        $keys[] = $val;
    }

    if (!count($data)) {
        sys::outjs(['e' => 'Неверное переданы данные.'], $name_mcache);
    }

    $sql->query('SELECT `id` FROM `privileges` WHERE `server`="' . $id . '" LIMIT 1');
    if (!$sql->num()) {
        $sql->query('INSERT INTO `privileges` set `server`="' . $id . '", `active`="0"');
    }

    $sql->query('INSERT INTO `privileges_list` set `server`="' . $id . '", `name`="' . $aData['name'] . '", `flags`="' . $aData['flags'] . '", `data`="' . sys::b64js($data) . '"');

    sys::outjs(['s' => 'ok'], $name_mcache);
}

$html->nav('Управление администраторами', $cfg['http'] . 'servers/id/' . $id . '/section/settings/subsection/admins');
$html->nav('Настройка платных привилегий');

$sql->query('SELECT `id`, `name`, `flags`, `data` FROM `privileges_list` WHERE `server`="' . $id . '" ORDER BY `id` ASC LIMIT 10');
while ($privilege = $sql->get()) {
    $data = sys::b64djs($privilege['data']);

    $time = '';

    if (isset($data[0])) {
        $time = 'Навсегда / ' . $data[0] . ' ' . $cfg['currency'] . '; ';

        unset($data[0]);
    }

    foreach ($data as $days => $price) {
        $time .= $days . ' ' . sys::day($time) . ' / ' . $price . ' ' . $cfg['currency'] . '; ';
    }

    $html->get('list', 'sections/servers/' . $server['game'] . '/settings/privileges');

    $html->set('id', $privilege['id']);
    $html->set('name', $privilege['name']);
    $html->set('flags', $privilege['flags']);
    $html->set('time', $time);

    $html->pack('list');
}

$html->get('privileges', 'sections/servers/' . $server['game'] . '/settings');

$html->set('id', $id);
$html->set('list', $html->arr['list']);

$html->pack('main');
