describe('Painel de Controle - Página de listar Espaços', () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.login('alessandrofeitoza@example.com', 'Aurora@2024');
        cy.visit('/painel/espacos');
    });

    it('Garante que a página de listar Espaços existe e funciona', () => {
        cy.get('h2').contains('Meus Espaços').should('be.visible');

        cy.contains('Dragão do Mar').should('be.visible');
        // TODO: regmel
        // cy.contains('Galeria Caatinga').should('not.exist');
        cy.get('tbody').contains('Casa da Capoeira').should('be.visible');
        cy.get('tbody').contains('13/08/2024 20:25:00').should('be.visible');

        // Garante que um espaço pode ser excluido
        cy.get('h2').contains('Meus Espaços').should('be.visible');

        cy.get('[data-cy="remove-3"]').contains('Excluir').click();

        cy.contains('Confirmar').click();

        // TODO: regmel
        // cy.contains('Casa da Capoeira').should('not.exist');
        cy.get('.success.snackbar').contains('O Espaço foi excluído').should('be.visible');
    });
});
