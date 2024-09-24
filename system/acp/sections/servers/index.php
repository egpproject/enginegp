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

if (isset($url['subsection']) and $url['subsection'] == 'search') {
    include(SEC . 'servers/search.php');
}

if ($id) {
    include(SEC . 'servers/server.php');
} else {
    $list = '';

    $status = [
        'working' => '<span class="text-green">Работает</span>',
        'off' => '<span class="text-red">Выключен</span>',
        'start' => 'Запускается',
        'restart' => 'Перезапускается',
        'change' => 'Смена карты',
        'install' => 'Устанавливается',
        'reinstall' => 'Переустанавливается',
        'update' => 'Обновляется',
        'recovery' => 'Восстанавливается',
        'overdue' => 'Просрочен',
        'blocked' => 'Заблокирован',
    ];

    $select = 'WHERE `user`!="-1"';
    $url_search = '';

    if (isset($url['search']) and in_array($url['search'], ['unit', 'tarif'])) {
        $select = 'WHERE `' . $url['search'] . '`="' . sys::int($url[$url['search']]) . '" AND `user`!="-1"';
        $url_search = '/search/' . $url['search'] . '/' . $url['search'] . '/' . $url[$url['search']];
    }

    $sql->query('SELECT `id` FROM `servers` ' . $select);

    $aPage = sys::page($page, $sql->num(), 20);

    sys::page_gen($aPage['ceil'], $page, $aPage['page'], 'acp/servers' . $url_search);

    $servers = $sql->query('SELECT `id`, `unit`, `tarif`, `user`, `address`, `port`, `game`, `status`, `slots`, `name`, `time` FROM `servers` ' . $select . ' ORDER BY `id` ASC LIMIT ' . $aPage['num'] . ', 20');
    while ($server = $sql->get($servers)) {
        $sql->query('SELECT `name` FROM `units` WHERE `id`="' . $server['unit'] . '" LIMIT 1');
        $unit = $sql->get();

        $sql->query('SELECT `name` FROM `tarifs` WHERE `id`="' . $server['tarif'] . '" LIMIT 1');
        $tarif = $sql->get();

        $server_address = $server['address'] . ':' . $server['port'];

        $list .= '<tr>';
        $list .= '<td class="text-center">' . $server['id'] . '</td>';
        $list .= '<td><a href="' . $cfg['http'] . 'acp/servers/id/' . $server['id'] . '">' . $server['name'] . '</a></td>';
        $list .= '<td><a href="' . $cfg['http'] . 'acp/servers/search/unit/unit/' . $server['unit'] . '">#' . $server['unit'] . ' ' . $unit['name'] . '</a></td>';
        $list .= '<td class="text-center">' . $server['slots'] . ' шт.</td>';
        $list .= '<td class="text-center">' . strtoupper($server['game']) . '</td>';
        $list .= '<td class="text-center"><a href="' . $cfg['http'] . 'servers/id/' . $server['id'] . '" target="_blank">Перейти</a></td>';
        $list .= '</tr>';

        $list .= '<tr>';
        $list .= '<td class="text-center"><a href="' . $cfg['http'] . 'acp/users/id/' . $server['user'] . '">USER_' . $server['user'] . '</a></td>';
        $list .= '<td>' . $server_address . '</td>';
        $list .= '<td><a href="' . $cfg['http'] . 'acp/servers/search/tarif/tarif/' . $server['tarif'] . '">#' . $server['tarif'] . ' ' . $tarif['name'] . '</a></td>';
        $list .= '<td class="text-center">' . $status[$server['status']] . '</td>';
        $list .= '<td class="text-center">' . date('d.m.Y - H:i:s', $server['time']) . '</td>';
        $list .= '<td class="text-center"><a href="#" onclick="return servers_delete(\'' . $server['id'] . '\')" class="text-red">Удалить</a></td>';
        $list .= '</tr>';
    }

    $html->get('index', 'sections/servers');

    $html->set('list', $list);

    $html->set('url_search', $url_search);

    $html->set('pages', $html->arr['pages'] ?? '');

    $html->pack('main');
}
