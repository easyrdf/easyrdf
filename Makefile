PHP = $(shell which php)

EXAMPLE_FILES = examples/*.php
SOURCE_FILES = lib/*.php \
               lib/*/*.php
TEST_FILES = test/*/*Test.php \
             test/*/*/*Test.php

# Perform basic PHP syntax check on all files
lint: $(EXAMPLE_FILES) $(SOURCE_FILES) $(TEST_FILES)
	@for file in $^; do  \
	  $(PHP) -l $$file || exit -1; \
	done
