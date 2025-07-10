describe('Créer et rejoindre une Room', () => {
  const email = 'admin@admin.com'
  const password = 'admin'
  const roomName = `RoomTest_${Date.now()}`
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

  it('devrait créer une nouvelle room et la rejoindre', () => {
    // Navigation vers les rooms
    cy.contains('Rooms').click()
    cy.contains('Toutes les rooms').click()
    cy.url().should('include', '/rooms')

    // Création de la room
    cy.contains('Créer une Room').click()
    cy.get('input[placeholder="Nom de la room"]').type(roomName)
    cy.get('img[alt="2048"]').parents('div.cursor-pointer').click()

    // Cibler le bouton Créer dans la modal spécifiquement
    cy.get('div.bg-\\[\\#0f172a\\]').within(() => {
      cy.contains('Créer').should('not.be.disabled').click()
    })

    // Vérification que la room a été créée
    cy.contains(roomName).should('be.visible')

    // Rejoindre la room
    cy.contains('Rejoindre la room')
      .should('have.attr', 'href')
      .then((href) => {
        cy.get(`a[href="${href}"]`).click()
        cy.url().should('include', '/rooms/')
      })
  })
})
