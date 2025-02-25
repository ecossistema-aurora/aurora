describe('Criar uma iniciativa e atualizar o dashboard', () => {
    beforeEach(() => {
        cy.visit('/login');
        cy.login('saracamilo@example.com', 'Aurora@2024');
        cy.url().should('include', '/');
    });

    Cypress.on('uncaught:exception', (err) => {
        if (err.message.includes('i.createPopper is not a function')) {
            console.warn('Erro ignorado do Popper.js:', err.message);
            return false;
        }
    });

    it('Deve criar uma iniciativa e verificar se o número de iniciativas aumentou', () => {
        cy.visit('/iniciativas');
        cy.get('.dashboard-card:contains("Registradas nos últimos 7 dias") h2.quantity')
            .invoke('text')
            .then((quantidadeAntes) => {
                const totalAntes = parseInt(quantidadeAntes.trim());

                criarNovaIniciativa();

                cy.visit('/iniciativas');

                cy.get('.dashboard-card:contains("Registradas nos últimos 7 dias") h2.quantity')
                    .invoke('text')
                    .should((quantidadeDepois) => {
                        const totalDepois = parseInt(quantidadeDepois.trim());
                        expect(totalDepois).to.eq(totalAntes + 1);
                    });
            });
    });

    function criarNovaIniciativa() {
        cy.contains('button, a', 'Criar uma iniciativa').click();
        cy.url().should('include', '/painel/iniciativas/adicionar');

        cy.get('input#nameInitiative').type('Iniciativa de Teste Cypress');
        cy.get('select[data-cy="culturalLanguage"]').select('Exposição');
        cy.get('select[data-cy="areasOfExpertise"]').select(['Artesanato', 'Cinema']);
        cy.get('select[data-cy="createdBy"]').should('be.visible').select('Sara Jennifer');
        cy.get('textarea[data-cy="shortDescription"]').type('Teste de iniciativa automatizada');
        cy.get('textarea[data-cy="longDescription"]').type('Descrição detalhada da iniciativa');
        cy.get('button[data-cy="submit"]').click();
    }
});
