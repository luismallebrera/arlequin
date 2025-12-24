<?php
/**
 * Plugin Name: WooCommerce Invoice PDF
 * Plugin URI: https://github.com/luismallebrera/arlequin
 * Description: Genera facturas en PDF con todos los metadatos del pedido y las imágenes de los productos
 * Version: 1.0.0
 * Author: Luis Mallebrera
 * Author URI: https://github.com/luismallebrera
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wc-invoice-pdf
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WC_INVOICE_PDF_VERSION', '1.0.0');
define('WC_INVOICE_PDF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_INVOICE_PDF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_INVOICE_PDF_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class WC_Invoice_PDF {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Check if WooCommerce is active
        add_action('plugins_loaded', array($this, 'check_dependencies'));
        
        // Initialize plugin
        add_action('init', array($this, 'init'));
        
        // Admin hooks
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        
        // Add download invoice button to order actions
        add_filter('woocommerce_admin_order_actions', array($this, 'add_invoice_action'), 10, 2);
        add_action('admin_init', array($this, 'handle_invoice_download'));
        
        // Add download invoice to order actions dropdown
        add_filter('woocommerce_order_actions', array($this, 'add_invoice_to_order_actions'));
        add_action('woocommerce_order_action_download_invoice_pdf', array($this, 'process_invoice_download_action'));
        
        // Add invoice button to My Account orders
        add_filter('woocommerce_my_account_my_orders_actions', array($this, 'add_my_account_invoice_action'), 10, 2);
        add_action('template_redirect', array($this, 'handle_frontend_invoice_download'));
        
        // Email attachment
        add_filter('woocommerce_email_attachments', array($this, 'attach_invoice_to_email'), 10, 3);
    }
    
    /**
     * Check if WooCommerce is active
     */
    public function check_dependencies() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            deactivate_plugins(WC_INVOICE_PDF_PLUGIN_BASENAME);
        }
    }
    
    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="error">
            <p><?php esc_html_e('WooCommerce Invoice PDF requiere que WooCommerce esté instalado y activado.', 'wc-invoice-pdf'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('wc-invoice-pdf', false, dirname(WC_INVOICE_PDF_PLUGIN_BASENAME) . '/languages');
        
        // Include required files
        require_once WC_INVOICE_PDF_PLUGIN_DIR . 'includes/class-pdf-generator.php';
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_scripts($hook) {
        if ('post.php' === $hook || 'edit.php' === $hook) {
            wp_enqueue_style(
                'wc-invoice-pdf-admin',
                WC_INVOICE_PDF_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                WC_INVOICE_PDF_VERSION
            );
        }
    }
    
    /**
     * Add invoice action to order actions
     */
    public function add_invoice_action($actions, $order) {
        $actions['download_invoice'] = array(
            'url'    => wp_nonce_url(
                admin_url('admin.php?action=download_invoice&order_id=' . $order->get_id()),
                'download_invoice_' . $order->get_id()
            ),
            'name'   => __('Descargar factura', 'wc-invoice-pdf'),
            'action' => 'download_invoice',
        );
        
        return $actions;
    }
    
    /**
     * Handle invoice download from admin
     */
    public function handle_invoice_download() {
        if (!isset($_GET['action']) || 'download_invoice' !== $_GET['action']) {
            return;
        }
        
        if (!isset($_GET['order_id'])) {
            return;
        }
        
        $order_id = absint($_GET['order_id']);
        
        // Verify nonce
        if (!wp_verify_nonce($_GET['_wpnonce'], 'download_invoice_' . $order_id)) {
            wp_die(__('Enlace de seguridad no válido', 'wc-invoice-pdf'));
        }
        
        // Check permissions
        if (!current_user_can('edit_shop_orders')) {
            wp_die(__('No tienes permisos para realizar esta acción', 'wc-invoice-pdf'));
        }
        
        $this->generate_and_download_invoice($order_id);
    }
    
    /**
     * Add invoice action to My Account orders
     */
    public function add_my_account_invoice_action($actions, $order) {
        $actions['download_invoice'] = array(
            'url'  => wp_nonce_url(
                add_query_arg(array(
                    'download_invoice' => 'true',
                    'order_id' => $order->get_id()
                ), wc_get_endpoint_url('orders', $order->get_id(), wc_get_page_permalink('myaccount'))),
                'download_invoice_' . $order->get_id()
            ),
            'name' => __('Descargar factura', 'wc-invoice-pdf'),
        );
        
        return $actions;
    }
    
    /**
     * Handle invoice download from frontend
     */
    public function handle_frontend_invoice_download() {
        if (!isset($_GET['download_invoice']) || 'true' !== $_GET['download_invoice']) {
            return;
        }
        
        if (!isset($_GET['order_id'])) {
            return;
        }
        
        $order_id = absint($_GET['order_id']);
        
        // Verify nonce
        if (!wp_verify_nonce($_GET['_wpnonce'], 'download_invoice_' . $order_id)) {
            wp_die(__('Enlace de seguridad no válido', 'wc-invoice-pdf'));
        }
        
        // Get order
        $order = wc_get_order($order_id);
        
        if (!$order) {
            wp_die(__('Pedido no encontrado', 'wc-invoice-pdf'));
        }
        
        // Check if user owns the order
        if (get_current_user_id() !== $order->get_customer_id()) {
            wp_die(__('No tienes permisos para ver esta factura', 'wc-invoice-pdf'));
        }
        
        $this->generate_and_download_invoice($order_id);
    }
    
    /**
     * Add invoice download to order actions dropdown
     */
    public function add_invoice_to_order_actions($actions) {
        $actions['download_invoice_pdf'] = __('Descargar factura PDF', 'wc-invoice-pdf');
        return $actions;
    }
    
    /**
     * Process invoice download from order actions dropdown
     */
    public function process_invoice_download_action($order) {
        $order_id = $order->get_id();
        $this->generate_and_download_invoice($order_id);
    }
    
    /**
     * Generate and download invoice
     */
    private function generate_and_download_invoice($order_id) {
        $generator = new WC_Invoice_PDF_Generator();
        $generator->generate($order_id);
    }
    
    /**
     * Attach invoice to completed order email
     */
    public function attach_invoice_to_email($attachments, $status, $order) {
        // Only for completed orders
        if (!is_a($order, 'WC_Order') || !isset($status) || 'customer_completed_order' !== $status) {
            return $attachments;
        }
        
        // Generate invoice
        $generator = new WC_Invoice_PDF_Generator();
        $file_path = $generator->generate($order->get_id(), true);
        
        if ($file_path && file_exists($file_path)) {
            $attachments[] = $file_path;
        }
        
        return $attachments;
    }
}

// Initialize plugin
function wc_invoice_pdf() {
    return WC_Invoice_PDF::get_instance();
}

// Start the plugin
wc_invoice_pdf();
