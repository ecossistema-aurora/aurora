describe('Admin - Criar Área de Atuação', () => {
    beforeEach(() => {
        cy.visit('/');
    });

    it('Deve seguir todo o fluxo de criação de Área de Atuação', () => {
        cy.contains('Entrar').click();
        cy.url().should('include', '/login');
        cy.get('input[name="email"]').type('talysonsoares@example.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.get('button').contains('Entrar').click();
        cy.get('.dropdown').click();
        cy.contains('Painel de Controle').click();
        cy.url().should('include', '/painel');

        cy.contains('Área de Atuação').click();
        cy.url().should('include', '/painel/area-atuacao');

        cy.contains('Criar').click();
        cy.url().should('include', '/painel/area-atuacao/adicionar');

        cy.get('button').contains('Salvar').click();
        cy.get('input[name="name"]:invalid').should('exist');

        cy.get('input[name="name"]').type('Atuação Teste');
        cy.get('button').contains('Salvar').click();

        cy.url().should('include', '/painel/area-atuacao');
        cy.contains('.alert-success', 'Área de atuação foi criada').should('be.visible');
        cy.contains('td', 'Atuação Teste').should('be.visible');
    });
});
