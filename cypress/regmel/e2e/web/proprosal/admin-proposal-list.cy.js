describe('Listagem de propostas', () => {
    beforeEach(() => {
       cy.visit('/login');
        cy.get('input[name="email"]').type('admin@regmel.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.get('button[data-cy="submit"]').click();
        cy.wait(1500);
        cy.contains('Propostas').click();
    });

    it('Verifica a visualização da proposta e seus detalhes', () => {
        const today = new Date().toLocaleDateString('pt-BR');

        cy.get('h2').contains('Empresas - Propostas');

        const headers = [
            'Empresa',
            'Município',
            'Status',
            'Qtd. Domicílios',
            'Área Total',
            'Valor Global da Proposta',
            'Ações',
        ];
        headers.forEach(text =>
            cy.contains('.gridjs-th-content', text)
        );

        cy.get(':nth-child(2) > [data-column-id="empresa"] > span').should('contain.text', 'Empresa Teste LTDA');
        cy.get(':nth-child(2) > [data-column-id="município"] > span').should('contain.text','Russas-CE');
        cy.get(':nth-child(2) > [data-column-id="status"] > :nth-child(1)').should('contain.text','Sem Adesão do Município');
        cy.get(':nth-child(2) > [data-column-id="qtd.Domicílios"] > span').should('contain.text','100');
        cy.get(':nth-child(2) > [data-column-id="áreaTotal"] > span').should('contain.text','1000');
        cy.get(':nth-child(2) > [data-column-id="valorGlobalDaProposta"] > span').should('contain.text','R$ 4.500.000,00');
        cy.get(':nth-child(2) > [data-column-id="ações"] > span > .btn').should('contain.text','Ver Proposta').click();

        cy.get('#proposalDetailsLabel').should('contain.text','Proposta - Área Teste');

        cy.get('.modal-body > :nth-child(1)').should('contain.text','Informações de localização');
        cy.get(':nth-child(2) > :nth-child(1) > .mt-4 > strong').should('contain.text','Região');
        cy.get(':nth-child(2) > :nth-child(2) > .mt-4 > strong').should('contain.text','Estado');
        cy.get(':nth-child(2) > :nth-child(3) > .mt-4 > strong').should('contain.text','Cidade');
        cy.get('#region-name').should('contain.text','Nordeste');
        cy.get('#state-name').should('contain.text','CE');
        cy.get('#city-name').should('contain.text','Russas-CE');

        cy.get('.modal-body > :nth-child(4)').should('contain.text','Informações do proponente');

        cy.get(':nth-child(5) > :nth-child(1) > .mt-4 > strong').should('contain.text','Empresa');
        cy.get(':nth-child(5) > :nth-child(2) > .mt-4 > strong').should('contain.text','Criado por');
        cy.get(':nth-child(5) > :nth-child(3) > .mt-4 > strong').should('contain.text','Data');
        cy.get('#company-name').should('contain.text','Empresa Teste LTDA');
        cy.get('#created-by').should('contain.text','João Silva');
        cy.get('#created-at').should('contain.text',`${today}`);

        cy.get('.modal-body > :nth-child(7)').should('contain.text','Informações legais');

        cy.get(':nth-child(8) > .col-md-4 > .mt-4 > strong').should('contain.text','Áreas de intervenção');
        cy.get('.col-md-8 > .mt-4 > strong').should('contain.text','A área é caracterizada como:');
        cy.get('#proposal-name').should('contain.text','Área Teste');
        cy.get('#area-characteristic').should('contain.text','Núcleo urbano regularizado ou em processo de regularização fundiária');

        cy.get('.modal-body > :nth-child(10)').should('contain.text','Informações da Área');

        cy.get(':nth-child(11) > :nth-child(1) > .mt-4 > strong').should('contain.text','Área estimada da poligonal');
        cy.get(':nth-child(11) > :nth-child(2) > .mt-4 > strong').should('contain.text','Número de domicílios');
        cy.get(':nth-child(11) > :nth-child(3) > .mt-4 > strong').should('contain.text','Custo médio por domicílio');
        cy.get('.align-items-end > .mt-4 > strong').should('contain.text','Valor Global da Proposta');
        cy.get(':nth-child(11) > :nth-child(1) > p').should('contain.text','1000  m²');
        cy.get('#quantity-houses').should('contain.text','100');
        cy.get('#price-per-household').contains('R$ 45.000,00').should('exist');
        cy.get('#total-price').contains('R$ 4.500.000,00').should('exist');

        cy.get('.modal-body > :nth-child(13)').should('contain.text','Baixar arquivo KML/KMZ/SHP');
        cy.get(':nth-child(14) > .col > .mt-4 > strong').should('contain.text','Arquivo do Projeto');
        cy.get('#project-file > a').contains('Clique aqui para baixar o arquivo do projeto.').click();

        const downloadsFolder = Cypress.config('downloadsFolder');
        const fileName = 'Proposta-Empresa Teste LTDA-projeto-Russas-CE-2311801-01.kml';

        cy.readFile(`${downloadsFolder}/${fileName}`, 'binary', { timeout: 15000 })
            .should(buffer => {
                expect(buffer.length).to.be.gt(1000);
            });

        cy.get(':nth-child(15) > .col > .mt-4').should('contain.text','Área estimada da poligonal');
        cy.get('object').should('have.attr', 'data');
    });
});
