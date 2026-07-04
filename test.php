<?php
require_once("../../../wp-load.php");

echo "<h1>بررسی همه منابع داده</h1>";

// ===== ۱. داده‌های پنل جدید =====
$new_teams = get_option('livepoint_teams', array());
echo "<h2>پنل جدید (livepoint_teams): " . count($new_teams) . " تیم</h2>";
echo "<pre>";
print_r($new_teams);
echo "</pre>";

// ===== ۲. داده‌های ACF (اگر فعال باشد) =====
if (function_exists('have_rows')) {
    $acf_teams = array();
    while (have_rows('teams', 'option')) : the_row();
        $acf_teams[] = array(
            'name' => get_sub_field('team_name'),
            'alive' => get_sub_field('alive'),
            'active' => get_sub_field('active'),
        );
    endwhile;
    echo "<h2>داده‌های ACF: " . count($acf_teams) . " تیم</h2>";
    echo "<pre>";
    print_r($acf_teams);
    echo "</pre>";
} else {
    echo "<h2>ACF فعال نیست</h2>";
}

// ===== ۳. داده‌های پست‌های تیم (اگر نوع پست team وجود داشته باشد) =====
$post_teams = get_posts(array(
    'post_type' => 'team',
    'posts_per_page' => -1,
    'post_status' => 'publish'
));
echo "<h2>پست‌های تیم (post_type=team): " . count($post_teams) . " تیم</h2>";
echo "<pre>";
foreach ($post_teams as $p) {
    echo $p->post_title . " (ID: " . $p->ID . ")\n";
}
echo "</pre>";