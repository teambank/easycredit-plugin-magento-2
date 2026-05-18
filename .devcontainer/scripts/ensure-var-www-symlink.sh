#!/usr/bin/env bash
# Default vhost uses DocumentRoot /var/www/html — point it at stub pub or Magento pub.
set -euo pipefail

ROOT="${containerWorkspaceFolder:-/workspace}"
MAGENTO_DIR="${MAGENTO_DIR:-/opt/magento}"

if [[ -f "${MAGENTO_DIR}/bin/magento" ]]; then
  sudo ln -sfn "${MAGENTO_DIR}/pub" /var/www/html
else
  sudo ln -sfn "${ROOT}/.devcontainer/stub/pub" /var/www/html
fi
sudo service apache2 reload 2>/dev/null || sudo apachectl graceful
