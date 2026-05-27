#!/bin/bash

# Migration Status Checker
# This script shows the migration status for all organized folders

echo "=================================="
echo "SCS ERP - Migration Status Report"
echo "=================================="
echo ""

FOLDERS=("core" "admissions" "academic" "faculty" "students" "exams" "fees" "infrastructure" "masters" "permissions" "system" "event_coordinator" "hr")

for folder in "${FOLDERS[@]}"; do
    path="database/migrations/$folder"
    
    if [ -d "$path" ]; then
        count=$(ls "$path"/*.php 2>/dev/null | wc -l | xargs)
        echo "📁 $folder ($count migrations)"
        echo "-----------------------------------"
        php artisan migrate:status --path="$path" 2>/dev/null | tail -n +3
        echo ""
    fi
done

echo "=================================="
echo "Total Migration Statistics"
echo "=================================="
php artisan tinker --execute="
    \$total = DB::table('migrations')->count();
    \$latest = DB::table('migrations')->orderBy('batch', 'desc')->first();
    echo \"Total Migrations Run: \$total\\n\";
    echo \"Latest Batch: {\$latest->batch}\\n\";
    echo \"Latest Migration: {\$latest->migration}\\n\";
"
