<?php
/**
 * PHP バージョン互換シム。
 *
 * LISH_MIN_PHP を引き上げたら、不要になったシムをここから削除する（MAJOR リリース時）。
 * 現在の最低要件: PHP 8.0
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

// PHP 8.0 未満で動かす想定は無いため、現時点でシムは不要。
// 将来 PHP の新関数を使いたくなった場合、ここにポリフィルを置く。
//
// 例:
// if (!function_exists('array_find')) {   // PHP 8.4
//     function array_find(array $array, callable $callback) { ... }
// }
