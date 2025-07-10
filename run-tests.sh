#!/bin/bash

# Script de lancement rapide des tests Roomies
# Usage: ./run-tests.sh [backend|frontend|e2e|all]

set -e  # Arréter en cas d'erreur

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_header() {
    echo -e "\n${BLUE}?? $1${NC}"
    echo "======================================"
}

print_success() {
    echo -e "${GREEN}? $1${NC}"
}

print_error() {
    echo -e "${RED}? $1${NC}"
}

print_info() {
    echo -e "${YELLOW}?? $1${NC}"
}

# Vérifier que nous sommes dans le bon répertoire
if [[ ! -d "backend" || ! -d "frontend" ]]; then
    print_error "Veuillez exécuter ce script depuis la racine du projet Roomies"
    exit 1
fi

# Fonction pour les tests backend
run_backend_tests() {
    print_header "Tests Backend (PHPUnit)"
    
    cd backend
    
    # Réinitialiser l'environnement de test d'abord
    if [[ -f "reset-test-env.sh" ]]; then
        print_info "Réinitialisation de l'environnement de test..."
        chmod +x reset-test-env.sh
        ./reset-test-env.sh
    else
        # Fallback si le script n'existe pas
        print_info "Préparation de la base de données de test..."
        php bin/console doctrine:database:create --env=test --if-not-exists --quiet || true
        php bin/console doctrine:migrations:migrate --env=test --no-interaction --quiet || true
    fi
    
    # Exécuter les tests
    print_info "Exécution des tests backend..."
    if php bin/phpunit --testdox; then
        print_success "Tests backend réussis !"
    else
        print_error "Échec des tests backend"
        cd ..
        exit 1
    fi
    
    cd ..
}

# Fonction pour les tests frontend
run_frontend_tests() {
    print_header "Tests Frontend (Vitest)"
    
    cd frontend
    
    # Vérifier les dépendances
    if [[ ! -d "node_modules" ]]; then
        print_info "Installation des dépendances frontend..."
        npm install
    fi
    
    # Vérifier que vitest est installé
    if ! npx vitest --version &>/dev/null; then
        print_info "Installation de Vitest..."
        npm install --save-dev vitest @vue/test-utils jsdom @vitest/coverage-v8
    fi
    
    # Exécuter les tests
    print_info "Exécution des tests frontend..."
    if npm run test; then
        print_success "Tests frontend réussis !"
    else
        print_error "échec des tests frontend"
        cd ..
        exit 1
    fi
    
    cd ..
}

# Fonction pour les tests E2E
run_e2e_tests() {
    print_header "Tests End-to-End (Cypress)"
    
    # Vérifier que l'application fonctionne
    print_info "Vérification que l'application est démarrée..."
    
    if ! curl -s http://localhost:8000/api/games &>/dev/null; then
        print_error "Backend non accessible sur http://localhost:8000"
        print_info "Démarrez le backend avec: cd backend && symfony server:start"
        exit 1
    fi
    
    if ! curl -s http://localhost:5173 &>/dev/null; then
        print_error "Frontend non accessible sur http://localhost:5173"
        print_info "Démarrez le frontend avec: cd frontend && npm run dev"
        exit 1
    fi
    
    cd frontend
    
    # Vérifier que Cypress est installé
    if ! npx cypress --version &>/dev/null; then
        print_info "Installation de Cypress..."
        npm install --save-dev cypress
    fi
    
    # Exécuter les tests E2E
    print_info "Exécution des tests E2E..."
    if npm run e2e; then
        print_success "Tests E2E réussis !"
    else
        print_error "échec des tests E2E"
        cd ..
        exit 1
    fi
    
    cd ..
}

# Fonction pour tous les tests
run_all_tests() {
    print_header "Exécution de tous les tests"
    
    run_backend_tests
    run_frontend_tests
    
    print_info "Tests E2E nécessitent que l'app soit démarrée"
    print_info "Pour les E2E, utilisez: ./run-tests.sh e2e"
    
    print_success "Tous les tests unitaires et d'intégration réussis !"
}

# Script principal
case "${1:-all}" in
    "backend")
        run_backend_tests
        ;;
    "frontend")
        run_frontend_tests
        ;;
    "e2e")
        run_e2e_tests
        ;;
    "all")
        run_all_tests
        ;;
    *)
        echo "Usage: $0 [backend|frontend|e2e|all]"
        echo ""
        echo "Exemples:"
        echo "  $0 backend    # Tests backend uniquement"
        echo "  $0 frontend   # Tests frontend uniquement"
        echo "  $0 e2e        # Tests E2E (app doit étre démarrée)"
        echo "  $0 all        # Tous les tests sauf E2E"
        exit 1
        ;;
esac