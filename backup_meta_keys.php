<?php
/**
 * Script to BACKUP meta keys before deletion
 * This creates a SQL file that can be used to restore the data if needed
 */

// Load WordPress
require_once( '/var/www/html/wp-load.php' );

if ( ! defined( 'ABSPATH' ) ) {
    die( 'WordPress not found. Please adjust the path to wp-load.php' );
}

$backup_file = __DIR__ . '/meta_keys_backup_' . date( 'Y-m-d_His' ) . '.sql';

echo "=== CREANDO BACKUP DE META KEYS ===\n\n";

// Meta keys that will be kept (not backed up as they won't be deleted)
$used_meta_keys = array(
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
    '_custom_related_field',
    '_min_quantity',
    '_wcmmq_s_min_quantity',
    '_wcmmq_s_max_quantity',
    '_wcmmq_s_product_step',
    'add_custom_body_class',
);

$woocommerce_keys = array(
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

$keep_meta_keys = array_merge( $used_meta_keys, $woocommerce_keys );

global $wpdb;

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

// Create backup file
$sql_content = "-- WordPress WooCommerce Meta Keys Backup\n";
$sql_content .= "-- Generated: " . date( 'Y-m-d H:i:s' ) . "\n";
$sql_content .= "-- Database: " . DB_NAME . "\n\n";
$sql_content .= "-- This file contains meta keys that will be deleted\n";
$sql_content .= "-- You can restore them by running this SQL file\n\n";

$backed_up_count = 0;
$backed_up_records = 0;

foreach ( $all_meta_keys as $meta_key ) {
    // Skip product attributes (attribute_*)
    if ( strpos( $meta_key, 'attribute_' ) === 0 ) {
        continue;
    }
    
    // Skip Elementor meta keys (_elementor*)
    if ( strpos( $meta_key, '_elementor' ) === 0 ) {
        continue;
    }
    
    // Skip if it's in the keep list
    if ( in_array( $meta_key, $keep_meta_keys ) ) {
        continue;
    }
    
    // Get all records for this meta key
    $records = $wpdb->get_results( $wpdb->prepare(
        "SELECT meta_id, post_id, meta_key, meta_value 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = %s",
        $meta_key
    ), ARRAY_A );
    
    if ( ! empty( $records ) ) {
        $sql_content .= "\n-- Meta Key: $meta_key (" . count( $records ) . " records)\n";
        
        foreach ( $records as $record ) {
            $meta_value = $wpdb->prepare( '%s', $record['meta_value'] );
            $meta_value = trim( $meta_value, "'" );
            $meta_value = str_replace( "'", "\\'", $meta_value );
            
            $sql_content .= "INSERT INTO {$wpdb->postmeta} (meta_id, post_id, meta_key, meta_value) VALUES (";
            $sql_content .= "{$record['meta_id']}, {$record['post_id']}, '{$record['meta_key']}', '{$meta_value}');\n";
            
            $backed_up_records++;
        }
        
        $backed_up_count++;
        echo "  ✅ Backed up: $meta_key (" . count( $records ) . " records)\n";
    }
}

// Write to file
file_put_contents( $backup_file, $sql_content );

echo "\n=== RESUMEN DEL BACKUP ===\n";
echo "==========================\n";
echo "Meta keys respaldadas: $backed_up_count\n";
echo "Total de registros: $backed_up_records\n";
echo "Archivo de backup: $backup_file\n";
echo "Tamaño: " . number_format( filesize( $backup_file ) / 1024, 2 ) . " KB\n";

echo "\n✅ Backup completado exitosamente!\n";
echo "ℹ️  Guarda este archivo en un lugar seguro antes de eliminar las meta keys.\n";
