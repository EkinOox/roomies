/// <reference types="cypress" />
// ***********************************************
// This example commands.ts shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })
//
// Import des s�lecteurs
import { AUTH_SELECTORS, ROOM_SELECTORS, GENERIC_SELECTORS } from './selectors'

/**
 * Commandes Cypress personnalis�es pour simplifier les tests
 */

// Commande pour se connecter rapidement dans les tests
Cypress.Commands.add('login', (email: string, password: string) => {
  cy.visit('/login')
  cy.get(`[data-cy="${AUTH_SELECTORS.LOGIN_EMAIL}"]`).type(email)
  cy.get(`[data-cy="${AUTH_SELECTORS.LOGIN_PASSWORD}"]`).type(password)
  cy.get(`[data-cy="${AUTH_SELECTORS.LOGIN_SUBMIT}"]`).click()

  // Attendre la redirection vers /rooms
  cy.url().should('include', '/rooms')
})

// Commande pour cr�er une room rapidement
Cypress.Commands.add('createRoom', (name: string, game: string, maxPlayers: number) => {
  cy.get(`[data-cy="${ROOM_SELECTORS.CREATE_ROOM_BUTTON}"]`).click()
  cy.get(`[data-cy="${ROOM_SELECTORS.ROOM_NAME_INPUT}"]`).type(name)
  cy.get(`[data-cy="${ROOM_SELECTORS.GAME_SELECTOR}"]`).contains(game).click()
  cy.get(`[data-cy="${ROOM_SELECTORS.MAX_PLAYERS_INPUT}"]`).clear().type(maxPlayers.toString())
  cy.get(`[data-cy="${ROOM_SELECTORS.CREATE_ROOM_SUBMIT}"]`).click()

  // V�rifier que la room a �t� cr��e
  cy.contains(name).should('be.visible')
})

// Commande pour attendre le chargement complet
Cypress.Commands.add('waitForPageLoad', () => {
  // Attendre que les spinners de chargement disparaissent
  cy.get(`[data-cy="${GENERIC_SELECTORS.LOADING_SPINNER}"]`, { timeout: 1000 }).should('not.exist')
})

// Commande pour cr�er un utilisateur via l'API
Cypress.Commands.add('createUser', (userData: { email: string; username: string; password: string }) => {
  cy.request({
    method: 'POST',
    url: `${Cypress.env('BACKEND_URL')}/register`,
    body: userData,
    failOnStatusCode: false // Ignore si l'utilisateur existe d�j�
  })
})

// Commande pour nettoyer les donn�es de test
Cypress.Commands.add('cleanDatabase', () => {
  // Cette commande n�cessiterait une route API d�di�e au nettoyage
  cy.task('clearDatabase')
})

// Commande pour pr�parer des donn�es de test
Cypress.Commands.add('seedTestData', () => {
  cy.task('seedTestData')
})

declare namespace Cypress {
  interface Chainable<Subject = any> {
    saveLocalStorage(): Chainable<Subject>
    restoreLocalStorage(): Chainable<Subject>
  }
}
// D�clarations TypeScript pour les commandes personnalis�es
declare global {
  namespace Cypress {
    interface Chainable {
      /**
       * Connexion rapide avec email et mot de passe
       */
      login(email: string, password: string): Chainable<void>

      /**
       * Cr�ation rapide d'une room
       */
      createRoom(name: string, game: string, maxPlayers: number): Chainable<void>

      /**
       * Attendre le chargement complet de la page
       */
      waitForPageLoad(): Chainable<void>

      /**
       * Cr�er un utilisateur via l'API
       */
      createUser(userData: { email: string; username: string; password: string }): Chainable<void>

      /**
       * Nettoyer la base de donn�es de test
       */
      cleanDatabase(): Chainable<void>

      /**
       * Pr�parer des donn�es de test
       */
      seedTestData(): Chainable<void>
    }
  }
}

export {}
