describe('Painel de Controle – Timeline de Iniciativas', () => {
    beforeEach(() => {
        cy.viewport(1920, 1080)
        cy.login('alessandrofeitoza@example.com', 'Aurora@2024')

        cy.visit('/painel/iniciativas')
        cy.contains('tbody tr', 'Vozes do Sertão')
            .contains('Timeline')
            .click()

        cy.url().should('match', /\/painel\/iniciativas\/[0-9a-f\-]+\/timeline$/)
    })

    it('Exibe o cabeçalho e o botão Voltar', () => {
        cy.get('h2')
            .should('contain.text', 'Iniciativa - Vozes do Sertão - Timeline')

        cy.contains('a.btn', 'Voltar')
            .should('be.visible')
    })

    it('Lista ambos os eventos com botão Detalhes', () => {
        cy.get('table.table tbody tr').its('length').should('be.gte', 2)

        cy.contains('tbody tr', 'A entidade foi criada')
            .within(() => {
                cy.contains('button', 'Detalhes').should('be.visible')
            })

        cy.contains('tbody tr', 'A entidade foi atualizada')
            .within(() => {
                cy.contains('button', 'Detalhes').should('be.visible')
            })
    })

    it('Abre o modal de detalhes do evento de atualização e verifica as colunas "De" e "Para"', () => {
        cy.contains('tbody tr', 'A entidade foi atualizada')
            .within(() => cy.contains('button', 'Detalhes').click())

        cy.get('#modal-timeline').should('be.visible')

        cy.get('#modal-timeline .modal-body table thead tr')
            .within(() => {
                cy.contains('th', 'De')
                cy.contains('th', 'Para')
            })

        cy.get('#modal-timeline-table-body tr')
            .contains('td', 'Nome')
            .parent()
            .within(() => {
                cy.get('td').eq(1).should('contain.text', 'Voz')
                cy.get('td').eq(2).should('contain.text', 'Vozes do Sertão')
            })
    })
})
