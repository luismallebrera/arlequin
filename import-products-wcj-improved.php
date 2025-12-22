<?php
/**
 * Import WooCommerce Products with WCJ Input Fields
 * 
 * This script imports products and rebuilds the serialized WCJ input fields array
 * 
 * Usage: Upload CSV file and access yoursite.com/import-products-wcj-improved.php
 */

// Load WordPress
require_once('wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Import Products with WCJ Input Fields</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .success { color: green; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; margin: 20px 0; }
        .error { color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 20px 0; }
        .info { color: #004085; background: #cce5ff; border: 1px solid #b8daff; padding: 15px; margin: 20px 0; }
        input[type="file"] { margin: 20px 0; padding: 10px; }
        button { padding: 12px 30px; background: #0073aa; color: white; border: none; cursor: pointer; font-size: 16px; }
        button:hover { background: #005177; }
        .progress { margin: 10px 0; }
        h1 { color: #0073aa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📥 Import Products with WCJ Input Fields</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $file = $_FILES['csv_file']['tmp_name'];
            
            if (($handle = fopen($file, 'r')) !== FALSE) {
                // Skip BOM if present
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }
                
                // Read header row
                $headers = fgetcsv($handle);
                
                $imported = 0;
                $updated = 0;
                $errors = array();
                
                while (($data = fgetcsv($handle)) !== FALSE) {
                    $row = array_combine($headers, $data);
                    
                    try {
                        // Check if product exists by SKU
                        $product_id = null;
                        if (!empty($row['SKU'])) {
                            $product_id = wc_get_product_id_by_sku($row['SKU']);
                        }
                        
                        if ($product_id) {
                            // Update existing product
                            $product = wc_get_product($product_id);
                            $updated++;
                        } else {
                            // Create new product
                            $product = new WC_Product_Simple();
                            $imported++;
                        }
                        
                        // Set basic product data
                        if (!empty($row['Name'])) {
                            $product->set_name($row['Name']);
                        }
                        if (!empty($row['SKU'])) {
                            $product->set_sku($row['SKU']);
                        }
                        if (!empty($row['Regular Price'])) {
                            $product->set_regular_price($row['Regular Price']);
                        }
                        if (!empty($row['Sale Price'])) {
                            $product->set_sale_price($row['Sale Price']);
                        }
                        if (!empty($row['Stock Status'])) {
                            $product->set_stock_status($row['Stock Status']);
                        }
                        if (!empty($row['Short Description'])) {
                            $product->set_short_description($row['Short Description']);
                        }
                        if (!empty($row['Description'])) {
                            $product->set_description($row['Description']);
                        }
                        if (!empty($row['Published'])) {
                            $product->set_status($row['Published']);
                        }
                        
                        // Save product to get ID
                        $product_id = $product->save();
                        
                        // Set categories
                        if (!empty($row['Categories'])) {
                            $categories = explode('|', $row['Categories']);
                            $cat_ids = array();
                            foreach ($categories as $cat_name) {
                                $cat_name = trim($cat_name);
                                if (!empty($cat_name)) {
                                    $term = get_term_by('name', $cat_name, 'product_cat');
                                    if (!$term) {
                                        $term = wp_insert_term($cat_name, 'product_cat');
                                        if (!is_wp_error($term)) {
                                            $cat_ids[] = $term['term_id'];
                                        }
                                    } else {
                                        $cat_ids[] = $term->term_id;
                                    }
                                }
                            }
                            if (!empty($cat_ids)) {
                                wp_set_post_terms($product_id, $cat_ids, 'product_cat');
                            }
                        }
                        
                        // Set tags
                        if (!empty($row['Tags'])) {
                            $tags = explode('|', $row['Tags']);
                            $tags = array_map('trim', $tags);
                            $tags = array_filter($tags);
                            if (!empty($tags)) {
                                wp_set_post_terms($product_id, $tags, 'product_tag');
                            }
                        }
                        
                        // Rebuild WCJ Input Fields serialized array
                        $wcj_fields = array();
                        
                        // Get total number of fields
                        if (isset($row['wcj_local_total_number']) && !empty($row['wcj_local_total_number'])) {
                            $wcj_fields['local_total_number'] = $row['wcj_local_total_number'];
                            $total_fields = intval($row['wcj_local_total_number']);
                        } else {
                            // Try to detect from columns
                            $total_fields = 0;
                            foreach ($headers as $header) {
                                if (preg_match('/wcj_enabled_local_(\d+)/', $header, $matches)) {
                                    $field_num = intval($matches[1]);
                                    if ($field_num > $total_fields) {
                                        $total_fields = $field_num;
                                    }
                                }
                            }
                            if ($total_fields > 0) {
                                $wcj_fields['local_total_number'] = $total_fields;
                            }
                        }
                        
                        // Build the fields array
                        for ($i = 1; $i <= $total_fields; $i++) {
                            $field_keys = array(
                                'enabled', 'title', 'placeholder', 'type', 'required', 
                                'required_message', 'order', 'class'
                            );
                            
                            foreach ($field_keys as $key) {
                                $col_name = "wcj_{$key}_local_{$i}";
                                if (isset($row[$col_name]) && $row[$col_name] !== '') {
                                    $wcj_fields["{$key}_local_{$i}"] = $row[$col_name];
                                }
                            }
                        }
                        
                        // Save the WCJ fields as serialized array
                        if (!empty($wcj_fields)) {
                            update_post_meta($product_id, '_wcj_product_input_fields', $wcj_fields);
                        }
                        
                    } catch (Exception $e) {
                        $sku = isset($row['SKU']) ? $row['SKU'] : 'Unknown';
                        $errors[] = "Error importing SKU {$sku}: " . $e->getMessage();
                    }
                }
                
                fclose($handle);
                
                echo "<div class='success'>";
                echo "<h3>✅ Import Complete!</h3>";
                echo "<p><strong>Products imported:</strong> $imported</p>";
                echo "<p><strong>Products updated:</strong> $updated</p>";
                echo "<p><strong>Total processed:</strong> " . ($imported + $updated) . "</p>";
                echo "</div>";
                
                if (!empty($errors)) {
                    echo "<div class='error'>";
                    echo "<h4>⚠️ Errors encountered:</h4>";
                    foreach ($errors as $error) {
                        echo "<p>• $error</p>";
                    }
                    echo "</div>";
                }
            } else {
                echo "<div class='error'>❌ Could not read CSV file</div>";
            }
        }
        ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="info">
                <p><strong>📄 Upload your exported CSV file:</strong></p>
                <p>Make sure you've exported using the improved export script.</p>
            </div>
            <input type="file" name="csv_file" accept=".csv" required>
            <br>
            <button type="submit">🚀 Start Import</button>
        </form>
        
        <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-left: 4px solid #0073aa;">
            <h3>📋 Instructions:</h3>
            <ol>
                <li>Export products from your source site using <code>export-products-wcj-fields-improved.php</code></li>
                <li>Make sure "Booster for WooCommerce" plugin is installed and active on this site</li>
                <li>Upload the exported CSV file using the form above</li>
                <li>Click "Start Import" and wait for completion</li>
                <li><strong>Delete this script from your server after use for security!</strong></li>
            </ol>
            
            <h4>⚙️ What gets imported:</h4>
            <ul>
                <li>Product name, SKU, prices, stock status</li>
                <li>Categories and tags</li>
                <li>Product descriptions</li>
                <li>All WCJ Product Input Fields configuration (title, placeholder, type, required, etc.)</li>
            </ul>
        </div>
    </div>
</body>
</html>
