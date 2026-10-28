<?php
/**
 * Plugin Name: News PhotoCard Pro
 * Plugin URI: https://nexolbd.com/
 * Description: Create branded 1080×1080 news cards with twelve customizable designs, a live design preview, Elementor and shortcode buttons. Developed by Nexol BD.
 * Version: 3.11.3
 * Author: Nexol BD
 * Author URI: https://nexolbd.com/
 * Update URI: https://github.com/programmerashraful/news-photocard-pro
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: news-photocard-pro
 * Requires at least: 5.8
 * Tested up to: 7.1
 * Requires PHP: 7.4
 * Tags: news, photo card, elementor, social media, image generator, design, template builder, content branding
 */

/**
 * Nexol BD publisher tools.
 * Support: info@nexolbd.com | +8801737963893 | https://nexolbd.com/
 * See NOTICE.txt for the original project's attribution.
 */

if (!defined('ABSPATH')) exit;
define('UDNPC_VERSION', '3.11.3');
define('UDNPC_PLUGIN_FILE', __FILE__);
require_once plugin_dir_path(__FILE__) . 'includes/class-photocard-license.php';
new UDNPC_License();
require_once plugin_dir_path(__FILE__) . 'includes/class-github-updater.php';
new UDNPC_GitHub_Updater();
require_once plugin_dir_path(__FILE__) . 'includes/class-photocard-core.php';
add_action('elementor/widgets/register', function($widgets_manager){
    require_once plugin_dir_path(__FILE__) . 'includes/class-elementor-widget.php';
    $widgets_manager->register(new UDNPC_Elementor_Widget());
});
