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

if (!defined('EGP'))
    exit(header('Refresh: 0; URL=http://' . $_SERVER['HTTP_HOST'] . '/404'));

if ($id)
    include(SEC . 'servers/server.php');
else {
    $list = '';

    $servers = $sql->query('SELECT `id`, `unit`, `tarif`, `user`, `address`, `port`, `game`, `slots`, `name`, `overdue` FROM `servers` WHERE `user`!="-1" AND `time`<"' . $start_point . '" AND `overdue`>"' . $start_point . '" ORDER BY `id` ASC');
    while ($server = $sql->get($servers)) {
        $sql->query('SELECT `name` FROM `units` WHERE `id`="' . $server['unit'] . '" LIMIT 1');
        $unit = $sql->get();

        $sql->query('SELECT `name` FROM `tarifs` WHERE `id`="' . $server['tarif'] . '" LIMIT 1');
        $tarif = $sql->get();

        $server_address = $server['address'] . ':' . $server['port'];

        $list .= '<tr>';
        $list .= '<td class="text-center">' . $server['id'] . '</td>';
        $list .= '<td><a href="' . $cfg['http'] . 'acp/servers/id/' . $server['id'] . '">' . $server['name'] . '</a></td>';
        $list .= '<td>#' . $server['unit'] . ' ' . $unit['name'] . '</td>';
        $list .= '<td class="text-center">' . $server['slots'] . ' шт.</td>';
        $list .= '<td class="text-center">' . strtoupper($server['game']) . '</td>';
        $list .= '<td class="text-center"><a href="' . $cfg['http'] . 'servers/id/' . $server['id'] . '" target="_blank">Перейти</a></td>';
        $list .= '</tr>';

        $list .= '<tr>';
        $list .= '<td class="text-center"><a href="' . $cfg['http'] . 'acp/users/id/' . $server['user'] . '">USER_' . $server['user'] . '</a></td>';
        $list .= '<td>' . $server_address . '</td>';
        $list .= '<td>#' . $server['tarif'] . ' ' . $tarif['name'] . '</td>';
        $list .= '<td class="text-center">Просрочен</td>';
        $list .= '<td class="text-center">Удаление через: ' . sys::date('min', $server['overdue'] + $cfg['server_delete'] * 86400) . '</td>';
        $list .= '<td class="text-center"><a href="#" onclick="return servers_delete(\'' . $server['id'] . '\')" class="text-red">Удалить</a></td>';
        $list .= '</tr>';
    }

    $html->get('index', 'sections/servers');
    $html->set('list', $list);
    $html->set('pages', '');
    $html->pack('main');
}
