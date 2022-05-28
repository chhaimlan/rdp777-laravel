#!/bin/bash
isExistHttps=`pgrep nginx`
if [[ -n  $isExistHttps ]]; then
    systemctl stop nginx
fi
