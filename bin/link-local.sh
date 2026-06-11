#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WP_PLUGINS_DIR="$(cd "${ROOT_DIR}/.." && pwd)"
SOURCE_DIR="${ROOT_DIR}/plugins"

removed=0

for plugin_dir in "${WP_PLUGINS_DIR}"/coverkit-usecase-*; do
	[[ -e "${plugin_dir}" ]] || continue
	[[ -L "${plugin_dir}" ]] || continue

	rm "${plugin_dir}"
	echo "Removed legacy symlink ${plugin_dir}"
	removed=$((removed + 1))
done

if [[ ! -d "${SOURCE_DIR}" ]]; then
	echo "No plugins directory found at ${SOURCE_DIR}" >&2
	exit 1
fi

count="$(find "${SOURCE_DIR}" -mindepth 1 -maxdepth 1 -type d -name 'coverkit-usecase-*' | wc -l | tr -d ' ')"

echo "CoverKit Use Cases loads ${count} use case(s) from plugins/ via coverkit-usecases.php."
echo "Activate only the CoverKit Use Cases plugin in WordPress — individual use case symlinks are not required."

if [[ "${removed}" -gt 0 ]]; then
	echo "Cleaned up ${removed} legacy symlink(s)."
fi
