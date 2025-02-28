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

// Установка
if ($go) {
    if (!$aWebVHTtype && !in_array($server['tarif'], $aWebVHT)) {
        sys::outjs(['e' => 'Недоступно для данного тарифа.'], $nmch);
    }

    if ($aWebVHTtype && in_array($server['tarif'], $aWebVHT)) {
        sys::outjs(['e' => 'Недоступно для данного тарифа.'], $nmch);
    }

    $aData = [];

    $aData['subdomain'] = isset($_POST['subdomain']) ? strtolower($_POST['subdomain']) : sys::outjs(['e' => 'Необходимо указать адрес.'], $nmch);
    $aData['domain'] = isset($_POST['domain']) ? strtolower($_POST['domain']) : sys::outjs(['e' => 'Необходимо выбрать домен.'], $nmch);
    $aData['passwd'] = $_POST['passwd'] ?? sys::passwd($aWebParam[$url['subsection']]['passwd']);

    $aData['type'] = $url['subsection'];

    if (!$aWeb[$server['game']][$aData['type']]) {
        sys::outjs(['e' => 'Дополнительная услуга недоступна для установки.'], $nmch);
    }

    // Проверка на наличие уже установленной выбранной услуги
    switch ($aWebInstall[$server['game']][$aData['type']]) {
        case 'server':
            $sql->query('SELECT `id` FROM `web` WHERE `type`="' . $aData['type'] . '" AND `server`="' . $id . '" LIMIT 1');
            break;

        case 'user':
            $sql->query('SELECT `id` FROM `web` WHERE `type`="' . $aData['type'] . '" AND `user`="' . $server['user'] . '" LIMIT 1');
            break;

        case 'unit':
            $sql->query('SELECT `id` FROM `web` WHERE `type`="' . $aData['type'] . '" AND `user`="' . $server['user'] . '" AND `unit`="' . $server['unit'] . '" LIMIT 1');
            break;
    }

    if ($sql->num()) {
        sys::outjs(['i' => 'Дополнительная услуга уже установлена.'], $nmch);
    }

    // Проверка на наличие уже установленной подобной услуги
    switch ($aWebInstall[$server['game']][$aData['type']]) {
        case 'server':
            foreach ($aWebOne[$server['game']][$aData['type']] as $type) {
                $sql->query('SELECT `id` FROM `web` WHERE `type`="' . $type . '" AND `server`="' . $id . '" LIMIT 1');
                if ($sql->num()) {
                    sys::outjs(['i' => 'Подобная услуга уже установлена.', 'type' => $type], $nmch);
                }
            }
            break;

        case 'user':
            foreach ($aWebOne[$server['game']][$aData['type']] as $type) {
                $sql->query('SELECT `id` FROM `web` WHERE `type`="' . $type . '" AND `user`="' . $server['user'] . '" LIMIT 1');
                if ($sql->num()) {
                    sys::outjs(['i' => 'Подобная услуга уже установлена.', 'type' => $type], $nmch);
                }
            }
            break;

        case 'unit':
            foreach ($aWebOne[$server['game']][$aData['type']] as $type) {
                $sql->query('SELECT `id` FROM `web` WHERE `type`="' . $type . '" AND `unit`="' . $server['unit'] . '" LIMIT 1');
                if ($sql->num()) {
                    sys::outjs(['i' => 'Подобная услуга уже установлена.', 'type' => $type], $nmch);
                }
            }
    }

    // Проверка валидности поддомена
    if (sys::valid($aData['subdomain'], 'other', "/^[a-z0-9]+$/")) {
        sys::outjs(['e' => 'Адрес должен состоять из букв a-z и цифр.'], $nmch);
    }

    // Проверка длины поддомена
    if (!isset($aData['subdomain'][3]) || isset($aData['subdomain'][15])) {
        sys::outjs(['e' => 'Длина адреса не должна превышать 16-и символов и быть не менее 4-х символов.'], $nmch);
    }

    // Проверка наличия основного домена
    if (!in_array($aData['domain'], $aWebUnit['domains'])) {
        sys::outjs(['e' => 'Выбранный домен не найден.'], $nmch);
    }

    // Проверка запрещенного поддомена
    if (in_array($aData['subdomain'], $aWebUnit['subdomains'])) {
        sys::outjs(['e' => 'Нельзя создать данный адрес, придумайте другой.'], $nmch);
    }

    // Проверка поддомена на занятость
    $sql->query('SELECT `id` FROM `web` WHERE `domain`="' . $aData['subdomain'] . '.' . $aData['domain'] . '" LIMIT 1');
    if ($sql->num()) {
        sys::outjs(['e' => 'Данный адрес уже занят.'], $nmch);
    }

    // Если не указан пароль сгенерировать
    if ($aData['passwd'] == '') {
        $aData['passwd'] = sys::passwd($aWebParam[$aData['type']]['passwd']);
    }

    // Проверка длинны пароля
    if (!isset($aData['passwd'][5])) {
        sys::outjs(['e' => 'Необходимо указать пароль длинной не менее 6-и символов.'], $nmch);
    }

    // Проверка валидности пароля
    if (sys::valid($aData['passwd'], 'other', "/^[A-Za-z0-9]{6,16}$/")) {
        sys::outjs(['e' => 'Пароль должен состоять из букв a-z и цифр, не менее 4-х и не более 16-и символов.'], $nmch);
    }

    $sql->query('SELECT `mail` FROM `users` WHERE `id`="' . $server['user'] . '" LIMIT 1');
    if (!$sql->num()) {
        sys::outjs(['e' => 'Необходимо указать пользователя игрового сервера.'], $nmch);
    }

    $u = $sql->get();

    $sql->query('INSERT INTO `web` set `type`="' . $aData['type'] . '", `server`="' . $id . '", `user`="' . $server['user'] . '", `unit`="0", `config`=""');
    $wid = $sql->id();
    $uid = $wid + 10000;

    $login = 'h' . $uid;

    if (in_array($aData['subdomain'], $aWebUnit['isp']['subdomains'])) {
        $sql->query('DELETE FROM `web` WHERE `id`="' . $wid . '" LIMIT 1');

        sys::outjs(['e' => 'Нельзя создать указанный поддомен, придумайте что-то другое.'], $mcache);
    }

    // Создание вирт. хостинга
    $result = json_decode(file_get_contents(sys::updtext(
        $aWebUnit['isp']['account']['create'],
        ['login' => $login,
            'mail' => $u['mail'],
            'passwd' => $aData['passwd'],
            'domain' => $aData['subdomain'] . '.' . $aData['domain']]
    )), true);

    if (!isset($result['result']) || strtolower($result['result']) != 'ok') {
        $sql->query('DELETE FROM `web` WHERE `id`="' . $wid . '" LIMIT 1');

        sys::outjs(['e' => 'Не удалось создать виртуальный хостинг, обратитесь в тех.поддержку.'], $nmch);
    }

    // Обновление данных
    $sql->query('UPDATE `web` set `uid`="' . $uid . '", '
        . '`domain`="' . $aData['subdomain'] . '.' . $aData['domain'] . '", '
        . '`passwd`="' . $aData['passwd'] . '", '
        . '`login`="' . $login . '", `date`="' . $start_point . '" '
        . 'WHERE `id`="' . $wid . '" LIMIT 1');

    sys::outjs(['s' => 'ok'], $nmch);
}

$html->nav('Установка ' . $aWebname[$url['subsection']]);

$domains = '';

// Генерация списка доменов
foreach ($aWebUnit['domains'] as $domain) {
    $domains .= '<option value="' . $domain . '">.' . $domain . '</option>';
}

$html->get('install', 'sections/web/' . $url['subsection'] . '/free');
$html->set('id', $id);
$html->set('domains', $domains);
$html->pack('main');
