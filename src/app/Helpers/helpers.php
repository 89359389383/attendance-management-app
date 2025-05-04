<?php

if (!function_exists('sortLink')) {
    function sortLink($label, $field, $currentSort, $currentDirection, $tab = null)
    {
        $direction = ($currentSort === $field && $currentDirection === 'asc') ? 'desc' : 'asc';
        $arrow = $currentSort === $field ? ($currentDirection === 'asc' ? '▲' : '▼') : '';

        // 既存クエリ文字列の引き継ぎ
        $params = request()->query(); // 現在のクエリパラメータをすべて取得
        $params['sort'] = $field;
        $params['direction'] = $direction;

        if ($tab !== null) {
            $params['tab'] = $tab;
        }

        return '<a href="?' . http_build_query($params) . " \">{$label} {$arrow}</a>";
    }
}
