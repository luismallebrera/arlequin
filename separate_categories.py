import csv

def separate_categories(category_string):
    """
    Separate main categories from subcategories.
    Returns two strings: main_categories and subcategories
    """
    if not category_string or category_string.strip() == '':
        return '', ''
    
    # Split by comma to get individual category paths
    categories = [cat.strip() for cat in category_string.split(',')]
    
    main_cats = []
    sub_cats = []
    
    for cat in categories:
        if '>' in cat:
            # Split by '>' to separate main from sub
            parts = [p.strip() for p in cat.split('>')]
            main_cat = parts[0]
            sub_cat = ' > '.join(parts[1:])
            
            # Add main category if not already present
            if main_cat and main_cat not in main_cats:
                main_cats.append(main_cat)
            
            # Add full subcategory path
            if sub_cat:
                sub_cats.append(f"{main_cat} > {sub_cat}")
        else:
            # No subcategory, just main category
            if cat and cat not in main_cats:
                main_cats.append(cat)
    
    return ', '.join(main_cats), ', '.join(sub_cats)

# Read the filtered CSV file
input_file = '/workspaces/arlequin/products_with_two_main_categories.csv'
output_file = '/workspaces/arlequin/products_with_two_main_categories_separated.csv'

updated_products = []

with open(input_file, 'r', encoding='utf-8') as f:
    reader = csv.DictReader(f)
    headers = list(reader.fieldnames)
    
    # Find the position of 'Categorías' column
    cat_index = headers.index('Categorías')
    
    # Insert new columns after 'Categorías'
    new_headers = headers[:cat_index+1] + ['Categorías Principales', 'Subcategorías'] + headers[cat_index+1:]
    
    for row in reader:
        categories = row.get('Categorías', '')
        main_cats, sub_cats = separate_categories(categories)
        
        # Create new row with the separated columns
        new_row = {}
        for header in headers:
            new_row[header] = row[header]
        new_row['Categorías Principales'] = main_cats
        new_row['Subcategorías'] = sub_cats
        
        updated_products.append(new_row)

# Write updated products to new CSV
with open(output_file, 'w', encoding='utf-8', newline='') as f:
    writer = csv.DictWriter(f, fieldnames=new_headers)
    writer.writeheader()
    writer.writerows(updated_products)

print(f"Processed {len(updated_products)} products")
print(f"Results saved to: {output_file}")

# Show first 5 examples
if updated_products:
    print("\nFirst 5 products with separated categories:")
    print("=" * 100)
    for i, product in enumerate(updated_products[:5], 1):
        print(f"{i}. {product['Nombre']}")
        print(f"   Original: {product['Categorías']}")
        print(f"   Main Categories: {product['Categorías Principales']}")
        print(f"   Subcategories: {product['Subcategorías']}")
        print()
