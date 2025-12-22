<?php
/**
 * Plugin Name: WooCommerce Custom Options
 * Plugin URI: https://github.com/luismallebrera/arlequin
 * Description: Personaliza opciones de WooCommerce: productos relacionados solo por categorías, cantidad y columnas configurables.
 * Version: 1.0.0
 * Author: Luis Mallebrera
 * Author URI: https://github.com/luismallebrera
 * Text Domain: wc-options
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

/**
 * Main Plugin Class
 */
class WC_Custom_Options {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
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

        // Add settings to WooCommerce Products tab
        add_filter( 'woocommerce_get_settings_products', array( $this, 'add_related_products_settings' ), 10, 2 );
        
        // Related products filters - applied early with correct hooks
        add_filter( 'woocommerce_product_related_posts_relate_by_category', array( $this, 'related_products_by_category' ), 999 );
        add_filter( 'woocommerce_product_related_posts_relate_by_tag', array( $this, 'related_products_by_tag' ), 999 );
        add_filter( 'woocommerce_output_related_products_args', array( $this, 'customize_related_products_args' ), 999 );
        
        // Add custom RELATED field to products
        add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_related_custom_field' ) );
        add_action( 'woocommerce_process_product_meta', array( $this, 'save_related_custom_field' ) );
    }

    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e( 'WooCommerce Custom Options requiere que WooCommerce esté instalado y activado.', 'wc-options' ); ?></p>
        </div>
        <?php
    }

    /**
     * Related products by category
     */
    public function related_products_by_category( $relate_by_category ) {
        $option = get_option( 'wc_options_related_products_by', 'categories' );
        // Return true if option is 'categories' or 'both'
        return in_array( $option, array( 'categories', 'both' ) );
    }

    /**
     * Related products by tag
     */
    public function related_products_by_tag( $relate_by_tag ) {
        $option = get_option( 'wc_options_related_products_by', 'categories' );
        // Return true if option is 'tags' or 'both'
        return in_array( $option, array( 'tags', 'both' ) );
    }

    /**
     * Add related products settings to WooCommerce Products tab
     */
    public function add_related_products_settings( $settings, $current_section ) {
        // Only add to the default products section
        if ( '' === $current_section ) {
            $related_settings = array(
                array(
                    'title' => __( 'Productos Relacionados', 'wc-options' ),
                    'type'  => 'title',
                    'desc'  => __( 'Personaliza cómo se muestran los productos relacionados en la página del producto', 'wc-options' ),
                    'id'    => 'wc_options_related_products_section',
                ),
                array(
                    'title'   => __( 'Relacionar productos por', 'wc-options' ),
                    'desc'    => __( 'Selecciona cómo quieres que se relacionen los productos', 'wc-options' ),
                    'id'      => 'wc_options_related_products_by',
                    'default' => 'categories',
                    'type'    => 'select',
                    'class'   => 'wc-enhanced-select',
                    'options' => array(
                        'categories' => __( 'Solo categorías', 'wc-options' ),
                        'tags'       => __( 'Solo etiquetas', 'wc-options' ),
                        'both'       => __( 'Categorías y etiquetas', 'wc-options' ),
                    ),
                ),
                array(
                    'title'    => __( 'Número de productos', 'wc-options' ),
                    'desc'     => __( 'Cantidad de productos relacionados a mostrar', 'wc-options' ),
                    'id'       => 'wc_options_related_products_number',
                    'default'  => '4',
                    'type'     => 'number',
                    'css'      => 'width: 70px;',
                    'custom_attributes' => array(
                        'min'  => 1,
                        'max'  => 12,
                        'step' => 1,
                    ),
                ),
                array(
                    'title'    => __( 'Columnas', 'wc-options' ),
                    'desc'     => __( 'Número de columnas para la cuadrícula de productos relacionados', 'wc-options' ),
                    'id'       => 'wc_options_related_products_columns',
                    'default'  => '4',
                    'type'     => 'number',
                    'css'      => 'width: 70px;',
                    'custom_attributes' => array(
                        'min'  => 1,
                        'max'  => 6,
                        'step' => 1,
                    ),
                ),
                array(
                    'type' => 'sectionend',
                    'id'   => 'wc_options_related_products_section',
                ),
            );

            // Add at the end of the settings array
            $settings = array_merge( $settings, $related_settings );
        }

        return $settings;
    }

    /**
     * Customize related products arguments
     */
    public function customize_related_products_args( $args ) {
        $args['posts_per_page'] = absint( get_option( 'wc_options_related_products_number', 4 ) );
        $args['columns'] = absint( get_option( 'wc_options_related_products_columns', 4 ) );
        return $args;
    }

    /**
     * Add custom RELATED field to product
     */
    public function add_related_custom_field() {
        global $post;
        
        echo '<div class="options_group">';
        
        woocommerce_wp_text_input( array(
            'id'          => '_custom_related_field',
            'label'       => __( 'RELATED', 'wc-options' ),
            'placeholder' => __( 'Información de productos relacionados', 'wc-options' ),
            'desc_tip'    => true,
            'description' => __( 'Campo personalizado para productos relacionados', 'wc-options' ),
        ) );
        
        echo '</div>';
    }

    /**
     * Save custom RELATED field
     */
    public function save_related_custom_field( $post_id ) {
        $related_value = isset( $_POST['_custom_related_field'] ) ? sanitize_text_field( $_POST['_custom_related_field'] ) : '';
        update_post_meta( $post_id, '_custom_related_field', $related_value );
    }
}

/**
 * Initialize the plugin
 */
function wc_custom_options_init() {
    new WC_Custom_Options();
}
add_action( 'plugins_loaded', 'wc_custom_options_init', 0 );
