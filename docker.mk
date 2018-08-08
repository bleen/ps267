include .env

.PHONY: up down stop prune ps shell

default: list

list:
	@echo "list"
	@echo "up"
	@echo "down"
	@echo "stop"
	@echo "prune"
	@echo "ps"
	@echo "shell"
	@echo "export-config"
	@echo "backup"
	@echo "reset"
	@echo "deploy"

up:
	@echo "Starting up containers for $(PROJECT_NAME)..."
	docker-compose pull --parallel
	docker-compose up -d --remove-orphans

down: stop

stop:
	@echo "Stopping containers for $(PROJECT_NAME)..."
	@docker-compose stop

prune:
	@echo "Removing containers for $(PROJECT_NAME)..."
	@docker-compose down -v

ps:
	@docker ps --filter name='$(PROJECT_NAME)*'

shell:
	docker exec -ti $(shell docker ps --filter name='$(PROJECT_NAME)_php' --format "{{ .ID }}") sh

export-config:
	@echo "Exporting configs to $(PROJECT_CONFIG_LOCATION)"
	docker-compose run --user root -w ${PROJECT_ROOT} php drush config-export --destination=${PROJECT_CONFIG_LOCATION}

backup:
	@echo "Backing up files and db for $(PROJECT_NAME)..."
	docker-compose run --user root -w ${PROJECT_ROOT} php drush sql-dump --result-file=../db.sql --gzip
	mv db.sql.gz sql/
	rsync -a web/sites/default/files ~/Dropbox\ \(Personal\)/Documents/Kids\ \&\ School/PS267/Helping\ out/ps267.org/backups/files

reset:
	@echo "Getting DB and files from 'Live' and using them to reset the local DB and files"
	docker-compose run --user root -w ${PROJECT_ROOT} php drush sql:sync --structure-tables-list=watchdog,cache_\*,semaphore,sessions @ps267.live @ps267.container -y
	docker-compose run --user root -w ${PROJECT_ROOT} php drush core:rsync @ps267.live:%files @ps267.container:%files -y
	docker exec -ti --user root $(shell docker ps --filter name='$(PROJECT_NAME)_php' --format "{{ .ID }}") chmod -R 777 ${PROJECT_ROOT}/sites/default/files

deploy:
	@echo "Deploying 'live' site"
	docker-compose run --user root -w ${PROJECT_ROOT} php drush @ps267.live state:set system.maintenance_mode 1
	docker-compose run --user root -w ${PROJECT_ROOT} php drush @ps267.live ssh git pull bleen master
	docker-compose run --user root -w ${PROJECT_ROOT} php drush @ps267.live updb -y
	docker-compose run --user root -w ${PROJECT_ROOT} php drush @ps267.live config:import --partial -y
	docker-compose run --user root -w ${PROJECT_ROOT} php drush @ps267.live state:set system.maintenance_mode 0
	docker-compose run --user root -w ${PROJECT_ROOT} php drush @ps267.live cr
	@echo "Deployment complete"
