describe('Painel de Controle - Página de listar Oportunidades', () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.login('alessandrofeitoza@example.com', 'Aurora@2024');
        cy.visit('/painel/oportunidades');
    });

    it('Garante que a página de listar Oportunidades existe e funciona', () => {
        cy.get('h2').contains('Minhas Oportunidades').should('be.visible');

        cy.get('table > tbody').contains('tr', 'Inscrição para o Festival de Danças Folclóricas - Encontro Nordestino').as('row');
        cy.get('@row').should('be.visible');

        cy.get('@row').find('[data-column-id="ações"]').contains('Excluir').should('not.be.visible');
        cy.get('@row').find('[data-column-id="ações"]').contains('Ações').click();

        cy.get('@row')
            .find('[data-column-id="ações"]')
            .contains('Excluir')
            .click();

        cy.contains('Confirmar').click();

        cy.contains('Inscrição para o Festival de Danças Folclóricas - Encontro Nordestino').should('not.exist');
        cy.get('.success.snackbar').contains('A Oportunidade foi excluída').should('be.visible');
    });
})
