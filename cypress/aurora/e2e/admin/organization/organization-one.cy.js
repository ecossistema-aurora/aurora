describe('Painel de Controle - Página de listar Organizações', () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.login('alessandrofeitoza@example.com', 'Aurora@2024');
        cy.visit('/painel/organizacoes/8c4ca8bd-6e33-1b62-c58b-a66969c49f66');
    });

    it('Garante que a página de detalhes de uma organização existe e funciona', () => {
        cy.get('h2').contains('Organização - Indaiatuba').should('be.visible');
        cy.get('#pills-info-tab > .ms-2').contains('Informações').should('be.visible');
        cy.get('#pills-members-tab > .ms-2').contains('Membros').should('be.visible');
        cy.get('#pills-timeline-tab > .ms-2').contains('Linha do tempo').should('be.visible');
    });
});
