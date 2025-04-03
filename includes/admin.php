<?php
if (!defined('ABSPATH')) {
    exit;
}

// === 1. Meta box in product edit screen ===
add_action('add_meta_boxes', 'apdg_add_ai_description_box');
function apdg_add_ai_description_box()
{
    global $post;
    if ('product' === get_post_type($post)) {
        add_meta_box(
            'apdg_ai_description',
            '🧠 Generate AI Description',
            'apdg_render_ai_description_box',
            'product',
            'normal',
            'high'
        );
    }
}

function apdg_render_ai_description_box($post)
{
    echo '<button type="button" id="apdg-generate-btn" class="button button-primary">🧠 Write with AI</button>';
    echo '<p style="margin-top:10px;color:#777;">Click to automatically generate and fill the product description.</p>';
}

// === 2. Load admin.js only on relevant edit screens ===
add_action('admin_enqueue_scripts', 'apdg_enqueue_admin_assets');
function apdg_enqueue_admin_assets($hook)
{
    $is_product_edit   = ($hook === 'post.php' && isset($_GET['post']) && get_post_type(intval($_GET['post'])) === 'product');
    $is_category_edit  = ($hook === 'term.php' && isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'product_cat');
    $is_brand_edit     = ($hook === 'term.php' && isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'product_brand');
    $is_settings_page  = ($hook === 'toplevel_page_ai-description-generator'); // 🧠 BURASI ÖNEMLİ

    if (!$is_product_edit && !$is_category_edit && !$is_brand_edit && !$is_settings_page) return;

    wp_enqueue_script(
        'apdg-admin-js',
        plugin_dir_url(__FILE__) . '../assets/js/admin.js',
        array('jquery'),
        '1.0',
        true
    );

    wp_localize_script('apdg-admin-js', 'apdg_ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('apdg_nonce')
    ));
}


// === 3. Admin menu ===
add_action('admin_menu', 'apdg_admin_menu');
function apdg_admin_menu()
{
    add_menu_page(
        'AI Description Generator',
        'AI Description',
        'manage_options',
        'ai-description-generator',
        'apdg_render_settings_page',
        'dashicons-lightbulb',
        56
    );
}

// === 4. Settings page ===
function apdg_render_settings_page()
{
    $saved_key   = get_option('apdg_license_key');
    $saved_lang  = get_option('apdg_language');
    $output_mode = get_option('apdg_output_mode', 'both');
?>
    <div class="wrap">
        <h2>AI Description Generator Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('apdg_settings_group');
            do_settings_sections('apdg_settings_group');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">License Key</th>
                    <td>
                        <input
                            type="text"
                            name="apdg_license_key"
                            value="<?php echo esc_attr($saved_key); ?>"
                            class="regular-text"
                            placeholder="License key is not required for the free version." />
                    </td>

                </tr>
                <tr valign="top">
                    <th scope="row">Language</th>
                    <td>
                        <select name="apdg_language">
                            <option value="Turkish" <?php selected($saved_lang, 'Turkish'); ?>>Turkish</option>
                            <option value="English" <?php selected($saved_lang, 'English'); ?>>English</option>
                            <option value="German" <?php selected($saved_lang, 'German'); ?>>German</option>
                            <option value="French" <?php selected($saved_lang, 'French'); ?>>French</option>
                            <option value="Spanish" <?php selected($saved_lang, 'Spanish'); ?>>Spanish</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Description Type</th>
                    <td>
                        <label><input type="radio" name="apdg_output_mode" value="long" <?php checked($output_mode, 'long'); ?> /> Long Description</label><br>
                        <label><input type="radio" name="apdg_output_mode" value="short" <?php checked($output_mode, 'short'); ?> /> Short Description</label><br>
                        <label><input type="radio" name="apdg_output_mode" value="both" <?php checked($output_mode, 'both'); ?> /> Both</label>
                    </td>
                </tr>
                <hr>
                <h2>🧠 Bulk AI Generation</h2>
                <p>Automatically generate descriptions for all items below:</p>

                <h3>📦 Products</h3>
                <p>
                    <button type="button" class="button button-primary" id="apdg-bulk-product-empty">🧠 Only for empty fields</button>
                    <button type="button" class="button" id="apdg-bulk-product-overwrite">🔁 Regenerate all</button>
                </p>

                <h3>📁 Categories</h3>
                <p>
                    <button type="button" class="button button-primary" id="apdg-bulk-category-empty">🧠 Only for empty fields</button>
                    <button type="button" class="button" id="apdg-bulk-category-overwrite">🔁 Regenerate all</button>
                </p>

                <h3>🏷️ Brands</h3>
                <p>
                    <button type="button" class="button button-primary" id="apdg-bulk-brand-empty">🧠 Only for empty fields</button>
                    <button type="button" class="button" id="apdg-bulk-brand-overwrite">🔁 Regenerate all</button>
                </p>

            </table>
            <?php submit_button('Save Settings'); ?>
        </form>
        
    </div>
<?php
}

// === 5. Register settings ===
add_action('admin_init', 'apdg_register_settings');
function apdg_register_settings()
{
    register_setting('apdg_settings_group', 'apdg_license_key');
    register_setting('apdg_settings_group', 'apdg_language');
    register_setting('apdg_settings_group', 'apdg_output_mode');
}

// === 6. Add category AI description button ===
add_action('product_cat_edit_form_fields', 'apdg_add_category_ai_button', 10, 2);
function apdg_add_category_ai_button($term, $taxonomy)
{
    echo '
    <tr class="form-field">
        <th scope="row" valign="top"><label for="apdg-category-ai-button">AI Description</label></th>
        <td>
            <button type="button" class="button" id="apdg-generate-category-btn" data-term-id="' . esc_attr($term->term_id) . '">🧠 Write with AI</button>
            <p class="description">Click to generate a description for this category.</p>
        </td>
    </tr>
    ';
}

// === 7. Add brand AI description button ===
add_action('product_brand_edit_form_fields', 'apdg_add_brand_ai_button', 10, 2);
function apdg_add_brand_ai_button($term, $taxonomy)
{
    echo '
    <tr class="form-field">
        <th scope="row" valign="top"><label for="apdg-brand-ai-button">AI Description</label></th>
        <td>
            <button type="button" class="button" id="apdg-generate-brand-btn" data-term-id="' . esc_attr($term->term_id) . '">🧠 Write with AI</button>
            <p class="description">Click to generate a description for this brand.</p>
        </td>
    </tr>
    ';
}
