<?php
/**
 * Script to analyze and identify unused meta keys in WordPress database
 * This script will help identify meta keys that can be safely deleted
 */

// Load WordPress
require_once( '/var/www/html/wp-load.php' );

if ( ! defined( 'ABSPATH' ) ) {
    die( 'WordPress not found. Please adjust the path to wp-load.php' );
}

echo "=== ANÁLISIS DE META KEYS EN LA BASE DE DATOS ===\n\n";

// Meta keys that are currently used by the plugins
$used_meta_keys = array(
    // WooCommerce Custom Product Addons - Quantity addons
    '_enable_custom_addons',
    
    // WooCommerce Custom Product Addons - Text fields (enabled checkboxes)
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
    
    // WooCommerce Custom Product Addons - Select field options
    '_text_field_option_nombre_persona',
    '_text_field_option_fecha_evento',
    '_text_field_option_inicial',
    
    // WooCommerce Options plugin
    '_custom_related_field',
    '_min_quantity',
    
    // WooCommerce Min Max Quantities
    '_wcmmq_s_min_quantity',
    '_wcmmq_s_max_quantity',
    '_wcmmq_s_product_step',
    
    // Custom theme/styling
    'add_custom_body_class',
);

echo "Meta keys que DEBERÍAN estar en uso:\n";
echo "=====================================\n";
foreach ( $used_meta_keys as $key ) {
    echo "  - $key\n";
}
echo "\n";

// Query database for all unique meta keys
global $wpdb;
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

echo "Total de meta keys personalizadas encontradas: " . count( $all_meta_keys ) . "\n\n";

// Identify unused meta keys
$unused_meta_keys = array();
$old_wcj_keys = array();

foreach ( $all_meta_keys as $meta_key ) {
    // Skip product attributes (attribute_*)
    if ( strpos( $meta_key, 'attribute_' ) === 0 ) {
        continue;
    }
    
    // Skip Elementor meta keys (_elementor*)
    if ( strpos( $meta_key, '_elementor' ) === 0 ) {
        continue;
    }
    
    // Skip standard WooCommerce meta keys
    if ( in_array( $meta_key, array(
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
        '_download_expiry', '_purchase_note', '_variation_description'
    ) ) ) {
        continue;
    }
    
    // Check if it's in the used list
    if ( ! in_array( $meta_key, $used_meta_keys ) ) {
        // Check if it's an old WCJ key
        if ( strpos( $meta_key, '_wcj_' ) === 0 || strpos( $meta_key, 'wcj_' ) === 0 ) {
            $old_wcj_keys[] = $meta_key;
        } else {
            $unused_meta_keys[] = $meta_key;
        }
    }
}

// Display unused meta keys
echo "=== META KEYS NO UTILIZADAS (pueden ser eliminadas) ===\n";
echo "========================================================\n";
if ( ! empty( $unused_meta_keys ) ) {
    foreach ( $unused_meta_keys as $key ) {
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
            $key
        ) );
        echo "  - $key (usado en $count productos)\n";
    }
} else {
    echo "  No se encontraron meta keys no utilizadas.\n";
}
echo "\n";

// Display old WCJ keys
if ( ! empty( $old_wcj_keys ) ) {
    echo "=== META KEYS ANTIGUAS DE WCJ (WooCommerce Jetpack) ===\n";
    echo "========================================================\n";
    foreach ( $old_wcj_keys as $key ) {
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
            $key
        ) );
        echo "  - $key (usado en $count productos)\n";
    }
    echo "\n";
}

// Check for products with our meta keys
echo "=== PRODUCTOS CON CUSTOM ADDONS HABILITADOS ===\n";
echo "================================================\n";
$products_with_addons = $wpdb->get_var(
    "SELECT COUNT(DISTINCT post_id) 
    FROM {$wpdb->postmeta} 
    WHERE meta_key = '_enable_custom_addons' 
    AND meta_value = 'yes'"
);
echo "Productos con quantity add-ons habilitados: $products_with_addons\n\n";

foreach ( $used_meta_keys as $key ) {
    if ( strpos( $key, '_enable_text_field_' ) === 0 ) {
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT post_id) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = %s 
            AND meta_value = 'yes'",
            $key
        ) );
        if ( $count > 0 ) {
            $field_name = str_replace( '_enable_text_field_', '', $key );
            echo "  - $field_name: $count productos\n";
        }
    }
}

echo "\n=== RESUMEN ===\n";
echo "===============\n";
echo "Meta keys en uso: " . count( $used_meta_keys ) . "\n";
echo "Meta keys no utilizadas: " . count( $unused_meta_keys ) . "\n";
echo "Meta keys antiguas de WCJ: " . count( $old_wcj_keys ) . "\n";
echo "Total a revisar: " . ( count( $unused_meta_keys ) + count( $old_wcj_keys ) ) . "\n";

echo "\n✅ Análisis completado.\n";
