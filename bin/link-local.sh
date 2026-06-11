#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WP_PLUGINS_DIR="$(cd "${ROOT_DIR}/.." && pwd)"
SOURCE_DIR="${ROOT_DIR}/plugins"

if [[ ! -d "${SOURCE_DIR}" ]]; then
	echo "No plugins directory found at ${SOURCE_DIR}" >&2
	exit 1
fi

linked=0

for plugin_dir in "${SOURCE_DIR}"/*/; do
	[[ -d "${plugin_dir}" ]] || continue

	plugin_name="$(basename "${plugin_dir}")"
	target="${WP_PLUGINS_DIR}/${plugin_name}"

	if [[ -L "${target}" ]]; then
		rm "${target}"
	elif [[ -e "${target}" ]]; then
		echo "Skip ${plugin_name}: ${target} exists and is not a symlink." >&2
		continue
	fi

	ln -s "${plugin_dir%/}" "${target}"
	echo "Linked ${plugin_name} -> ${target}"
	linked=$((linked + 1))
done

if [[ "${linked}" -eq 0 ]]; then
	echo "No plugins linked. Add a plugin under plugins/ first." >&2
	exit 1
fi

echo "Done. ${linked} plugin(s) linked."
