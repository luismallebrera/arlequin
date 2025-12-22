import csv

# Read the updated CSV file
input_file = '/workspaces/arlequin/Productos-Export-Extracted-Fields-ON_OFF-UPDATED.csv'

print("="*100)
print("MUESTRA DE PRODUCTOS PROCESADOS")
print("="*100)

with open(input_file, 'r', encoding='utf-8') as f:
    reader = csv.DictReader(f)
    
    count = 0
    for row in reader:
        if count >= 10:  # Show first 10 products
            break
            
        print(f"\nProducto ID: {row['ID']}")
        print(f"Nombre: {row['Title'][:60]}...")
        print(f"\nCampos encontrados:")
        
        # Show all field titles
        fields_found = []
        for i in range(1, 9):
            field_title = row.get(f'field_{i}_title', '').strip()
            if field_title:
                fields_found.append(field_title)
        
        print(f"  {', '.join(fields_found)}")
        
        print(f"\nEstado de enable fields:")
        print(f"  _enable_text_field_nombre_persona: {row['_enable_text_field_nombre_persona']}")
        print(f"  _enable_text_field_fecha_evento: {row['_enable_text_field_fecha_evento']}")
        print(f"  _enable_text_field_localidad: {row['_enable_text_field_localidad']}")
        print("-"*100)
        
        count += 1

print("\n" + "="*100)
print("RESUMEN COMPLETO")
print("="*100)

# Count statistics
with open(input_file, 'r', encoding='utf-8') as f:
    reader = csv.DictReader(f)
    rows = list(reader)

total = len(rows)
nombre_on = sum(1 for row in rows if row['_enable_text_field_nombre_persona'] == 'on')
nombre_off = total - nombre_on
fecha_on = sum(1 for row in rows if row['_enable_text_field_fecha_evento'] == 'on')
fecha_off = total - fecha_on
localidad_on = sum(1 for row in rows if row['_enable_text_field_localidad'] == 'on')
localidad_off = total - localidad_on

print(f"\nTotal de productos procesados: {total}")
print(f"\n_enable_text_field_nombre_persona:")
print(f"  ON:  {nombre_on} ({nombre_on/total*100:.1f}%)")
print(f"  OFF: {nombre_off} ({nombre_off/total*100:.1f}%)")

print(f"\n_enable_text_field_fecha_evento:")
print(f"  ON:  {fecha_on} ({fecha_on/total*100:.1f}%)")
print(f"  OFF: {fecha_off} ({fecha_off/total*100:.1f}%)")

print(f"\n_enable_text_field_localidad:")
print(f"  ON:  {localidad_on} ({localidad_on/total*100:.1f}%)")
print(f"  OFF: {localidad_off} ({localidad_off/total*100:.1f}%)")
