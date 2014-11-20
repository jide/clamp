Clamp
=====

### [<span class="octicon octicon-link"></span>](#welcome-to-clamp) Welcome to Clamp

Clamp is a Command Line Apache MySQL PHP for Mac OS X made for local development. It aims at being ultra simple to use and configure.

*   Automatically creates a host and a database.
*   Uses system's Apache and PHP, and MariaDB using homebrew.
*   System-wide configuration remains unchanged.
*   All data are self contained in a `.clamp` folder.
*   Configuration is done using a `clamp.json` JSON file.

<span class="octicon octicon-alert"></span> It should **only** be used for local development. It is absolutely not secure for anything else.

### [<span class="octicon octicon-link"></span>](#installation) Installation

```
$ brew install https://raw.githubusercontent.com/jide/clamp/master/clamp.rb
```

You must have [homebrew](http://brew.sh) installed.

### [<span class="octicon octicon-link"></span>](#installation) OS X Yosemite

Run the install script again to update to latest version. After this, the `clamp update` command will be available and you won't have to call this script directly again :

```
$ brew install https://raw.githubusercontent.com/perasmus/clamp_formula/master/clamp.rb
```

If you have trouble running MySQL server, you may have to reinstall MariaDB :

*   Download and install [XCode 6](https://developer.apple.com/downloads/download.action?path=Developer_Tools/xcode_6.1/xcode_6.1.dmg)
*   Update Hombrew : cd /usr/local && git pull
*   Uninstall MariaDB : `brew uninstall mariadb`
*   Reinstall MariaDB : `brew install mariadb`

Since in Yosemite Apache version is 2.4, you may have to correct your .htaccess file :

```
  Order allow,deny
```

Becomes :

```
  <IfModule mod_authz_core.c>
    Require all denied
  </IfModule>
  <IfModule !mod_authz_core.c>
    Order allow,deny
  </IfModule>
```

### [<span class="octicon octicon-link"></span>](#quickstart) Usage

To serve the current folder :

```
$ clamp
```

Use <kbd>Ctrl</kbd> + <kbd>C</kbd> to exit.

By default, this will start apache, create a database named "db", start MySQL daemon and set the host as "localhost". You can customize these settings using the [configuration](#configuration) file.

You can connect to MySQL through localhost:3306 with user "root" and a blank password. [Sequel pro](http://www.sequelpro.com) is a great app for managing your databases.

### [<span class="octicon octicon-link"></span>](#all-commands) All commands

```
$ clamp apache start // Start apache.
$ clamp apache stop // Stop apache.

$ clamp mysql start // Install database, start daemon and create db.
$ clamp mysql stop // Shortcut for mysql daemon stop.
$ clamp mysql daemon start // Start mysql daemon.
$ clamp mysql daemon stop // Stop mysql daemon.
$ clamp mysql install // Install.
$ clamp mysql create-db [database] // Create a database.
$ clamp mysql export [database?] [file?] // Export databases.
$ clamp mysql import [file?] [database?] // Import SQL file.

$ clamp host set [host] // Add a host.
$ clamp host unset [host] // Remove a host.

$ clamp config write // Writes the default clamp.json file in the current folder.

$ clamp update // Update to latest version.
```

### [<span class="octicon octicon-link"></span>](#configuration) Configuration

All configuration resides in a clamp.json file. All the parameters are built using this file and then passed to the different commands. It uses a few tricks that make it very flexible.

To copy the default configuration file in the current directory, use `clamp config write`.

A simple configuration :

```json
{
    "address": "localhost",
    "memory": "256M",
    "database": "db",
}
```

You can also configure each command and their options :

```json
{
    "apache": {
        "commands": {},
        "options": {}
    },
    "host": {
        "options": {}
    },
    "mysql": {
        "commands": {},
        "databases": [],
        "options": {}
    },
    "php": {
        "options": {}
    }
}
```

See the [default configuration file](https://github.com/jide/clamp/blob/master/clamp.defaults.json) for a detailed view of the options.

You can insert the current working direcory :

```json
"errorlog": "'{{$cwd}}/.clamp/logs/apache.error.log'"
```

You can even use a path to another option :

```json
{
    "php": {
        "options": {
            "memory_limit": "{{$.memory}}",
            "pdo_mysql.default_socket": "{{$.mysql.options.socket}}",
            "mysql.default_socket": "{{$.mysql.options.socket}}",
            "mysqli.default_socket": "{{$.mysql.options.socket}}"
        }
    }
}
```

### [<span class="octicon octicon-link"></span>](#file-structure) File structure

Everything is self-contained in a `.clamp` folder inside your project: The database files, the logs and the socket / PID files.

```
.clamp
  - data // The database files
  - logs // Apache and MySQL logs
  - tmp // PID files and sockets
```

Note that this is the default configuration. But since you can define these paths in the configuration file, you are free to use a completly different structure.