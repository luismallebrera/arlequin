<?php
/**
 * Plugin Name: WooCommerce Custom Options
 * Plugin URI: https://github.com/luismallebrera/arlequin
 * Description: Personaliza opciones de WooCommerce: productos relacionados, campo RELATED personalizado, cantidad mínima por producto y botones personalizados de productos.
 * Version: 1.5.1
 * Author: Luis Mallebrera
 * Author URI: https://github.com/luismallebrera
 * Text Domain: wc-options
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 9.5
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
        add_action( 'before_woocommerce_init', array( $this, 'declare_compatibility' ) );
    }
    
    /**
     * Declare compatibility with WooCommerce features
     */
    public function declare_compatibility() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
        }
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
        
        // Minimum quantity support
        add_filter( 'woocommerce_quantity_input_args', array( $this, 'set_min_quantity' ), 10, 2 );
        add_filter( 'woocommerce_available_variation', array( $this, 'set_variation_min_quantity' ), 10, 3 );
        
        // Custom product buttons
        add_action( 'woocommerce_before_variations_form', array( $this, 'display_custom_product_buttons' ) );
        add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'display_custom_product_buttons' ) );
        add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_custom_buttons_tab' ) );
        add_action( 'woocommerce_product_data_panels', array( $this, 'add_custom_buttons_tab_content' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
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
        
        woocommerce_wp_text_input( array(
            'id'          => '_min_quantity',
            'label'       => __( 'Cantidad Mínima', 'wc-options' ),
            'placeholder' => '1',
            'desc_tip'    => true,
            'description' => __( 'Cantidad mínima que el cliente debe comprar de este producto. Déjalo vacío para usar 1 por defecto.', 'wc-options' ),
            'type'        => 'number',
            'custom_attributes' => array(
                'step' => '1',
                'min'  => '1',
            ),
        ) );
        
        woocommerce_wp_text_input( array(
            'id'          => '_quantity_step',
            'label'       => __( 'Incremento de Cantidad', 'wc-options' ),
            'placeholder' => '1',
            'desc_tip'    => true,
            'description' => __( 'Cantidad que se incrementa o decrementa al hacer clic en los botones +/-. Por ejemplo, si estableces 5, cada clic aumentará o disminuirá la cantidad en 5 unidades.', 'wc-options' ),
            'type'        => 'number',
            'custom_attributes' => array(
                'step' => '1',
                'min'  => '1',
            ),
        ) );
        
        echo '</div>';
    }

    /**
     * Save custom RELATED field
     */
    public function save_related_custom_field( $post_id ) {
        $related_value = isset( $_POST['_custom_related_field'] ) ? sanitize_text_field( $_POST['_custom_related_field'] ) : '';
        update_post_meta( $post_id, '_custom_related_field', $related_value );
        
        $min_quantity = isset( $_POST['_min_quantity'] ) ? absint( $_POST['_min_quantity'] ) : '';
        if ( $min_quantity > 0 ) {
            update_post_meta( $post_id, '_min_quantity', $min_quantity );
        } else {
            delete_post_meta( $post_id, '_min_quantity' );
        }
        
        $quantity_step = isset( $_POST['_quantity_step'] ) ? absint( $_POST['_quantity_step'] ) : '';
        if ( $quantity_step > 0 ) {
            update_post_meta( $post_id, '_quantity_step', $quantity_step );
        } else {
            delete_post_meta( $post_id, '_quantity_step' );
        }
        
        // Save custom product buttons
        $this->save_custom_buttons_fields( $post_id );
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
    
    /**
     * Set minimum quantity for products
     */
    public function set_min_quantity( $args, $product ) {
        if ( ! $product ) {
            return $args;
        }
        
        $min_quantity = get_post_meta( $product->get_id(), '_min_quantity', true );
        
        if ( $min_quantity && $min_quantity > 1 ) {
            $min_quantity = absint( $min_quantity );
            $args['min_value'] = $min_quantity;
            // Always start at minimum quantity or higher
            $args['input_value'] = isset( $args['input_value'] ) && $args['input_value'] > $min_quantity 
                ? $args['input_value'] 
                : $min_quantity;
        }
        
        // Set quantity step
        $quantity_step = get_post_meta( $product->get_id(), '_quantity_step', true );
        if ( $quantity_step && $quantity_step > 1 ) {
            $args['step'] = absint( $quantity_step );
        }
        
        return $args;
    }
    
    /**
     * Set minimum quantity for product variations
     */
    public function set_variation_min_quantity( $data, $product, $variation ) {
        $min_quantity = get_post_meta( $variation->get_id(), '_min_quantity', true );
        
        if ( ! $min_quantity ) {
            // Check parent product if variation doesn't have min quantity
            $min_quantity = get_post_meta( $product->get_id(), '_min_quantity', true );
        }
        
        if ( $min_quantity && $min_quantity > 1 ) {
            $min_quantity = absint( $min_quantity );
            $data['min_qty'] = $min_quantity;
            // Set max_qty to prevent values below minimum
            $data['max_qty'] = isset( $data['max_qty'] ) ? max( $data['max_qty'], $min_quantity ) : '';
            // Ensure initial quantity value starts at minimum
            if ( ! isset( $data['is_in_stock'] ) || $data['is_in_stock'] ) {
                $data['min_qty'] = $min_quantity;
            }
        }
        
        // Set quantity step for variations
        $quantity_step = get_post_meta( $variation->get_id(), '_quantity_step', true );
        if ( ! $quantity_step ) {
            // Check parent product if variation doesn't have quantity step
            $quantity_step = get_post_meta( $product->get_id(), '_quantity_step', true );
        }
        
        if ( $quantity_step && $quantity_step > 1 ) {
            $data['step'] = absint( $quantity_step );
        }
        
        return $data;
    }
    
    /**
     * Add custom buttons tab to product data
     */
    public function add_custom_buttons_tab( $tabs ) {
        $tabs['custom_buttons'] = array(
            'label'    => __( 'Botones', 'wc-options' ),
            'target'   => 'custom_buttons_product_data',
            'class'    => array( 'show_if_simple', 'show_if_variable' ),
            'priority' => 80,
        );
        return $tabs;
    }
    
    /**
     * Add custom buttons tab content
     */
    public function add_custom_buttons_tab_content() {
        global $post;
        
        ?>
        <div id="custom_buttons_product_data" class="panel woocommerce_options_panel">
            <div class="options_group custom-product-buttons-group">
                <p style="padding: 12px; margin: 0; color: #666; background: #f9f9f9; border-bottom: 1px solid #ddd;">
                    <?php esc_html_e( 'Añade botones que enlacen a otros productos. Estos botones aparecerán antes del formulario de compra.', 'wc-options' ); ?>
                </p>
                
                <?php
                $buttons = get_post_meta( $post->ID, '_custom_product_buttons', true );
                if ( ! is_array( $buttons ) ) {
                    $buttons = array();
                }
                $buttons_title = get_post_meta( $post->ID, '_custom_buttons_title', true );
                ?>
                
                <div style="padding: 12px; border-bottom: 1px solid #e5e5e5;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 600;">
                        <?php esc_html_e( 'Título de la sección (opcional):', 'wc-options' ); ?>
                    </label>
                    <input type="text" name="_custom_buttons_title" value="<?php echo esc_attr( $buttons_title ); ?>" placeholder="<?php esc_attr_e( 'Ejemplo: Disponible también en:', 'wc-options' ); ?>" style="width: 100%; max-width: 500px;">
                    <p class="description" style="margin-top: 6px;">
                        <?php esc_html_e( 'Este título aparecerá encima de los botones en la página del producto.', 'wc-options' ); ?>
                    </p>
                </div>
                
                <div id="custom-buttons-container" style="padding: 12px;">
                    <?php
                    if ( empty( $buttons ) ) {
                        echo '<p class="no-buttons-message" style="color: #999; font-style: italic;">' . __( 'No hay botones configurados. Haz clic en "Añadir Botón" para crear uno.', 'wc-options' ) . '</p>';
                    } else {
                        foreach ( $buttons as $index => $button ) {
                            $this->render_button_fields( $index, $button );
                        }
                    }
                    ?>
                </div>
                
                <p style="padding: 0 12px 12px 12px;">
                    <button type="button" class="button button-primary add-custom-button" id="add-custom-button">
                        <span class="dashicons dashicons-plus-alt" style="margin-top: 3px;"></span>
                        <?php esc_html_e( 'Añadir Botón', 'wc-options' ); ?>
                    </button>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render button fields
     */
    private function render_button_fields( $index, $button = array() ) {
        $product_id = isset( $button['product_id'] ) ? $button['product_id'] : '';
        $button_text = isset( $button['button_text'] ) ? $button['button_text'] : '';
        $is_current = isset( $button['is_current'] ) ? $button['is_current'] : false;
        
        ?>
        <div class="custom-button-row">
            <div>
                <label>
                    <?php esc_html_e( 'Producto de destino:', 'wc-options' ); ?>
                </label>
                <select name="_custom_product_buttons[<?php echo esc_attr( $index ); ?>][product_id]" class="wc-product-search" data-placeholder="<?php esc_attr_e( 'Buscar producto...', 'wc-options' ); ?>" data-allow_clear="true">
                    <?php if ( $product_id ) : 
                        $product = wc_get_product( $product_id );
                        if ( $product ) : ?>
                            <option value="<?php echo esc_attr( $product_id ); ?>" selected="selected">
                                <?php echo esc_html( $product->get_name() ) . ' (#' . $product_id . ')'; ?>
                            </option>
                        <?php endif;
                    endif; ?>
                </select>
            </div>
            <div>
                <label>
                    <?php esc_html_e( 'Texto del botón:', 'wc-options' ); ?>
                </label>
                <input type="text" name="_custom_product_buttons[<?php echo esc_attr( $index ); ?>][button_text]" value="<?php echo esc_attr( $button_text ); ?>" placeholder="<?php esc_attr_e( 'Ver producto', 'wc-options' ); ?>">
            </div>
            <div style="margin-bottom: 0;">
                <label style="width: auto; display: inline-flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" name="_custom_product_buttons[<?php echo esc_attr( $index ); ?>][is_current]" value="1" <?php checked( $is_current, true ); ?> style="margin: 0 6px 0 0;">
                    <?php esc_html_e( 'Botón actual (producto activo)', 'wc-options' ); ?>
                </label>
            </div>
            <button type="button" class="button remove-custom-button">
                <?php esc_html_e( 'Eliminar', 'wc-options' ); ?>
            </button>
        </div>
        <?php
    }
    
    /**
     * Save custom buttons fields
     */
    public function save_custom_buttons_fields( $post_id ) {
        // Check if we have the nonce (WooCommerce sets it)
        if ( ! isset( $_POST['woocommerce_meta_nonce'] ) ) {
            return;
        }
        
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' ) ) {
            return;
        }
        
        // Check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        $buttons = array();
        
        // Debug: Log what we're receiving
        error_log( 'Custom Product Buttons POST data: ' . print_r( $_POST['_custom_product_buttons'] ?? 'NOT SET', true ) );
        
        if ( isset( $_POST['_custom_product_buttons'] ) && is_array( $_POST['_custom_product_buttons'] ) ) {
            foreach ( $_POST['_custom_product_buttons'] as $button ) {
                $product_id = isset( $button['product_id'] ) ? absint( $button['product_id'] ) : 0;
                $button_text = isset( $button['button_text'] ) ? sanitize_text_field( $button['button_text'] ) : '';
                $is_current = isset( $button['is_current'] ) && $button['is_current'] === '1';
                
                error_log( "Processing button - Product ID: $product_id, Text: $button_text, Is Current: " . ( $is_current ? 'YES' : 'NO' ) );
                
                // Only save if both product ID and button text are provided
                if ( $product_id && $button_text ) {
                    $buttons[] = array(
                        'product_id'  => $product_id,
                        'button_text' => $button_text,
                        'is_current'  => $is_current,
                    );
                }
            }
        }
        
        error_log( 'Buttons to save: ' . print_r( $buttons, true ) );
        
        if ( ! empty( $buttons ) ) {
            $result = update_post_meta( $post_id, '_custom_product_buttons', $buttons );
            error_log( 'Update result: ' . ( $result ? 'SUCCESS' : 'FAILED' ) );
        } else {
            delete_post_meta( $post_id, '_custom_product_buttons' );
            error_log( 'Deleted meta (no buttons)' );
        }
        
        // Save buttons title
        if ( isset( $_POST['_custom_buttons_title'] ) ) {
            $buttons_title = sanitize_text_field( $_POST['_custom_buttons_title'] );
            if ( ! empty( $buttons_title ) ) {
                update_post_meta( $post_id, '_custom_buttons_title', $buttons_title );
            } else {
                delete_post_meta( $post_id, '_custom_buttons_title' );
            }
        }
    }
    
    /**
     * Display custom product buttons on frontend
     */
    public function display_custom_product_buttons() {
        global $product;
        
        if ( ! $product ) {
            return;
        }
        
        // Prevent duplicate display - only show once
        static $displayed = false;
        if ( $displayed ) {
            return;
        }
        $displayed = true;
        
        $buttons = get_post_meta( $product->get_id(), '_custom_product_buttons', true );
        
        if ( ! is_array( $buttons ) || empty( $buttons ) ) {
            return;
        }
        
        // Get the custom title
        $buttons_title = get_post_meta( $product->get_id(), '_custom_buttons_title', true );
        
        echo '<div class="wc-custom-product-buttons-wrapper">';
        
        // Display title if set
        if ( ! empty( $buttons_title ) ) {
            echo '<h3 class="wc-custom-buttons-title">' . esc_html( $buttons_title ) . '</h3>';
        }
        
        echo '<div class="wc-custom-product-buttons">';
        
        foreach ( $buttons as $button ) {
            $product_id = isset( $button['product_id'] ) ? absint( $button['product_id'] ) : 0;
            $button_text = isset( $button['button_text'] ) ? esc_html( $button['button_text'] ) : '';
            $is_current = isset( $button['is_current'] ) && $button['is_current'];
            
            if ( ! $product_id || ! $button_text ) {
                continue;
            }
            
            $linked_product = wc_get_product( $product_id );
            
            if ( ! $linked_product ) {
                continue;
            }
            
            $product_url = get_permalink( $product_id );
            
            // Sanitize button text to create a safe CSS class
            $button_class = sanitize_html_class( strtolower( str_replace( array( ' ', '_' ), '-', $button_text ) ) );
            
            // Build CSS classes
            $css_classes = array(
                'wc-custom-product-button',
                'button',
                'alt',
                'wc-button-' . $button_class
            );
            
            // Add is-current class if this is the current product
            if ( $is_current ) {
                $css_classes[] = 'is-current';
            }
            
            echo '<a href="' . esc_url( $product_url ) . '" class="' . esc_attr( implode( ' ', $css_classes ) ) . '">';
            echo $button_text;
            echo '</a>';
        }
        
        echo '</div>'; // Close wc-custom-product-buttons
        echo '</div>'; // Close wc-custom-product-buttons-wrapper
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts( $hook ) {
        global $post;
        
        // Only load on product edit pages
        if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
            return;
        }
        
        if ( ! $post || $post->post_type !== 'product' ) {
            return;
        }
        
        $plugin_url = plugin_dir_url( __FILE__ );
        $version = '1.5.1';
        
        // Enqueue WooCommerce product search
        wp_enqueue_script( 'wc-enhanced-select' );
        wp_enqueue_style( 'woocommerce_admin_styles' );
        
        // Enqueue custom admin CSS
        wp_enqueue_style(
            'wc-options-admin',
            $plugin_url . 'assets/css/admin-buttons.css',
            array(),
            $version
        );
        
        // Enqueue custom admin script
        wp_enqueue_script(
            'wc-options-admin',
            $plugin_url . 'assets/js/admin-buttons.js',
            array( 'jquery', 'wc-enhanced-select' ),
            $version,
            true
        );
        
        // Localize script
        wp_localize_script( 'wc-options-admin', 'wcOptionsAdmin', array(
            'placeholder' => __( 'Buscar producto...', 'wc-options' ),
        ) );
    }
    
    /**
     * Enqueue frontend styles
     */
    public function enqueue_frontend_styles() {
        if ( ! is_product() ) {
            return;
        }
        
        $plugin_url = plugin_dir_url( __FILE__ );
        $version = '1.5.0';
        
        wp_enqueue_style(
            'wc-options-frontend',
            $plugin_url . 'assets/css/frontend-buttons.css',
            array(),
            $version
        );
    }
}

/**
 * Initialize the plugin
 */
function wc_custom_options_init() {
    new WC_Custom_Options();
}
add_action( 'plugins_loaded', 'wc_custom_options_init', 0 );
