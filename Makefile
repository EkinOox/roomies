# Makefile pour simplifier l'ex�cution des tests Roomies
.PHONY: help install test test-backend test-frontend test-e2e clean

# Affiche l'aide avec toutes les commandes disponibles
help:
	@echo "?? Roomies App - Commandes de Test"
	@echo ""
	@echo "?? Installation:"
	@echo "  make install          - Installe toutes les dépendances"
	@echo "  make install-backend  - Installe dépendances backend uniquement"
	@echo "  make install-frontend - Installe dépendances frontend uniquement"
	@echo ""
	@echo "?? Tests:"
	@echo "  make test            - Exécute tous les tests"
	@echo "  make test-backend    - Tests backend (PHPUnit)"
	@echo "  make test-frontend   - Tests frontend (Vitest)"
	@echo "  make test-e2e        - Tests End-to-End (Cypress)"
	@echo "  make test-watch      - Tests frontend en mode watch"
	@echo "  make test-coverage   - Tests avec couverture de code"
	@echo ""
	@echo "?? Développement:"
	@echo "  make dev             - Démarre backend + frontend"
	@echo "  make dev-backend     - Démarre uniquement le backend"
	@echo "  make dev-frontend    - Démarre uniquement le frontend"
	@echo ""
	@echo "?? Maintenance:"
	@echo "  make clean           - Nettoie les caches et dépendances"
	@echo "  make reset-db        - Réinitialise la base de données de test"

# Installation compléte
install: install-backend install-frontend

install-backend:
	@echo "?? Installation des dépendances backend..."
	cd backend && composer install
	cd backend && composer require --dev phpunit/phpunit symfony/test-pack

install-frontend:
	@echo "?? Installation des dépendances frontend..."
	cd frontend && npm install
	cd frontend && npm install --save-dev vitest @vue/test-utils jsdom cypress @vitest/ui @vitest/coverage-v8

# Tests complets
test: test-backend test-frontend
	@echo "? Tous les tests terminés avec succés!"

# Tests backend avec PHPUnit
test-backend:
	@echo "?? Ex�cution des tests backend..."
	docker compose exec backend php bin/phpunit

test-backend-coverage:
	@echo "?? Tests backend avec couverture..."
	cd backend && php bin/phpunit --coverage-html coverage/

# Tests frontend avec Vitest
test-frontend:
	@echo "?? Ex�cution des tests frontend..."
	cd frontend && npm run test:unit

test-watch:
	@echo "?? Tests frontend en mode watch..."
	cd frontend && npm run test:watch

test-coverage:
	@echo "?? Tests frontend avec couverture..."
	cd frontend && npm run test:coverage

# Tests End-to-End
test-e2e:
	@echo "?? Exécution des tests E2E..."
	@echo "??  Assurez-vous que backend et frontend sont démarrés!"
	cd frontend && npm run e2e

test-e2e-open:
	@echo "?? Ouverture interface Cypress..."
	cd frontend && npm run e2e:open

# Tests spécifiques
test-unit-backend:
	@echo "?? Tests unitaires backend uniquement..."
	cd backend && php bin/phpunit --testsuite Unit

test-integration-backend:
	@echo "?? Tests d'intégration backend..."
	cd backend && php bin/phpunit --testsuite Integration

test-auth:
	@echo "?? Tests d'authentification..."
	cd backend && php bin/phpunit tests/Integration/Controller/AuthControllerTest.php
	cd frontend && npx vitest tests/unit/stores/useAuthStore.test.ts

# Développement
dev:
	@echo "?? Démarrage complet de l'application..."
	@echo "Backend: http://localhost:8000"
	@echo "Frontend: http://localhost:5173"
	@echo "Ouvrez deux terminaux pour voir les logs séparément:"
	@echo "Terminal 1: make dev-backend"
	@echo "Terminal 2: make dev-frontend"

dev-backend:
	@echo "?? Démarrage du backend Symfony..."
	cd backend && symfony server:start

dev-frontend:
	@echo "?? Démarrage du frontend Vue.js..."
	cd frontend && npm run dev

# Base de données
setup-db:
	@echo "??? Configuration de la base de données..."
	cd backend && php bin/console doctrine:database:create --if-not-exists
	cd backend && php bin/console doctrine:migrations:migrate --no-interaction

setup-test-db:
	@echo "?? Configuration de la base de test..."
	cd backend && php bin/console doctrine:database:create --env=test --if-not-exists
	cd backend && php bin/console doctrine:migrations:migrate --env=test --no-interaction

reset-db:
	@echo "??? Réinitialisation de la base de test..."
	cd backend && php bin/console doctrine:database:drop --force --env=test --if-exists
	cd backend && php bin/console doctrine:database:create --env=test
	cd backend && php bin/console doctrine:migrations:migrate --env=test --no-interaction

# Nettoyage
clean:
	@echo "?? Nettoyage des caches et dépendances..."
	cd backend && php bin/console cache:clear
	cd backend && php bin/console cache:clear --env=test
	cd frontend && rm -rf node_modules/.vite
	cd frontend && rm -rf dist
	cd frontend && rm -rf coverage

clean-install: clean
	@echo "?? Nettoyage complet et réinstallation..."
	cd backend && rm -rf vendor
	cd frontend && rm -rf node_modules
	make install

# CI/CD
ci-backend:
	@echo "?? Pipeline CI Backend..."
	cd backend && composer install --no-dev --optimize-autoloader
	cd backend && php bin/console doctrine:database:create --env=test --if-not-exists
	cd backend && php bin/console doctrine:migrations:migrate --env=test --no-interaction
	cd backend && php bin/phpunit --coverage-text

ci-frontend:
	@echo "?? Pipeline CI Frontend..."
	cd frontend && npm ci
	cd frontend && npm run test:coverage
	cd frontend && npm run build

ci: ci-backend ci-frontend test-e2e
	@echo "? Pipeline CI terminé avec succés!"