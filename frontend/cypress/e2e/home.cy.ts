describe('Accueil', () => {
  it('affiche la page d\'accueil', () => {
    cy.visit('http://localhost:5173'); // ou 5173 selon ton front
    cy.contains('Play. Chat. Connect.');
  });
});
