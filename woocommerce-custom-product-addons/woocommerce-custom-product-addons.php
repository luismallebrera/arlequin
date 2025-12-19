<?php
/**
 * Plugin Name: WooCommerce Custom Product Add-ons
 * Plugin URI: https://github.com/luismallebrera/arlequin
 * Description: Adds custom quantity-based add-on fields to WooCommerce product pages with dynamic price calculation for bracelets, labels, and reminders. Per-product control.
 * Version: 1.1.0
 * Author: Luis Mallebrera
 * Author URI: https://github.com/luismallebrera
 * Text Domain: wc-custom-addons
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'WC_CUSTOM_ADDONS_VERSION', '1.1.0' );
define( 'WC_CUSTOM_ADDONS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WC_CUSTOM_ADDONS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Plugin Class
 */
class WC_Custom_Product_Addons {
    
    /**
     * Add-on prices
     */
    private static $addon_prices = array(
        'pulseras'     => 0.30,
        'etiquetas'    => 0.50,
        'recordatorio' => 1.30,
    );
    
    /**
     * Constructor
     */
    public function __construct() {
        // Check if WooCommerce is active
        add_action( 'plugins_loaded', array( $this, 'init' ) );
        
        // Load text domain
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Check if WooCommerce is active
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
            return;
        }
        
        // Admin: Add product settings
        add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_product_settings' ) );
        add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_settings' ) );
        
        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        
        // Display custom fields on product page
        add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'display_custom_fields' ) );
        
        // Save custom field data to cart
        add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 2 );
        
        // Display custom data in cart
        add_filter( 'woocommerce_get_item_data', array( $this, 'get_item_data' ), 10, 2 );
        
        // Add custom data to cart item price
        add_action( 'woocommerce_before_calculate_totals', array( $this, 'calculate_totals' ) );
        
        // Save custom data to order
        add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_order_item_meta' ), 10, 4 );
    }
    
    /**
     * Load text domain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'wc-custom-addons', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }
    
    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e( 'WooCommerce Custom Product Add-ons requires WooCommerce to be installed and activated.', 'wc-custom-addons' ); ?></p>
        </div>
        <?php
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        if ( is_product() ) {
            // Enqueue CSS
            wp_enqueue_style(
                'wc-custom-addons-styles',
                WC_CUSTOM_ADDONS_PLUGIN_URL . 'assets/css/custom-addons.css',
                array(),
                WC_CUSTOM_ADDONS_VERSION
            );
            
            // Enqueue JavaScript
            wp_enqueue_script(
                'wc-custom-addons-script',
                WC_CUSTOM_ADDONS_PLUGIN_URL . 'assets/js/custom-addons.js',
                array( 'jquery' ),
                WC_CUSTOM_ADDONS_VERSION,
                true
            );
            
            // Pass data to JavaScript
            wp_localize_script(
                'wc-custom-addons-script',
                'wcCustomAddons',
                array(
                    'prices' => self::$addon_prices,
                    'currency_symbol' => get_woocommerce_currency_symbol(),
                    'currency_position' => get_option( 'woocommerce_currency_pos' ),
                    'price_decimals' => wc_get_price_decimals(),
                    'price_decimal_separator' => wc_get_price_decimal_separator(),
                    'price_thousand_separator' => wc_get_price_thousand_separator(),
                )
            );
        }
    }
    
    /**
     * Add product settings in admin
     */
    public function add_product_settings() {
        global $post;
        
        echo '<div class="options_group">';
        
        woocommerce_wp_checkbox( array(
            'id'            => '_enable_custom_addons',
            'label'         => __( 'Enable Custom Add-ons', 'wc-custom-addons' ),
            'description'   => __( 'Enable custom add-ons (Pulseras, Etiquetas, Recordatorio) for this product', 'wc-custom-addons' ),
            'desc_tip'      => true,
        ) );
        
        echo '</div>';
    }
    
    /**
     * Save product settings
     */
    public function save_product_settings( $post_id ) {
        $enable_addons = isset( $_POST['_enable_custom_addons'] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_enable_custom_addons', $enable_addons );
    }
    
    /**
     * Display custom fields on product page
     */
    public function display_custom_fields() {
        global $product;
        
        // Only show on simple and variable products
        if ( ! $product || ( ! $product->is_type( 'simple' ) && ! $product->is_type( 'variable' ) ) ) {
            return;
        }
        
        // Check if add-ons are enabled for this product
        $enable_addons = get_post_meta( $product->get_id(), '_enable_custom_addons', true );
        if ( $enable_addons !== 'yes' ) {
            return;
        }
        
        // Add nonce field for security
        wp_nonce_field( 'wc_custom_addons_nonce_action', 'wc_custom_addons_nonce' );
        
        ?>
        <div class="wc-custom-addons-wrapper">
            <h3 class="wc-custom-addons-title"><?php esc_html_e( 'Personaliza tu pedido', 'wc-custom-addons' ); ?></h3>
            
            <div class="wc-custom-addon-field">
                <label for="addon_pulseras">
                    <?php esc_html_e( 'AÑADIR PULSERAS', 'wc-custom-addons' ); ?>
                    <span class="wc-addon-price">(<?php echo wc_price( self::$addon_prices['pulseras'] ); ?> <?php esc_html_e( 'por unidad', 'wc-custom-addons' ); ?>)</span>
                </label>
                <input type="number" 
                       id="addon_pulseras" 
                       name="addon_pulseras" 
                       class="wc-custom-addon-qty" 
                       data-price="<?php echo esc_attr( self::$addon_prices['pulseras'] ); ?>"
                       min="0" 
                       max="100" 
                       step="1" 
                       value="0" />
            </div>
            
            <div class="wc-custom-addon-field">
                <label for="addon_etiquetas">
                    <?php esc_html_e( 'AÑADIR ETIQUETAS', 'wc-custom-addons' ); ?>
                    <span class="wc-addon-price">(<?php echo wc_price( self::$addon_prices['etiquetas'] ); ?> <?php esc_html_e( 'por unidad', 'wc-custom-addons' ); ?>)</span>
                </label>
                <input type="number" 
                       id="addon_etiquetas" 
                       name="addon_etiquetas" 
                       class="wc-custom-addon-qty" 
                       data-price="<?php echo esc_attr( self::$addon_prices['etiquetas'] ); ?>"
                       min="0" 
                       max="100" 
                       step="1" 
                       value="0" />
            </div>
            
            <div class="wc-custom-addon-field">
                <label for="addon_recordatorio">
                    <?php esc_html_e( 'AÑADIR RECORDATORIO', 'wc-custom-addons' ); ?>
                    <span class="wc-addon-price">(<?php echo wc_price( self::$addon_prices['recordatorio'] ); ?> <?php esc_html_e( 'por unidad', 'wc-custom-addons' ); ?>)</span>
                </label>
                <input type="number" 
                       id="addon_recordatorio" 
                       name="addon_recordatorio" 
                       class="wc-custom-addon-qty" 
                       data-price="<?php echo esc_attr( self::$addon_prices['recordatorio'] ); ?>"
                       min="0" 
                       max="100" 
                       step="1" 
                       value="0" />
            </div>
            
            <div class="wc-custom-addons-total">
                <strong><?php esc_html_e( 'Coste adicional:', 'wc-custom-addons' ); ?></strong>
                <span class="wc-addons-total-price"><?php echo wc_price( 0 ); ?></span>
            </div>
        </div>
        <?php
    }
    
    /**
     * Add custom data to cart item
     */
    public function add_cart_item_data( $cart_item_data, $product_id ) {
        // Verify nonce
        if ( ! isset( $_POST['wc_custom_addons_nonce'] ) || 
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wc_custom_addons_nonce'] ) ), 'wc_custom_addons_nonce_action' ) ) {
            return $cart_item_data;
        }
        
        $addons = array();
        
        // Process pulseras
        if ( isset( $_POST['addon_pulseras'] ) ) {
            $qty = absint( $_POST['addon_pulseras'] );
            if ( $qty > 0 ) {
                $addons['pulseras'] = array(
                    'label' => __( 'Pulseras', 'wc-custom-addons' ),
                    'quantity' => $qty,
                    'price' => self::$addon_prices['pulseras'],
                );
            }
        }
        
        // Process etiquetas
        if ( isset( $_POST['addon_etiquetas'] ) ) {
            $qty = absint( $_POST['addon_etiquetas'] );
            if ( $qty > 0 ) {
                $addons['etiquetas'] = array(
                    'label' => __( 'Etiquetas', 'wc-custom-addons' ),
                    'quantity' => $qty,
                    'price' => self::$addon_prices['etiquetas'],
                );
            }
        }
        
        // Process recordatorio
        if ( isset( $_POST['addon_recordatorio'] ) ) {
            $qty = absint( $_POST['addon_recordatorio'] );
            if ( $qty > 0 ) {
                $addons['recordatorio'] = array(
                    'label' => __( 'Recordatorio', 'wc-custom-addons' ),
                    'quantity' => $qty,
                    'price' => self::$addon_prices['recordatorio'],
                );
            }
        }
        
        // Add to cart item data if we have any addons
        if ( ! empty( $addons ) ) {
            $cart_item_data['wc_custom_addons'] = $addons;
            
            // Make cart item unique
            $cart_item_data['unique_key'] = md5( microtime() . wp_rand() );
        }
        
        return $cart_item_data;
    }
    
    /**
     * Display custom data in cart
     */
    public function get_item_data( $item_data, $cart_item ) {
        if ( isset( $cart_item['wc_custom_addons'] ) && ! empty( $cart_item['wc_custom_addons'] ) ) {
            foreach ( $cart_item['wc_custom_addons'] as $addon_key => $addon ) {
                $total = $addon['quantity'] * $addon['price'];
                $item_data[] = array(
                    'key'   => $addon['label'],
                    'value' => sprintf(
                        '%d × %s = %s',
                        $addon['quantity'],
                        wc_price( $addon['price'] ),
                        wc_price( $total )
                    ),
                );
            }
        }
        
        return $item_data;
    }
    
    /**
     * Calculate cart totals with custom addon prices
     */
    public function calculate_totals( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }
        
        // Loop through cart items
        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            if ( isset( $cart_item['wc_custom_addons'] ) && ! empty( $cart_item['wc_custom_addons'] ) ) {
                // Calculate total addon price
                $addon_price = 0;
                foreach ( $cart_item['wc_custom_addons'] as $addon ) {
                    $addon_price += $addon['quantity'] * $addon['price'];
                }
                
                // Get current product price
                $current_price = floatval( $cart_item['data']->get_price() );
                
                // Set new price
                $cart_item['data']->set_price( $current_price + $addon_price );
            }
        }
    }
    
    /**
     * Add custom data to order meta
     */
    public function add_order_item_meta( $item, $cart_item_key, $values, $order ) {
        if ( isset( $values['wc_custom_addons'] ) && ! empty( $values['wc_custom_addons'] ) ) {
            foreach ( $values['wc_custom_addons'] as $addon_key => $addon ) {
                $total = $addon['quantity'] * $addon['price'];
                $item->add_meta_data(
                    $addon['label'],
                    sprintf(
                        '%d × %s = %s',
                        $addon['quantity'],
                        wc_price( $addon['price'] ),
                        wc_price( $total )
                    ),
                    true
                );
            }
        }
    }
    
    /**
     * Get addon prices (for external access)
     */
    public static function get_addon_prices() {
        return self::$addon_prices;
    }
}

/**
 * Initialize the plugin
 */
function wc_custom_product_addons_init() {
    new WC_Custom_Product_Addons();
}
add_action( 'plugins_loaded', 'wc_custom_product_addons_init', 0 );
