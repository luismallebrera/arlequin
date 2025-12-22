<?php
/**
 * Plugin Name: WooCommerce Custom Options
 * Plugin URI: https://github.com/luismallebrera/arlequin
 * Description: Personaliza opciones de WooCommerce: productos relacionados solo por categorías, cantidad y columnas configurables.
 * Version: 1.1.0
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
        add_filter( 'woocommerce_related_products', array( $this, 'custom_related_products' ), 999, 3 );
        
        // Add custom RELATED field to products
        add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_related_custom_field' ) );
        add_action( 'woocommerce_process_product_meta', array( $this, 'save_related_custom_field' ) );
        
        // Quick Edit support
        add_action( 'woocommerce_product_quick_edit_end', array( $this, 'add_related_quick_edit_field' ) );
        add_action( 'woocommerce_product_bulk_edit_end', array( $this, 'add_related_bulk_edit_field' ) );
        add_action( 'manage_product_posts_custom_column', array( $this, 'add_related_custom_column_data' ), 10, 2 );
        add_action( 'woocommerce_product_quick_edit_save', array( $this, 'save_related_quick_edit_field' ) );
        add_action( 'woocommerce_product_bulk_edit_save', array( $this, 'save_related_bulk_edit_field' ) );
        add_action( 'admin_footer', array( $this, 'add_related_quick_edit_script' ) );
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
        // Return false if using custom field
        if ( $option === 'custom' ) {
            return false;
        }
        // Return true if option is 'categories' or 'both'
        return in_array( $option, array( 'categories', 'both' ) );
    }

    /**
     * Related products by tag
     */
    public function related_products_by_tag( $relate_by_tag ) {
        $option = get_option( 'wc_options_related_products_by', 'categories' );
        // Return false if using custom field
        if ( $option === 'custom' ) {
            return false;
        }
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
                        'custom'     => __( 'Campo personalizado RELATED', 'wc-options' ),
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
     * Custom related products based on RELATED field
     */
    public function custom_related_products( $related_posts, $product_id, $args ) {
        $option = get_option( 'wc_options_related_products_by', 'categories' );
        
        // Only apply if custom option is selected
        if ( $option !== 'custom' ) {
            return $related_posts;
        }
        
        // Get custom related field value
        $custom_related = get_post_meta( $product_id, '_custom_related_field', true );
        
        if ( empty( $custom_related ) ) {
            return $related_posts;
        }
        
        // Search for products with the same RELATED tag value
        $args_query = array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'post__not_in'   => array( $product_id ),
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => '_custom_related_field',
                    'value'   => $custom_related,
                    'compare' => '='
                )
            )
        );
        
        $related_query = new \WP_Query( $args_query );
        $custom_ids = $related_query->posts;
        
        return ! empty( $custom_ids ) ? $custom_ids : $related_posts;
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
            'placeholder' => __( 'Etiqueta de productos relacionados', 'wc-options' ),
            'desc_tip'    => true,
            'description' => __( 'Productos con la misma etiqueta se mostrarán como relacionados (ej: "verano", "boda", "pack-especial"). Solo funciona si seleccionas "Campo personalizado RELATED" en los ajustes.', 'wc-options' ),
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

    /**
     * Add RELATED field to Quick Edit
     */
    public function add_related_quick_edit_field() {
        ?>
        <br class="clear" />
        <label class="alignleft">
            <span class="title"><?php esc_html_e( 'RELATED', 'wc-options' ); ?></span>
            <span class="input-text-wrap">
                <input type="text" name="_custom_related_field" class="text" placeholder="<?php esc_attr_e( 'Etiqueta de productos relacionados', 'wc-options' ); ?>" value="">
            </span>
        </label>
        <?php
    }

    /**
     * Add RELATED field to Bulk Edit
     */
    public function add_related_bulk_edit_field() {
        ?>
        <label class="alignleft">
            <span class="title"><?php esc_html_e( 'RELATED', 'wc-options' ); ?></span>
            <span class="input-text-wrap">
                <select name="_custom_related_field_bulk" class="text">
                    <option value=""><?php esc_html_e( '— Sin cambios —', 'wc-options' ); ?></option>
                    <option value="wc_options_clear"><?php esc_html_e( 'Borrar', 'wc-options' ); ?></option>
                </select>
                <input type="text" name="_custom_related_field" class="text" placeholder="<?php esc_attr_e( 'Etiqueta de productos relacionados', 'wc-options' ); ?>" value="">
            </span>
        </label>
        <br class="clear" />
        <?php
    }

    /**
     * Add custom column data for Quick Edit to read
     */
    public function add_related_custom_column_data( $column, $post_id ) {
        if ( $column === 'name' ) {
            $related_value = get_post_meta( $post_id, '_custom_related_field', true );
            echo '<div class="hidden wc-options-related-data" data-related="' . esc_attr( $related_value ) . '"></div>';
        }
    }

    /**
     * Save RELATED field from Quick Edit
     */
    public function save_related_quick_edit_field( $product ) {
        if ( isset( $_REQUEST['_custom_related_field'] ) ) {
            $product_id = is_object( $product ) ? $product->get_id() : $product;
            $related_value = sanitize_text_field( $_REQUEST['_custom_related_field'] );
            update_post_meta( $product_id, '_custom_related_field', $related_value );
        }
    }

    /**
     * Save RELATED field from Bulk Edit
     */
    public function save_related_bulk_edit_field( $product ) {
        $product_id = is_object( $product ) ? $product->get_id() : $product;
        
        // Check if bulk edit action is set
        if ( isset( $_REQUEST['_custom_related_field_bulk'] ) ) {
            $bulk_action = sanitize_text_field( $_REQUEST['_custom_related_field_bulk'] );
            
            // Clear the field
            if ( $bulk_action === 'wc_options_clear' ) {
                update_post_meta( $product_id, '_custom_related_field', '' );
            }
            // Set new value
            elseif ( isset( $_REQUEST['_custom_related_field'] ) && ! empty( $_REQUEST['_custom_related_field'] ) ) {
                $related_value = sanitize_text_field( $_REQUEST['_custom_related_field'] );
                update_post_meta( $product_id, '_custom_related_field', $related_value );
            }
        }
    }

    /**
     * Add JavaScript for Quick Edit
     */
    public function add_related_quick_edit_script() {
        global $current_screen;
        
        // Only load on product list page
        if ( ! $current_screen || $current_screen->id !== 'edit-product' ) {
            return;
        }
        ?>
        <script type="text/javascript">
        jQuery(function($) {
            // Quick Edit
            $('#the-list').on('click', '.editinline', function() {
                var post_id = $(this).closest('tr').attr('id').replace('post-', '');
                var $row = $('#post-' + post_id);
                var $related_data = $row.find('.wc-options-related-data');
                var related_value = $related_data.data('related') || '';
                
                // Set the value in quick edit
                setTimeout(function() {
                    var $quick_edit_row = $('#edit-' + post_id);
                    $quick_edit_row.find('input[name="_custom_related_field"]').val(related_value);
                }, 100);
            });
        });
        </script>
        <?php
    }
}

/**
 * Initialize the plugin
 */
function wc_custom_options_init() {
    new WC_Custom_Options();
}
add_action( 'plugins_loaded', 'wc_custom_options_init', 0 );
