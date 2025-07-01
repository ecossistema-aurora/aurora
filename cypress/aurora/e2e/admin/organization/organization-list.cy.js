describe('Painel de Controle – Listagem de Organizações', () => {
    beforeEach(() => {
        cy.viewport(1920, 1080)
        cy.login('alessandrofeitoza@example.com', 'Aurora@2024')
        cy.visit('/painel/organizacoes')
    })

    it('Exibe corretamente a tabela de organizações', () => {
        cy.contains('h2', 'Minhas Organizações').should('be.visible')

        cy.get('table.table tbody tr')
            .its('length')
            .should('be.gte', 1)

        cy.get('table.table tbody tr').first().within(() => {
            cy.get('td').eq(0)
                .find('a')
                .should('have.attr', 'href')
                .and('match', /\/painel\/organizacoes\/\d+/)

            cy.get('td').eq(1)
                .invoke('text')
                .should('match', /Publicado|Rascunho|Lixeira/i)

            cy.get('td').eq(2)
                .invoke('text')
                .should('match', /\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}/)
        })
    })
})
