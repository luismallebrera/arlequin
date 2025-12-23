<?php
/**
 * Quick check script to verify database state after cleanup
 */

require_once( '/var/www/html/wp-load.php' );

if ( ! defined( 'ABSPATH' ) ) {
    die( 'WordPress not found. Please adjust the path to wp-load.php' );
}

echo "=== VERIFICACIÓN POST-LIMPIEZA ===\n\n";

global $wpdb;

// Count total postmeta records for products
$total_records = $wpdb->get_var( "
    SELECT COUNT(*) 
    FROM {$wpdb->postmeta} 
    WHERE post_id IN (
        SELECT ID FROM {$wpdb->posts} 
        WHERE post_type = 'product'
    )
" );

echo "📊 Total de registros en postmeta para productos: " . number_format( $total_records ) . "\n\n";

// Count custom meta keys
$custom_keys = $wpdb->get_var( "
    SELECT COUNT(DISTINCT meta_key) 
    FROM {$wpdb->postmeta} 
    WHERE post_id IN (
        SELECT ID FROM {$wpdb->posts} 
        WHERE post_type = 'product'
    )
    AND meta_key LIKE '\_%'
" );

echo "🔑 Meta keys personalizadas únicas: $custom_keys\n\n";

// Check our important meta keys
$our_keys = array(
    '_enable_custom_addons',
    '_enable_text_field_frase_texto',
    '_enable_text_field_numero_cuenta',
    '_enable_text_field_menu',
    '_enable_text_field_nombre_persona',
    '_enable_text_field_fecha_evento',
    '_wcj_product_input_fields',
    '_custom_related_field',
);

echo "Verificando meta keys importantes:\n";
echo "===================================\n";
foreach ( $our_keys as $key ) {
    $count = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(DISTINCT post_id) 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = %s",
        $key
    ) );
    
    $status = $count > 0 ? '✅' : '⚠️';
    echo "$status $key: $count productos\n";
}

echo "\n";

// Check for any remaining WCJ keys
$old_wcj = $wpdb->get_results( "
    SELECT DISTINCT meta_key, COUNT(*) as count
    FROM {$wpdb->postmeta} 
    WHERE post_id IN (
        SELECT ID FROM {$wpdb->posts} 
        WHERE post_type = 'product'
    )
    AND meta_key LIKE '%wcj%'
    AND meta_key != '_wcj_product_input_fields'
    GROUP BY meta_key
", ARRAY_A );

if ( ! empty( $old_wcj ) ) {
    echo "Meta keys de WCJ antiguas que aún existen:\n";
    echo "===========================================\n";
    foreach ( $old_wcj as $key_data ) {
        echo "  - {$key_data['meta_key']}: {$key_data['count']} registros\n";
    }
} else {
    echo "✅ No se encontraron meta keys antiguas de WCJ\n";
}

echo "\n";

// Table size
$table_size = $wpdb->get_row( "
    SELECT 
        ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS size_mb,
        TABLE_ROWS
    FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '{$wpdb->postmeta}'
" );

if ( $table_size ) {
    echo "💾 Tamaño de la tabla wp_postmeta: {$table_size->size_mb} MB\n";
    echo "📋 Filas en la tabla: " . number_format( $table_size->TABLE_ROWS ) . "\n";
}

echo "\n✅ Verificación completada.\n";
