clamp
=====

### [<span class="octicon octicon-link"></span>](#welcome-to-clamp) Welcome to Clamp

Clamp is a Command Line Apache MySQL PHP for Mac OS X made for local development. It aims at being ultra simple to use and configure.

*   Uses system's Apache and PHP, and MariaDB using homebrew.
*   All data are self contained in a `.clamp` folder.
*   Configuration is done using a JSON file.

<span class="octicon octicon-alert"></span> It should **only** be used for local development. It is absolutely not secure for anything else.

### [<span class="octicon octicon-link"></span>](#installation) Installation

    $ curl http://jide.github.io/clamp/install.sh | sh`</pre>

    You must have [homebrew](http://brew.sh) installed.

    ### [<span class="octicon octicon-link"></span>](#quickstart) Usage

    To serve the current folder :

    `$ clamp`

    Use <kbd>Ctrl</kbd> + <kbd>C</kbd> to exit.

    By default, this will start apache, create a database named "db", start mysql daemon and set the host as "localhost". You can customize these settings using the [configuration](#configuration) file.

    ### [<span class="octicon octicon-link"></span>](#all-commands) All commands

    `$ clamp apache start // Start apache.
    $ clamp apache stop // Stop apache.

    $ clamp mysql start // Install database, start daemon and create db.
    $ clamp mysql stop // Shortcut for mysql daemon stop.
    $ clamp mysql daemon start // Start mysql daemon.
    $ clamp mysql daemon stop // Stop mysql daemon.
    $ clamp mysql install // Install.
    $ clamp mysql create-db [database] // Create a database.
    $ clamp mysql export [database] [file] // Export databases.
    $ clamp mysql import [file] [database] // Import SQL file.

    $ clamp host set [host] // Add a host.
    $ clamp host unset [host] // Remove a host.
    `

    ### [<span class="octicon octicon-link"></span>](#configuration) Configuration

    All configuration resides in a clamp.json file. All the parameters are built using this file and then passed to the different commands. It uses a few tricks that make it very flexible. 

    A simple configuration :

    `{
        "address": "localhost",
        "memory": "256M",
        "database": "db",
    }`

    You can also configure each executable and their options :

    `{
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
    }`

    You can insert the current working direcory :

    `"errorlog": "'{{$cwd}}/.clamp/logs/apache.error.log'"`</pre>

    You can even use a path to another option :

    `"php": {
        "options": {
            "memory_limit": "{{$.memory}}",
            "pdo_mysql.default_socket": "{{$.mysql.options.socket}}",
            "mysql.default_socket": "{{$.mysql.options.socket}}",
            "mysqli.default_socket": "{{$.mysql.options.socket}}"
        }
    }`

    See the [default configuration file](https://github.com/jide/clamp/blob/master/clamp.defaults.json) for a detailed view of the options.

    ### [<span class="octicon octicon-link"></span>](#file-structure) File structure

    Everything is self-contained in a `.clamp` folder inside your project: The database files, the logs and the socket and PID files.

    `.clamp
      - data // The database files
      - logs // Apache and MySQL logs
      - tmp // PIDs and sockets

Note that this is the default configuration. But since you can define these paths in the configuration file, you are free to use a completly different structure.
