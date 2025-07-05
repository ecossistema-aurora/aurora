describe('E2E: navegação, validação e edição na página de Eventos', () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.login('henriquelopeslima@example.com', 'Aurora@2024');
        cy.visit('/painel/eventos');

        cy.get('table tbody tr')
            .filter((_, tr) => Cypress.$(tr).find('.badge.bg-success').length > 0)
            .first()
            .within(() => {
                cy.contains('a.btn-outline-warning', 'Editar')
                    .should('be.visible')
                    .click();
            });

        cy.url().should('match', /\/painel\/eventos\/[0-9a-f-]+\/editar$/);
    });

    it('Garante que a página de editar eventos funciona', () => {
        cy.get('button[data-bs-target="#panelsStayOpen-collapseOne"]')
            .should('be.visible');
        cy.get('label[for="name"]').should('be.visible');
        cy.get('label[for="short-description"]').should('be.visible');
        cy.get('label[for="long-description"]').should('be.visible');

        cy.get('button[data-bs-target="#panelsStayOpen-collapseTwo"]')
            .click();
        cy.get('label[for="ageRating"]').should('be.visible');
        cy.get('label[for="maximumCapacity"]').should('be.visible');
        cy.get('label[for="phone"]').should('be.visible');

        cy.get('button[data-bs-target="#panelsStayOpen-collapseThree"]')
            .click();
        cy.contains('Adicione data, hora e local da ocorrência').should('be.visible');

        cy.get('button[data-bs-target="#panelsStayOpen-collapseFour"]')
            .click();
        cy.contains('Instagram').should('be.visible');
        cy.contains('YouTube').should('be.visible');

        cy.get('#event-type')
            .should('exist')
            .select('Presencial');

        cy.intercept('POST', /\/painel\/eventos\/.*\/editar$/).as('updateEvent');

        cy.get('button[form="event-edit-form"][type="submit"]')
            .scrollIntoView()
            .should('be.visible')
            .click();

        cy.wait('@updateEvent').its('response.statusCode').should('be.oneOf', [200, 302]);
        cy.url().should('include', '/painel/eventos');
        cy.get('.toast')
            .should('be.visible')
            .and('contain.text', 'O Evento foi atualizado');
    });
});
