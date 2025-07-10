import { defineConfig } from 'cypress'

/**
 * Configuration Cypress pour les tests End-to-End
 * Tests complets des sc�narios utilisateur
 */
export default defineConfig({
  e2e: {
    // URL de base de l'application
    baseUrl: 'http://localhost:5173',

    // Dossier contenant les tests E2E
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',

    // Configuration des timeouts
    defaultCommandTimeout: 10000,
    requestTimeout: 10000,
    responseTimeout: 10000,

    // Configuration de la fen�tre de test
    viewportWidth: 1280,
    viewportHeight: 720,

    // Variables d'environnement pour les tests
    env: {
      BACKEND_URL: 'http://localhost:8000',
      TEST_USER_EMAIL: 'test@roomies.com',
      TEST_USER_PASSWORD: 'TestPassword123',
      TEST_USER_USERNAME: 'testuser'
    },

    // Configuration des captures d'�cran et vid�os
    screenshotOnRunFailure: true,
    video: true,
    videosFolder: 'cypress/videos',
    screenshotsFolder: 'cypress/screenshots',

    setupNodeEvents(on, config) {
      // Configuration des �v�nements Node.js
      // Utile pour les t�ches de pr�paration de donn�es

      on('task', {
        // T�che pour nettoyer la base de donn�es de test
        clearDatabase() {
          // Logique de nettoyage de la DB
          return null
        },

        // T�che pour cr�er des donn�es de test
        seedTestData() {
          // Logique pour ins�rer des donn�es de test
          return null
        }
      })
    },
  },

  component: {
    // Configuration pour les tests de composants
    devServer: {
      framework: 'vue',
      bundler: 'vite',
    },
  },
})
