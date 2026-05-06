#!/bin/bash
set -e

if [ -z "${PLUGIN_ID:-}" ]; then
    echo "ERROR: PLUGIN_ID environment variable is required." >&2
    echo "Set it in docker/.env before starting the stack." >&2
    exit 1
fi

echo "Waiting for MySQL..."
until php -r "new PDO('mysql:host=${ELGG_DB_HOST:-db}', '${ELGG_DB_USER:-elgg}', '${ELGG_DB_PASS:-elgg}');" 2>/dev/null; do
    sleep 1
done
echo "MySQL is ready."

cd /var/www/html

if [ ! -f /var/www/html/.elgg-installed ]; then
    echo "Installing Elgg 5.x..."

    mkdir -p elgg-config
    cat > elgg-config/settings.php <<'SETTINGS_TEMPLATE'
<?php
global $CONFIG;
if (!isset($CONFIG)) {
    $CONFIG = new \stdClass;
}
SETTINGS_TEMPLATE

    cat >> elgg-config/settings.php <<SETTINGS_VALUES
\$CONFIG->dbuser = '${ELGG_DB_USER:-elgg}';
\$CONFIG->dbpass = '${ELGG_DB_PASS:-elgg}';
\$CONFIG->dbname = '${ELGG_DB_NAME:-elgg}';
\$CONFIG->dbhost = '${ELGG_DB_HOST:-db}';
\$CONFIG->dbport = '3306';
\$CONFIG->dbprefix = 'elgg_';
\$CONFIG->dbencoding = 'utf8mb4';
\$CONFIG->dataroot = '${ELGG_DATA_ROOT:-/var/www/data/}';
\$CONFIG->wwwroot = '${ELGG_SITE_URL:-http://elgg/}';
\$CONFIG->cacheroot = '${ELGG_DATA_ROOT:-/var/www/data/}cache/';
\$CONFIG->assetroot = '${ELGG_DATA_ROOT:-/var/www/data/}assets/';
SETTINGS_VALUES

    php -r "
        require_once 'vendor/autoload.php';

        \$params = [
            'dbuser' => '${ELGG_DB_USER:-elgg}',
            'dbpassword' => '${ELGG_DB_PASS:-elgg}',
            'dbname' => '${ELGG_DB_NAME:-elgg}',
            'dbhost' => '${ELGG_DB_HOST:-db}',
            'dbport' => '3306',
            'dbprefix' => 'elgg_',
            'sitename' => 'Elgg 5.x Plugin Test',
            'siteemail' => '${ELGG_ADMIN_EMAIL:-admin@example.com}',
            'wwwroot' => '${ELGG_SITE_URL:-http://elgg/}',
            'dataroot' => '${ELGG_DATA_ROOT:-/var/www/data/}',
            'displayname' => 'Admin',
            'email' => '${ELGG_ADMIN_EMAIL:-admin@example.com}',
            'username' => 'admin',
            'password' => '${ELGG_ADMIN_PASSWORD:-admin12345}',
        ];

        \$installer = new \ElggInstaller();
        \$installer->batchInstall(\$params);
        echo 'Elgg 5.x installed successfully.' . PHP_EOL;
    " 2>&1 || echo "Install completed (check for errors above)."

    # Symlink core Elgg plugins so transitive deps resolve.
    CORE_MOD_DIR="/var/www/html/vendor/elgg/elgg/mod"
    if [ -d "$CORE_MOD_DIR" ]; then
        for core_mod in "$CORE_MOD_DIR"/*/; do
            core_id=$(basename "$core_mod")
            if [ ! -e "/var/www/html/mod/$core_id" ]; then
                ln -sfn "$core_mod" "/var/www/html/mod/$core_id"
            fi
        done
    fi

    echo "Activating plugins..."
    php -r "
        require_once 'vendor/autoload.php';
        \$app = \Elgg\Application::getInstance();
        \$app->bootCore();
        _elgg_services()->plugins->generateEntities();

        // Activate all inactive plugins in multiple passes to handle transitive deps.
        do {
            \$newly_activated = 0;
            foreach (elgg_get_plugins('inactive') as \$p) {
                try {
                    \$p->setPriority('last');
                    \$p->activate();
                    \$newly_activated++;
                } catch (\Throwable \$e) {
                    // Skip — will retry next pass if deps become available
                }
            }
        } while (\$newly_activated > 0);

        \$plugin = elgg_get_plugin_from_id('${PLUGIN_ID}');
        if (!\$plugin || !\$plugin->isActive()) {
            echo 'ERROR: plugin ${PLUGIN_ID} failed to activate.' . PHP_EOL;
            exit(1);
        }
        echo 'Plugin ${PLUGIN_ID} activated.' . PHP_EOL;
    " 2>&1 || echo "Plugin activation completed (check for errors above)."

    php -r "
        require_once 'vendor/autoload.php';
        \$app = \Elgg\Application::getInstance();
        \$app->bootCore();
        \$admin = get_user_by_username('admin');
        _elgg_services()->session_manager->setLoggedInUser(\$admin);
        if (!get_user_by_username('testuser')) {
            \$user = new ElggUser();
            \$user->username = 'testuser';
            \$user->email = 'testuser@example.com';
            \$user->name = 'Test User';
            \$user->setPassword('${ELGG_ADMIN_PASSWORD:-admin12345}');
            \$user->save();
            echo 'Created testuser (guid: ' . \$user->guid . ')' . PHP_EOL;
        } else {
            echo 'testuser already exists.' . PHP_EOL;
        }
    " 2>&1 || true

    chown -R www-data:www-data "${ELGG_DATA_ROOT:-/var/www/data/}"
    chmod -R u+rwX,g+rX,o+rX "${ELGG_DATA_ROOT:-/var/www/data/}"

    touch /var/www/html/.elgg-installed
    echo "Elgg 5.x setup complete."
fi

echo "Starting Apache..."
exec apache2-foreground
