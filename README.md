[![Build Status](https://travis-ci.org/jkossen/capo.svg?branch=master)](https://travis-ci.org/jkossen/capo)

Capo - Cacti portal
===================

A JSON API and HTML5 frontend for querying multiple Cacti installations and
displaying graphs

![Capo](../master/doc/screenshots/capo1.png?raw=true)

- Search with a single query through multiple Cacti installations
- Display graphs from different Cacti installations next to eachother
- Export graphs and graph selections to PDF
- Save graph selections to reload them at a later time
- Weathermap support with auto-refreshing and slideshow
- Groups based authorization (authorize groups for access to specific cacti instances)
- LDAP possible, though not required
- WSSE authenticated JSON API to integrate with other applications

Capo was made using the Symfony PHP framework, and can be installed just like
any other symfony project. The web frontend is constructed in HTML5 using
Twitter Bootstrap and jQuery and supports all regular browsers.

The software is quite stable and has been in use for quite a while at a large
organization. Documentation is currently lacking, but has top priority and will
follow soon.

*NOTE*: Unfortunately, Capo is no longer under active development. If you want to contribute and/or take over the project please contact me.

What's missing currently are installation instructions. Basically it comes down to:

- create a capo database and user
- install composer (getcomposer.org/download)
- composer install (will ask a few questions regarding database setup)

- adjust the SALT, CODE and IP addresses in scripts/capo_head.php
- copy the scripts/capo_* files onto the actual Cacti servers

- Adjust (make sure paths are correct) and install scripts/capo.cron into the cron of the Capo server
- Log in as an admin to Capo, add Cacti instances (cacti servers), groups and users

After that, you should be able to use Capo, search for graphs and show them on screen.

Copyright (c) 2013 by Jochem Kossen <jochem@jkossen.nl>. This software was
released under the GNU GPLv3 license. See the LICENSE file or
https://www.gnu.org/licenses/gpl-3.0.html for more information.

