#!/bin/bash
# File: /var/www/html/pivpn/logger.sh
LOG_FILE="/var/log/openvpn-status.log"
DB_FILE="/var/www/html/pivpn/usage_history.db"

# 1. Parse using Tabs (-F'\t') 
# Field 2=Name, Field 6=Received, Field 7=Sent
sudo grep "CLIENT_LIST" "$LOG_FILE" | grep -v "HEADER" | awk -F'\t' '{print $2 "," $6 "," $7}' | while IFS=',' read -r name down up; do
    
    # 2. Only save if we have valid data
    if [ -z "$name" ] || [ "$name" == "Common Name" ]; then continue; fi
    
    echo "Saving: $name (Down: $down, Up: $up)"
    sqlite3 "$DB_FILE" "INSERT INTO usage (name, up, down) VALUES ('$name', $up, $down);"
done
