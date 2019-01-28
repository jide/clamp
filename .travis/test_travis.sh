#!/usr/bin/env bash

set -e
# make the exit code of commands the left most non-zero, vs last
set -o pipefail

# TODO: test multiple configs
cp .travis/clamp.json .

set +e
OUTPUT=$(/usr/local/opt/coreutils/libexec/gnubin/timeout 20 './clamp' | tee /dev/tty)
retVal=$?
set -e
# cleanup any lingering children
pkill httpd | true

echo -n "Test 1: "
if [ $retVal != 124 ]; then
  echo "Non-124 exit code ($retVal)!"
  exit 1
fi
echo "OK"

echo -n "Test 2: "
if [[ "$OUTPUT" != *"Apache server started"* ]]; then
  echo "ERROR: Apache didn't seem to start!"
  exit 1
fi
echo "OK"

echo -n "Test 3: "
if [[ "$OUTPUT" != *"MySQL server started"* ]]; then
  echo "ERROR: MySQL didn't seem to start!"
  exit 1
fi
echo "OK"
