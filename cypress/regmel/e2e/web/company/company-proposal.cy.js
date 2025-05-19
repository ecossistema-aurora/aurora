describe('Cadastro de proposta', () => {
    beforeEach(() => {
        cy.visit('/login');
        cy.get('input[name="email"]').type('joao@empresa.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.get('button[data-cy="submit"]').click();
        cy.wait(1000);
        cy.contains('Empresas/OSC').click();
        cy.contains('Empresa Teste LTDA').click();
        cy.contains('Propostas').click();
    });

    it('Acessa, preenche e envia o formulário de proposta para município não credenciado', () => {
        cy.get('a').contains('Nova Proposta').click();

        cy.get('select#region').select('Nordeste', {force: true});

        cy.get('#state + .ts-wrapper')
            .click()
            .find('.ts-dropdown .ts-dropdown-content')
            .contains('Ceará')
            .click();

        cy.wait(2000);

        cy.get('#city + .ts-wrapper')
            .click()
            .type('Russas')
            .find('.ts-dropdown .ts-dropdown-content')
            .contains('Russas')
            .click({force: true});

        cy.get('input[name="name"]').type('Área Teste');
        cy.get('input[name="area_characteristic"]').first().click();
        cy.get('input[name="map"]').selectFile('./cypress/regmel/fixtures/file.pdf');
        cy.get('input[name="project"]').selectFile('./cypress/regmel/fixtures/Setores_Selecionados_Novo_PAC.kml');
        cy.get('input[name="area_size"]').type('1000');
        cy.get('input[name="quantity_houses"]').type('100');

        cy.get('input[name="acceptTerms"]').check({ force: true });

        cy.get('button[type="submit"]').click();

        // Verifica se a proposta foi criada com sucesso
        cy.contains('Empresa Teste LTDA').click();
        cy.contains('Propostas').click();
        cy.get('table > tbody').contains('tr', 'Área Teste').find('[data-column-id="status"]').should('contain', 'Sem Adesão do Município');
    });

    it('Acessa, preenche e envia o formulário de proposta para município credenciado', () => {
        cy.get('a').contains('Nova Proposta').click();

        cy.get('select#region').select('Nordeste', {force: true});

        cy.get('#state + .ts-wrapper')
            .click()
            .find('.ts-dropdown .ts-dropdown-content')
            .contains('Ceará')
            .click();

        cy.wait(2000);

        cy.get('#city + .ts-wrapper')
            .click()
            .type('Santana')
            .find('.ts-dropdown .ts-dropdown-content')
            .contains('Santana do Acaraú')
            .click({force: true});

        cy.get('input[name="name"]').type('Área nas Brenhas');
        cy.get('input[name="area_characteristic"]').first().click();
        cy.get('input[name="map"]').selectFile('./cypress/regmel/fixtures/file.pdf');
        cy.get('input[name="project"]').selectFile('./cypress/regmel/fixtures/Setores_Selecionados_Novo_PAC.kml');
        cy.get('input[name="area_size"]').type('1000');
        cy.get('input[name="quantity_houses"]').type('100');

        cy.get('input[name="acceptTerms"]').check({ force: true });

        cy.get('button[type="submit"]').click();

        // Verifica se a proposta foi criada com sucesso
        cy.contains('Empresa Teste LTDA').click();
        cy.contains('Propostas').click();
        cy.get('table > tbody').contains('tr', 'Área nas Brenhas').find('[data-column-id="status"]').should('contain', 'Enviada');
    });
});
