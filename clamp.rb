require "formula"

class Clamp < Formula
  homepage "http://jide.github.io/clamp"
  url "https://github.com/jide/clamp/archive/1.0.tar.gz"
  version "1.0"
  sha1 ""

  depends_on "mariadb"

  def install
    inreplace "clamp", /\/usr\/local\/clamp/, prefix
    prefix.install Dir["*"]
    bin.install_symlink '../clamp'
  end

  test do
    system bin/"clamp", "help"
  end
end
