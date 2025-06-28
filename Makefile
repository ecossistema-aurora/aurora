# Makefile para automatizar setup do projeto PHP com Docker

include .env

.PHONY: up install_dependencies generate_proxies migrate_database load_fixtures install_frontend compile_frontend generate_keys

# Função para bloquear comandos em produção
guard-not-prod:
ifeq ($(APP_ENV),prod)
	$(error Este comando não pode ser executado em produção)
endif

# Inicia os serviços Docker em modo detached
up:
	docker compose up -d

# Para os serviços Docker
stop:
	docker compose stop

# Para e remove os serviços Docker
down:
	docker compose --profile '*' down

# Para e remove os serviços Docker
container_php:
	docker compose exec php bash

# Instala dependências dentro do contêiner PHP
install_dependencies:
	docker compose exec -T php bash -c "composer install --ignore-platform-req=ext-mongodb"

# Gera os arquivos de Proxies do MongoDB
generate_proxies:
	docker compose exec -T php bash -c "php bin/console doctrine:mongodb:generate:proxies"

# Executa as migrations no banco relacional e no não relacional
migrate_database: migrate_orm migrate_odm

# Executa as migrations no banco de dados relacional
migrate_orm:
	docker compose exec -T php bash -c "php bin/console doctrine:migrations:migrate -n"

# Executa as migrations no banco de dados não relacional
migrate_odm:
	docker compose exec -T php bash -c "php bin/console app:mongo:migrations:execute"

# Executa as fixtures de dados
load_fixtures: guard-not-prod
	docker compose exec -T php bash -c "php bin/console doctrine:fixtures:load -n --purge-exclusions=city --purge-exclusions=state"

# Instala dependências do frontend
install_frontend:
	docker compose exec -T php bash -c "php bin/console importmap:install"

# Compila os arquivos do frontend
compile_frontend: reset
	docker compose exec -T php bash -c "php bin/console asset-map:compile"

# Abre uma instância gráfica do cypress
open_cypress: load_fixtures
	xhost +local:
	CYPRESS_MODE=open docker compose --profile tests up

# Executa as fixtures de dados e os testes de front-end
tests_front: guard-not-prod
	if [ "$(fixtures)" != "no" ]; then \
		make load_fixtures;\
	fi;
	docker compose up cypress

# Executa as fixtures de dados e os testes de back-end
tests_back:	guard-not-prod
	if [ "$(fixtures)" != "no" ]; then \
		make load_fixtures;\
	fi;
	docker compose exec -T php bash -c "php bin/paratest $(filename) --no-coverage"

# Executa as fixtures de dados e os testes de back-end
tests_back_coverage: guard-not-prod
	if [ "$(fixtures)" != "no" ]; then \
		make load_fixtures;\
	fi;
	docker compose exec -T php bash -c "php -d memory_limit=512M bin/paratest $(filename)"

# Limpa o cache do projeto
reset:
	docker compose exec -T php bash -c "php bin/console cache:clear"

# Limpa a cache e o banco
reset-deep:	guard-not-prod
	rm -rf var/storage
	rm -rf assets/uploads
	rm -rf assets/vendor
	rm -rf public/assets
	rm -rf var/cache
	rm -rf var/log
	docker compose exec -T php bash -c "php bin/console cache:clear"
	docker compose exec -T php bash -c "php bin/console doctrine:mongodb:schema:drop --search-index"
	docker compose exec -T php bash -c "php bin/console doctrine:mongodb:schema:drop --collection"
	docker compose exec -T php bash -c "php bin/console doctrine:mongodb:schema:drop --db"
	docker compose exec -T php bash -c "php bin/console doctrine:mongodb:schema:create"
	docker compose exec -T php bash -c "php bin/console d:d:d -f"
	docker compose exec -T php bash -c "php bin/console d:d:c"
	make migrate_database
	docker compose exec -T php bash -c "php bin/console importmap:install"
	docker compose exec -T php bash -c "php bin/console asset-map:compile"

# Executa o php cs fixer
style:
	docker compose exec -T -e PHP_CS_FIXER_IGNORE_ENV=1 php bash -c "php bin/console app:code-style"
	docker compose exec -T php bash -c "php vendor/bin/phpcs --config-set installed_paths src/Standards"
	docker compose exec -T php bash -c "php vendor/bin/phpcs"

create-admin-user:
	docker compose exec -T php bash -c "php bin/console app:create-admin-user"

# Gera as chaves de autenticação JWT
generate_keys:
	docker compose exec -T php bash -c "php bin/console lexik:jwt:generate-keypair --overwrite -n"

# Copiar arquivos de configurações locais dos pacotes utilizados
copy_dist:
	cp phpcs.xml.dist phpcs.xml
	cp phpunit.xml.dist phpunit.xml

permissions:
	mkdir -p var/
	mkdir -p vendor/
	mkdir -p config/jwt
	chmod -R 775 assets/ config/jwt var/ vendor/ public/

# Comando para rodar todos os passos juntos
setup: guard-not-prod up install_dependencies copy_dist reset-deep generate_proxies migrate_database load_fixtures install_frontend compile_frontend generate_keys
