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

If you can and want to help out, please let me know.

Copyright (c) 2013 by Jochem Kossen <jochem@jkossen.nl>. This software was
released under the GNU GPLv3 license. See the LICENSE file or
https://www.gnu.org/licenses/gpl-3.0.html for more information.

