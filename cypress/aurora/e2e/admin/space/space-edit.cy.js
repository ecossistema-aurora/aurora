describe('Teste de navegação, validação e edição da página de Espaços', () => {
    beforeEach(() => {
        cy.viewport(1920, 1080)
        cy.login('alessandrofeitoza@example.com', 'Aurora@2024')

        cy.intercept('POST', '/painel/espacos/*/editar').as('updateSpace')

        cy.visit('/painel/espacos')
        cy.contains('tbody tr', 'Publicado')
            .first()
            .within(() => {
                cy.contains('Editar').click()
            })

        cy.url().should('match', /\/painel\/espacos\/[0-9a-f\-]+\/editar$/)
    })

    it('Garante que a página de editar espaços funciona', () => {
        cy.get(':nth-child(1) > .accordion-header > .accordion-button')
            .contains('Informações de apresentação')
            .should('be.visible')

        cy.get('[for="name"]').should('be.visible').and('contain', 'Nome do espaço')
        cy.get('#add-activityAreas-btn').click()
        cy.get("button[data-label='Fotografia']").click()

        cy.get('#add-tags-btn').click()
        cy.get("button[data-label='Social']").click()

        cy.get('[for="short-description"]').type('Descrição curta')
        cy.get('[for="long-description"]').type('Descrição longa')

        cy.get(':nth-child(2) > .accordion-header > .accordion-button')
            .contains('Dados de endereço')
            .click()
            .should('have.attr', 'aria-expanded', 'true')

        cy.get('#cep').type('57600210').blur()
        cy.get('.col-md-8 > .form-label').should('contain', 'Logradouro')
        cy.get('#no_number').click()

        cy.get('.entity-address-data > :nth-child(3) > :nth-child(1) > .form-label')
            .should('contain', 'Estado')
        cy.get('.entity-address-data > :nth-child(3) > :nth-child(2) > .form-label')
            .should('contain', 'Município')

        cy.get(':nth-child(3) > .accordion-header > .accordion-button')
            .contains('Capacidade e Acessibilidade')
            .click()
            .should('have.attr', 'aria-expanded', 'true')

        cy.get('.entity-accessibility > :nth-child(1) > .col-md-4 > .form-label')
            .should('contain', 'Capacidade de pessoas')

        cy.get(':nth-child(4) > .accordion-header > .accordion-button')
            .contains('Horário de funcionamento')
            .click()
            .should('have.attr', 'aria-expanded', 'true')

        cy.get('.opening-hours-row .col-md-4 .form-label')
            .should('contain', 'Dias da semana')

        cy.get(':nth-child(5) > .accordion-header > .accordion-button')
            .contains('Permissões')
            .click()
            .should('have.attr', 'aria-expanded', 'true')

        cy.get('.mb-3 > .form-label')
            .should('contain', 'Permitir livre vinculação com')

        cy.get(':nth-child(6) > .accordion-header > .accordion-button')
            .contains('Redes sociais')
            .click()
            .should('have.attr', 'aria-expanded', 'true')

        cy.get('.container-fluid > :nth-child(2) > :nth-child(1) > .form-label')
            .should('contain', 'Instagram')

        cy.get("button[form='space-edit-form'][type='submit']")
            .contains('Salvar')
            .click()
    })
})
