#!/bin/bash

# Script de diagnostic et d'exécution des tests
# Permet de vérifier la configuration et d'exécuter les tests avec diagnostic

echo "Diagnostic et Tests - Roomies App"
echo "======================================"

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher les erreurs
print_error() {
    echo -e "${RED}? $1${NC}"
}

# Fonction pour afficher les succés
print_success() {
    echo -e "${GREEN}? $1${NC}"
}

# Fonction pour afficher les avertissements
print_warning() {
    echo -e "${YELLOW}?? $1${NC}"
}

# Fonction pour afficher les infos
print_info() {
    echo -e "${BLUE}?? $1${NC}"
}

# Vérification des prérequis
echo -e "\n${BLUE}?? Vérification des prérequis...${NC}"

# Vérifier Node.js
if command -v node &> /dev/null; then
    NODE_VERSION=$(node --version)
    print_success "Node.js installé: $NODE_VERSION"
else
    print_error "Node.js n'est pas installé"
    exit 1
fi

# Vérifier PHP
if command -v php &> /dev/null; then
    PHP_VERSION=$(php --version | head -n 1)
    print_success "PHP installé: $PHP_VERSION"
else
    print_error "PHP n'est pas installé"
    exit 1
fi

# Vérifier Composer
if command -v composer &> /dev/null; then
    COMPOSER_VERSION=$(composer --version)
    print_success "Composer installé: $COMPOSER_VERSION"
else
    print_error "Composer n'est pas installé"
    exit 1
fi

# Installation des dépendances si nécessaire
echo -e "\n${BLUE}?? Vérification des dépendances...${NC}"

# Backend
if [ ! -d "backend/vendor" ]; then
    print_warning "Dépendances backend manquantes, installation..."
    cd backend
    composer install
    composer require --dev phpunit/phpunit symfony/test-pack
    cd ..
else
    print_success "Dépendances backend présentes"
fi

# Frontend
if [ ! -d "frontend/node_modules" ]; then
    print_warning "Dépendances frontend manquantes, installation..."
    cd frontend
    npm install
    npm install --save-dev vitest @vue/test-utils jsdom @vitest/ui @vitest/coverage-v8
    cd ..
else
    print_success "Dépendances frontend présentes"
fi

# Tests Backend
echo -e "\n${BLUE}?? Tests Backend (PHPUnit)...${NC}"
cd backend

# Vérifier la configuration PHPUnit
if [ -f "phpunit.xml.dist" ]; then
    print_success "Configuration PHPUnit trouvée"
else
    print_error "Configuration PHPUnit manquante"
fi

# Exécuter les tests backend
print_info "Exécution des tests backend..."
if php bin/phpunit --verbose 2>&1; then
    print_success "Tests backend réussis"
else
    print_error "échec des tests backend"
    echo "Détails de l'erreur ci-dessus ??"
fi

cd ..

# Tests Frontend
echo -e "\n${BLUE}?? Tests Frontend (Vitest)...${NC}"
cd frontend

# Vérifier la configuration Vitest
if [ -f "vitest.config.ts" ]; then
    print_success "Configuration Vitest trouvée"
else
    print_error "Configuration Vitest manquante"
fi

# Vérifier les fichiers de test
if [ -d "tests" ]; then
    TEST_COUNT=$(find tests -name "*.test.ts" -o -name "*.spec.ts" | wc -l)
    print_success "Répertoire tests trouvé avec $TEST_COUNT fichiers de test"
else
    print_error "Répertoire tests manquant"
fi

# Exécuter les tests frontend
print_info "Exécution des tests frontend..."
if npm run test 2>&1; then
    print_success "Tests frontend réussis"
else
    print_error "échec des tests frontend"
    echo "Détails de l'erreur ci-dessus ??"
    
    # Diagnostic supplémentaire
    print_info "Diagnostic des erreurs frontend:"
    echo "- Vérifiez que tous les imports sont corrects"
    echo "- Vérifiez la configuration des mocks dans tests/setup.ts"
    echo "- Lancez 'npm run test:watch' pour plus de détails"
fi

cd ..

# Tests E2E (optionnel)
echo -e "\n${BLUE}?? Tests E2E (Cypress)...${NC}"
print_info "Les tests E2E nécessitent que l'application soit démarrée"
print_info "Pour les exécuter manuellement:"
echo "1. Terminal 1: cd backend && symfony server:start"
echo "2. Terminal 2: cd frontend && npm run dev"  
echo "3. Terminal 3: cd frontend && npm run e2e:open"

# Résumé final
echo -e "\n${BLUE}?? Résumé des tests${NC}"
echo "======================================"
print_info "Pour relancer individuellement:"
echo "Backend:  cd backend && php bin/phpunit"
echo "Frontend: cd frontend && npm run test"
echo "E2E:      cd frontend && npm run e2e:open"
echo ""
print_info "Pour le développement en continu:"
echo "Tests:    cd frontend && npm run test:watch"
echo "App:      make dev (ou démarrer backend + frontend séparément)"

echo -e "\n${GREEN}?? Diagnostic terminé!${NC}"