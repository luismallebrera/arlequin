<?php
/**
 * PDF Generator Class
 * Uses TCPDF library to generate invoice PDFs
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WC_Invoice_PDF_Generator {
    
    /**
     * Generate invoice PDF
     * 
     * @param int $order_id Order ID
     * @param bool $save_to_temp Save to temp directory instead of forcing download
     * @return string|void File path if saved to temp, void if downloaded
     */
    public function generate($order_id, $save_to_temp = false) {
        // Get order
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return false;
        }
        
        // Load TCPDF library
        require_once WC_INVOICE_PDF_PLUGIN_DIR . 'lib/tcpdf/tcpdf.php';
        
        // Create PDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('WooCommerce Invoice PDF');
        $pdf->SetAuthor(get_bloginfo('name'));
        $pdf->SetTitle(sprintf(__('Factura #%s', 'wc-invoice-pdf'), $order->get_order_number()));
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        
        // Add page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Generate HTML content
        $html = $this->generate_invoice_html($order);
        
        // Write HTML
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // File name
        $filename = sprintf('factura-%s.pdf', $order->get_order_number());
        
        if ($save_to_temp) {
            // Save to temp directory
            $temp_dir = get_temp_dir();
            $file_path = $temp_dir . $filename;
            $pdf->Output($file_path, 'F');
            return $file_path;
        } else {
            // Force download
            $pdf->Output($filename, 'D');
            exit;
        }
    }
    
    /**
     * Generate invoice HTML content
     * 
     * @param WC_Order $order Order object
     * @return string HTML content
     */
    private function generate_invoice_html($order) {
        $html = '';
        
        // Header with logo and company info
        $html .= '<table style="width: 100%; margin-bottom: 20px;">';
        $html .= '<tr>';
        
        // Logo
        $logo_id = get_theme_mod('custom_logo');
        if ($logo_id) {
            $logo_url = wp_get_attachment_image_src($logo_id, 'full');
            if ($logo_url) {
                $html .= '<td style="width: 40%;">';
                $html .= '<img src="' . esc_url($logo_url[0]) . '" style="max-width: 150px; max-height: 80px;">';
                $html .= '</td>';
            }
        } else {
            $html .= '<td style="width: 40%;"><h1>' . get_bloginfo('name') . '</h1></td>';
        }
        
        // Invoice info
        $html .= '<td style="width: 60%; text-align: right;">';
        $html .= '<h2 style="color: #333; margin: 0 0 10px 0;">FACTURA</h2>';
        $html .= '<p style="margin: 0; line-height: 1.6;">';
        $html .= '<strong>Número:</strong> #' . $order->get_order_number() . '<br>';
        $html .= '<strong>Fecha:</strong> ' . $order->get_date_created()->date_i18n('d/m/Y') . '<br>';
        $html .= '<strong>Estado:</strong> ' . wc_get_order_status_name($order->get_status());
        $html .= '</p>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        
        // Billing and shipping addresses
        $html .= '<table style="width: 100%; margin-bottom: 20px; border-collapse: collapse;">';
        $html .= '<tr>';
        
        // Billing address
        $html .= '<td style="width: 50%; padding: 10px; background-color: #f8f8f8; vertical-align: top;">';
        $html .= '<h3 style="margin: 0 0 10px 0; color: #333;">Dirección de facturación</h3>';
        $html .= '<p style="margin: 0; line-height: 1.6;">';
        $html .= $order->get_formatted_billing_address() ? $order->get_formatted_billing_address() : '-';
        if ($order->get_billing_email()) {
            $html .= '<br><strong>Email:</strong> ' . $order->get_billing_email();
        }
        if ($order->get_billing_phone()) {
            $html .= '<br><strong>Teléfono:</strong> ' . $order->get_billing_phone();
        }
        $html .= '</p>';
        $html .= '</td>';
        
        // Shipping address
        $html .= '<td style="width: 50%; padding: 10px; background-color: #f0f0f0; vertical-align: top;">';
        $html .= '<h3 style="margin: 0 0 10px 0; color: #333;">Dirección de envío</h3>';
        $html .= '<p style="margin: 0; line-height: 1.6;">';
        $html .= $order->get_formatted_shipping_address() ? $order->get_formatted_shipping_address() : '-';
        $html .= '</p>';
        $html .= '</td>';
        
        $html .= '</tr>';
        $html .= '</table>';
        
        // Order items
        $html .= '<h3 style="margin: 20px 0 10px 0; color: #333;">Productos</h3>';
        $html .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">';
        
        // Table header
        $html .= '<thead>';
        $html .= '<tr style="background-color: #333; color: #fff;">';
        $html .= '<th style="padding: 10px; text-align: left;">Imagen</th>';
        $html .= '<th style="padding: 10px; text-align: left;">Producto</th>';
        $html .= '<th style="padding: 10px; text-align: center;">Cantidad</th>';
        $html .= '<th style="padding: 10px; text-align: right;">Precio</th>';
        $html .= '<th style="padding: 10px; text-align: right;">Total</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        
        // Table body
        $html .= '<tbody>';
        
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            
            $html .= '<tr style="border-bottom: 1px solid #ddd;">';
            
            // Product image
            $html .= '<td style="padding: 10px; width: 80px;">';
            if ($product && $product->get_image_id()) {
                $image_url = wp_get_attachment_image_src($product->get_image_id(), 'thumbnail');
                if ($image_url) {
                    $html .= '<img src="' . esc_url($image_url[0]) . '" style="max-width: 60px; max-height: 60px;">';
                }
            }
            $html .= '</td>';
            
            // Product name and meta
            $html .= '<td style="padding: 10px; vertical-align: top;">';
            $html .= '<strong>' . $item->get_name() . '</strong>';
            
            // Product SKU
            if ($product && $product->get_sku()) {
                $html .= '<br><small style="color: #666;">SKU: ' . $product->get_sku() . '</small>';
            }
            
            // Item meta data
            $meta_data = $item->get_meta_data();
            if (!empty($meta_data)) {
                $html .= '<br><small style="color: #666;">';
                foreach ($meta_data as $meta) {
                    $data = $meta->get_data();
                    // Skip hidden meta
                    if (strpos($data['key'], '_') === 0) {
                        continue;
                    }
                    $html .= '<br>' . esc_html($data['key']) . ': ' . esc_html($data['value']);
                }
                $html .= '</small>';
            }
            
            $html .= '</td>';
            
            // Quantity
            $html .= '<td style="padding: 10px; text-align: center; vertical-align: top;">';
            $html .= $item->get_quantity();
            $html .= '</td>';
            
            // Price
            $html .= '<td style="padding: 10px; text-align: right; vertical-align: top;">';
            $html .= wc_price($order->get_item_subtotal($item, false, false));
            $html .= '</td>';
            
            // Total
            $html .= '<td style="padding: 10px; text-align: right; vertical-align: top;">';
            $html .= wc_price($order->get_line_subtotal($item, false, false));
            $html .= '</td>';
            
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        
        // Order totals
        $html .= '<table style="width: 100%; margin-bottom: 20px;">';
        $html .= '<tr>';
        $html .= '<td style="width: 60%;"></td>';
        $html .= '<td style="width: 40%;">';
        
        $html .= '<table style="width: 100%; border-collapse: collapse;">';
        
        // Subtotal
        $html .= '<tr>';
        $html .= '<td style="padding: 5px; text-align: left;"><strong>Subtotal:</strong></td>';
        $html .= '<td style="padding: 5px; text-align: right;">' . wc_price($order->get_subtotal()) . '</td>';
        $html .= '</tr>';
        
        // Shipping
        if ($order->get_shipping_total() > 0) {
            $html .= '<tr>';
            $html .= '<td style="padding: 5px; text-align: left;"><strong>Envío:</strong></td>';
            $html .= '<td style="padding: 5px; text-align: right;">' . wc_price($order->get_shipping_total()) . '</td>';
            $html .= '</tr>';
        }
        
        // Tax
        if ($order->get_total_tax() > 0) {
            $html .= '<tr>';
            $html .= '<td style="padding: 5px; text-align: left;"><strong>Impuestos:</strong></td>';
            $html .= '<td style="padding: 5px; text-align: right;">' . wc_price($order->get_total_tax()) . '</td>';
            $html .= '</tr>';
        }
        
        // Discount
        if ($order->get_total_discount() > 0) {
            $html .= '<tr>';
            $html .= '<td style="padding: 5px; text-align: left;"><strong>Descuento:</strong></td>';
            $html .= '<td style="padding: 5px; text-align: right;">-' . wc_price($order->get_total_discount()) . '</td>';
            $html .= '</tr>';
        }
        
        // Total
        $html .= '<tr style="background-color: #333; color: #fff;">';
        $html .= '<td style="padding: 10px; text-align: left;"><strong>TOTAL:</strong></td>';
        $html .= '<td style="padding: 10px; text-align: right;"><strong>' . wc_price($order->get_total()) . '</strong></td>';
        $html .= '</tr>';
        
        $html .= '</table>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        
        // Payment method
        $html .= '<div style="margin-top: 20px; padding: 10px; background-color: #f8f8f8;">';
        $html .= '<strong>Método de pago:</strong> ' . $order->get_payment_method_title();
        $html .= '</div>';
        
        // Customer note
        if ($order->get_customer_note()) {
            $html .= '<div style="margin-top: 10px; padding: 10px; background-color: #fff3cd; border-left: 4px solid #ffc107;">';
            $html .= '<strong>Nota del cliente:</strong><br>';
            $html .= nl2br(esc_html($order->get_customer_note()));
            $html .= '</div>';
        }
        
        // Order meta data
        $order_meta = $order->get_meta_data();
        if (!empty($order_meta)) {
            $html .= '<h3 style="margin: 20px 0 10px 0; color: #333;">Información adicional del pedido</h3>';
            $html .= '<table style="width: 100%; border-collapse: collapse;">';
            
            foreach ($order_meta as $meta) {
                $data = $meta->get_data();
                // Skip hidden meta and internal WooCommerce meta
                if (strpos($data['key'], '_') === 0) {
                    continue;
                }
                
                $html .= '<tr style="border-bottom: 1px solid #ddd;">';
                $html .= '<td style="padding: 8px; width: 40%; background-color: #f8f8f8;"><strong>' . esc_html($data['key']) . '</strong></td>';
                $html .= '<td style="padding: 8px; width: 60%;">' . esc_html($data['value']) . '</td>';
                $html .= '</tr>';
            }
            
            $html .= '</table>';
        }
        
        // Footer
        $html .= '<div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #333; text-align: center; color: #666; font-size: 9px;">';
        $html .= get_bloginfo('name') . ' - ' . get_bloginfo('description');
        if (get_bloginfo('admin_email')) {
            $html .= '<br>' . get_bloginfo('admin_email');
        }
        $html .= '<br>Factura generada el ' . date_i18n('d/m/Y H:i:s');
        $html .= '</div>';
        
        return $html;
    }
}
