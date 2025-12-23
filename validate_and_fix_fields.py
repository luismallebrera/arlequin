import csv

input_file = '/workspaces/arlequin/Productos-Export-Extracted-Fields-BODA.csv'
output_file = '/workspaces/arlequin/Productos-Export-Extracted-Fields-BODA.csv'

# Leer el CSV
rows = []
with open(input_file, 'r', encoding='utf-8') as f:
    reader = csv.reader(f)
    rows = list(reader)

print("Validando y corrigiendo todos los campos enabled...")
print("=" * 80)

correcciones = 0

# Procesar cada fila
for i, row in enumerate(rows):
    if i == 0:  # Header
        continue
    
    # Verificar field_1, field_2, field_3, field_4
    for field_num in [1, 2, 3, 4]:
        title_idx = field_num * 2 + 1  # 3, 5, 7, 9
        enabled_idx = field_num * 2 + 2  # 4, 6, 8, 10
        
        if len(row) > enabled_idx:
            title = row[title_idx] if len(row) > title_idx else ''
            enabled = row[enabled_idx]
            
            # Regla: Si el título está vacío, enabled debe ser "off"
            if not title.strip() and enabled != 'off':
                print(f"Corrigiendo ID {row[0]}, field_{field_num}: '{title}' -> enabled de '{enabled}' a 'off'")
                row[enabled_idx] = 'off'
                correcciones += 1
            
            # Regla: Si el título tiene contenido y enabled está vacío, poner "on"
            elif title.strip() and not enabled.strip():
                print(f"Corrigiendo ID {row[0]}, field_{field_num}: '{title}' -> enabled de vacío a 'on'")
                row[enabled_idx] = 'on'
                correcciones += 1

print("=" * 80)
print(f"\n✅ Total de correcciones realizadas: {correcciones}")

# Guardar el archivo actualizado
with open(output_file, 'w', encoding='utf-8', newline='') as f:
    writer = csv.writer(f)
    writer.writerows(rows)

print("✅ Archivo actualizado correctamente!")

# Mostrar resumen final
print("\n" + "=" * 80)
print("RESUMEN FINAL:")
print("=" * 80)
for field_num in [1, 2, 3, 4]:
    title_idx = field_num * 2 + 1
    enabled_idx = field_num * 2 + 2
    
    count_on = 0
    count_off = 0
    count_empty_title = 0
    
    for i, row in enumerate(rows[1:], 1):
        if len(row) > enabled_idx:
            title = row[title_idx] if len(row) > title_idx else ''
            enabled = row[enabled_idx]
            
            if not title.strip():
                count_empty_title += 1
            
            if enabled == 'on':
                count_on += 1
            elif enabled == 'off':
                count_off += 1
    
    print(f"field_{field_num}:")
    print(f"  - Títulos vacíos: {count_empty_title}")
    print(f"  - Enabled 'on': {count_on}")
    print(f"  - Enabled 'off': {count_off}")
