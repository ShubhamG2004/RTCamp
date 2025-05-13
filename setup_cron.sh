#!/bin/bash

# Get the absolute path to the cron.php file
CRON_SCRIPT_PATH="$(pwd)/cron.php"

# Verify PHP is available
PHP_PATH=$(which php)
if [ -z "$PHP_PATH" ]; then
    echo "Error: PHP is not installed or not in PATH."
    exit 1
fi

# Create the cron job command (run daily at midnight)
CRON_JOB="0 0 * * * $PHP_PATH $CRON_SCRIPT_PATH"

# Add to crontab
(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -

# Verify the cron job was added
echo "Cron job has been set up to run every 24 hours at midnight."
echo "Current crontab:"
crontab -l

# Create a log file for the cron job
touch "$(pwd)/cron.log"
chmod 666 "$(pwd)/cron.log"
echo "Cron job logs will be written to: $(pwd)/cron.log"