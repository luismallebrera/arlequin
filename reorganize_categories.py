import csv
import re

def parse_categories(categories_str):
    """
    Parsea las categorías y separa las principales con sus subcategorías.
    Retorna una lista de tuplas (main_category, subcategory).
    """
    if not categories_str or categories_str.strip() == '':
        return []
    
    # Dividir por comas que no están dentro de comillas
    parts = []
    current = []
    in_quotes = False
    
    for char in categories_str:
        if char == '"':
            in_quotes = not in_quotes
        elif char == ',' and not in_quotes:
            parts.append(''.join(current).strip())
            current = []
            continue
        current.append(char)
    
    if current:
        parts.append(''.join(current).strip())
    
    # Procesar cada parte para extraer main > sub
    category_pairs = []
    seen_mains = set()
    
    for part in parts:
        part = part.strip().strip('"')
        if ' > ' in part:
            # Es una categoría con subcategoría
            main, sub = part.split(' > ', 1)
            main = main.strip()
            sub = sub.strip()
            if main not in seen_mains:
                category_pairs.append((main, sub))
                seen_mains.add(main)
        else:
            # Es solo una categoría principal
            if part not in seen_mains:
                category_pairs.append((part, ''))
                seen_mains.add(part)
    
    return category_pairs

def process_csv(input_file, output_file):
    """
    Procesa el CSV y reorganiza las categorías en columnas separadas.
    """
    with open(input_file, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        fieldnames = reader.fieldnames
        
        # Leer todas las filas para determinar el número máximo de categorías
        rows = list(reader)
        max_categories = 0
        
        for row in rows:
            categories_str = row.get('Categorías', '')
            pairs = parse_categories(categories_str)
            max_categories = max(max_categories, len(pairs))
        
        # Crear nuevos nombres de campo
        new_fieldnames = [f for f in fieldnames if f != 'Categorías']
        
        # Agregar columnas para categorías principales y subcategorías
        cat_columns = []
        for i in range(1, max_categories + 1):
            cat_columns.append(f'main_category_{i}')
            cat_columns.append(f'subcategory_{i}')
        
        # Insertar las nuevas columnas después de "Precio normal"
        insert_index = new_fieldnames.index('Precio normal') + 1
        new_fieldnames = new_fieldnames[:insert_index] + cat_columns + new_fieldnames[insert_index:]
        
        # Escribir el archivo de salida
        with open(output_file, 'w', encoding='utf-8', newline='') as out_f:
            writer = csv.DictWriter(out_f, fieldnames=new_fieldnames)
            writer.writeheader()
            
            for row in rows:
                new_row = {k: v for k, v in row.items() if k != 'Categorías'}
                
                # Procesar categorías
                categories_str = row.get('Categorías', '')
                pairs = parse_categories(categories_str)
                
                # Llenar las columnas de categorías
                for i, (main, sub) in enumerate(pairs, 1):
                    new_row[f'main_category_{i}'] = main
                    new_row[f'subcategory_{i}'] = sub
                
                # Llenar el resto con vacío
                for i in range(len(pairs) + 1, max_categories + 1):
                    new_row[f'main_category_{i}'] = ''
                    new_row[f'subcategory_{i}'] = ''
                
                writer.writerow(new_row)
    
    print(f"Procesamiento completo. Archivo generado: {output_file}")
    print(f"Número máximo de categorías encontradas: {max_categories}")

if __name__ == "__main__":
    input_file = "/workspaces/arlequin/products_with_two_main_categories.csv"
    output_file = "/workspaces/arlequin/products_categories_reorganized.csv"
    
    process_csv(input_file, output_file)
