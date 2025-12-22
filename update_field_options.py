#!/usr/bin/env python3
"""
Script para actualizar los campos _text_field_option_nombre_persona y 
_text_field_option_fecha_evento en Productos-Export-23.csv con los datos
de NOMBRE y FECHAS de Productos-Export-Extracted-Fields6.csv
"""

import csv
import os

def main():
    # Archivos de entrada y salida
    source_file = '/workspaces/arlequin/Productos-Export-Extracted-Fields6.csv'
    target_file = '/workspaces/arlequin/Productos-Export-23.csv'
    output_file = '/workspaces/arlequin/Productos-Export-23-updated.csv'
    
    # Leer el archivo fuente y crear un diccionario con los datos
    field_data = {}
    
    with open(source_file, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            product_id = row['ID']
            
            # Buscar el campo que contiene "NOMBRE"
            nombre_field = None
            fecha_field = None
            
            # Revisar todos los campos field_X_title
            for i in range(1, 9):  # field_1 a field_8
                title_key = f'field_{i}_title'
                enabled_key = f'field_{i}_enabled'
                
                if title_key in row and row[title_key]:
                    title = row[title_key].upper()
                    enabled = row[enabled_key]
                    
                    # Solo tomar en cuenta si está habilitado ("on")
                    if enabled == 'on':
                        # Buscar campo de NOMBRE
                        if 'NOMBRE' in title and not nombre_field:
                            nombre_field = row[title_key]
                        
                        # Buscar campo de FECHA
                        if 'FECHA' in title and not fecha_field:
                            fecha_field = row[title_key]
            
            # Limpiar los textos de fecha quitando preposiciones
            if fecha_field:
                fecha_field = fecha_field.replace('FECHA DE LA ', 'FECHA ')
                fecha_field = fecha_field.replace('FECHA DEL ', 'FECHA ')
                fecha_field = fecha_field.replace('FECHA DE ', 'FECHA ')
            
            field_data[product_id] = {
                'nombre': nombre_field or '',
                'fecha': fecha_field or ''
            }
    
    print(f"Datos extraídos de {len(field_data)} productos")
    
    # Leer el archivo destino y actualizarlo
    updated_rows = []
    matches = 0
    
    with open(target_file, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        fieldnames = reader.fieldnames
        
        for row in reader:
            product_id = row['ID']
            
            # Si hay datos para este producto, actualizarlos
            if product_id in field_data:
                row['_text_field_option_nombre_persona'] = field_data[product_id]['nombre']
                row['_text_field_option_fecha_evento'] = field_data[product_id]['fecha']
                
                if field_data[product_id]['nombre'] or field_data[product_id]['fecha']:
                    matches += 1
                    print(f"ID {product_id}: nombre='{field_data[product_id]['nombre']}', fecha='{field_data[product_id]['fecha']}'")
            
            updated_rows.append(row)
    
    # Escribir el archivo de salida
    with open(output_file, 'w', encoding='utf-8', newline='') as f:
        writer = csv.DictWriter(f, fieldnames=fieldnames, quoting=csv.QUOTE_ALL)
        writer.writeheader()
        writer.writerows(updated_rows)
    
    print(f"\n✓ Archivo actualizado: {output_file}")
    print(f"✓ Productos actualizados: {matches}")

if __name__ == '__main__':
    main()
