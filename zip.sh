#!/bin/bash
#
# Package Free WCAG plugin for distribution
#

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_SLUG="free-wcag"
ZIP_FILE="${SCRIPT_DIR}/${PLUGIN_SLUG}.zip"

# Remove existing zip if present
if [ -f "$ZIP_FILE" ]; then
    rm "$ZIP_FILE"
    echo "Removed existing ${PLUGIN_SLUG}.zip"
fi

# Create the zip file
cd "$SCRIPT_DIR"

zip -r "$ZIP_FILE" . \
    -x ".git/*" \
    -x ".git" \
    -x ".gitignore" \
    -x ".gitattributes" \
    -x "*.DS_Store" \
    -x "*.zip" \
    -x "zip.sh" \
    -x "node_modules/*" \
    -x "*.log" \
    -x ".distignore" \
    -x "package.json" \
    -x "package-lock.json" \
    -x "composer.json" \
    -x "composer.lock" \
    -x "phpcs.xml*" \
    -x "phpunit.xml*" \
    -x "tests/*" \
    -x ".editorconfig" \
    -x ".eslintrc*" \
    -x ".prettierrc*" \
    -x "*.md" \
    -x "Thumbs.db"

echo ""
echo "✅ Created ${PLUGIN_SLUG}.zip"
echo "📦 Size: $(du -h "$ZIP_FILE" | cut -f1)"

