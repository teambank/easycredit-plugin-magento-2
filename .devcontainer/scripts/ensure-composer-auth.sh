#!/usr/bin/env bash
# Make repo.magento.com credentials visible to Composer and bin/magento (sampledata:deploy).
set -euo pipefail

ROOT="${containerWorkspaceFolder:-/workspace}"
MAGENTO_DIR="${MAGENTO_DIR:-/opt/magento}"

AUTH_SRC=""
for candidate in \
  "${ROOT}/.devcontainer/auth.json" \
  "${MAGENTO_DIR}/auth.json"; do
  if [[ -f "${candidate}" ]]; then
    AUTH_SRC="${candidate}"
    break
  fi
done

if [[ -z "${AUTH_SRC}" ]]; then
  echo "[composer-auth] Warning: no auth.json found at .devcontainer/auth.json — repo.magento.com may return HTTP 401." >&2
  if [[ "${BASH_SOURCE[0]}" != "${0}" ]]; then
    return 0
  fi
  exit 0
fi

COMPOSER_HOME="${COMPOSER_HOME:-${HOME}/.composer}"
mkdir -p "${COMPOSER_HOME}"
install -m 600 "${AUTH_SRC}" "${COMPOSER_HOME}/auth.json"

# COMPOSER_AUTH must be inline JSON, not a file path — use auth.json files instead.
unset COMPOSER_AUTH

# bin/magento often shells out to composer without inheriting project-root auth.json.
if [[ -d "${MAGENTO_DIR}/bin" && ! -f "${MAGENTO_DIR}/auth.json" ]]; then
  install -m 644 "${AUTH_SRC}" "${MAGENTO_DIR}/auth.json"
fi
