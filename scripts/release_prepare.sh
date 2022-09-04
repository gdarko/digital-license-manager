#!/bin/bash

SCRIPT_DIR="$( cd -- "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"
PLUGIN_DIR="$( cd -- "$(dirname "$SCRIPT_DIR")" >/dev/null 2>&1 ; pwd -P )"
PLUGINS_ROOT_DIR="$( cd -- "$(dirname "$PLUGIN_DIR")" >/dev/null 2>&1 ; pwd -P )"
PLUGIN_SLUG=$(basename $PLUGIN_DIR)

if [[ -f "$PLUGIN_DIR/composer.json" ]]; then
  rm -rf "$PLUGIN_DIR/vendor"
  composer install --no-dev
fi

if [ -f "$PLUGINS_ROOT_DIR/$PLUGIN_SLUG.zip" ]; then
  rm "$PLUGINS_ROOT_DIR/$PLUGIN_SLUG.zip"
fi

cd $PLUGINS_ROOT_DIR

zip -r "$PLUGIN_SLUG.zip" "$PLUGIN_SLUG" \
 -x="*tests*" \
 -x="*scripts*" \
 -x="*bin*" \
 -x="*.git*" \
 -x="*.phpcs.xml.dist*" \
 -x="*.phpunit.result.cache*" \
 -x="*.travis.yml*" \
 -x="*composer.json*" \
 -x="*composer.lock*" \
 -x="*phpunit.xml.dist*" \
 -x="*README.md*"

echo "New version ready."
