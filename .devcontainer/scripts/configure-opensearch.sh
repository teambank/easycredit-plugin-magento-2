#!/usr/bin/env bash
# Point Magento at the compose OpenSearch service and keep the dev cluster writable.
set -euo pipefail

MAGENTO_DIR="${MAGENTO_DIR:-/opt/magento}"
OS_HOST="${OPENSEARCH_HOST:-opensearch}"
OS_PORT="${OPENSEARCH_PORT:-9200}"
OS_PREFIX="${OPENSEARCH_INDEX_PREFIX:-magento2}"
OS_TIMEOUT="${OPENSEARCH_TIMEOUT:-15}"
OS_URL="http://${OS_HOST}:${OS_PORT}"

wait_for_opensearch() {
  local i
  for i in $(seq 1 60); do
    if curl -sf "${OS_URL}" >/dev/null 2>&1; then
      return 0
    fi
    sleep 2
  done
  echo "OpenSearch not reachable at ${OS_URL}" >&2
  return 1
}

configure_cluster() {
  curl -sf -X PUT "${OS_URL}/_cluster/settings" \
    -H 'Content-Type: application/json' \
    -d '{
      "persistent": {
        "cluster.routing.allocation.disk.threshold_enabled": false,
        "cluster.blocks.create_index": null
      }
    }' >/dev/null
}

configure_magento_db() {
  if [[ ! -f "${MAGENTO_DIR}/app/etc/env.php" ]]; then
    return 0
  fi

  export OS_HOST OS_PORT OS_PREFIX OS_TIMEOUT
  php -r '
    $host = getenv("OS_HOST");
    $port = getenv("OS_PORT");
    $prefix = getenv("OS_PREFIX");
    $timeout = getenv("OS_TIMEOUT");
    $pdo = new PDO("mysql:host=db;dbname=magento", "magento", "magento");
    $configs = [
      ["default", 0, "catalog/search/engine", "opensearch"],
      ["default", 0, "catalog/search/opensearch_server_hostname", $host],
      ["default", 0, "catalog/search/opensearch_server_port", $port],
      ["default", 0, "catalog/search/opensearch_index_prefix", $prefix],
      ["default", 0, "catalog/search/opensearch_enable_auth", "0"],
      ["default", 0, "catalog/search/opensearch_server_timeout", $timeout],
    ];
    $stmt = $pdo->prepare(
      "INSERT INTO core_config_data (scope, scope_id, path, value)
       VALUES (?, ?, ?, ?)
       ON DUPLICATE KEY UPDATE value = VALUES(value)"
    );
    foreach ($configs as $c) {
      $stmt->execute($c);
    }
  '
}

echo "[devcontainer] Configuring OpenSearch (${OS_URL})..."
wait_for_opensearch
configure_cluster
configure_magento_db
echo "[devcontainer] OpenSearch ready for Magento (host=${OS_HOST}, port=${OS_PORT})."
