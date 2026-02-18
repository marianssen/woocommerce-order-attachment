<?php
/**
 * Plugin Name: WooCommerce Order Attachment
 * Description: Attach a file to WooCommerce completed order emails via a file upload field on the order edit screen.
 * Version: 1.1.0
 * Requires at least: 6.0
 * Requires PHP: 8.2
 * Author: Your Name
 * Author URI: https://marianrehak.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woocommerce-order-attachment
 * Domain Path: /languages
 * WC requires at least: 10.0.0
 * WC tested up to: 10.3.6
 * Requires Plugins: woocommerce
 */

if (!defined('ABSPATH')) {
    exit;
}


add_action('plugins_loaded', 'woa_init');
function woa_init()
{
    // Woocommerce dependecy check
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'woa_woocommerce_missing_notice');
        return;
    }

    // Declare HPOS compatibility.
    add_action('before_woocommerce_init', 'woa_declare_hpos_compatibility');

    // Add meta box to order edit screen.
    add_action('add_meta_boxes', 'woa_add_meta_box');

    // Save meta box data — covers both classic CPT and HPOS screens.
    add_action('woocommerce_process_shop_order_meta', 'woa_save_attachment_meta');
    // Save meta box data — covers both classic CPT and HPOS screens.
    add_action('woocommerce_process_shop_order_meta', 'woa_save_attachment_meta');
    add_action('woocommerce_update_order', 'woa_save_attachment_meta');

    // Enqueue admin scripts.
    add_action('admin_enqueue_scripts', 'woa_admin_scripts');

    // Attach file to completed order email.
    add_filter('woocommerce_email_attachments', 'woa_attach_to_completed_email', 10, 3);
}

/**
 * Admin notice if WooCommerce is not active.
 */
function woa_woocommerce_missing_notice()
{
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            esc_html_e(
                'WooCommerce Order Attachment requires WooCommerce to be installed and active.',
                'woocommerce-order-attachment'
            );
            ?>
        </p>
    </div>
    <?php
}

/**
 * Declare compatibility with WooCommerce HPOS.
 */
function woa_declare_hpos_compatibility()
{
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            __FILE__,
            true
        );
    }
}

/**
 * Register the meta box on the order edit screen.
 * Works for both classic shop_order CPT and the HPOS woocommerce_page_wc-orders screen.
 */
function woa_add_meta_box()
{
    $screens = array('shop_order', 'woocommerce_page_wc-orders');

    foreach ($screens as $screen) {
        add_meta_box(
            'woa_attachment',
            esc_html__('Order Attachment', 'woocommerce-order-attachment'),
            'woa_meta_box_html',
            $screen,
            'side',
            'default'
        );
    }
}

/**
 * Render the meta box HTML.
 *
 * @param WP_Post|WC_Order $post_or_order Post object (classic) or order object (HPOS).
 */
function woa_meta_box_html($post_or_order)
{
    $order = woa_get_order($post_or_order);
    if (!$order) {
        return;
    }

    $attachment_id = absint($order->get_meta('_woa_attachment_id'));
    $file_name = '';

    if ($attachment_id) {
        $file_name = basename(get_attached_file($attachment_id));
    }

    wp_nonce_field('woa_save_attachment', 'woa_nonce');
    ?>
    <p>
        <label for="woa_attachment_id">
            <p>
                <strong><?php esc_html_e('Attach a file to the completed order email.', 'woocommerce-order-attachment'); ?></strong><br />
                <?php esc_html_e('Allowed file types: pdf, doc, docx, xls, xlsx, jpg, png, zip.', 'woocommerce-order-attachment'); ?>
            </p>

            <input type="hidden" id="woa_attachment_id" name="woa_attachment_id"
                value="<?php echo esc_attr($attachment_id); ?>" />

            <p>
                <button type="button" class="button woa-upload-btn">
                    <?php esc_html_e('Upload / Select File', 'woocommerce-order-attachment'); ?>
                </button>
                <?php if ($attachment_id): ?>
                    <button type="button" class="button woa-remove-btn">
                        <?php esc_html_e('Remove', 'woocommerce-order-attachment'); ?>
                    </button>
                <?php endif; ?>
            </p>

            <?php if ($file_name): ?>
                <p class="woa-file-name">
                    <?php
                    echo esc_html(
                        sprintf(
                            /* translators: %s: file name */
                            __('Current file: %s', 'woocommerce-order-attachment'),
                            $file_name
                        )
                    );
                    ?>
                </p>
            <?php endif; ?>

            <?php
}

/**
 * Enqueue scripts and styles.
 *
 * @param string $hook Current admin page hook.
 */
function woa_admin_scripts($hook)
{
    // Only enqueue on order edit screens.
    $screen = get_current_screen();
    if (!$screen) {
        return;
    }

    $is_order_screen = in_array($screen->id, array('shop_order', 'woocommerce_page_wc-orders'), true);

    // Also check for 'shop_order' post type for classic editor if screen id different (generic edit.php)
    if ('post' === $screen->base && 'shop_order' === $screen->post_type) {
        $is_order_screen = true;
    }

    if (!$is_order_screen) {
        return;
    }

    wp_enqueue_media();

    wp_enqueue_script(
        'woa-admin-js',
        plugin_dir_url(__FILE__) . 'assets/js/admin.js',
        array('jquery'),
        '1.0.1',
        true
    );

    wp_localize_script('woa-admin-js', 'woa_params', array(
        'i18n' => array(
            'select_attachment' => __('Select Attachment', 'woocommerce-order-attachment'),
            'use_this_file' => __('Use this file', 'woocommerce-order-attachment'),
            'current_file' => __('Current file:', 'woocommerce-order-attachment'),
            'remove' => __('Remove', 'woocommerce-order-attachment'),
            'file_too_large' => __('File is too large. Maximum size is 2MB.', 'woocommerce-order-attachment'),
        ),
        'allowed_mimes' => woa_get_allowed_mime_types(),
    ));
}

/**
 * Get allowed MIME types for attachments.
 *
 * @return array
 */
function woa_get_allowed_mime_types()
{
    $mimes = array(
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/zip' => 'zip',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    );

    /**
     * Filter allowed MIME types for WooCommerce Order Attachment.
     *
     * @param array $mimes Allowed MIME types.
     */
    return apply_filters('woa_allowed_file_types', $mimes);
}

/**
 * Save the attachment meta when the order is saved.
 * Hooked to both classic and HPOS save actions.
 *
 * @param int $order_id Order ID.
 */
function woa_save_attachment_meta($order_id)
{
    // Verify nonce.
    if (
        !isset($_POST['woa_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['woa_nonce'])), 'woa_save_attachment')
    ) {
        return;
    }

    // Verify capability.
    if (!current_user_can('edit_shop_order', $order_id) && !current_user_can('edit_order', $order_id)) {
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    if (isset($_POST['woa_attachment_id'])) {
        $attachment_id = absint($_POST['woa_attachment_id']);

        if ($attachment_id) {
            // verify attachment
            if (get_post_type($attachment_id) !== 'attachment') {
                return;
            }

            // verify file size
            $file_path = get_attached_file($attachment_id);
            if (!$file_path || !file_exists($file_path) || filesize($file_path) > 2097152) {
                return;
            }

            // verify MIME type
            $file_mime = get_post_mime_type($attachment_id);
            $allowed_mimes = woa_get_allowed_mime_types();

            if (!array_key_exists($file_mime, $allowed_mimes)) {
                return;
            }

            $order->update_meta_data('_woa_attachment_id', $attachment_id);
        } else {
            $order->delete_meta_data('_woa_attachment_id');
        }
    } else {
        $order->delete_meta_data('_woa_attachment_id');
    }

    $order->save_meta_data();
}

/**
 * Attach the file to the WooCommerce completed order email.
 *
 * @param array    $attachments Current attachments.
 * @param string   $email_id    Email ID.
 * @param WC_Order $order       Order object.
 * @return array
 */
function woa_attach_to_completed_email($attachments, $email_id, $order)
{
    if ('customer_completed_order' !== $email_id || !is_a($order, 'WC_Order')) {
        return $attachments;
    }

    $attachment_id = absint($order->get_meta('_woa_attachment_id'));
    if (!$attachment_id) {
        return $attachments;
    }

    $file_path = get_attached_file($attachment_id);
    if ($file_path && file_exists($file_path)) {
        $attachments[] = $file_path;
    }

    return $attachments;
}

/**
 * Helper: resolve a WC_Order from either a WP_Post or WC_Order object.
 * Handles both classic CPT and HPOS contexts.
 *
 * @param WP_Post|WC_Order $post_or_order
 * @return WC_Order|false
 */
function woa_get_order($post_or_order)
{
    if (is_a($post_or_order, 'WC_Order')) {
        return $post_or_order;
    }

    if (is_a($post_or_order, 'WP_Post')) {
        return wc_get_order($post_or_order->ID);
    }

    return false;
}
