import csv

# Leer el archivo CSV
with open('wc-product-export-CATS.csv', 'r', encoding='utf-8') as file:
    reader = csv.DictReader(file)
    
    products_with_more_than_2 = []
    
    for row in reader:
        categories = row.get('Categorías', '')
        if categories:
            # Separar por comas
            cat_list = [cat.strip() for cat in categories.split(',')]
            
            # Filtrar categorías principales (las que no tienen ">")
            main_categories = [cat for cat in cat_list if '>' not in cat]
            
            if len(main_categories) > 2:
                products_with_more_than_2.append({
                    'ID': row['ID'],
                    'SKU': row['SKU'],
                    'Nombre': row['Nombre'],
                    'Num_Main_Cats': len(main_categories),
                    'Main_Categories': ', '.join(main_categories)
                })
    
    print(f"\n{'='*100}")
    print(f"PRODUCTOS CON MÁS DE 2 CATEGORÍAS PRINCIPALES")
    print(f"{'='*100}\n")
    
    if products_with_more_than_2:
        print(f"Total de productos con más de 2 categorías principales: {len(products_with_more_than_2)}\n")
        
        for product in products_with_more_than_2:
            print(f"ID: {product['ID']}")
            print(f"SKU: {product['SKU']}")
            print(f"Nombre: {product['Nombre']}")
            print(f"Número de categorías principales: {product['Num_Main_Cats']}")
            print(f"Categorías principales: {product['Main_Categories']}")
            print(f"{'-'*100}\n")
    else:
        print("No se encontraron productos con más de 2 categorías principales.")
        print("Todos los productos tienen 1 o 2 categorías principales.")
