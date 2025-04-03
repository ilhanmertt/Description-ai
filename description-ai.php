<?php
/**
 * Plugin Name: Description AI
 * Plugin URI: https://seomew.com.tr/description-ai
 * Description: Generate product, category, and brand descriptions for WooCommerce using AI powered by Groq.
 * Version: 1.0.0
 * Author: Mert Ilhan
 * Author URI: https://seomew.com.tr
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: description-ai
 */

require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handler.php';

// Add "Settings" link to plugin actions list
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'description_ai_add_settings_link');
function description_ai_add_settings_link($links)
{
    $settings_link = '<a href="admin.php?page=ai-description-generator">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
