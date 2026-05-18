#!/usr/bin/env bash
#
# Shared Magento + EasyCredit store installation (CI + devcontainer).
#
# PLUGIN_SOURCE=composer  — require teambank/easycredit-plugin-magento-2 via Composer (CI).
# PLUGIN_SOURCE=app_code  — bind-mounted app/code/Netzkollektiv/EasyCredit; requires plugin
#                           composer.json deps (e.g. netzkollektiv/easycredit-api-v3-php) into vendor/.
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"

WORKSPACE="${WORKSPACE:-${GITHUB_WORKSPACE:-${REPO_ROOT}}}"
MAGENTO_DIR="${MAGENTO_DIR:-/opt/magento}"
MAGENTO_DIR="${MAGENTO_DIR%/}"

PLUGIN_PACKAGE="${PLUGIN_PACKAGE:-teambank/easycredit-plugin-magento-2}"
PLUGIN_CONSTRAINT="${PLUGIN_CONSTRAINT:-*}"
# composer — require plugin via path/git (CI). app_code — plugin bind-mounted under app/code (devcontainer).
PLUGIN_SOURCE="${PLUGIN_SOURCE:-composer}"
PLUGIN_CODE_PATH="${PLUGIN_CODE_PATH:-${MAGENTO_DIR}/app/code/Netzkollektiv/EasyCredit}"

MAGENTO_CLONE="${MAGENTO_CLONE:-0}"
MAGENTO_VERSION="${MAGENTO_VERSION:-2.4.9}"
MAGENTO_REPO="${MAGENTO_REPO:-https://github.com/magento/magento2}"

# CI clones Magento on the runner (DB on 127.0.0.1). Devcontainer uses compose service names.
if [[ "${MAGENTO_CLONE}" == "1" ]]; then
  DB_HOST="${DB_HOST:-127.0.0.1}"
  DB_NAME="${DB_NAME:-magento}"
  DB_USER="${DB_USER:-root}"
  DB_PASSWORD="${DB_PASSWORD:-root}"
  DB_ROOT_USER="${DB_ROOT_USER:-root}"
  DB_ROOT_PASSWORD="${DB_ROOT_PASSWORD:-root}"
  BASE_URL="${BASE_URL:-http://localhost/}"
  SEARCH_ENGINE="${SEARCH_ENGINE:-elasticsearch8}"
  SEARCH_HOST="${SEARCH_HOST:-localhost}"
else
  DB_HOST="${DB_HOST:-${MAGENTO_DB_HOST:-db}}"
  DB_NAME="${DB_NAME:-${MAGENTO_DB_NAME:-magento}}"
  DB_USER="${DB_USER:-${MAGENTO_DB_USER:-magento}}"
  DB_PASSWORD="${DB_PASSWORD:-${MAGENTO_DB_PASSWORD:-magento}}"
  DB_ROOT_USER="${DB_ROOT_USER:-${MAGENTO_DB_ROOT_USER:-root}}"
  DB_ROOT_PASSWORD="${DB_ROOT_PASSWORD:-${MAGENTO_DB_ROOT_PASSWORD:-magento}}"
  BASE_URL="${BASE_URL:-${MAGENTO_BASE_URL:-http://localhost:8080/}}"
  SEARCH_ENGINE="${SEARCH_ENGINE:-${MAGENTO_SEARCH_ENGINE:-opensearch}}"
  SEARCH_HOST="${SEARCH_HOST:-${MAGENTO_SEARCH_HOST:-opensearch}}"
fi

SEARCH_PORT="${SEARCH_PORT:-9200}"
OPENSEARCH_INDEX_PREFIX="${OPENSEARCH_INDEX_PREFIX:-magento2}"
OPENSEARCH_TIMEOUT="${OPENSEARCH_TIMEOUT:-15}"

ADMIN_FIRSTNAME="${ADMIN_FIRSTNAME:-Admin}"
ADMIN_LASTNAME="${ADMIN_LASTNAME:-Istrator}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@magneto.com}"
ADMIN_USER="${ADMIN_USER:-admin}"
ADMIN_PASSWORD="${ADMIN_PASSWORD:-admin1234578!}"
BACKEND_FRONTNAME="${BACKEND_FRONTNAME:-admin}"

mysql_cli() {
  local user="$1" pass="$2"
  shift 2
  if command -v mysql >/dev/null 2>&1; then
    MYSQL_PWD="${pass}" mysql -u"${user}" -h"${DB_HOST}" "$@"
    return
  fi
  local sql="" db=""
  while (("$#")); do
    case "$1" in
      -e) sql="$2"; shift 2 ;;
      *) db="$1"; shift ;;
    esac
  done
  export MYSQL_PDO_HOST="${DB_HOST}" MYSQL_PDO_USER="${user}" MYSQL_PDO_PASS="${pass}"
  export MYSQL_PDO_DB="${db}" MYSQL_PDO_SQL="${sql}"
  php -r '
    $db = getenv("MYSQL_PDO_DB");
    $dsn = "mysql:host=" . getenv("MYSQL_PDO_HOST") . ($db ? ";dbname={$db}" : "");
    $pdo = new PDO($dsn, getenv("MYSQL_PDO_USER"), getenv("MYSQL_PDO_PASS"));
    $pdo->exec(getenv("MYSQL_PDO_SQL"));
  '
}

search_install_args() {
  case "${SEARCH_ENGINE}" in
    opensearch)
      printf '%s\n' \
        "--search-engine=opensearch" \
        "--opensearch-host=${SEARCH_HOST}" \
        "--opensearch-port=${SEARCH_PORT}" \
        "--opensearch-index-prefix=${OPENSEARCH_INDEX_PREFIX}" \
        "--opensearch-timeout=${OPENSEARCH_TIMEOUT}" \
        "--opensearch-enable-auth=0"
      ;;
    elasticsearch7|elasticsearch8)
      printf '%s\n' \
        "--search-engine=${SEARCH_ENGINE}" \
        "--elasticsearch-host=${SEARCH_HOST}" \
        "--elasticsearch-port=${SEARCH_PORT}"
      ;;
    *)
      echo "Unsupported SEARCH_ENGINE: ${SEARCH_ENGINE}" >&2
      exit 1
      ;;
  esac
}

configure_composer_allow_plugins() {
  # Magento < 2.4.4 has no config.allow-plugins; Composer 2.2+ blocks plugins until listed.
  if ! composer -V 2>/dev/null | grep -qE 'Composer version 2\.'; then
    return 0
  fi
  if composer config --no-plugins allow-plugins.laminas/laminas-dependency-plugin 2>/dev/null | grep -q '^true$'; then
    return 0
  fi
  echo "[install] Enabling Composer allow-plugins (Magento ${MAGENTO_VERSION} + Composer 2)..."
  composer config --no-plugins allow-plugins.laminas/laminas-dependency-plugin true
  composer config --no-plugins allow-plugins.magento/magento-composer-installer true
  composer config --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
  composer config --no-plugins 'allow-plugins.magento/*' true
}

composer_install() {
  # Production install only — dev/test packages break setup:di:compile (missing ParserInterface, etc.).
  COMPOSER_NO_DEV=1 composer install --no-interaction "$@" \
    || COMPOSER_NO_DEV=1 composer install --no-interaction "$@"
}

composer_require() {
  COMPOSER_NO_DEV=1 composer require --no-interaction --ignore-platform-reqs "$@"
}

disable_module_if_present() {
  local module="$1"
  local status
  status="$(php bin/magento module:status "${module}" 2>&1)" || true
  if [[ "${status}" == *"does not exist"* ]]; then
    echo "[install] Skipping ${module} (not installed)"
    return 0
  fi
  echo "[install] Disabling ${module}..."
  php bin/magento module:disable "${module}"
}

prepare_for_di_compile() {
  # PHPUnit is not installed (--no-dev). setup:di:compile must not scan test classes.
  local removed=0

  if [[ -d "${MAGENTO_DIR}/dev/tests" ]]; then
    echo "[install] Removing ${MAGENTO_DIR}/dev/tests..."
    rm -rf "${MAGENTO_DIR}/dev/tests"
    removed=1
  fi

  if [[ -d "${MAGENTO_DIR}/vendor/magento/magento2-base/dev/tests" ]]; then
    echo "[install] Removing vendor/magento/magento2-base/dev/tests..."
    rm -rf "${MAGENTO_DIR}/vendor/magento/magento2-base/dev/tests"
    removed=1
  fi

  if [[ -d "${MAGENTO_DIR}/vendor/magento" ]]; then
    local test_dir_count
    test_dir_count="$(find "${MAGENTO_DIR}/vendor/magento" -type d -name 'Test' 2>/dev/null | wc -l | tr -d ' ')"
    if [[ "${test_dir_count}" -gt 0 ]]; then
      echo "[install] Removing ${test_dir_count} Test/ directories under vendor/magento..."
      find "${MAGENTO_DIR}/vendor/magento" -depth -type d -name 'Test' -exec rm -rf {} +
      removed=1
    fi
  fi

  if [[ "${removed}" -eq 0 ]]; then
    echo "[install] No test directories to remove before di:compile."
  fi
}

if [[ "${MAGENTO_CLONE}" == "1" ]]; then
  mysql_cli "${DB_ROOT_USER}" "${DB_ROOT_PASSWORD}" \
    -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;"
  git clone --depth=1 -b "${MAGENTO_VERSION}" "${MAGENTO_REPO}" "${MAGENTO_DIR}"
fi

if [[ ! -x "${MAGENTO_DIR}/bin/magento" ]]; then
  echo "Missing ${MAGENTO_DIR}/bin/magento" >&2
  exit 1
fi

require_plugin_composer_dependencies() {
  local composer_json="${PLUGIN_CODE_PATH}/composer.json"
  if [[ ! -f "${composer_json}" ]]; then
    echo "Missing ${composer_json} — cannot install plugin libraries." >&2
    exit 1
  fi

  mapfile -t plugin_requires < <(
    PLUGIN_CODE_PATH="${PLUGIN_CODE_PATH}" php -r '
      $json = json_decode(file_get_contents(getenv("PLUGIN_CODE_PATH") . "/composer.json"), true);
      foreach ($json["require"] ?? [] as $name => $version) {
        if ($name === "php") {
          continue;
        }
        echo $name . "\t" . $version . PHP_EOL;
      }
    '
  )

  if [[ ${#plugin_requires[@]} -eq 0 ]]; then
    echo "No Composer dependencies in ${composer_json}." >&2
    exit 1
  fi

  local line pkg ver
  for line in "${plugin_requires[@]}"; do
    [[ -n "${line}" ]] || continue
    pkg="${line%%$'\t'*}"
    ver="${line#*$'\t'}"
    echo "[install] Requiring ${pkg}:${ver} (from plugin composer.json)..."
    composer_require "${pkg}:${ver}"
  done
}

install_plugin() {
  case "${PLUGIN_SOURCE}" in
    composer)
      echo "[install] Installing plugin via Composer (${PLUGIN_PACKAGE} ${PLUGIN_CONSTRAINT})..."
      composer config repositories.local path "${WORKSPACE}"
      COMPOSER_MIRROR_PATH_REPOS=1 composer_require \
        "${PLUGIN_PACKAGE}" "${PLUGIN_CONSTRAINT}"
      ;;
    app_code)
      echo "[install] Using bind-mounted plugin at ${PLUGIN_CODE_PATH}..."
      if [[ ! -f "${PLUGIN_CODE_PATH}/registration.php" ]]; then
        echo "Missing ${PLUGIN_CODE_PATH}/registration.php — is the workspace mounted to app/code?" >&2
        exit 1
      fi
      # Module is in app/code; libraries from composer.json must still land in Magento vendor/.
      require_plugin_composer_dependencies
      if [[ ! -d "${MAGENTO_DIR}/vendor/netzkollektiv/easycredit-api-v3-php" ]]; then
        echo "netzkollektiv/easycredit-api-v3-php was not installed under ${MAGENTO_DIR}/vendor." >&2
        exit 1
      fi
      echo "[install] netzkollektiv/easycredit-api-v3-php installed."
      ;;
    *)
      echo "Unsupported PLUGIN_SOURCE: ${PLUGIN_SOURCE} (use composer or app_code)" >&2
      exit 1
      ;;
  esac
}

cd "${MAGENTO_DIR}"

configure_composer_allow_plugins
composer_install
composer config minimum-stability dev
install_plugin
composer_require community-engineering/language-de_DE
if ! COMPOSER_NO_DEV=1 composer remove --no-interaction magento/composer-dependency-version-audit-plugin 2>/dev/null; then
  echo "[install] Keeping magento/composer-dependency-version-audit-plugin (required by another package)."
fi
composer_install

echo "[install] database: ${DB_USER}@${DB_HOST}/${DB_NAME}"

# shellcheck disable=SC2046
php bin/magento setup:install \
  --base-url="${BASE_URL}" \
  --db-host="${DB_HOST}" \
  --db-name="${DB_NAME}" \
  --db-user="${DB_USER}" \
  --db-password="${DB_PASSWORD}" \
  --admin-firstname="${ADMIN_FIRSTNAME}" \
  --admin-lastname="${ADMIN_LASTNAME}" \
  --admin-email="${ADMIN_EMAIL}" \
  --admin-user="${ADMIN_USER}" \
  --admin-password="${ADMIN_PASSWORD}" \
  --language=de_DE \
  --currency=EUR \
  --timezone=Europe/Berlin \
  --backend-frontname="${BACKEND_FRONTNAME}" \
  $(search_install_args)

mysql_cli "${DB_USER}" "${DB_PASSWORD}" \
  -e "UPDATE admin_user Set interface_locale = 'de_DE';" "${DB_NAME}"

disable_module_if_present Magento_AdminAnalytics
disable_module_if_present Magento_TwoFactorAuth
php bin/magento s:up
php bin/magento deploy:mode:set production -s
prepare_for_di_compile
php bin/magento s:di:com
php bin/magento s:static:depl de_DE en_US -j 8

php bin/magento indexer:set-mode realtime
php bin/magento config:set customer/address/telephone_show opt
php bin/magento config:set payment/easycredit/credentials/api_key "${EASYCREDIT_API_KEY:-}"
php bin/magento config:set payment/easycredit/credentials/api_token "${EASYCREDIT_API_TOKEN:-}"
php bin/magento config:set payment/easycredit/credentials/api_signature "${EASYCREDIT_API_SIGNATURE:-}"

php bin/magento cache:flush
