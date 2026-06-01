#!/bin/bash
# if [ -f ../nag-bk/auth.json ]; then
#     cp ../nag-bk/auth.json .
# fi
php bin/magento maintenance:enable
#composer update
composer install
rm -rf generated/*
php bin/magento setup:upgrade
php bin/magento setu:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
php bin/magento indexer:reindex
php bin/magento indexer:status
#php bin/magento indexer:reset
php bin/magento maintenance:disable
# if [ -f ./auth.json ]; then
#     trap "rm -f ./auth.json" EXIT
# fi

# This keeps the consumer alive after terminal closes.
# nohup php bin/magento queue:consumers:start async.operations.all &