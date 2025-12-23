import csv

input_file = '/workspaces/arlequin/Productos-Export-Extracted-Fields-BODA.csv'
output_file = '/workspaces/arlequin/Productos-Export-Extracted-Fields-BODA.csv'

# Leer el CSV
rows = []
with open(input_file, 'r', encoding='utf-8') as f:
    reader = csv.reader(f)
    rows = list(reader)

# Procesar cada fila
for i, row in enumerate(rows):
    if i == 0:  # Header row
        # Verificar que las columnas existen
        print(f"Columnas: {len(row)}")
        print(f"Últimas 3 columnas: {row[-3:]}")
        continue
    
    # Buscar en los campos field_X_title (posiciones 3, 5, 7, 9)
    field_titles = []
    for idx in [3, 5, 7, 9]:
        if idx < len(row):
            field_titles.append(row[idx])
    
    # Buscar FRASE/TEXTO
    has_frase_texto = any('FRASE/TEXTO' in title for title in field_titles)
    # Buscar NUMERO CUENTA
    has_numero_cuenta = any('NUMERO CUENTA' in title for title in field_titles)
    # Buscar MENÚ
    has_menu = any('MENÚ' in title for title in field_titles)
    
    # Asegurarnos de que la fila tenga suficientes columnas (16 total)
    while len(row) < 16:
        row.append('')
    
    # Posiciones de las columnas enable (13, 14, 15 - índices base 0)
    # _enable_text_field_frase_texto (posición 13)
    row[13] = 'on' if has_frase_texto else 'off'
    
    # _enable_text_field_numero_cuenta (posición 14)
    row[14] = 'on' if has_numero_cuenta else 'off'
    
    # _enable_text_field_menu (posición 15)
    row[15] = 'on' if has_menu else 'off'

# Guardar el archivo actualizado
with open(output_file, 'w', encoding='utf-8', newline='') as f:
    writer = csv.writer(f)
    writer.writerows(rows)

print("Archivo actualizado correctamente!")
print(f"\nResumen:")
print(f"- Filas procesadas: {len(rows) - 1}")
print(f"\nPrimeras filas con cambios:")
for i in range(1, min(6, len(rows))):
    row = rows[i]
    print(f"ID {row[0]}: frase_texto={row[13]}, numero_cuenta={row[14]}, menu={row[15]}")
