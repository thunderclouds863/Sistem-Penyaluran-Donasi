#!/usr/bin/env bash
# Start MariaDB (ephemeral), import schema + data demo, lalu Apache di $PORT.
set -euo pipefail
cd "$(dirname "$0")"
source ./demo.env   # DB_NAME, SQL_FILES, DOC_ROOT (opsional)
PORT=${PORT:-10000}

mkdir -p /run/mysqld && chown mysql:mysql /run/mysqld
mysqld_safe --bind-address=127.0.0.1 --innodb-buffer-pool-size=32M --performance-schema=OFF \
  --sql-mode="NO_ENGINE_SUBSTITUTION" > /dev/null 2>&1 &
for i in $(seq 30); do mysqladmin ping > /dev/null 2>&1 && break; sleep 1; done
mysql -uroot -e "ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD(''); FLUSH PRIVILEGES;"
# Buat DB hanya kalau dump tidak membuatnya sendiri (beberapa dump pakai CREATE DATABASE tanpa IF NOT EXISTS)
if ! (cd /var/www/html && grep -qi "CREATE DATABASE" $SQL_FILES); then
  mysql -uroot -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`"
fi
for f in $SQL_FILES; do
  echo "Import $f"
  if grep -qiE "^\s*USE " "/var/www/html/$f"; then mysql -uroot < "/var/www/html/$f"; else mysql -uroot "$DB_NAME" < "/var/www/html/$f"; fi
done

sed -i "s/^Listen 80$/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf
if [ -n "${DOC_ROOT:-}" ]; then
  sed -i "s#DocumentRoot /var/www/html#DocumentRoot \"/var/www/html/$DOC_ROOT\"#" /etc/apache2/sites-available/000-default.conf
  printf "<Directory \"/var/www/html/%s\">\n  AllowOverride All\n  Require all granted\n</Directory>\n" "$DOC_ROOT" >> /etc/apache2/apache2.conf
fi
exec apache2-foreground
