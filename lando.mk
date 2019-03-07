include .env

.PHONY: up down stop prune ps shell

default: list

list:
	@echo "list"
	@echo "export-config"
	@echo "backup"
	@echo "reset"
	@echo "update"
	@echo "deploy"

export-config:
	@echo "Exporting configs to $(PROJECT_CONFIG_LOCATION)"
	lando drush config-export -y --destination=${PROJECT_CONFIG_LOCATION}

backup:
	@echo "Backing up files and db for $(PROJECT_NAME)..."
	lando drush sql-dump --result-file=../db.sql --gzip
	lando ssh -c "mv db.sql.gz $(PROJECT_ROOT)/sql/"
	rsync -a $(PROJECT_LOCAL_ROOT)/web/sites/default/files /Users/bleen/Documents/Kids\ \&\ School/PS267/Helping\ out/ps267.org/backups/files

reset:
	@echo "Getting DB and files from 'Live' and using them to reset the local DB and files"
	lando drush sql:sync --structure-tables-list=watchdog,cache_\*,semaphore,sessions @ps267.live @self -y
	lando drush core:rsync @ps267.live:%files @self:%files -y
	lando ssh -c "chmod -R 777 ${PROJECT_DOCROOT}/sites/default/files"

update:
	@echo "Updating Drupal (and modules) based on changes to composer.json"
	lando composer update
	lando drush updb -y
	make export-config

deploy:
	@echo "Deploying 'live' site"
	lando drush @ps267.live state:set system.maintenance_mode 1
	lando drush @ps267.live ssh git pull bleen master
	lando drush @ps267.live updb -y
	lando drush @ps267.live config:import -y
	lando drush @ps267.live state:set system.maintenance_mode 0
	lando drush @ps267.live cr
	@echo "Deployment complete"