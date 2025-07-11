describe('Painel de Controle - Página de listar Eventos', () => {
    beforeEach(() => {
        cy.viewport(1920, 1080)
        cy.login('alessandrofeitoza@example.com', 'Aurora@2024')
        cy.visit('/painel/eventos/')
    })

    it('Garante que os eventos estejam visíveis e que é possível remover um', () => {
        cy.get('h2').contains('Meus Eventos').should('be.visible')
        cy.get('tbody > tr > :nth-child(1)').contains('Nordeste Literário').should('be.visible')
        cy.get('tbody > tr > :nth-child(2)').contains('Publicado').should('be.visible')
        cy.get('tbody > tr > :nth-child(3)').contains('14/08/2024 10:00:00').should('be.visible')
    })

    it('Garante que as ações Timeline, Editar e Excluir apareçam para um evento', () => {
        cy.contains('td:first-child', 'Cultura em ação')
            .parent('tr')
            .within(() => {
                cy.get('td:last-child').within(() => {
                    cy.contains('Timeline').should('be.visible')
                    cy.contains('Editar').should('be.visible')
                    cy.contains('Excluir').should('be.visible')
                })
            })
    })
})
