describe('Confirmação do usuário', () => {
    beforeEach(() => {
        cy.visit('/login');
        cy.get('input[name="email"]').type('admin@regmel.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.get('button[data-cy="submit"]').click();
        cy.wait(1000);
    });

    it('Acessa e confirma usuário não validado', () => {
        cy.get('a').contains('Usuários').click();

        cy.get('table > tbody').contains('tr', 'João Silva').find('td[data-column-id="status"]').should('contain', 'Aguardando Confirmar Conta');
        cy.get('table > tbody').contains('tr', 'João Silva').find('td[data-column-id="status"]').contains('Confirmar Usuário').click();
        cy.get('.modal-body').contains('João Silva').should('be.visible');
        cy.get('.modal-body').contains('joao@empresa.com').should('be.visible');
        cy.get('a[data-modal-button="confirm-link"]').click();

        cy.get('table > tbody').contains('tr', 'João Silva').find('td[data-column-id="status"]').should('contain', 'Ativo');
        cy.get('table > tbody').contains('tr', 'João Silva').find('td[data-column-id="status"]').should('not.contain', 'Confirmar Usuário');
    });
});
