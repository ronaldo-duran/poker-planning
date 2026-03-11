#!/bin/bash

# Script para ejecutar pruebas de Planning Poker
# Configurar y ejecutar suite completa de pruebas

echo "🧪 Planning Poker - Test Suite"
echo "=============================="
echo ""

# Verificar que Laravel esté listo
if [ ! -f "vendor/autoload.php" ]; then
    echo "❌ Dependencias no instaladas. Ejecuta: composer install"
    exit 1
fi

# Crear base de datos de prueba si no existe (PostgreSQL)
echo "📦 Preparando base de datos de pruebas..."
createdb poker_planning_test 2>/dev/null || echo "ℹ️  Base de datos de prueba ya existe"

# Ejecutar migraciones
echo "🔄 Ejecutando migraciones..."
php artisan migrate --database=postgres --force --env=testing

echo ""
echo "✅ Ambiente preparado"
echo ""
echo "Ejecutar pruebas:"
echo "  - Todas:           php artisan test"
echo "  - Unitarias:       php artisan test tests/Unit"
echo "  - Features:        php artisan test tests/Feature"
echo "  - Con formato:     php artisan test --testdox"
echo "  - Con cobertura:   php artisan test --coverage"
echo ""
