describe('Teste de navegação, validação e edição da página de Iniciativas', () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.login('alessandrofeitoza@example.com', 'Aurora@2024');

        cy.intercept('GET', '/painel/iniciativas').as('getList');
        cy.visit('/painel/iniciativas');
        cy.wait('@getList');

        cy.get('table[data-cy=table-initiative-list] tbody tr')
            .filter(':has(span.text-success)')
            .first()
            .find('a.btn-outline-warning')
            .click();

        cy.url().should('match', /\/painel\/iniciativas\/[0-9a-f-]+\/editar$/);
    });

    it('Garante que a página de editar iniciativas funciona', () => {
        cy.get('form#initiative-edit-form').should('exist');

        cy.get('form#initiative-edit-form')
            .find('.accordion-item').eq(0)
            .find('.accordion-header .accordion-button')
            .should('be.visible')
            .and('contain.text', 'Informações de apresentação');

        cy.get('[for="name"]').contains('Nome da iniciativa').should('be.visible');
        cy.get('[for="short-description"]').contains('Descrição curta').should('be.visible');
        cy.get('[for="long-description"]').contains('Descrição longa').should('be.visible');
    });
});
