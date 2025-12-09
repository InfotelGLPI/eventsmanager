#!/bin/bash
# Script to add missing foreign keys to eventsmanager plugin
# This fixes the warnings about "groups_assigned" and "users_close" not being foreign key fields

echo "=== Adding missing foreign keys to eventsmanager plugin ==="
echo ""
echo "This will add foreign key constraints to the glpi_plugin_eventsmanager_events table."
echo ""

# Get database credentials from GLPI config
GLPI_CONFIG="/var/www/glpi/config/config_db.php"

if [ ! -f "$GLPI_CONFIG" ]; then
    echo "Error: GLPI config file not found at $GLPI_CONFIG"
    exit 1
fi

# Extract database connection info
DB_HOST=$(grep "dbhost" "$GLPI_CONFIG" | cut -d"'" -f4)
DB_NAME=$(grep "dbdefault" "$GLPI_CONFIG" | cut -d"'" -f4)
DB_USER=$(grep "dbuser" "$GLPI_CONFIG" | cut -d"'" -f4)
DB_PASS=$(grep "dbpassword" "$GLPI_CONFIG" | cut -d"'" -f4)

echo "Database: $DB_NAME"
echo "Host: $DB_HOST"
echo "User: $DB_USER"
echo ""

# Execute the SQL file
SQL_FILE="/var/www/glpi/plugins/eventsmanager/sql/add-missing-foreign-keys.sql"

if [ ! -f "$SQL_FILE" ]; then
    echo "Error: SQL file not found at $SQL_FILE"
    exit 1
fi

echo "Executing SQL to add foreign keys..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SQL_FILE"

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ Foreign keys added successfully!"
    echo ""
    echo "The warnings about 'groups_assigned' and 'users_close' should now be resolved."
    echo "Please refresh your GLPI page."
else
    echo ""
    echo "✗ Error executing SQL. Please check the error messages above."
    exit 1
fi
