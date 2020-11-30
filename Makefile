PACKAGE = easyrdf
VERSION = $(shell php -r "print json_decode(file_get_contents('composer.json'))->version ?? 'dev';")
distdir = $(PACKAGE)-$(VERSION)
PHP = $(shell which php)
PHP_FLAGS = -d memory_limit=1G
COMPOSER_FLAGS=--no-ansi --no-interaction
PHPUNIT = vendor/bin/phpunit
PHPUNIT_FLAGS = -c config/phpunit.xml
PHPCS = vendor/bin/phpcs
PHPCS_FLAGS = --standard=./config/phpcs_ruleset.xml --encoding=utf8 --extensions=php
DOCTUM = vendor/bin/doctum.php

# Composer doesn't work with bsdtar - try and use GNU tar
TAR = $(shell which gtar || which gnutar || which tar)

# Disable copying extended attributes and resource forks on Mac OS X
export COPYFILE_DISABLE=true

EXAMPLE_FILES = examples/*.php
SOURCE_FILES = lib/*.php \
               lib/*/*.php
TEST_FILES = test/*/*Test.php \
             test/*/*/*Test.php
TEST_SUPPORT = Makefile test/cli_example_wrapper.php \
               test/TestHelper.php \
               test/EasyRdf/TestCase.php \
               test/EasyRdf/Http/MockClient.php \
               test/EasyRdf/Serialiser/NtriplesArray.php \
               test/fixtures/*
DOC_FILES = docs/*.md \
            docs/api
INFO_FILES = composer.json \
             doap.rdf \
             README.md \
             LICENSE.md \
             CHANGELOG.md

DISTFILES = $(EXAMPLE_FILES) $(SOURCE_FILES) $(TEST_FILES) \
            $(TEST_SUPPORT) $(INFO_FILES) $(DOC_FILES)


.DEFAULT: help
all: help

# TARGET:test                Run all the PHPUnit tests
.PHONY: test
test: $(PHPUNIT)
	mkdir -p reports
	$(PHP) $(PHP_FLAGS) $(PHPUNIT) $(PHPUNIT_FLAGS)

# TARGET:test-examples       Run PHPUnit tests for each of the examples
.PHONY: test-examples
test-examples: $(PHPUNIT)
	mkdir -p reports
	$(PHP) $(PHP_FLAGS) $(PHPUNIT) $(PHPUNIT_FLAGS) --testsuite "EasyRdf Examples"

# TARGET:test-lib            Run PHPUnit tests for the library
.PHONY: test-lib
test-lib: $(PHPUNIT)
	mkdir -p reports
	$(PHP) $(PHP_FLAGS) $(PHPUNIT) $(PHPUNIT_FLAGS) --testsuite "EasyRdf Library"

# TARGET:coverage            Run library tests and generate coverage report
.PHONY: coverage
coverage: $(PHPUNIT)
	mkdir -p reports/coverage
	$(PHP) $(PHP_FLAGS) $(PHPUNIT) $(PHPUNIT_FLAGS) --coverage-html ./reports/coverage --testsuite "EasyRdf Library"

# TARGET:apidocs             Generate HTML API documentation
.PHONY: apidocs
apidocs: $(DOCTUM)
	$(PHP) $(DOCTUM) update config/doctum.php -n -v --force

docs/api: apidocs

doap.rdf: doap.php composer.json vendor/autoload.php
	$(PHP) doap.php > doap.rdf

# TARGET:cs                  Check the code style of the PHP source code
.PHONY: cs
cs: $(PHPCS)
	$(PHPCS) $(PHPCS_FLAGS) lib test

# TARGET:lint                Perform basic PHP syntax check on all files
.PHONY: lint
lint: $(EXAMPLE_FILES) $(SOURCE_FILES) $(TEST_FILES)
	@for file in $^; do  \
	  $(PHP) -l $$file || exit -1; \
	done

# TARGET:dist                Build archives for distribution
.PHONY: dist
dist: $(distdir).tar.gz
	rm -Rf $(distdir)
	@echo "Done."

%.tar.gz: %
	$(TAR) zcf $@ $^

$(distdir): $(DISTFILES)
	$(gatherfiles)

define gatherfiles
	@for file in $^; do  \
		dir=$@/`dirname "$$file"`; \
		test -d "$$dir" || mkdir -p "$$dir" || exit -1; \
		cp -Rfp "$$file" "$@/$$file" || exit -1; \
	done
endef

# TARGET:clean               Delete any temporary and generated files
.PHONY: clean
clean:
	find . -name '.DS_Store' -type f -delete
	-rm -Rf $(distdir) reports vendor
	-rm -Rf docs/api doctumcache
	-rm -f composer.phar composer.lock
	-rm -f doap.rdf

# TARGET:check-fixme         Scan for files containing the words TODO or FIXME
.PHONY: check-fixme
check-fixme:
	@git grep -n -E 'FIXME|TODO' || echo "No FIXME or TODO lines found."

# TARGET:help                You're looking at it!
.PHONY: help
help:
	# Usage:
	#   make <target> [OPTION=value]
	#
	# Targets:
	@egrep "^# TARGET:" [Mm]akefile | sed 's/^# TARGET:/#   /'
	#
	# Options:
	#   PHP                 Path to php



# Composer rules
composer.phar:
	curl -s -o composer.phar -L http://getcomposer.org/composer-stable.phar

composer-install: composer.phar
	$(PHP) composer.phar $(COMPOSER_FLAGS) install

composer-update: clean composer.phar
	$(PHP) composer.phar $(COMPOSER_FLAGS) update

vendor/autoload.php: composer-install
vendor/bin/phpunit: composer-install
vendor/bin/phpcs: composer-install
vendor/bin/doctum.php: composer-install
