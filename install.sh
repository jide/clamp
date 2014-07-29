#!/bin/sh

# set the temp dir
TMP="${TMPDIR}"
if [ "x$TMP" = "x" ]; then
  TMP="/tmp"
fi
TMP="${TMP}/clamp.$$"
rm -rf "$TMP" || true
mkdir "$TMP"
if [ $? -ne 0 ]; then
  echo "failed to mkdir $TMP" >&2
  exit 1
fi

# New macs (10.9+) don't ship with /usr/local, however it is still in
# the default PATH. We still install there, we just need to create the
# directory first.
if [ ! -d "/usr/local/bin" ] ; then
  sudo mkdir -m 755 "/usr/local" || true
  sudo mkdir -m 755 "/usr/local/bin" || true
fi

BACK="$PWD"

ret=0
tar="${TAR}"
if [ -z "$tar" ]; then
  tar=`which tar 2>&1`
  ret=$?
fi

if [ $ret -eq 0 ]; then
  (exit 0)
else
  echo "No suitable tar program found." >&2
  exit 1
fi

brew=`which brew 2>&1`
ret=$?

if [ $ret -eq 0 ]; then
  echo "Installing MariaDB" >&2
  brew install mariadb
else
  echo "Please install homebrew before installing clamp. Visit http://brew.sh for more information." >&2
  exit 1
fi

url="https://github.com/jide/clamp/tarball/master"

echo "Fetching $url" >&2

if [ -d "/usr/local/clamp" ]; then
  rm -rf /usr/local/bin/clamp
  rm -rf /usr/local/clamp
fi

cd "$TMP" \
  && curl -SsL "$url" \
     | $tar -xzf - \
  && mv "$TMP"/* "$TMP"/clamp \
  && mv "$TMP"/clamp /usr/local \
  && ln -s /usr/local/clamp/clamp /usr/local/bin/clamp \
  && cd "$BACK" \

echo "Clamp is installed !" >&2

exit