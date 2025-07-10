describe('Gestion du profil utilisateur', () => {
  const email = 'test@test.com'
  const password = 'test'
  let authToken: string

  before(() => {
    // Connexion une seule fois et sauvegarde du token
    cy.visit('http://localhost:5173/auth')
    cy.get('input[type="email"]').type(email)
    cy.get('input[type="password"]').type(password)
    cy.contains('Se connecter').click()
    cy.url().should('not.include', '/auth')

    // Sauvegarder le token après connexion
    cy.window().then((win) => {
      authToken = win.localStorage.getItem('token') || win.localStorage.getItem('authToken') || ''
      cy.log('Token sauvegardé:', authToken)
    })
  })

  beforeEach(() => {
    // Restaurer le token avant chaque test
    cy.window().then((win) => {
      if (authToken) {
        win.localStorage.setItem('token', authToken)
        win.localStorage.setItem('authToken', authToken)
      }
    })

    // Visiter la page d'accueil avec le token restauré
    cy.visit('http://localhost:5173/')
    cy.url().should('not.include', '/auth')
  })

  it('devrait naviguer vers la page profile et afficher les informations utilisateur', () => {
    // Navigation vers le profil via l'avatar
    cy.get('[data-pc-name="avatar"]').click()
    cy.url().should('include', '/profile')

    // Vérification que les éléments du profil sont visibles
    cy.contains('Modifier').should('be.visible').click()
    cy.get('input[type="email"]').should('be.visible').and('have.value', email)
    cy.contains('Enregistrer').click()
  })

  it('devrait permettre de modifier les informations du profil', () => {
    // Navigation vers le profil via l'avatar
    cy.get('[data-pc-name="avatar"]').click()
    cy.url().should('include', '/profile')

    // Modifier le nom d'utilisateur par exemple
    const newUsername = `TestUser_${Date.now()}`
    cy.contains('Modifier').should('be.visible').click()
    cy.get('input[placeholder*="Nom d\'utilisateur"]').clear().type(newUsername)

    // Sauvegarder les modifications
    cy.contains('Enregistrer').click()
  })
})
