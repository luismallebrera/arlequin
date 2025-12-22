import csv

# Read the CSV file
input_file = '/workspaces/arlequin/Productos-Export-Extracted-Fields-ON_OFF.csv'
output_file = '/workspaces/arlequin/Productos-Export-Extracted-Fields-ON_OFF-UPDATED.csv'

with open(input_file, 'r', encoding='utf-8') as f:
    reader = csv.DictReader(f)
    rows = list(reader)
    fieldnames = reader.fieldnames

# Process each row
for row in rows:
    # Check for NOMBRE in field titles
    has_nombre = False
    has_fecha = False
    has_localidad = False
    
    # Check all field_X_title columns
    for i in range(1, 9):  # fields 1 through 8
        field_title_key = f'field_{i}_title'
        if field_title_key in row and row[field_title_key]:
            title = row[field_title_key].strip().upper()
            
            if 'NOMBRE' in title:
                has_nombre = True
            if 'FECHA' in title:
                has_fecha = True
            if 'LOCALIDAD' in title:
                has_localidad = True
    
    # Set the enable fields
    row['_enable_text_field_nombre_persona'] = 'on' if has_nombre else 'off'
    row['_enable_text_field_fecha_evento'] = 'on' if has_fecha else 'off'
    row['_enable_text_field_localidad'] = 'on' if has_localidad else 'off'

# Write the updated CSV
with open(output_file, 'w', encoding='utf-8', newline='') as f:
    writer = csv.DictWriter(f, fieldnames=fieldnames)
    writer.writeheader()
    writer.writerows(rows)

print(f"Procesado completado. Archivo guardado en: {output_file}")
print(f"Total de productos procesados: {len(rows)}")

# Show summary statistics
nombre_on = sum(1 for row in rows if row['_enable_text_field_nombre_persona'] == 'on')
fecha_on = sum(1 for row in rows if row['_enable_text_field_fecha_evento'] == 'on')
localidad_on = sum(1 for row in rows if row['_enable_text_field_localidad'] == 'on')

print(f"\nResumen:")
print(f"Productos con NOMBRE activado: {nombre_on} / {len(rows)}")
print(f"Productos con FECHA activada: {fecha_on} / {len(rows)}")
print(f"Productos con LOCALIDAD activada: {localidad_on} / {len(rows)}")
