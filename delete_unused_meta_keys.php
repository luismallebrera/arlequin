<?php
/**
 * Script to DELETE unused meta keys from WordPress database
 * 
 * WARNING: This will permanently delete meta data from your database.
 * ALWAYS make a backup before running this script!
 * 
 * Usage: php delete_unused_meta_keys.php [--dry-run] [--confirm]
 */

// Load WordPress
require_once( '/var/www/html/wp-load.php' );

if ( ! defined( 'ABSPATH' ) ) {
    die( 'WordPress not found. Please adjust the path to wp-load.php' );
}

// Check command line arguments
$dry_run = in_array( '--dry-run', $argv );
$confirmed = in_array( '--confirm', $argv );

if ( ! $dry_run && ! $confirmed ) {
    echo "⚠️  ERROR: You must use either --dry-run or --confirm\n\n";
    echo "Usage:\n";
    echo "  php delete_unused_meta_keys.php --dry-run    (see what would be deleted)\n";
    echo "  php delete_unused_meta_keys.php --confirm    (actually delete the data)\n\n";
    echo "⚠️  ALWAYS backup your database before using --confirm!\n";
    exit( 1 );
}

echo "=== " . ( $dry_run ? "SIMULACIÓN" : "ELIMINACIÓN REAL" ) . " DE META KEYS NO UTILIZADAS ===\n\n";

if ( $dry_run ) {
    echo "ℹ️  Modo DRY-RUN: No se eliminará nada, solo se mostrará lo que se haría.\n\n";
} else {
    echo "⚠️  MODO CONFIRMADO: Se eliminarán las meta keys de la base de datos!\n\n";
    sleep( 2 );
}

// Meta keys that should be kept (currently in use)
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

// Standard WooCommerce meta keys to keep
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

// Combine all keys to keep
$keep_meta_keys = array_merge( $used_meta_keys, $woocommerce_keys );

global $wpdb;

// Get all custom meta keys from products
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

$total_deleted = 0;
$total_records = 0;

echo "Meta keys que se " . ( $dry_run ? "eliminarían" : "van a eliminar" ) . ":\n";
echo "====================================================================\n\n";

foreach ( $all_meta_keys as $meta_data ) {
    $meta_key = $meta_data['meta_key'];
    $count = $meta_data['count'];
    
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
    
    echo "  🗑️  $meta_key ($count registros)\n";
    
    if ( ! $dry_run ) {
        // Actually delete the meta key
        $deleted = $wpdb->delete(
            $wpdb->postmeta,
            array( 'meta_key' => $meta_key ),
            array( '%s' )
        );
        
        if ( $deleted ) {
            echo "      ✅ Eliminados $deleted registros\n";
            $total_records += $deleted;
        } else {
            echo "      ⚠️  Error al eliminar\n";
        }
    }
    
    $total_deleted++;
}

echo "\n";
echo "=== RESUMEN ===\n";
echo "===============\n";
echo "Meta keys " . ( $dry_run ? "que se eliminarían" : "eliminadas" ) . ": $total_deleted\n";
if ( ! $dry_run ) {
    echo "Total de registros eliminados: $total_records\n";
}

if ( $dry_run ) {
    echo "\n✅ Simulación completada. Nada fue eliminado.\n";
    echo "ℹ️  Para eliminar realmente, ejecuta: php delete_unused_meta_keys.php --confirm\n";
} else {
    echo "\n✅ Eliminación completada.\n";
    echo "ℹ️  Se recomienda optimizar las tablas de la base de datos.\n";
}
