describe('Painel de Controle - Página de listar Oportunidades', () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.login('alessandrofeitoza@example.com', 'Aurora@2024');
        cy.visit('/painel/oportunidades');
    });

    it('Garante que a página de listar Oportunidades existe e funciona', () => {
        cy.get('h2').contains('Minhas Oportunidades').should('be.visible');

        cy.contains('Inscrição para o Festival de Danças Folclóricas - Encontro Nordestino').should('be.visible');
        
        // TODO: regmel
        // cy.contains('Edital para Seleção de Artistas de Rua - Circuito Cultural Nordestino').should('not.exist');

        cy.get('h2').contains('Minhas Oportunidades').should('be.visible');
    
        cy.get('[data-cy="remove-1"]').contains('Excluir').click();
    
        cy.contains('Confirmar').click();

        // TODO: regmel
        // cy.contains('Inscrição para o Festival de Danças Folclóricas - Encontro Nordestino').should('not.exist');
        cy.get('.success.snackbar').contains('A Oportunidade foi excluída').should('be.visible');
    });
})
