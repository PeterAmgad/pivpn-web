#!/bin/bash
DB_FILE="/var/www/html/pivpn/usage_history.db"

echo "<table>"
echo "<tr><th class='border-bottom' colspan='6' style='text-align:left;'>VPN Clients</th>"
echo "<th class='border-bottom' style='text-align:right;'><button id='show-form' onclick=\"document.getElementById('popup').style.display='flex';\" class='custom-btn-new'>+ New</button></th></tr>"

COMMAND=$(sudo cat /etc/openvpn/easy-rsa/pki/index.txt | grep '^V' | tail -n +2)

if [ -z "$COMMAND" ]; then
    echo "<tr><td colspan='7' style='text-align:center; padding:20px;'>No clients found.</td></tr>"
else
    echo "$COMMAND" | while read -r line; do
        NAME=$(echo "$line" | awk -F'/CN=' '{print $2}')
        CCD_FILE="/etc/openvpn/ccd/$NAME"
        STATUS_CLASS="active"
        if [ -f "$CCD_FILE" ] && sudo grep -q "^#" "$CCD_FILE"; then STATUS_CLASS="inactive"; fi

        echo "<tr class='border-bottom'><td colspan='6' style='vertical-align:middle;'>"
        echo "<span class='client-name'>$NAME</span>"
        
        # 1. SESSION & LIVE LAST SEEN
        SESSION=$(sudo grep "CLIENT_LIST" /var/log/openvpn-status.log | grep "$NAME" | awk -F'\t' '{
            d=$6; u=$7; time=$8;
            if (d >= 1048576) { ds=sprintf("%.2f MB", d/1048576) } else { ds=sprintf("%.2f KB", d/1024) }
            if (u >= 1048576) { us=sprintf("%.2f MB", u/1048576) } else { us=sprintf("%.2f KB", u/1024) }
            print "<small>Sess: ↓ " ds " · ↑ " us "</small>"
            print "<small class=\"last-seen\">Online: " time "</small>"
        }')
        
        if [ -z "$SESSION" ]; then
            echo " <small style='color:#ccc;'>Session: Offline</small>"
            LAST_DB=$(sqlite3 "$DB_FILE" "SELECT timestamp FROM usage WHERE name='$NAME' ORDER BY timestamp DESC LIMIT 1;")
            echo " <small class=\"last-seen\">Last seen: ${LAST_DB:-Never}</small>"
        else
            echo " $SESSION"
        fi

        # 2. ALL TIME USAGE
        TOTALS=$(sqlite3 "$DB_FILE" "SELECT SUM(down), SUM(up) FROM usage WHERE name='$NAME';" | awk -F'|' '{
            d=$1; u=$2;
            if (d == "") d=0; if (u == "") u=0;
            if (d >= 1073741824) { ds=sprintf("%.2f GB", d/1073741824) } else { ds=sprintf("%.2f MB", d/1048576) }
            if (u >= 1073741824) { us=sprintf("%.2f GB", u/1073741824) } else { us=sprintf("%.2f MB", u/1048576) }
            print "<small class=\"total-usage\">Total: ↓ " ds " · ↑ " us "</small>"
        }')
        echo " $TOTALS"
        echo "</td><td style='text-align:right; vertical-align:middle;'><div class='toggle_btn $STATUS_CLASS' onclick=\"toggleClient('$NAME')\"></div></td></tr>"
    done
fi
echo "</table>"
