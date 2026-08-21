#!/usr/bin/env bash
#
# Packages the runtime plugin files into build/tbt-quotes/ and build/tbt-quotes.zip.
#
# The ZIP contains a single top-level tbt-quotes/ folder, so it installs directly
# through Plugins > Add New Plugin > Upload Plugin. The unpacked build/tbt-quotes/
# directory is what the deploy workflow syncs to wp-content/plugins/tbt-quotes.
#
# Usage: bin/build-plugin.sh [output-dir]   (default: build)

set -euo pipefail

SLUG="tbt-quotes"
REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUTPUT_DIR="${1:-${REPO_ROOT}/build}"
STAGE_DIR="${OUTPUT_DIR}/${SLUG}"

# Allowlist: only these ship to the server. Anything not named here stays in the
# repository, which keeps tests, CI config and developer notes off the live site.
RUNTIME_PATHS=(
	"tbt-quotes.php"
	"uninstall.php"
	"readme.txt"
	"assets"
	"data"
)

cd "${REPO_ROOT}"

echo "==> Checking runtime files"
missing=0
for path in "${RUNTIME_PATHS[@]}"; do
	if [ ! -e "${path}" ]; then
		echo "    MISSING: ${path}" >&2
		missing=1
	fi
done
if [ "${missing}" -ne 0 ]; then
	echo "Refusing to build: the plugin runtime files listed above are not present." >&2
	echo "Run this from a branch that contains the plugin (for example after PR #1 is merged)." >&2
	exit 1
fi

echo "==> Reading version"
header_version="$(sed -n 's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*\([0-9][^[:space:]]*\).*/\1/p' tbt-quotes.php | head -1)"
const_version="$(sed -n "s/.*private const VERSION[[:space:]]*=[[:space:]]*'\([^']*\)'.*/\1/p" tbt-quotes.php | head -1)"
readme_version="$(sed -n 's/^[[:space:]]*Stable tag:[[:space:]]*\([0-9][^[:space:]]*\).*/\1/p' readme.txt | head -1)"

if [ -z "${header_version}" ]; then
	echo "Could not read 'Version:' from the tbt-quotes.php plugin header." >&2
	exit 1
fi

echo "    plugin header : ${header_version}"
echo "    VERSION const : ${const_version:-<not found>}"
echo "    readme.txt    : ${readme_version:-<not found>}"

# A mismatch means WordPress shows one version while the asset cache-buster and
# the readme claim another, so stop before that reaches the live site.
for other in "${const_version}" "${readme_version}"; do
	if [ -n "${other}" ] && [ "${other}" != "${header_version}" ]; then
		echo "Version mismatch: expected ${header_version} everywhere." >&2
		exit 1
	fi
done

echo "==> Validating data/quotes.json"
if command -v php >/dev/null 2>&1; then
	php -r 'json_decode(file_get_contents("data/quotes.json"), true); if (json_last_error() !== JSON_ERROR_NONE) { fwrite(STDERR, "quotes.json is not valid JSON: " . json_last_error_msg() . "\n"); exit(1); }'
elif command -v node >/dev/null 2>&1; then
	node -e 'JSON.parse(require("fs").readFileSync("data/quotes.json","utf8"))'
else
	echo "    skipped (no php or node available)"
fi

echo "==> Staging ${STAGE_DIR}"
rm -rf "${STAGE_DIR}" "${OUTPUT_DIR}/${SLUG}.zip"
mkdir -p "${STAGE_DIR}"
for path in "${RUNTIME_PATHS[@]}"; do
	cp -R "${path}" "${STAGE_DIR}/"
done

# Editor and OS noise must never reach wp-content/plugins.
find "${STAGE_DIR}" \( -name '.DS_Store' -o -name 'Thumbs.db' -o -name '*.orig' -o -name '*.rej' -o -name '*~' \) -delete

echo "==> Creating ${OUTPUT_DIR}/${SLUG}.zip"
( cd "${OUTPUT_DIR}" && zip -rq "${SLUG}.zip" "${SLUG}" )

echo "==> Done (version ${header_version})"
echo "    folder : ${STAGE_DIR}"
echo "    zip    : ${OUTPUT_DIR}/${SLUG}.zip"
