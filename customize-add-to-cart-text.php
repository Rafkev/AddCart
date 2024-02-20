<?php
/**
 * Plugin Name: Customize Add to Cart Text
 * Description: Allows users to customize the "Add to Cart" text in WooCommerce.
 * Version: 1.2
 * Author: RK Oluwasanmi
 * License: GPL2+
 */

// Add styles and scripts
add_action('admin_enqueue_scripts', 'add_to_cart_text_enqueue_scripts');

function add_to_cart_text_enqueue_scripts($hook) {
    if ('settings_page_add_to_cart_text_settings' !== $hook) {
        return;
    }
    wp_enqueue_style('add-to-cart-text-admin-css', plugins_url('css/admin-style.css', __FILE__));
    wp_enqueue_script('add-to-cart-text-admin-js', plugins_url('js/admin-script.js', __FILE__), array('jquery'), '1.0', true);
}

// Add a settings page to WordPress admin menu
add_action('admin_menu', 'add_to_cart_text_settings_menu');

function add_to_cart_text_settings_menu() {
    add_options_page('Add to Cart Text Settings', 'Add to Cart Text', 'manage_options', 'add_to_cart_text_settings', 'add_to_cart_text_settings_page');
}

// Render settings page
function add_to_cart_text_settings_page() {
    ?>
    <div class="wrap">
        <h2>Add to Cart Text Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('add_to_cart_text_settings_group');
            do_settings_sections('add_to_cart_text_settings_group');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Global Add to Cart Text:</th>
                    <td><input type="text" name="global_add_to_cart_text" value="<?php echo esc_attr(get_option('global_add_to_cart_text')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register and initialize settings
add_action('admin_init', 'add_to_cart_text_settings');

function add_to_cart_text_settings() {
    register_setting('add_to_cart_text_settings_group', 'global_add_to_cart_text');
}

// Change "Add to Cart" button text globally
add_filter('woocommerce_product_single_add_to_cart_text', 'custom_add_to_cart_text');
add_filter('woocommerce_product_add_to_cart_text', 'custom_add_to_cart_text');

function custom_add_to_cart_text($text) {
    $new_text = get_option('global_add_to_cart_text');
    return !empty($new_text) ? $new_text : $text;
}

// Add meta box to individual product edit page
add_action('add_meta_boxes', 'add_to_cart_text_meta_box');

function add_to_cart_text_meta_box() {
    add_meta_box('add-to-cart-text-meta-box', 'Custom Add to Cart Text', 'add_to_cart_text_meta_box_callback', 'product', 'side', 'default');
}

// Render meta box
function add_to_cart_text_meta_box_callback($post) {
    $custom_add_to_cart_text = get_post_meta($post->ID, '_custom_add_to_cart_text', true);
    ?>
    <label for="custom_add_to_cart_text">Custom Add to Cart Text:</label>
    <input type="text" id="custom_add_to_cart_text" name="custom_add_to_cart_text" value="<?php echo esc_attr($custom_add_to_cart_text); ?>" style="width: 100%;" />
    <?php
    wp_nonce_field('add_to_cart_text_save_meta_box_data', 'add_to_cart_text_meta_box_nonce');
}

// Save custom add to cart text for individual products
add_action('save_post', 'save_add_to_cart_text_meta_box_data');

function save_add_to_cart_text_meta_box_data($post_id) {
    if (!isset($_POST['add_to_cart_text_meta_box_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['add_to_cart_text_meta_box_nonce'], 'add_to_cart_text_save_meta_box_data')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (isset($_POST['custom_add_to_cart_text'])) {
        update_post_meta($post_id, '_custom_add_to_cart_text', sanitize_text_field($_POST['custom_add_to_cart_text']));
    }
}

// Filter individual product add to cart text
add_filter('woocommerce_product_single_add_to_cart_text', 'filter_individual_add_to_cart_text', 10, 2);
add_filter('woocommerce_product_add_to_cart_text', 'filter_individual_add_to_cart_text', 10, 2);

function filter_individual_add_to_cart_text($text, $product) {
    $custom_add_to_cart_text = get_post_meta($product->get_id(), '_custom_add_to_cart_text', true);
    return !empty($custom_add_to_cart_text) ? $custom_add_to_cart_text : $text;
}

// Support for multiple languages
add_filter('gettext', 'translate_custom_add_to_cart_text', 20, 3);

function translate_custom_add_to_cart_text($translated_text, $text, $domain) {
    if ($text === 'Add to Cart') {
        $translated_text = get_option('global_add_to_cart_text');
    }
    return $translated_text;
}
