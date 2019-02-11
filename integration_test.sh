#!/usr/bin/env bash

set -e
# make the exit code of commands the left most non-zero, vs last
set -o pipefail

# TODO: test multiple configs

set +e
OUTPUT=$(/usr/local/opt/coreutils/libexec/gnubin/timeout 20 './clamp' | tee /dev/tty)
retVal=$?
set -e
# cleanup any lingering children
pkill httpd || true
echo ""

# retVal will be 124 if the command exited due to timeout
# see https://www.gnu.org/software/coreutils/manual/html_node/timeout-invocation.html
echo -n "Test 1: Checking that command exited normally..."
if [ $retVal != 124 ]; then
  echo "Non-124 exit code ($retVal)!"
  exit 1
fi
echo " OK"

echo -n "Test 2: Checking that Apache started..."
if [[ "$OUTPUT" != *"Apache server started"* ]]; then
  echo "ERROR: Apache didn't seem to start!"
  exit 1
fi
echo " OK"

echo -n "Test 3: Checking that MySQL started..."
if [[ "$OUTPUT" != *"MySQL server started"* ]]; then
  echo "ERROR: MySQL didn't seem to start!"
  exit 1
fi
echo " OK"

echo -n "Test 4: Checking that sudo wasn't used..."
if [[ "$OUTPUT" == *"sudo"* ]]; then
  echo "ERROR: sudo was called!"
  exit 1
fi
echo " OK"
