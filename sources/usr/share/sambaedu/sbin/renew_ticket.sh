#!/bin/bash
exec >>/var/log/test_cron 2>&1
kinit -k -t /etc/sambaedu/www-sambaedu.keytab www-sambaedu -V

