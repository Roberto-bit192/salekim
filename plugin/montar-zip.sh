#!/usr/bin/env bash
# Gera dist/salekim-site.zip a partir de plugin/salekim-site.
# Uso: bash plugin/montar-zip.sh
set -euo pipefail
cd "$(dirname "$0")"
for f in $(find salekim-site -name '*.php'); do php -l "$f" >/dev/null; done
mkdir -p ../dist
rm -f ../dist/salekim-site.zip
zip -rqX ../dist/salekim-site.zip salekim-site -x '*.DS_Store'
echo "ok: dist/salekim-site.zip ($(du -h ../dist/salekim-site.zip | cut -f1))"
