#!/usr/bin/env bash
# Assumed to run in a Shopware 6 development environment

../../../vendor/shopware/platform/src/Administration/Resources/administration/node_modules/.bin/eslint --ignore-path .eslintignore --config ../../../vendor/shopware/platform/src/Administration/Resources/administration/.eslintrc.js --ext .js,.vue --fix .
