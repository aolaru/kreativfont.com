#!/usr/bin/env bash
set -euo pipefail
task_root="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
task_stage="$(mktemp -d)"
mkdir -p "${task_root}/dist" "${task_stage}/kreativ-gpt-font-dentifier"
cp -R "${task_root}/plugin/." "${task_stage}/kreativ-gpt-font-dentifier/"
cd "${task_stage}"
zip -qr "${task_root}/dist/kreativ-gpt-font-dentifier-0.6.0.zip" kreativ-gpt-font-dentifier
echo "${task_root}/dist/kreativ-gpt-font-dentifier-0.6.0.zip"
