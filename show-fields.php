<?php
require_once("../../../wp-load.php");

if ( ! function_exists('get_field') ) {
    wp_die('ACF فعال نیست');
}

echo '<h1>مقادیر فیلدهای صفحه live-point</h1>';
echo '<pre>';
print_r(get_fields('option'));
echo '</pre>';
?>