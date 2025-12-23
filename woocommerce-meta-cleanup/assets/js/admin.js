(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Tab navigation
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            var target = $(this).attr('href');
            
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            $('.tab-content').removeClass('active');
            $(target).addClass('active');
        });

        // Analyze button
        $('#btn-analyze').on('click', function() {
            var $button = $(this);
            $button.prop('disabled', true).text('Analizando...');
            $('#loading-overlay').show();

            $.ajax({
                url: wcMetaCleanup.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wc_meta_cleanup_analyze',
                    nonce: wcMetaCleanup.nonce
                },
                success: function(response) {
                    if (response.success) {
                        displayAnalysisResults(response.data);
                        $('#analyze-results').slideDown();
                    } else {
                        alert('Error: ' + (response.data.message || 'Unknown error'));
                    }
                },
                error: function() {
                    alert('Error al analizar la base de datos');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Analizar Base de Datos');
                    $('#loading-overlay').hide();
                }
            });
        });

        // Backup button
        $('#btn-backup').on('click', function() {
            var $button = $(this);
            
            if (!confirm('¿Deseas crear un backup de las meta keys que serán eliminadas?')) {
                return;
            }

            $button.prop('disabled', true).text('Creando backup...');
            $('#loading-overlay').show();

            $.ajax({
                url: wcMetaCleanup.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wc_meta_cleanup_backup',
                    nonce: wcMetaCleanup.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var html = '<div class="success-message">';
                        html += '<h4>✅ Backup creado exitosamente</h4>';
                        html += '<p><strong>Archivo:</strong> ' + response.data.file + '</p>';
                        html += '<p><strong>Tamaño:</strong> ' + response.data.size + '</p>';
                        html += '<p><strong>Meta keys respaldadas:</strong> ' + response.data.count + '</p>';
                        html += '<p><strong>Ubicación:</strong> <code>' + response.data.path + '</code></p>';
                        html += '</div>';
                        $('#backup-result').html(html);
                    } else {
                        alert('Error: ' + (response.data.message || 'Unknown error'));
                    }
                },
                error: function() {
                    alert('Error al crear el backup');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Crear Backup');
                    $('#loading-overlay').hide();
                }
            });
        });

        // Confirm checkbox
        $('#confirm-delete').on('change', function() {
            $('#btn-delete').prop('disabled', !$(this).is(':checked'));
        });

        // Delete button
        $('#btn-delete').on('click', function() {
            var $button = $(this);
            
            if (!confirm('⚠️ ATENCIÓN: Esta acción eliminará permanentemente las meta keys no utilizadas.\n\n¿Estás seguro de que deseas continuar?')) {
                return;
            }

            if (!confirm('Esta acción es IRREVERSIBLE.\n\n¿Tienes un backup de la base de datos?\n\nEscribe SI para confirmar.')) {
                return;
            }

            $button.prop('disabled', true).text('Eliminando...');
            $('#loading-overlay').show();

            $.ajax({
                url: wcMetaCleanup.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wc_meta_cleanup_delete',
                    nonce: wcMetaCleanup.nonce
                },
                success: function(response) {
                    if (response.success) {
                        displayDeletionResults(response.data);
                        $('#confirm-delete').prop('checked', false);
                    } else {
                        alert('Error: ' + (response.data.message || 'Unknown error'));
                    }
                },
                error: function() {
                    alert('Error al eliminar las meta keys');
                },
                complete: function() {
                    $button.prop('disabled', true).text('Eliminar Meta Keys No Utilizadas');
                    $('#loading-overlay').hide();
                }
            });
        });

        function displayAnalysisResults(data) {
            var html = '';
            
            // Stats
            html += '<div class="stats-grid">';
            html += '<div class="stat-card">';
            html += '<h3>Total de Meta Keys</h3>';
            html += '<div class="stat-value">' + data.total_keys + '</div>';
            html += '</div>';
            html += '<div class="stat-card">';
            html += '<h3>Meta Keys Protegidas</h3>';
            html += '<div class="stat-value">' + data.used_keys.length + '</div>';
            html += '</div>';
            html += '<div class="stat-card">';
            html += '<h3>Meta Keys No Utilizadas</h3>';
            html += '<div class="stat-value">' + data.unused_keys.length + '</div>';
            html += '</div>';
            html += '</div>';

            // Unused keys table
            if (data.unused_keys.length > 0) {
                html += '<h4>Meta Keys No Utilizadas (serán eliminadas):</h4>';
                html += '<table class="results-table widefat">';
                html += '<thead><tr><th>Meta Key</th><th>Registros</th></tr></thead>';
                html += '<tbody>';
                data.unused_keys.forEach(function(item) {
                    html += '<tr>';
                    html += '<td><code>' + item.meta_key + '</code></td>';
                    html += '<td><span class="badge badge-warning">' + item.count + ' registros</span></td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
            } else {
                html += '<div class="success-message">';
                html += '<h4>✅ No se encontraron meta keys no utilizadas</h4>';
                html += '<p>Todas las meta keys en la base de datos están en uso o protegidas.</p>';
                html += '</div>';
            }

            $('#analyze-content').html(html);
        }

        function displayDeletionResults(data) {
            var html = '<div class="success-message">';
            html += '<h4>✅ Limpieza completada exitosamente</h4>';
            html += '<p><strong>Meta keys eliminadas:</strong> ' + data.total_keys + '</p>';
            html += '<p><strong>Total de registros eliminados:</strong> ' + data.total_records + '</p>';
            
            if (data.deleted_keys.length > 0) {
                html += '<div class="deleted-keys-list">';
                html += '<p><strong>Detalles:</strong></p>';
                html += '<ul>';
                data.deleted_keys.forEach(function(item) {
                    html += '<li>' + item.key + ' (' + item.records + ' registros)</li>';
                });
                html += '</ul>';
                html += '</div>';
            }
            
            html += '<p style="margin-top: 15px;">La tabla wp_postmeta ha sido optimizada.</p>';
            html += '</div>';
            
            $('#delete-result').html(html);
        }
    });

})(jQuery);
