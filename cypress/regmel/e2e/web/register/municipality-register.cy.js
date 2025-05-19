describe('Cadastro de Município', () => {
    let timestamp, randomEmailMunicipio, randomEmailUsuario, randomCPF;

    Cypress.on('uncaught:exception', (err, runnable) => {
        if (err.message.includes('fire is not a function')) return false;
        if (err.message.includes('VIEW_AUTHENTICATION_ERROR_CNPJ_INVALID')) return false;
        if (err.message.includes("Identifier 'form' has already been declared")) return false;
    });

    beforeEach(() => {
        timestamp = Date.now();
        randomEmailMunicipio = `municipio${timestamp}@teste.com`;
        randomEmailUsuario = `jose${timestamp}@municipio.com`;
        randomCPF = `${Math.floor(10000000000 + Math.random() * 89999999999)}`;
        cy.visit('/cadastro/municipio');
    });

    it('preenche e envia o formulário com sucesso (sem anexo)', () => {
        cy.get('select#opportunity').then(($select) => {
            if ($select.find('option').length > 1) {
                cy.get('select#opportunity').select(1);
            }
        });

        cy.get('input[name="firstname"]').should('be.visible').type('José', { force: true });
        cy.get('input[name="lastname"]').type('Silva', { force: true });
        cy.get('input[name="position"]').type('Diretor', { force: true });
        cy.get('input[name="cpf"]').type(randomCPF, { force: true });
        cy.get('input[name="userEmail"]').type(randomEmailUsuario, { force: true });
        cy.get('input[name="password"]').type('Aurora@2024', { force: true });
        cy.get('input[name="confirm_password"]').type('Aurora@2024', { force: true });

        cy.get('#state + .ts-wrapper')
            .click()
            .find('.ts-dropdown .ts-dropdown-content')
            .contains('Ceará')
            .click();

        cy.wait(2000);

        cy.get('#city + .ts-wrapper')
            .click()
            .type('Santana do Acaraú')
            .find('.ts-dropdown .ts-dropdown-content')
            .contains('Santana do Acaraú')
            .click({ force: true });

        cy.get('input[name="site"]').type('https://municipio.ce.gov.br', { force: true });
        cy.get('input[name="phone"]').first().type('11999999999', { force: true });
        cy.get('input[name="email"]').first().type(randomEmailMunicipio, { force: true });

        cy.get('input[name="hasHousingExperience"][value="1"]').check({ force: true });
        cy.get('input[name="hasPlhis"][value="0"]').check({ force: true });

        cy.get('input[type=file]').selectFile('./cypress/regmel/fixtures/file.pdf');

        cy.get('input[name="acceptTerms"]').check({ force: true });
        cy.get('input[name="acceptPrivacy"]').check({ force: true });
        cy.get('input[name="acceptImage"]').check({ force: true });

        cy.get('button[type="submit"]').click();
        cy.wait(3000);
    });

    it('valida se o município aparece na listagem após login admin', () => {
        cy.visit('/');
        cy.contains('Entrar').click();
        cy.get('input[name="email"]').type('admin@regmel.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.contains('button', 'Entrar').click();

        cy.url().should('include', '/painel');
        cy.contains('Municípios').click();
        cy.url().should('include', '/painel/admin/municipios');
        cy.contains('Santana do Acaraú').should('be.visible');
    });

    it('valida se o documento aparece na listagem com o nome correto', () => {
        cy.visit('/');
        cy.contains('Entrar').click();
        cy.get('input[name="email"]').type('admin@regmel.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.contains('button', 'Entrar').click();

        cy.wait(3000);
        cy.contains('Termos de Adesão').click();
        cy.url().should('include', '/painel/admin/municipios-documentos');
        cy.contains('Termo-SantanaDoAcarau-CE-1.pdf').should('be.visible');
    });
});
