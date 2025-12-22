import csv

# Mapping between field titles and enable columns
FIELD_MAPPING = {
    'NOMBRE': '_enable_text_field_nombre_persona',
    'FECHA': '_enable_text_field_fecha_evento',
    'LOCALIDAD': '_enable_text_field_localidad',
    'IGLESIA': '_enable_text_field_iglesia',
    'HORA MISA': '_enable_text_field_hora_misa',
    'RESTAURANTE/ LUGAR DE CELEBRACIÓN': '_enable_text_field_restaurante',
    'RESTAURANTE/ LUGAR DE CELEBRACIÓN ': '_enable_text_field_restaurante',  # with trailing space
    'OBSERVACIONES': '_enable_text_field_observaciones',
    'HORA EVENTO': '_enable_text_field_hora_evento',
    'HORA DEL EVENTO': '_enable_text_field_hora_evento',  # variant
    'INICIAL': '_enable_text_field_inicial'
}

# Read the CSV file
input_file = '/workspaces/arlequin/Productos-Export-Extracted-Fields-ON_OFF.csv'
output_file = '/workspaces/arlequin/Productos-Export-Extracted-Fields-ON_OFF-CORRECTED.csv'

with open(input_file, 'r', encoding='utf-8') as f:
    reader = csv.DictReader(f)
    rows = list(reader)
    fieldnames = reader.fieldnames

# Process each row
for row in rows:
    # Initialize all enable fields to 'off'
    for enable_field in FIELD_MAPPING.values():
        if enable_field in row:
            row[enable_field] = 'off'
    
    # Check all field_X_title columns and copy the enabled value
    for i in range(1, 9):  # fields 1 through 8
        field_title_key = f'field_{i}_title'
        field_enabled_key = f'field_{i}_enabled'
        
        if field_title_key in row and row[field_title_key]:
            title = row[field_title_key].strip()
            
            # Check if this title matches any of our mappings
            if title in FIELD_MAPPING:
                enable_field = FIELD_MAPPING[title]
                if enable_field in row:
                    # Copy the enabled value (on/off)
                    enabled_value = row[field_enabled_key].strip() if row[field_enabled_key] else 'off'
                    row[enable_field] = enabled_value

# Write the updated CSV
with open(output_file, 'w', encoding='utf-8', newline='') as f:
    writer = csv.DictWriter(f, fieldnames=fieldnames)
    writer.writeheader()
    writer.writerows(rows)

print(f"Procesado completado. Archivo guardado en: {output_file}")
print(f"Total de productos procesados: {len(rows)}")

# Show summary statistics
stats = {}
for enable_field in set(FIELD_MAPPING.values()):
    on_count = sum(1 for row in rows if row.get(enable_field, '') == 'on')
    off_count = sum(1 for row in rows if row.get(enable_field, '') == 'off')
    stats[enable_field] = {'on': on_count, 'off': off_count}

print(f"\nResumen:")
for field_name, counts in sorted(stats.items()):
    total = counts['on'] + counts['off']
    print(f"{field_name}:")
    print(f"  ON:  {counts['on']} ({counts['on']/total*100:.1f}% de {total})")
    print(f"  OFF: {counts['off']} ({counts['off']/total*100:.1f}% de {total})")
