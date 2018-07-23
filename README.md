# Openmeetings

**Minimum ILIAS Version:**
5.1.0

**Maximum ILIAS Version:**
5.3.999

**Responsible Developer:**
Uwe Kohnle - kohnle AT internetlehrer-gmbh.de

**Supported Languages:**
German, English, French

**Related Plugins:**
-

**Bug Tracker:**
[ILIAS MantisBT](https://www.ilias.de/mantis/view_all_bug_page.php)

### Quick Installation Guide

Install Openmetings as per instructions at http://openmeetings.apache.org/

Create administrative user in openmeetings with right 'Soap'.
Install openmetings plugin to ILIAS in ./Customizing/global/plugins/Services/Repository/RepositoryObject/Openmeetings
Complete configuration screen on ILIAS
* Set full URL including "http://".
* Set port. Default is 5080.
* Set directory. Default is 'openmeetings'.
* Set username.
* Set password.

Openmeetings room is now available as a repository object within ILIAS.