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

if [ $ret -eq 0 ] && [ -x "$tar" ]; then
  echo "tar=$tar"
  echo "version:"
  $tar --version
  ret=$?
fi

if [ $ret -eq 0 ]; then
  (exit 0)
else
  echo "No suitable tar program found."
  exit 1
fi

url="https://github.com/jide/clamp/tarball/master"

echo "fetching: $url" >&2

echo "$TMP"

cd "$TMP" \
  && curl -SsL "$url" \
     | $tar -xzf - \
  && mv "$TMP"/* "$TMP"/clamp \
  && mv "$TMP"/clamp /usr/local \
  && ln -s /usr/local/clamp/clamp /usr/local/bin/clamp \
  && cd "$BACK" \
