#!/bin/bash

# Database Meta Keys Cleanup - Master Script
# This script guides you through the entire cleanup process

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}================================================${NC}"
echo -e "${BLUE}   WordPress/WooCommerce Meta Keys Cleanup${NC}"
echo -e "${BLUE}================================================${NC}"
echo ""

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP no está instalado o no está en el PATH${NC}"
    exit 1
fi

# Step 1: Analysis
echo -e "${YELLOW}PASO 1: Análisis de Meta Keys${NC}"
echo "----------------------------------------------"
echo "Analizando la base de datos..."
echo ""
php analyze_unused_meta_keys.php
echo ""
read -p "Presiona ENTER para continuar..."
echo ""

# Step 2: Backup
echo -e "${YELLOW}PASO 2: Crear Backup${NC}"
echo "----------------------------------------------"
echo -e "${RED}⚠️  IMPORTANTE: Asegúrate de tener un backup completo de tu base de datos${NC}"
echo ""
echo "¿Deseas crear un backup específico de las meta keys que se eliminarían?"
read -p "(s/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Ss]$ ]]; then
    echo "Creando backup..."
    php backup_meta_keys.php
    echo ""
    echo -e "${GREEN}✅ Backup creado exitosamente${NC}"
else
    echo -e "${YELLOW}⚠️  Saltando creación de backup específico${NC}"
fi
echo ""
read -p "Presiona ENTER para continuar..."
echo ""

# Step 3: Dry Run
echo -e "${YELLOW}PASO 3: Simulación (Dry Run)${NC}"
echo "----------------------------------------------"
echo "Ejecutando simulación para ver qué se eliminaría..."
echo ""
php delete_unused_meta_keys.php --dry-run
echo ""
read -p "Presiona ENTER para continuar..."
echo ""

# Step 4: Confirmation
echo -e "${YELLOW}PASO 4: Confirmación${NC}"
echo "----------------------------------------------"
echo -e "${RED}⚠️  ATENCIÓN: Estás a punto de ELIMINAR meta keys de la base de datos${NC}"
echo ""
echo "¿Has revisado la lista de meta keys que se eliminarán?"
read -p "(s/n): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Ss]$ ]]; then
    echo -e "${YELLOW}Operación cancelada por el usuario${NC}"
    exit 0
fi

echo ""
echo "¿Tienes un backup completo de tu base de datos?"
read -p "(s/n): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Ss]$ ]]; then
    echo -e "${YELLOW}Por favor, crea un backup antes de continuar${NC}"
    echo "Puedes usar: wp db export backup.sql"
    exit 0
fi

echo ""
echo -e "${RED}¿ESTÁS SEGURO de que deseas eliminar las meta keys no utilizadas?${NC}"
read -p "(escribe 'SI' para confirmar): " -r
echo ""
if [[ ! $REPLY == "SI" ]]; then
    echo -e "${YELLOW}Operación cancelada por el usuario${NC}"
    exit 0
fi

# Step 5: Actual Deletion
echo -e "${YELLOW}PASO 5: Eliminación${NC}"
echo "----------------------------------------------"
echo "Eliminando meta keys no utilizadas..."
echo ""
php delete_unused_meta_keys.php --confirm
echo ""

# Step 6: Optimization
echo -e "${YELLOW}PASO 6: Optimización (Opcional)${NC}"
echo "----------------------------------------------"
echo "¿Deseas optimizar la tabla wp_postmeta?"
read -p "(s/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Ss]$ ]]; then
    echo "Optimizando tabla..."
    if command -v wp &> /dev/null; then
        wp db query "OPTIMIZE TABLE wp_postmeta"
        echo -e "${GREEN}✅ Tabla optimizada${NC}"
    else
        echo -e "${YELLOW}⚠️  WP-CLI no está disponible${NC}"
        echo "Ejecuta manualmente: wp db query 'OPTIMIZE TABLE wp_postmeta'"
    fi
fi

echo ""
echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}   ✅ Proceso completado exitosamente${NC}"
echo -e "${GREEN}================================================${NC}"
echo ""
echo "Recomendaciones:"
echo "1. Verifica que tu sitio funcione correctamente"
echo "2. Guarda el archivo de backup en un lugar seguro"
echo "3. Si algo sale mal, restaura desde el backup"
echo ""
