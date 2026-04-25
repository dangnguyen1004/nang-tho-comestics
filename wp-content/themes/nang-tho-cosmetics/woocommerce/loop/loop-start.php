<?php
/**
 * Product loop start
 *
 * @package Nang_Tho_Cosmetics
 * @version 3.3.0
 */

defined('ABSPATH') || exit;

$columns = wc_get_loop_prop('columns', wc_get_default_products_per_row());
?>
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-6">