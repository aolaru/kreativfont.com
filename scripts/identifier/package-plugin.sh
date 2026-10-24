#!/usr/bin/env bash
set -euo pipefail
task_root="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
task_version="$(php -r 'preg_match("/Plugin Name:.*?Version:\\s*([^\\s]+)/s", file_get_contents($argv[1]), $m); echo $m[1];' "${task_root}/plugin/gpt-font-id.php")"
task_stage="$(mktemp -d)"
mkdir -p "${task_root}/dist" "${task_stage}/kreativ-gpt-font-dentifier"
cp -R "${task_root}/plugin/." "${task_stage}/kreativ-gpt-font-dentifier/"
cd "${task_stage}"
zip -qr "${task_root}/dist/kreativ-gpt-font-dentifier-${task_version}.zip" kreativ-gpt-font-dentifier
echo "${task_root}/dist/kreativ-gpt-font-dentifier-${task_version}.zip"
