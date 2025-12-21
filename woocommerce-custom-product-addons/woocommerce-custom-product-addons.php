<?php
/**
 * Plugin Name: WooCommerce Custom Product Add-ons
 * Plugin URI: https://github.com/luismallebrera/arlequin
 * Description: Adds custom quantity-based add-on fields and custom text fields to WooCommerce product pages with per-product control. Includes event fields for baptisms, communions, and more.
 * Version: 1.3.1
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
define( 'WC_CUSTOM_ADDONS_VERSION', '1.3.1' );
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
        'pulseras'     => 2.50,
        'etiquetas'    => 0.50,
        'recordatorio' => 1.75,
    );

    /**
     * Custom text fields configuration
     */
    private static $custom_text_fields = array(
        'nombre_persona'        => 'NOMBRE (NIÑA/NIÑO/BEBÉ/NOVIOS/PROFE)',
        'inicial'               => 'INICIAL',
        'fecha_evento'          => 'FECHA (COMUNIÓN/BAUTIZO/BODA/EVENTO)',
        'hora_evento'           => 'HORA DEL EVENTO',
        'localidad'             => 'LOCALIDAD',
        'iglesia'               => 'IGLESIA',
        'hora_misa'             => 'HORA MISA',
        'restaurante'           => 'RESTAURANTE/LUGAR DE CELEBRACIÓN',
        'ano_curso'             => 'AÑO CURSO',
        'observaciones'         => 'OBSERVACIONES',
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

        echo '<div class="options_group custom-fields-container">';

        woocommerce_wp_checkbox( array(
            'id'            => '_enable_custom_addons',
            'label'         => __( 'Enable Custom Add-ons', 'wc-custom-addons' ),
            'description'   => __( 'Enable custom add-ons (Pulseras, Etiquetas, Recordatorio) for this product', 'wc-custom-addons' ),
            'desc_tip'      => true,
        ) );

        echo '</div>';

        // Custom text fields section
        echo '<div class="options_group custom-fields-inner-container">';
        echo '<h4 class="options_title" style="padding: 10px 12px; margin: 0; border-bottom: 1px solid #eee;">' . __( 'Custom Text Fields', 'wc-custom-addons' ) . '</h4>';

        foreach ( self::$custom_text_fields as $field_key => $field_label ) {
            woocommerce_wp_checkbox( array(
                'id'            => '_enable_text_field_' . $field_key,
                'label'         => $field_label,
                'description'   => sprintf( __( 'Enable "%s" text field for this product', 'wc-custom-addons' ), $field_label ),
                'desc_tip'      => true,
            ) );
        }

        echo '</div>';
    }

    /**
     * Save product settings
     */
    public function save_product_settings( $post_id ) {
        // Check user capabilities
        if ( ! current_user_can( 'edit_product', $post_id ) ) {
            return;
        }

        // Check nonce (WooCommerce handles this in woocommerce_process_product_meta hook)
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Sanitize and save the checkbox value for quantity add-ons
        $enable_addons = isset( $_POST['_enable_custom_addons'] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_enable_custom_addons', sanitize_text_field( $enable_addons ) );

        // Save custom text field settings
        foreach ( self::$custom_text_fields as $field_key => $field_label ) {
            $field_enabled = isset( $_POST['_enable_text_field_' . $field_key] ) ? 'yes' : 'no';
            update_post_meta( $post_id, '_enable_text_field_' . $field_key, sanitize_text_field( $field_enabled ) );
        }
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

        // Check if any text fields are enabled
        $enabled_text_fields = array();
        foreach ( self::$custom_text_fields as $field_key => $field_label ) {
            if ( get_post_meta( $product->get_id(), '_enable_text_field_' . $field_key, true ) === 'yes' ) {
                $enabled_text_fields[ $field_key ] = $field_label;
            }
        }

        // If neither add-ons nor text fields are enabled, don't display anything
        if ( $enable_addons !== 'yes' && empty( $enabled_text_fields ) ) {
            return;
        }

        // Add nonce field for security
        wp_nonce_field( 'wc_custom_addons_nonce_action', 'wc_custom_addons_nonce' );

        ?>
        <div class="wc-custom-addons-wrapper">

            <?php if ( ! empty( $enabled_text_fields ) ) : ?>
            <div class="wc-custom-text-fields">
                <h3 class="wc-custom-addons-title"><?php esc_html_e( 'Información adicional', 'wc-custom-addons' ); ?></h3>

                <?php foreach ( $enabled_text_fields as $field_key => $field_label ) :
                    // Observaciones is not required, all others are
                    $is_required = ( $field_key !== 'observaciones' );
                ?>
                <div class="wc-custom-text-field">
                    <label for="text_field_<?php echo esc_attr( $field_key ); ?>">
                        <?php echo esc_html( $field_label ); ?>
                        <?php if ( $is_required ) : ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <input type="text"
                           id="text_field_<?php echo esc_attr( $field_key ); ?>"
                           name="text_field_<?php echo esc_attr( $field_key ); ?>"
                           class="wc-custom-text-input"
                           value=""
                           placeholder="<?php echo esc_attr( $field_label ); ?>"
                           <?php echo $is_required ? 'required' : ''; ?> />
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ( $enable_addons === 'yes' ) : ?>
            <h3 class="wc-custom-addons-title"><?php esc_html_e( 'Personaliza tu pedido', 'wc-custom-addons' ); ?></h3>
            <p class="wc-custom-addons-text"><?php esc_html_e( 'Los productos añadidos se realizarán con el mismo diseño seleccionado en este pack, sin necesidad de volver a elegirlo.', 'wc-custom-addons' ); ?></p>

            <div class="wc-custom-addon-field">
                <label for="addon_pulseras">
                    <?php esc_html_e( 'AÑADIR PULSERAS', 'wc-custom-addons' ); ?>
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
            <?php endif; ?>
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

        // Validate required text fields
        $enabled_required_fields = array();
        foreach ( self::$custom_text_fields as $field_key => $field_label ) {
            // Check if field is enabled for this product
            if ( get_post_meta( $product_id, '_enable_text_field_' . $field_key, true ) === 'yes' ) {
                // Observaciones is not required, all others are
                if ( $field_key !== 'observaciones' ) {
                    $enabled_required_fields[ $field_key ] = $field_label;
                }
            }
        }

        // Check if required fields are filled
        $missing_fields = array();
        foreach ( $enabled_required_fields as $field_key => $field_label ) {
            if ( ! isset( $_POST['text_field_' . $field_key] ) ||
                 empty( trim( sanitize_text_field( wp_unslash( $_POST['text_field_' . $field_key] ) ) ) ) ) {
                $missing_fields[] = $field_label;
            }
        }

        // If there are missing required fields, throw an error
        if ( ! empty( $missing_fields ) ) {
            $error_message = __( 'Por favor, complete los siguientes campos obligatorios: ', 'wc-custom-addons' ) . implode( ', ', $missing_fields );
            wc_add_notice( $error_message, 'error' );
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

        // Process custom text fields
        $text_fields = array();
        foreach ( self::$custom_text_fields as $field_key => $field_label ) {
            if ( isset( $_POST['text_field_' . $field_key] ) ) {
                $field_value = sanitize_text_field( wp_unslash( $_POST['text_field_' . $field_key] ) );
                if ( ! empty( $field_value ) ) {
                    $text_fields[ $field_key ] = array(
                        'label' => $field_label,
                        'value' => $field_value,
                    );
                }
            }
        }

        // Add text fields to cart item data if we have any
        if ( ! empty( $text_fields ) ) {
            $cart_item_data['wc_custom_text_fields'] = $text_fields;

            // Make cart item unique if not already done
            if ( ! isset( $cart_item_data['unique_key'] ) ) {
                $cart_item_data['unique_key'] = md5( microtime() . wp_rand() );
            }
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

        // Display custom text fields
        if ( isset( $cart_item['wc_custom_text_fields'] ) && ! empty( $cart_item['wc_custom_text_fields'] ) ) {
            foreach ( $cart_item['wc_custom_text_fields'] as $field_key => $field_data ) {
                $item_data[] = array(
                    'key'   => $field_data['label'],
                    'value' => $field_data['value'],
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

        // Add custom text fields to order
        if ( isset( $values['wc_custom_text_fields'] ) && ! empty( $values['wc_custom_text_fields'] ) ) {
            foreach ( $values['wc_custom_text_fields'] as $field_key => $field_data ) {
                $item->add_meta_data(
                    $field_data['label'],
                    $field_data['value'],
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
