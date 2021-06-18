# Makefile for building the project

# Dependencies:
# * make
# * composer
# * npm
# * krankerl (https://github.com/ChristophWurst/krankerl)

app_name=postmag
build_dir=$(CURDIR)/build
artifacts_dir=$(build_dir)/artifacts

all: build

.PHONY: clean-deps-composer
clean-deps-composer:
	rm -rf $(CURDIR)/vendor
	rm -f $(CURDIR)/composer.phar

.PHONY: clean-deps-npm
clean-deps-npm:
	rm -rf $(CURDIR)/node_modules

.PHONY: clean-deps
clean-deps: clean-deps-composer clean-deps-npm

.PHONY: clean-js
clean-js:
	rm -rf $(CURDIR)/js

.PHONY: clean
clean: clean-deps clean-js
	rm -rf $(build_dir)
	
.PHONY: install-deps-composer
install-deps-composer:
	composer install -o --no-dev
	
.PHONY: install-deps-composer-dev
install-deps-composer-dev:
	composer install -o
	
.PHONY: install-deps-npm
install-deps-npm:
	npm install --production
	
.PHONY: install-deps-npm-dev
install-deps-npm-dev:
	npm install
	
.PHONY: install-deps
install-deps: install-deps-composer install-deps-npm

.PHONY: install-deps-dev
install-deps-dev: install-deps-composer-dev install-deps-npm-dev

.PHONY: build
build: install-deps-dev
	npm run build

.PHONY: build-dev
build-dev: install-deps-dev
	npm run dev

.PHONY: test
test: install-deps-dev
	$(CURDIR)/vendor/phpunit/phpunit/phpunit -c phpunit.xml
	$(CURDIR)/vendor/phpunit/phpunit/phpunit -c phpunit.integration.xml
	npm run lint

.PHONY: package
package:
	krankerl package
