describe('Página de Lista de Espaços', () => {
    beforeEach(() => {
        cy.viewport(1920, 1080);
        cy.visit('/espacos');
    });

    it('Garante que a página de lista de espaços existe', () => {
        cy.get('a.name-one').contains('Início').should('be.visible');
        cy.get('a.name-one').contains('Espaços').should('be.visible');
        cy.get('h2').contains('Espaços').should('be.visible');
    });

    it('Garante que as opções de visualização estão presentes', () => {
        cy.contains('a', 'Lista').should('be.visible');
        cy.contains('a', 'Mapa').should('be.visible');
    });

    it('Garante que as abas estão funcionando', () => {
        const abas = ['#pills-list-tab', '#pills-map-tab', '#pills-indicators-tab'];

        abas.forEach((aba) => {
            cy.get(aba).click().should('have.class', 'active');
        });
    });

    it('Garante que os cards de espaços estão visíveis', () => {
        cy.get('.space-card').should('have.length.greaterThan', 0);

        cy.get('.space-card').first().within(() => {
            cy.get('.space-card__title').should('be.visible');
            cy.get('.space-card__type').should('be.visible');
            cy.get('.btn').contains('Acessar espaço').should('be.visible');
        });
    });

    it('Garante que o filtro funciona', () => {
        cy.get('#open-filter').click();
        cy.get('#space-name').type('Dragão do Mar');
        cy.get('#apply-filters').click();

        cy.get('.align-items-end > .fw-bold').contains('1 Espaços Encontrados').should('be.visible');
        cy.get('.space-card__title').contains('Dragão do Mar').should('be.visible');
    });

    it('Garante que as opções de ordenação funcionam', () => {
        cy.get('#order-select')
            .should('exist')
            .should('be.visible');

        cy.get('#order-select').select('Mais Recente').should('have.value', 'DESC');
        cy.get('#order-select').select('Mais Antigo').should('have.value', 'ASC');
    });
});
