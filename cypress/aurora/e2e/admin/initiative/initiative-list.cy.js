describe('Painel de Controle – Página de listar Iniciativas', () => {
    beforeEach(() => {
        cy.viewport(1920, 1080)
        cy.login('alessandrofeitoza@example.com', 'Aurora@2024')
        cy.visit('/painel/iniciativas')
    })

    it('Deve exibir o título e pelo menos uma iniciativa com colunas válidas', () => {
        cy.get('h2')
            .should('contain.text', 'Minhas Iniciativas')

        cy.get('table[data-cy="table-initiative-list"]')
            .should('be.visible')
            .find('tbody tr')
            .should('have.length.at.least', 1)

        cy.get('table[data-cy="table-initiative-list"] tbody tr')
            .first()
            .within(() => {
                cy.get('td').eq(0)
                    .find('a')
                    .invoke('text')
                    .then(t => expect(t.trim()).not.to.be.empty)

                cy.get('td').eq(1)
                    .invoke('text')
                    .then(t => {
                        const status = t.trim()
                        expect(status).to.match(/^(Publicado|Rascunho|Na lixeira)$/)
                    })

                cy.get('td').eq(2)
                    .invoke('text')
                    .then(t => {
                        expect(t.trim()).to.match(/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}$/)
                    })
            })
    })
})
