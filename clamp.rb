require "formula"

class Clamp < Formula
  homepage "http://jide.github.io/clamp"
  url "https://github.com/jide/clamp.git", :using => :git, :tag => "1.0"
  version "1.0"
  sha1 "9ea012258bbe04a233f5c9b3e0c8583ded041396"

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
