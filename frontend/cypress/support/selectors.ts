/**
 * Configuration des attributs data-cy pour les tests E2E
 * À ajouter aux composants Vue pour faciliter les tests Cypress
 */

// Attributs pour les composants d'authentification
export const AUTH_SELECTORS = {
  // Login
  LOGIN_EMAIL: 'login-email',
  LOGIN_PASSWORD: 'login-password',
  LOGIN_SUBMIT: 'login-submit',
  LOGIN_LINK: 'login-link',

  // Register
  REGISTER_EMAIL: 'register-email',
  REGISTER_USERNAME: 'register-username',
  REGISTER_PASSWORD: 'register-password',
  REGISTER_CONFIRM_PASSWORD: 'register-confirm-password',
  REGISTER_SUBMIT: 'register-submit',
  REGISTER_LINK: 'register-link',

  // Logout
  LOGOUT_BUTTON: 'logout-button',
}

// Attributs pour la gestion des rooms
export const ROOM_SELECTORS = {
  // Liste des rooms
  ROOM_CARD: 'room-card',
  ROOM_NAME: 'room-name',
  ROOM_CREATOR: 'room-creator',
  ROOM_PLAYERS: 'room-players',
  JOIN_ROOM_LINK: 'join-room-link',
  DELETE_ROOM_BUTTON: 'delete-room-button',

  // Création de room
  CREATE_ROOM_BUTTON: 'create-room-button',
  ROOM_CREATION_MODAL: 'room-creation-modal',
  ROOM_NAME_INPUT: 'room-name-input',
  GAME_SELECTOR: 'game-selector',
  MAX_PLAYERS_INPUT: 'max-players-input',
  CREATE_ROOM_SUBMIT: 'create-room-submit',
  CREATE_ROOM_CANCEL: 'create-room-cancel',

  // Suppression de room
  DELETE_MODAL: 'delete-room-modal',
  CONFIRM_DELETE_BUTTON: 'confirm-delete-button',
  CANCEL_DELETE_BUTTON: 'cancel-delete-button',
}

// Attributs pour le profil utilisateur
export const PROFILE_SELECTORS = {
  // Informations personnelles
  PROFILE_USERNAME: 'profile-username',
  PROFILE_EMAIL: 'profile-email',
  PROFILE_AVATAR: 'profile-avatar',

  // Édition du profil
  EDIT_PROFILE_BUTTON: 'edit-profile-button',
  SAVE_PROFILE_BUTTON: 'save-profile-button',
  CANCEL_EDIT_BUTTON: 'cancel-edit-button',

  // Avatar
  AVATAR_SELECTOR: 'avatar-selector',
  AVATAR_OPTION: 'avatar-option',
  CONFIRM_AVATAR_BUTTON: 'confirm-avatar-button',

  // Favoris
  FAVORITES_SECTION: 'favorites-section',
  FAVORITE_GAME: 'favorite-game',
  ADD_FAVORITE_BUTTON: 'add-favorite-button',
  REMOVE_FAVORITE_BUTTON: 'remove-favorite-button',
}

// Attributs pour la navigation
export const NAV_SELECTORS = {
  MAIN_NAV: 'main-navigation',
  HOME_LINK: 'nav-home',
  ROOMS_LINK: 'nav-rooms',
  PROFILE_LINK: 'nav-profile',
  USER_AVATAR: 'nav-user-avatar',
  USER_MENU: 'user-menu',
}

// Attributs génériques
export const GENERIC_SELECTORS = {
  LOADING_SPINNER: 'loading-spinner',
  ERROR_MESSAGE: 'error-message',
  SUCCESS_MESSAGE: 'success-message',
  MODAL_OVERLAY: 'modal-overlay',
  MODAL_CLOSE: 'modal-close',
  FORM_SUBMIT: 'form-submit',
  FORM_CANCEL: 'form-cancel',
}

/**
 * Fonction utilitaire pour générer l'attribut data-cy
 */
export function dataCy(selector: string): string {
  return `[data-cy="${selector}"]`
}

/**
 * Commandes Cypress personnalisées pour simplifier les tests
 * À ajouter dans cypress/support/commands.ts
 */
export const CYPRESS_COMMANDS = `
// Commande pour se connecter rapidement dans les tests
Cypress.Commands.add('login', (email: string, password: string) => {
  cy.visit('/login')
  cy.get('[data-cy="${AUTH_SELECTORS.LOGIN_EMAIL}"]').type(email)
  cy.get('[data-cy="${AUTH_SELECTORS.LOGIN_PASSWORD}"]').type(password)
  cy.get('[data-cy="${AUTH_SELECTORS.LOGIN_SUBMIT}"]').click()
})

// Commande pour créer une room rapidement
Cypress.Commands.add('createRoom', (name: string, game: string, maxPlayers: number) => {
  cy.get('[data-cy="${ROOM_SELECTORS.CREATE_ROOM_BUTTON}"]').click()
  cy.get('[data-cy="${ROOM_SELECTORS.ROOM_NAME_INPUT}"]').type(name)
  cy.get('[data-cy="${ROOM_SELECTORS.GAME_SELECTOR}"]').contains(game).click()
  cy.get('[data-cy="${ROOM_SELECTORS.MAX_PLAYERS_INPUT}"]').clear().type(maxPlayers.toString())
  cy.get('[data-cy="${ROOM_SELECTORS.CREATE_ROOM_SUBMIT}"]').click()
})

// Commande pour attendre le chargement complet
Cypress.Commands.add('waitForPageLoad', () => {
  cy.get('[data-cy="${GENERIC_SELECTORS.LOADING_SPINNER}"]', { timeout: 1000 }).should('not.exist')
})
`
