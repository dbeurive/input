MAKEFILE_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

# ----------------------------------------------------------------------------------------------------------------------
# Settings
# ----------------------------------------------------------------------------------------------------------------------

ARTIFACTS_DIR  := $(MAKEFILE_DIR)../artifacts
ZIP            := /usr/bin/zip
VENDOR         := dbeurive
PACKAGE_NAME   := input

# ----------------------------------------------------------------------------------------------------------------------
# Administration
# ----------------------------------------------------------------------------------------------------------------------

test:
	cd $(MAKEFILE_DIR) && phpunit

# ----------------------------------------------------------------------------------------------------------------------

composer:
ifndef version
	$(error version is not defined) 
endif
	sed 's|__VERSION__|$(version)|g'  $(MAKEFILE_DIR)/composer.tpl > $(MAKEFILE_DIR)/composer.json

# ----------------------------------------------------------------------------------------------------------------------

clean:
	find $(MAKEFILE_DIR) -name .Ulysses-Group.plist -exec rm -rf {} \;
	find $(MAKEFILE_DIR) -name .DS_Store -exec rm -rf {} \;

# ----------------------------------------------------------------------------------------------------------------------

artifact:
ifndef version
	$(error version is not defined) 
endif
	$(MAKE) composer
	$(MAKE) clean
	rm -f $(ARTIFACTS_DIR)/$(VENDOR)-$(PACKAGE_NAME)-$(version).zip
	cd $(MAKEFILE_DIR) && \
   rm -f $(ARTIFACTS_DIR)/$(VENDOR)-$(PACKAGE_NAME)-$(version).zip && \
   find . -not -path '*/\.*' -print | egrep '^\./src|\./composer.json$$' \
   | xargs $(ZIP) -vr $(ARTIFACTS_DIR)/$(VENDOR)-$(PACKAGE_NAME)-$(version).zip

# ----------------------------------------------------------------------------------------------------------------------

publish:
ifndef version
	$(error version is not defined) 
endif
ifndef comment
	$(error comment is not defined) 
endif
	$(MAKE) composer
	git add composer.json
	git commit -m "Create new composer's specifications"
	git push origin master
	git tag
	git tag -a $(version) -m "$(comment)"
	git push origin --tags


