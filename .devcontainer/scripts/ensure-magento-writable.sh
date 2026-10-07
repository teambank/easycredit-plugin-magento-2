#!/usr/bin/env bash
# Magento CLI and setup:install run as vscode; Apache serves as www-data. Both need write access.
set -euo pipefail

MAGENTO_DIR="${MAGENTO_DIR:-/opt/magento}"

if [[ ! -d "${MAGENTO_DIR}/bin" ]]; then
  exit 0
fi

writable=(
  "${MAGENTO_DIR}"
)

for dir in "${writable[@]}"; do
  sudo mkdir -p "${dir}"
done

sudo chown -R vscode:www-data "${writable[@]}"
sudo chmod -R g+rwX "${writable[@]}"
sudo find "${writable[@]}" -type d -exec chmod g+s {} +

# Keep project auth.json world-readable; bind mounts may not allow chown and vscode must read it.
if [[ -f "${MAGENTO_DIR}/auth.json" ]]; then
  sudo chmod a+r "${MAGENTO_DIR}/auth.json" 2>/dev/null || chmod a+r "${MAGENTO_DIR}/auth.json" 2>/dev/null || true
fi
