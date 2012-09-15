PACKAGE = easyrdf
VERSION = $(shell php -r "print json_decode(file_get_contents('composer.json'))->version;")
distdir = $(PACKAGE)-$(VERSION)
PHP = $(shell which php)
PHPUNIT = $(PHP) $(shell which phpunit) --strict --log-junit ./reports/test-results.xml
PHPCS = phpcs --standard=Zend --tab-width=4 --encoding=utf8 -n
PHPDOC = phpdoc --title "EasyRdf $(VERSION) API Documentation" --output "HTML:frames:default"

EXAMPLE_FILES = examples/*.php
SOURCE_FILES = lib/EasyRdf.php \
               lib/EasyRdf/*.php \
               lib/EasyRdf/*/*.php
TEST_FILES = test/*/*Test.php \
             test/*/*/*Test.php
TEST_SUPPORT = Makefile test/cli_example_wrapper.php \
               test/TestHelper.php \
               test/EasyRdf/TestCase.php \
               test/EasyRdf/Http/MockClient.php \
               test/fixtures/*
DOC_FILES = doap.rdf \
            docs \
            README.md \
            LICENSE.md \
            CHANGELOG.md

DISTFILES = $(EXAMPLE_FILES) $(SOURCE_FILES) $(TEST_FILES) \
            $(TEST_SUPPORT) $(DOC_FILES)

.DEFAULT: help
all: help

# TARGET:test                Run all the PHPUnit tests
.PHONY: test
test:
	mkdir -p reports
	$(PHPUNIT) test

# TARGET:test-examples       Run PHPUnit tests for each of the examples
.PHONY: test-examples
test-examples:
	mkdir -p reports
	$(PHPUNIT) test/examples

# TARGET:test-lib            Run PHPUnit tests for the library
.PHONY: test-lib
test-lib:
	mkdir -p reports
	$(PHPUNIT) test/EasyRdf

# TARGET:coverage            Run all the tests and generate a code coverage report
.PHONY: coverage
coverage:
	mkdir -p reports/coverage
	$(PHPUNIT) --coverage-html ./reports/coverage test

# TARGET:docs                Generate HTML documentation
.PHONY: docs
docs: index.html doap.rdf
	mkdir -p docs
	$(PHPDOC) -d lib -t docs
	
index.html: homepage.php
	$(PHP) homepage.php > index.html

doap.rdf: doap.php
	$(PHP) doap.php > doap.rdf

# TARGET:cs                  Check the code style of the PHP source code
.PHONY: cs
cs: $(SOURCE_FILES) $(TEST_FILES)
	$(PHPCS) $^

# TARGET:lint                Perform basic PHP syntax check on all files
.PHONY: lint
lint: $(EXAMPLE_FILES) $(SOURCE_FILES) $(TEST_FILES)
	@for file in $^; do  \
	  $(PHP) -l $$file || exit -1; \
	done

# TARGET:dist                Build tarball for distribution
.PHONY: dist
dist: $(distdir)
	tar zcf $(distdir).tar.gz $(distdir)
	rm -Rf $(distdir)
	@echo "Created $(distdir).tar.gz"

$(distdir): $(DISTFILES)
	@for file in $^; do  \
		dir=$(distdir)/`dirname "$$file"`; \
		test -d "$$dir" || mkdir -p "$$dir" || exit -1; \
		cp -Rfp "$$file" "$(distdir)/$$file" || exit -1; \
	done

# TARGET:clean               Delete any temporary and generated files
.PHONY: clean
clean:
	-rm -Rf $(distdir) docs reports
	-rm -f doap.rdf index.html

# TARGET:check-fixme         Scan for files containing the words TODO or FIXME
.PHONY: check-fixme
check-fixme:
	@git grep -n -E 'FIXME|TODO' || echo "No FIXME or TODO lines found."

# TARGET:check-whitespace    Scan for files with trailing whitespace
.PHONY: check-whitespace
check-whitespace:
	@git grep -n -E '[ 	]+$$' || echo "No trailing whitespace found."

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
