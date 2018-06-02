include .env

.PHONY: up down stop prune ps shell

default: up

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

backup:
	@echo "Backing up files and db for $(PROJECT_NAME)..."
	docker-compose run --user root -w /var/www/html/web php drush sql-dump --result-file=../db.sql --gzip
	mv db.sql.gz sql/
	rsync -a web/sites/default/files ~/Dropbox\ \(Personal\)/Documents/Kids\ \&\ School/PS267/Helping\ out/ps267.org/backups/files