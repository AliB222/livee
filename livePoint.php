<?php

/**
 * Plugin Name: LivePoint
 * Plugin URI: 
 * Description: افزونه نمایش Live Point
 * Version: 2.1
 * Author: Reza.Esmaillo
 * Author URI: 
 **/

/**
 * Add sub options page with a custom post id
 */

function my_acf_save_post($post_id) {
    // بررسی کنید که آیا در حال ذخیره صفحه تنظیمات هستید
    if ($post_id !== 'options') {
        return;
    }

    // دریافت اطلاعات از API
    $response = wp_remote_get('https://itsalib2.ir/wp-content/plugins/livePoint/api.php');

    if (is_wp_error($response)) {
        return; // خطا در درخواست API
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!isset($data['teams']) || !is_array($data['teams'])) {
        return; // بررسی صحت داده‌ها
    }

    $total_alive = 0;
    $total_team = 0;

    foreach ($data['teams'] as $team) {
        if (isset($team['alive']) && $team['alive'] > 0) {
            $total_alive += $team['alive'];
            $total_team++;
        }
    }

    // به‌روز رسانی فیلدهای ACF
    // اگر فیلدها وجود ندارند، به‌روز رسانی آن‌ها با مقادیر جدید
    update_field('total-team', $total_team, 'options');
    update_field('total-alive', $total_alive, 'options');
}
add_action('acf/save_post', 'my_acf_save_post', 20);




if( function_exists('acf_add_options_page') ) {
	acf_add_options_sub_page(array(
		'page_title' 	=> 'CSV Sync',
		'menu_title' 	=> 'CSV Sync',
		'parent_slug' 	=> 'users.php',
		'post_id'       => 'aa_ucs',
		'autoload'      => false,
	));
}

/**
 * Registers a metabox with ACF for a particular screen. You may need to find the screen ID yourself.
 */
function aa_ucs_register_acf_metabox() {
	// Verify the screen ID
	if ( !acf_is_screen( 'users_page_acf-options-csv-sync' ) ) return;
	
	// Add meta box
	add_meta_box( 'meta-box-id', __( 'My Meta Box', 'textdomain' ), 'aa_ucs_display_acf_metabox', 'acf_options_page', 'normal' );
}
add_action( 'acf/input/admin_head', 'aa_ucs_register_acf_metabox', 10 );

/**
 * Display custom metabox on an ACF options page
 */
function aa_ucs_display_acf_metabox() {
	$text = get_option( 'example-text' );
	?>
	<input type="text" name="exampletext" placeholder="Enter some example text to be saved" value="<?php echo esc_attr($text); ?>">
	<?php
}

/**
 * Submit metabox form, save the results
 * 
 * @param $post_id
 */
function aa_ucs_save_acf_metabox_fields( $post_id ) {
	if ( $post_id != 'aa_ucs' ) return;
	
	$text = isset($_POST['exampletext']) ? stripslashes($_POST['exampletext']) : false;
	
	update_option( 'example-text', $text );
}
add_action( 'acf/save_post', 'aa_ucs_save_acf_metabox_fields', 20 );

if ( file_exists( plugin_dir_path( __FILE__ ) . 'livepoint-panel.php' ) ) {
    include_once plugin_dir_path( __FILE__ ) . 'livepoint-panel.php';
}


if ( file_exists( plugin_dir_path( __FILE__ ) . 'lp-save-api.php' ) ) {
    include_once plugin_dir_path( __FILE__ ) . 'lp-save-api.php';
}