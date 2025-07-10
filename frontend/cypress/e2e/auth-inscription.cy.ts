describe('Inscription', () => {
  beforeEach(() => {
    cy.clearLocalStorage();
    cy.visit('http://localhost:5173/auth');
    cy.contains('Inscription').click(); // Aller sur le formulaire d'inscription
  });

  it("remplit les champs et s'inscrit avec succès", () => {
    const random = Math.floor(Math.random() * 100000);

    // Remplir les champs
    cy.get('input[type="email"]').type(`test${random}@mail.com`);
    cy.get('input[type="text"]').type(`testuser${random}`);
    cy.get('input[type="password"]').type('azerty123');

    // Cliquer sur le bouton d'inscription
    cy.contains("S'inscrire").click();

    // Vérifie qu'on reste sur /auth (pas de redirection), et qu'on a un message de succès
    cy.url().should('include', '/auth');
    cy.contains('Inscription réussie !').should('be.visible');
  });

  it("affiche une erreur si l'email est déjà utilisé", () => {
    // Utilise un compte déjà existant
    cy.get('input[type="email"]').type('test@mail.com');
    cy.get('input[type="text"]').type('testuser');
    cy.get('input[type="password"]').type('azerty123');

    cy.contains("S'inscrire").click();

    // Vérifie qu'on a une erreur
    cy.contains("déjà utilisé").should('be.visible'); // adapte selon le vrai message d'erreur de ton backend
  });
});
