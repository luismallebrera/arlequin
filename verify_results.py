import csv

# Read the corrected CSV file
input_file = '/workspaces/arlequin/Productos-Export-Extracted-Fields-ON_OFF-CORRECTED.csv'

print("="*120)
print("VERIFICACIÓN DE PRODUCTOS PROCESADOS")
print("="*120)

with open(input_file, 'r', encoding='utf-8') as f:
    reader = csv.DictReader(f)
    
    count = 0
    for row in reader:
        if count >= 5:  # Show first 5 products
            break
            
        print(f"\nProducto ID: {row['ID']}")
        print(f"Nombre: {row['Title'][:70]}")
        print(f"\nCampos del producto (field_X_title → field_X_enabled):")
        
        # Show all field titles and their enabled status
        for i in range(1, 9):
            field_title = row.get(f'field_{i}_title', '').strip()
            field_enabled = row.get(f'field_{i}_enabled', '').strip()
            if field_title:
                print(f"  field_{i}: {field_title:50} → {field_enabled}")
        
        print(f"\nValores copiados a enable fields:")
        enable_fields = [
            '_enable_text_field_nombre_persona',
            '_enable_text_field_fecha_evento',
            '_enable_text_field_localidad',
            '_enable_text_field_iglesia',
            '_enable_text_field_hora_misa',
            '_enable_text_field_restaurante',
            '_enable_text_field_observaciones',
            '_enable_text_field_hora_evento',
            '_enable_text_field_inicial'
        ]
        
        for field in enable_fields:
            value = row.get(field, '')
            if value == 'on':
                print(f"  {field:45} → {value}")
        
        print("-"*120)
        count += 1
