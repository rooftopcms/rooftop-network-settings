<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://rooftopcms.com
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Rooftop Network Settings
 * Plugin URI:        https://github.com/rooftopcms/rooftop-network-settings
 * Description:       rooftop-network-settings adds a GTM form to the network admin panel and inserts it into site admin pages.
 * Version:           1.2.1
 * Author:            RooftopCMS
 * Author URI:        https://rooftopcms.com
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       rooftop-network-settings
 * Domain Path:       /languages
 */

add_filter( 'network_admin_menu', 'rooftop_add_settings_page', 1);

function rooftop_add_settings_page() {
    add_menu_page( "Rooftop Setup", "Rooftop Setup", "manage_sites", "rooftop-settings", "rooftop_add_gtm_page" );
    add_submenu_page( "rooftop-settings", "GTM", "GTM", "manage_sites", "rooftop-settings-gtm", "rooftop_gtm_callback" );

    global $submenu;
    if ( isset( $submenu[ "rooftop-settings" ][0][0] ) ) {
        $_submenu = $submenu;
        unset($_submenu["rooftop-settings"][0]);
        $submenu = $_submenu;
    }

}

function rooftop_gtm_callback() {

    $network_id = get_current_site()->id;

    if($_POST && array_key_exists('method', $_POST)) {
        $method = strtoupper($_POST['method']);
    }elseif($_POST && array_key_exists('id', $_POST)){
        $method = 'PATCH';
    }else {
        $method = $_SERVER['REQUEST_METHOD'];
    }

    if( "GET" == $method ) {
        render_gtm_settings( );
    }else {
        if(!isset($_POST['gtm-field-token']) || !wp_verify_nonce($_POST['gtm-field-token'], 'rooftop-network-settings-add-key')) {
            print '<div class="wrap"><div class="errors"><p>Form token not verified</p></div></div>';
            exit;
        }

        $gtm_key = $_POST['gtm_key'];

        if( update_network_option( $network_id, "gtm-tag", $gtm_key ) ) {
            render_gtm_settings( );
        }else {
            render_gtm_settings( );
        }
    }
}

function render_gtm_settings() {
    $network_id = get_current_site()->id;

    $gtm_key = get_network_option( $network_id, "gtm-tag", "" );
    require_once plugin_dir_path( __FILE__ ) . 'partials/settings.php';
}

function render_gtm_in_admin() {
    $site = get_current_site();
    $network = get_network_by_path( $site->domain, "/" );
    $tag = get_network_option( $network->id, "gtm-tag" );

    if( $tag && strlen( $tag ) ) {
        $script = <<<EOL
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','${tag}');
EOL;

        wp_enqueue_script( 'rooftop-settings-gtm-script', plugin_dir_url( __FILE__ )."plugin.js", array(), "1.0" );
        wp_add_inline_script( 'rooftop-settings-gtm-script', $script );
    }
}
add_action( 'admin_enqueue_scripts', 'render_gtm_in_admin', 11 );


function render_rt_attributes_in_admin() {
    $user = wp_get_current_user();

    $id = $user->ID;
    $email = $user->user_email;

    $script = <<<EOL
    dataLayer = [{
        'user-id': '${id}',
        'user-email': '${email}'
    }];
EOL;

    wp_enqueue_script( 'rooftop-settings-gtm-script', plugin_dir_url( __FILE__ )."plugin.js", array(), "1.0" );
    wp_add_inline_script( 'rooftop-settings-gtm-script', $script );
}
add_action( 'admin_enqueue_scripts', 'render_rt_attributes_in_admin', 10 );


function render_maintenance_notice_in_dr_mode() {
    if( "dr" === @$_ENV['WP_ENV'] ) {
        $output = <<<EOL
<div class="notice notice-warning" style="padding: 10px 5px">
    <p>We're doing some maintenance on Rooftop.</p>
    <p>Don't worry, your site is still available and everything will be back to normal in a few minutes.</p>
</div>
EOL;
    }else {
        $output = "";
    }

    echo $output;
}
add_action( 'login_message', 'render_maintenance_notice_in_dr_mode' );
?>
