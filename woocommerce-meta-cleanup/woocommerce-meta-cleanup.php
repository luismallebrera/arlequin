<?php
/**
 * Plugin Name: WooCommerce Meta Keys Cleanup
 * Plugin URI: https://github.com/luismallebrera/arlequin
 * Description: Herramienta para analizar y limpiar meta keys no utilizadas en productos de WooCommerce
 * Version: 1.0.0
 * Author: Luis Mallebrera
 * Author URI: https://github.com/luismallebrera
 * Text Domain: wc-meta-cleanup
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
define( 'WC_META_CLEANUP_VERSION', '1.0.0' );
define( 'WC_META_CLEANUP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WC_META_CLEANUP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Plugin Class
 */
class WC_Meta_Cleanup {

    /**
     * Meta keys that should be kept (protected)
     */
    private static $protected_meta_keys = array(
        // Custom Product Addons
        '_enable_custom_addons',
        '_enable_text_field_nombre_persona',
        '_enable_text_field_inicial',
        '_enable_text_field_fecha_evento',
        '_enable_text_field_hora_evento',
        '_enable_text_field_localidad',
        '_enable_text_field_iglesia',
        '_enable_text_field_hora_misa',
        '_enable_text_field_restaurante',
        '_enable_text_field_ano_curso',
        '_enable_text_field_frase_texto',
        '_enable_text_field_numero_cuenta',
        '_enable_text_field_menu',
        '_enable_text_field_observaciones',
        '_text_field_option_nombre_persona',
        '_text_field_option_fecha_evento',
        '_text_field_option_inicial',
        
        // WooCommerce Options
        '_custom_related_field',
        '_min_quantity',
        
        // WooCommerce Min Max Quantities
        '_wcmmq_s_min_quantity',
        '_wcmmq_s_max_quantity',
        '_wcmmq_s_product_step',
        
        // Custom theme/styling
        'add_custom_body_class',
    );

    /**
     * Standard WooCommerce meta keys to keep
     */
    private static $woocommerce_keys = array(
        '_sku', '_price', '_regular_price', '_sale_price', '_stock', '_stock_status',
        '_manage_stock', '_backorders', '_sold_individually', '_weight', '_length',
        '_width', '_height', '_downloadable', '_virtual', '_tax_status', '_tax_class',
        '_product_image_gallery', '_thumbnail_id', '_featured', '_visibility',
        '_edit_last', '_edit_lock', '_product_attributes', '_sale_price_dates_from',
        '_sale_price_dates_to', '_min_variation_price', '_max_variation_price',
        '_min_price_variation_id', '_max_price_variation_id', '_min_variation_regular_price',
        '_max_variation_regular_price', '_min_variation_sale_price', '_max_variation_sale_price',
        '_default_attributes', '_product_version', '_wp_old_slug', '_wc_average_rating',
        '_wc_rating_count', '_wc_review_count', '_downloadable_files', '_download_limit',
        '_download_expiry', '_purchase_note', '_variation_description',
    );

    /**
     * Initialize the plugin
     */
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
        add_action( 'wp_ajax_wc_meta_cleanup_analyze', array( __CLASS__, 'ajax_analyze' ) );
        add_action( 'wp_ajax_wc_meta_cleanup_delete', array( __CLASS__, 'ajax_delete' ) );
        add_action( 'wp_ajax_wc_meta_cleanup_backup', array( __CLASS__, 'ajax_backup' ) );
    }

    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __( 'Meta Keys Cleanup', 'wc-meta-cleanup' ),
            __( 'Meta Cleanup', 'wc-meta-cleanup' ),
            'manage_woocommerce',
            'wc-meta-cleanup',
            array( __CLASS__, 'render_admin_page' )
        );
    }

    /**
     * Enqueue admin assets
     */
    public static function enqueue_admin_assets( $hook ) {
        if ( 'woocommerce_page_wc-meta-cleanup' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'wc-meta-cleanup-admin',
            WC_META_CLEANUP_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WC_META_CLEANUP_VERSION
        );

        wp_enqueue_script(
            'wc-meta-cleanup-admin',
            WC_META_CLEANUP_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            WC_META_CLEANUP_VERSION,
            true
        );

        wp_localize_script( 'wc-meta-cleanup-admin', 'wcMetaCleanup', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'wc_meta_cleanup_nonce' ),
        ) );
    }

    /**
     * Render admin page
     */
    public static function render_admin_page() {
        ?>
        <div class="wrap wc-meta-cleanup">
            <h1><?php _e( 'WooCommerce Meta Keys Cleanup', 'wc-meta-cleanup' ); ?></h1>
            
            <div class="notice notice-warning">
                <p><strong><?php _e( '⚠️ IMPORTANTE:', 'wc-meta-cleanup' ); ?></strong> 
                <?php _e( 'Siempre haz un backup completo de tu base de datos antes de eliminar meta keys.', 'wc-meta-cleanup' ); ?></p>
            </div>

            <div class="wc-meta-cleanup-tabs">
                <nav class="nav-tab-wrapper">
                    <a href="#tab-analyze" class="nav-tab nav-tab-active"><?php _e( 'Analizar', 'wc-meta-cleanup' ); ?></a>
                    <a href="#tab-protected" class="nav-tab"><?php _e( 'Meta Keys Protegidas', 'wc-meta-cleanup' ); ?></a>
                    <a href="#tab-cleanup" class="nav-tab"><?php _e( 'Limpieza', 'wc-meta-cleanup' ); ?></a>
                </nav>

                <!-- Tab: Analyze -->
                <div id="tab-analyze" class="tab-content active">
                    <h2><?php _e( 'Análisis de Meta Keys', 'wc-meta-cleanup' ); ?></h2>
                    <p><?php _e( 'Analiza la base de datos para ver qué meta keys están en uso y cuáles no.', 'wc-meta-cleanup' ); ?></p>
                    
                    <button id="btn-analyze" class="button button-primary button-large">
                        <?php _e( 'Analizar Base de Datos', 'wc-meta-cleanup' ); ?>
                    </button>

                    <div id="analyze-results" style="display:none;">
                        <h3><?php _e( 'Resultados del Análisis', 'wc-meta-cleanup' ); ?></h3>
                        <div id="analyze-content"></div>
                    </div>
                </div>

                <!-- Tab: Protected -->
                <div id="tab-protected" class="tab-content">
                    <h2><?php _e( 'Meta Keys Protegidas', 'wc-meta-cleanup' ); ?></h2>
                    <p><?php _e( 'Estas meta keys NO serán eliminadas:', 'wc-meta-cleanup' ); ?></p>
                    
                    <div class="protected-keys-section">
                        <h3><?php _e( 'Custom Product Addons', 'wc-meta-cleanup' ); ?></h3>
                        <ul class="protected-list">
                            <?php foreach ( self::$protected_meta_keys as $key ) : ?>
                                <li><code><?php echo esc_html( $key ); ?></code></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="protected-keys-section">
                        <h3><?php _e( 'Meta Keys Estándar de WooCommerce', 'wc-meta-cleanup' ); ?></h3>
                        <p><?php _e( 'Todas las meta keys estándar de WooCommerce están protegidas (sku, price, stock, etc.)', 'wc-meta-cleanup' ); ?></p>
                    </div>
                </div>

                <!-- Tab: Cleanup -->
                <div id="tab-cleanup" class="tab-content">
                    <h2><?php _e( 'Limpieza de Meta Keys', 'wc-meta-cleanup' ); ?></h2>
                    
                    <div class="cleanup-section">
                        <h3><?php _e( '1. Crear Backup', 'wc-meta-cleanup' ); ?></h3>
                        <p><?php _e( 'Crea un backup de las meta keys que serán eliminadas.', 'wc-meta-cleanup' ); ?></p>
                        <button id="btn-backup" class="button button-secondary">
                            <?php _e( 'Crear Backup', 'wc-meta-cleanup' ); ?>
                        </button>
                        <div id="backup-result"></div>
                    </div>

                    <hr>

                    <div class="cleanup-section">
                        <h3><?php _e( '2. Eliminar Meta Keys No Utilizadas', 'wc-meta-cleanup' ); ?></h3>
                        <p><?php _e( 'Elimina permanentemente las meta keys no utilizadas de la base de datos.', 'wc-meta-cleanup' ); ?></p>
                        
                        <div class="notice notice-error inline">
                            <p><strong><?php _e( '⚠️ ATENCIÓN:', 'wc-meta-cleanup' ); ?></strong> 
                            <?php _e( 'Esta acción es irreversible. Asegúrate de tener un backup.', 'wc-meta-cleanup' ); ?></p>
                        </div>

                        <label>
                            <input type="checkbox" id="confirm-delete" />
                            <?php _e( 'He hecho un backup y entiendo que esta acción es irreversible', 'wc-meta-cleanup' ); ?>
                        </label>
                        <br><br>

                        <button id="btn-delete" class="button button-primary" disabled>
                            <?php _e( 'Eliminar Meta Keys No Utilizadas', 'wc-meta-cleanup' ); ?>
                        </button>
                        <div id="delete-result"></div>
                    </div>
                </div>
            </div>

            <div id="loading-overlay" style="display:none;">
                <div class="spinner is-active"></div>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX: Analyze database
     */
    public static function ajax_analyze() {
        check_ajax_referer( 'wc_meta_cleanup_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        global $wpdb;

        $protected_keys = array_merge( self::$protected_meta_keys, self::$woocommerce_keys );

        // Get all custom meta keys
        $all_meta_keys = $wpdb->get_results( "
            SELECT DISTINCT meta_key, COUNT(*) as count
            FROM {$wpdb->postmeta} 
            WHERE post_id IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'product'
            )
            AND meta_key LIKE '\_%'
            GROUP BY meta_key
            ORDER BY meta_key
        ", ARRAY_A );

        $unused_keys = array();
        $used_keys = array();

        foreach ( $all_meta_keys as $meta_data ) {
            $meta_key = $meta_data['meta_key'];
            
            // Check if key is in protected list OR starts with 'attribute_' OR starts with '_elementor'
            $is_protected = in_array( $meta_key, $protected_keys ) || 
                           strpos( $meta_key, 'attribute_' ) === 0 ||
                           strpos( $meta_key, '_elementor' ) === 0;
            
            if ( $is_protected ) {
                $used_keys[] = $meta_data;
            } else {
                $unused_keys[] = $meta_data;
            }
        }

        wp_send_json_success( array(
            'total_keys' => count( $all_meta_keys ),
            'used_keys'  => $used_keys,
            'unused_keys' => $unused_keys,
        ) );
    }

    /**
     * AJAX: Create backup
     */
    public static function ajax_backup() {
        check_ajax_referer( 'wc_meta_cleanup_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        global $wpdb;

        $protected_keys = array_merge( self::$protected_meta_keys, self::$woocommerce_keys );

        $upload_dir = wp_upload_dir();
        $backup_file = $upload_dir['basedir'] . '/wc-meta-backup-' . date( 'Y-m-d-His' ) . '.sql';

        $sql_content = "-- WooCommerce Meta Keys Backup\n";
        $sql_content .= "-- Generated: " . date( 'Y-m-d H:i:s' ) . "\n\n";

        // Get all custom meta keys
        $all_meta_keys = $wpdb->get_col( "
            SELECT DISTINCT meta_key
            FROM {$wpdb->postmeta} 
            WHERE post_id IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'product'
            )
            AND meta_key LIKE '\_%'
            ORDER BY meta_key
        " );

        $backed_up_count = 0;

        foreach ( $all_meta_keys as $meta_key ) {
            // Skip if key is protected OR starts with 'attribute_' OR starts with '_elementor'
            if ( in_array( $meta_key, $protected_keys ) || 
                 strpos( $meta_key, 'attribute_' ) === 0 ||
                 strpos( $meta_key, '_elementor' ) === 0 ) {
                continue;
            }

            $records = $wpdb->get_results( $wpdb->prepare(
                "SELECT meta_id, post_id, meta_key, meta_value 
                FROM {$wpdb->postmeta} 
                WHERE meta_key = %s",
                $meta_key
            ), ARRAY_A );

            if ( ! empty( $records ) ) {
                $sql_content .= "\n-- Meta Key: $meta_key\n";
                
                foreach ( $records as $record ) {
                    $meta_value = esc_sql( $record['meta_value'] );
                    $sql_content .= "INSERT INTO {$wpdb->postmeta} (meta_id, post_id, meta_key, meta_value) VALUES ";
                    $sql_content .= "({$record['meta_id']}, {$record['post_id']}, '{$record['meta_key']}', '{$meta_value}');\n";
                }
                
                $backed_up_count++;
            }
        }

        file_put_contents( $backup_file, $sql_content );

        wp_send_json_success( array(
            'file' => basename( $backup_file ),
            'path' => $backup_file,
            'size' => size_format( filesize( $backup_file ) ),
            'count' => $backed_up_count,
        ) );
    }

    /**
     * AJAX: Delete unused meta keys
     */
    public static function ajax_delete() {
        check_ajax_referer( 'wc_meta_cleanup_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        global $wpdb;

        $protected_keys = array_merge( self::$protected_meta_keys, self::$woocommerce_keys );

        // Get all custom meta keys
        $all_meta_keys = $wpdb->get_results( "
            SELECT DISTINCT meta_key, COUNT(*) as count
            FROM {$wpdb->postmeta} 
            WHERE post_id IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'product'
            )
            AND meta_key LIKE '\_%'
            GROUP BY meta_key
        ", ARRAY_A );

        $total_deleted = 0;
        $total_records = 0;
        $deleted_keys = array();

        foreach ( $all_meta_keys as $meta_data ) {
            $meta_key = $meta_data['meta_key'];
            
            // Skip if key is protected OR starts with 'attribute_' OR starts with '_elementor'
            if ( in_array( $meta_key, $protected_keys ) || 
                 strpos( $meta_key, 'attribute_' ) === 0 ||
                 strpos( $meta_key, '_elementor' ) === 0 ) {
                continue;
            }

            $deleted = $wpdb->delete(
                $wpdb->postmeta,
                array( 'meta_key' => $meta_key ),
                array( '%s' )
            );

            if ( $deleted ) {
                $total_records += $deleted;
                $total_deleted++;
                $deleted_keys[] = array(
                    'key' => $meta_key,
                    'records' => $deleted,
                );
            }
        }

        // Optimize table
        $wpdb->query( "OPTIMIZE TABLE {$wpdb->postmeta}" );

        wp_send_json_success( array(
            'total_keys' => $total_deleted,
            'total_records' => $total_records,
            'deleted_keys' => $deleted_keys,
        ) );
    }

    /**
     * Get all protected meta keys
     */
    public static function get_protected_keys() {
        return array_merge( self::$protected_meta_keys, self::$woocommerce_keys );
    }
}

// Initialize the plugin
add_action( 'plugins_loaded', array( 'WC_Meta_Cleanup', 'init' ) );
