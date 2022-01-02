# CHANGELOG

## Version 2.0.2

### bug

* assign dependabot pr to patrick1990 (#124)
* increase dependabot pull request limit for npm dependencies (#116)
* set one admin setting twice in system test for github actions (#114)

### dependencies

* Bump phpunit/phpunit from 9.5.10 to 9.5.11 (#125)
* Bump eslint from 8.4.1 to 8.5.0 (#122)
* Bump sass from 1.45.0 to 1.45.1 (#121)
* Bump sass from 1.44.0 to 1.45.0 (#117)
* Bump @babel/preset-env from 7.16.4 to 7.16.5 (#118)
* Bump @babel/core from 7.16.0 to 7.16.5 (#119)
* Bump @babel/eslint-parser from 7.16.3 to 7.16.5 (#120)
* Bump eslint from 8.4.0 to 8.4.1 (#111)
* Bump sass-loader from 12.3.0 to 12.4.0 (#112)
* Bump webpack from 5.64.4 to 5.65.0 (#110)
* Bump eslint from 8.3.0 to 8.4.0 (#109)

## Version 2.0.1

### bug

* add tolerance for timestamp checks in integration tests (#108)
* Make dailog box wait on admin settings system test more robust (#105)
* Change dependabot interval to weekly (#103)

### dependencies

* Bump sass from 1.43.5 to 1.44.0 (#106)
* Bump @nextcloud/axios from 1.7.0 to 1.8.0 (#104)
* Bump webpack from 5.64.3 to 5.64.4 (#100)
* Bump sass from 1.43.4 to 1.43.5 (#99)
* Bump webpack from 5.64.2 to 5.64.3 (#98)
* Bump selenium-webdriver from 4.0.0 to 4.1.0 (#95)
* Bump eslint from 8.2.0 to 8.3.0 (#96)
* Bump webpack from 5.64.1 to 5.64.2 (#97)
* Bump @babel/preset-env from 7.16.0 to 7.16.4 (#94)
* Bump webpack from 5.64.0 to 5.64.1 (#93)
* Bump eslint from 7.20.0 to 8.2.0 (#78)

## Version 2.0.0

### nextcloud

* Support for NC version 23 (#91)

### feature

* Add selenium frontend tests (#70)

### bug

* change strategy to check for staleness in system tests (#89)
* default timeout for system tests  (#75)

### dependencies

* Bump webpack from 5.63.0 to 5.64.0 (#92)
* Bump phpunit/phpunit from 8.5.13 to 9.5.10 (#58)
* Bump jquery from 3.5.1 to 3.6.0 (#85)
* Bump webpack-merge from 5.7.3 to 5.8.0 (#86)
* Bump sass from 1.32.8 to 1.43.4 (#90)
* Bump sass-loader from 11.0.1 to 12.3.0 (#87)
* Bump @babel/eslint-parser from 7.16.0 to 7.16.3 (#88)
* Bump webpack from 5.21.2 to 5.63.0 (#84)
* Bump @nextcloud/router from 1.2.0 to 2.0.0 (#79)
* Bump babel-loader from 8.2.2 to 8.2.3 (#81)
* Bump style-loader from 2.0.0 to 3.3.1 (#82)
* Bump @nextcloud/axios from 1.6.0 to 1.7.0 (#76)
* Bump @nextcloud/dialogs from 3.1.1 to 3.1.2 (#34)
* Bump selenium-webdriver from 4.0.0-rc-2 to 4.0.0 (#77)
* Bump @babel/eslint-parser from 7.12.17 to 7.16.0 (#72)
* Bump webpack-cli from 4.5.0 to 4.9.1 (#65)
* Bump clean-webpack-plugin from 3.0.0 to 4.0.0 (#73)
* Bump @babel/core from 7.12.16 to 7.16.0 (#67)
* Bump @nextcloud/browserslist-config from 1.0.0 to 2.2.0 (#71)
* Bump @babel/preset-env from 7.12.16 to 7.16.0 (#68)
* Bump css-loader from 5.0.2 to 6.5.1 (#69)

## Version 1.5.0

### feature

* Connect to Nextcloud unified search (#60)

## Version 1.4.0

### feature

* Automatic PR to support new NC versions (#48)

### bug

* Prevent parallel Github Actions workflow runs (#49)

## Version 1.3.1

### bug

* add pat to release cycle to trigger auto app packaging (#44)
* allow boolean fields to be nullable for oracle (#43)
* make php tests more robust on timestamp and id checks (#42)
* use svg icons for navigation bar to avoid overlapping labels (#40)

## Version 1.3.0

### feature

* enable dependabot (#32)

## Version 1.2.0

### feature

* add github action for packaging app (#31)

## Version 1.1.4

### feature

* create changelog file (#26)
* run app tests on pr into dev (#27)
* add github action for checking semantic labels (#28)
* add github action for implementing release cycle (#29)

## Version 1.1.2

### bug

* Update Readme (#10)
* Fix bug on ready countdown if one creates a new alias (#11)
* Added branch protection and branch structure to prepare for CI/CD (Github Actions) and Dependabot.

## Version 1.1.1

### bug

* Update info.xml to the release version

## Version 1.1.0

### feature

* Add a button for sending test mails (#8)
* Add a ready countdown that shows the user when an alias is ready (#9)

