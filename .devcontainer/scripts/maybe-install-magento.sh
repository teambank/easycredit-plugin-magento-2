#!/usr/bin/env bash
# Run install-magento-store.sh once until app/etc/env.php exists (Magento files live in /opt/magento from the image).
set -euo pipefail

ROOT="${containerWorkspaceFolder:-/workspace}"
MAGENTO_DIR="${MAGENTO_DIR:-/opt/magento}"
INSTALL_SCRIPT="${ROOT}/.devcontainer/scripts/install-magento-store.sh"

mkdir -p "${ROOT}"
if [[ -L "${ROOT}/magento" ]] || [[ ! -e "${ROOT}/magento" ]]; then
  ln -sfn "${MAGENTO_DIR}" "${ROOT}/magento"
fi

bash "${ROOT}/.devcontainer/scripts/ensure-var-www-symlink.sh"
# shellcheck source=/dev/null
source "${ROOT}/.devcontainer/scripts/ensure-composer-auth.sh"
bash "${ROOT}/.devcontainer/scripts/ensure-magento-writable.sh"
bash "${ROOT}/.devcontainer/scripts/configure-opensearch.sh"

if [[ -f "${MAGENTO_DIR}/app/etc/env.php" ]]; then
  echo "[devcontainer] Magento already configured (${MAGENTO_DIR}/app/etc/env.php); skipping setup:install."
  exit 0
fi

echo "[devcontainer] No env.php yet; running install-magento-store.sh..."
export WORKSPACE="${ROOT}"
export MAGENTO_DIR
export MAGENTO_CLONE=0
export BASE_URL="${MAGENTO_BASE_URL:-http://localhost:8080/}"
export DB_HOST="${MAGENTO_DB_HOST:-db}"
export DB_NAME="${MAGENTO_DB_NAME:-magento}"
export DB_USER="${MAGENTO_DB_USER:-magento}"
export DB_PASSWORD="${MAGENTO_DB_PASSWORD:-magento}"
export DB_ROOT_USER="${MAGENTO_DB_ROOT_USER:-root}"
export DB_ROOT_PASSWORD="${MAGENTO_DB_ROOT_PASSWORD:-magento}"
export SEARCH_ENGINE="${MAGENTO_SEARCH_ENGINE:-opensearch}"
export SEARCH_HOST="${MAGENTO_SEARCH_HOST:-opensearch}"
export SEARCH_PORT="${MAGENTO_SEARCH_PORT:-9200}"
export PLUGIN_SOURCE=app_code
export PLUGIN_CODE_PATH="${MAGENTO_DIR}/app/code/Netzkollektiv/EasyCredit"
export EASYCREDIT_API_KEY="${EASYCREDIT_API_KEY:-}"
export EASYCREDIT_API_TOKEN="${EASYCREDIT_API_TOKEN:-}"
export EASYCREDIT_API_SIGNATURE="${EASYCREDIT_API_SIGNATURE:-}"

bash "${INSTALL_SCRIPT}"

bash "${ROOT}/.devcontainer/scripts/configure-opensearch.sh"
bash "${ROOT}/.devcontainer/scripts/ensure-var-www-symlink.sh"
