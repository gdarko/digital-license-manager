#!/bin/bash

BASEDIR=$(dirname "$0")

cd $BASEDIR
cd ..
rm -rf vendor/
composer install --no-dev
cd ..

rm digital-license-manager.zip

zip -r digital-license-manager.zip digital-license-manager \
-x "digital-license-manager/tests/*" \
-x "digital-license-manager/scripts/*" \
-x "digital-license-manager/bin/*" \
-x "digital-license-manager/.git/*" \
-x "digital-license-manager/.gitignore" \
-x "digital-license-manager/.gitattributes" \
-x "digital-license-manager/.phpcs.xml.dist" \
-x "digital-license-manager/.phpunit.result.cache" \
-x "digital-license-manager/.travis.yml" \
-x "digital-license-manager/composer.json" \
-x "digital-license-manager/composer.lock" \
-x "digital-license-manager/phpunit.xml.dist"

echo "NEW VERSION READY"
