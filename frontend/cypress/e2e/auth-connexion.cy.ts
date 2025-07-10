describe('Connexion', () => {
  beforeEach(() => {
    cy.clearLocalStorage();
    cy.visit('http://localhost:5173/auth');
  });

  it('remplit les champs et se connecte avec succès', () => {

    // Remplir l'email
    cy.get('input[type="email"]').type('admin@admin.com');
    // Remplir le mot de passe
    cy.get('input[type="password"]').type('admin');
    // Cliquer sur le bouton de connexion
    cy.contains('Se connecter').click();

    // Vérifier la redirection vers "/"
    cy.url().should('eq', 'http://localhost:5173/');
  });

  it('affiche une erreur en cas de mauvais identifiants', () => {
    // Remplir l'email incorrect
    cy.get('input[type="email"]').type('fake@fake.com');
    // Remplir un mauvais mot de passe
    cy.get('input[type="password"]').type('wrongpass');
    // Cliquer sur le bouton de connexion
    cy.contains('Se connecter').click();

    // On doit rester sur /auth
    cy.url().should('include', '/auth');
    // Et voir un message d'erreur
    cy.contains('Identifiants invalides').should('be.visible');
  });
});
