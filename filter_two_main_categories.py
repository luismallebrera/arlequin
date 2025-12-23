import csv

def count_main_categories(category_string):
    """
    Count main categories (top-level categories separated by commas).
    Sub-categories are separated by '>' and should not be counted separately.
    """
    if not category_string or category_string.strip() == '':
        return 0
    
    # Split by comma and count unique main categories
    categories = [cat.strip() for cat in category_string.split(',')]
    
    # Extract only the top-level category (before any '>')
    main_categories = []
    for cat in categories:
        if '>' in cat:
            main_cat = cat.split('>')[0].strip()
        else:
            main_cat = cat.strip()
        if main_cat and main_cat not in main_categories:
            main_categories.append(main_cat)
    
    return len(main_categories)

# Read the CSV file
input_file = '/workspaces/arlequin/wc-product-export-CATS.csv'
output_file = '/workspaces/arlequin/products_with_two_main_categories.csv'

products_with_two_cats = []

with open(input_file, 'r', encoding='utf-8') as f:
    reader = csv.DictReader(f)
    headers = reader.fieldnames
    
    for row in reader:
        categories = row.get('Categorías', '')
        main_cat_count = count_main_categories(categories)
        
        if main_cat_count == 2:
            products_with_two_cats.append(row)

# Write filtered products to new CSV
with open(output_file, 'w', encoding='utf-8', newline='') as f:
    if products_with_two_cats:
        writer = csv.DictWriter(f, fieldnames=headers)
        writer.writeheader()
        writer.writerows(products_with_two_cats)

print(f"Found {len(products_with_two_cats)} products with exactly 2 main categories")
print(f"Results saved to: {output_file}")

# Show first 10 examples
if products_with_two_cats:
    print("\nFirst 10 products with 2 main categories:")
    print("-" * 80)
    for i, product in enumerate(products_with_two_cats[:10], 1):
        print(f"{i}. {product['Nombre']}")
        print(f"   Categories: {product['Categorías']}")
        print()
