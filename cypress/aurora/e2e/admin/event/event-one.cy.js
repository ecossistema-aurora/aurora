describe('Painel de Controle - Página de detalhar um evento', () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.login('alessandrofeitoza@example.com', 'Aurora@2024');
        cy.visit('/painel/eventos/96318947-df03-41c9-be75-095a85c12e96');
    });

    it('Garante que a aba de dados gerais funciona', () => {
        cy.get('#pills-info.tab-pane').should('have.class', 'active show');
        cy.get('h2').contains('Evento - Festival da Rapadura');
        cy.get('[data-cy="event-name"]').should('be.visible');
        cy.get('[data-cy="event-name"]').siblings('span').should('not.be.empty');
        cy.get('[data-cy="event-subtitle"]').should('be.visible');
        cy.get('[data-cy="event-subtitle"]').siblings('span').contains('Subtítulo de exemplo ');
        cy.get('[data-cy="event-short-description"]').should('be.visible');
        cy.get('[data-cy="event-short-description"]').siblings('span').contains('Descrição curta');
        cy.get('[data-cy="event-long-description"]').should('be.visible');
        cy.get('[data-cy="event-long-description"]').siblings('span').contains('Não informado');
        cy.get('[data-cy="event-cultural-languages"]').should('contain', 'Linguagens culturais:');
        cy.get('[data-cy="event-tags"]').invoke('text').should('match', /Tags \(\d+\)/);
        cy.contains('label.fw-bold', 'Site:').should('exist');
        cy.contains('label.fw-bold', 'Site:').siblings('span').then(($span) => {
            if ($span.find('a').length > 0) {
                expect($span.find('a').attr('href')).not.to.be.empty;
                expect($span.find('a').attr('target')).to.equal('_blank');
            } else {
                expect($span.text().trim()).to.equal('Não informado');
            }
        });

        cy.contains('label.fw-bold', 'Telefone:').should('exist');
        cy.contains('label.fw-bold', 'Telefone:').siblings('span').contains('8585998585');
    });

    it('Garante que a aba de inscrições', () => {
        cy.get('#pills-inscriptions-tab').click();
        cy.get('#pills-inscriptions.tab-pane').should('have.class', 'active show');

        cy.get('table').contains('Sara Jennifer');
        cy.get('table').contains('Anna Kelly');
        cy.get('table').contains('Henrique');
    });

    it('Garante que conseguimos recusar a participação de um usuário ao evento', () => {
        cy.get('#pills-inscriptions-tab').click();

        cy.get('tbody tr').first().as('firsLine');

        cy.get('@firsLine').within(() => {
            cy.get('[data-column-id="nome"]').should('contain', 'Sara Jennifer');

            cy.get('[data-cy^="suspend-inscription-"]').click();
        });

        cy.get('#modalRemoveConfirm').should('be.visible');
        cy.contains('a', 'Confirmar').click();

        cy.get('.toast-body')
            .should('contain', 'Inscrição suspensa');

        cy.get('#pills-inscriptions-tab').click();

        cy.get('@firsLine').within(() => {
            cy.get('[data-column-id="status"] .badge')
                .should('have.class', 'bg-warning')
                .and('contain', 'Suspenso');
        });

        cy.get('@firsLine').within(() => {
            cy.get('[data-cy^="suspend-inscription-"]').should('not.exist');
        });
    });
})
