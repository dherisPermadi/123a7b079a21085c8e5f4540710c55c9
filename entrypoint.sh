#!/bin/bash
sleep 5

# Run migration script
php config/migration/migrate.php

# Run worker
nohup php worker.php &

# Start PHP server
php -S 0.0.0.0:8000
