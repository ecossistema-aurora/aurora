describe('Cadastro de Empresa/OSC', () => {
    let timestamp, randomEmailEmpresa, randomCPF;

    beforeEach(() => {
        timestamp = Date.now();
        randomEmailEmpresa = `empresa${timestamp}@teste.com`;
        randomCPF = `${Math.floor(10000000000 + Math.random() * 89999999999)}`;
        cy.visit('/cadastro/empresa');
    });

    it('preenche e envia o formulário com sucesso', () => {
        cy.get('select#opportunity').then($select => {
            if ($select.find('option').length > 1) {
                cy.get('select#opportunity').select(1);
            }
        });

        cy.get('input[name="cnpj"]').type('12345678000199');
        cy.get('select[name="framework"]', { timeout: 10000 }).should('be.visible').select('profit');
        cy.get('input[name="name"]').type('Empresa Teste LTDA');
        cy.get('input[name="email"]').first().type(randomEmailEmpresa);
        cy.get('input[name="site"]').type('https://empresa.com');
        cy.get('input[name="phone"]').first().type('11999999999');

        cy.get('input[name="firstname"]').type('João');
        cy.get('input[name="lastname"]').type('Silva');
        cy.get('input[name="userPhone"]').type('11988888888');
        cy.get('input[name="position"]').type('Diretor');
        cy.get('input[name="cpf"]').type(randomCPF);
        cy.get('input[name="userEmail"]').type('joao@empresa.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.get('input[name="confirm_password"]').type('Aurora@2024');

        cy.get('input[name="acceptTerms"]').check({ force: true });
        cy.get('input[name="acceptPrivacy"]').check({ force: true });
        cy.get('input[name="acceptImage"]').check({ force: true });

        cy.get('button[type="submit"]').click();

        cy.wait(5000);
        cy.url().then(url => {
            cy.log('URL após submit:', url);
            cy.screenshot('pos-submit');
        });
    });

    it('valida se a empresa aparece na listagem após login admin', () => {
        cy.request('/').then(resp => {
            expect(resp.status).to.eq(200);
        });

        cy.visit('/');
        cy.contains('Entrar').click();

        cy.get('input[name="email"]').type('admin@regmel.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.contains('button', 'Entrar').click();

        cy.wait(5000);
        cy.url().then(url => {
            cy.log('URL após login:', url);
            cy.screenshot('pos-login');
        });

        cy.contains('Empresas/OSC').click();
        cy.url().should('include', '/painel/admin/empresas');
        cy.contains('Empresa Teste LTDA').should('be.visible');
    });
});
