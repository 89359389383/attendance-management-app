<?php

if (!function_exists('sortLink')) {
    function sortLink($label, $field, $currentSort, $currentDirection, $tab)
    {
        $direction = ($currentSort === $field && $currentDirection === 'asc') ? 'desc' : 'asc';
        $arrow = $currentSort === $field ? ($currentDirection === 'asc' ? '▲' : '▼') : '';
        $params = http_build_query(['sort' => $field, 'direction' => $direction, 'tab' => $tab]);
        return "<a href=\"?" . $params . "\">{$label} {$arrow}</a>";
    }
}
