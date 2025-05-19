document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.view-member').forEach(function(link) {
        link.addEventListener('click', function() {
            try {
                const dataInfo = this.getAttribute('data-info');
                const agentData = JSON.parse(dataInfo);

                let contentHtml = '<div class="container-fluid">';
                contentHtml += '<div class="row mb-2">';
                contentHtml += '<div class="col-sm-6"><strong>Nome:</strong> ' + (agentData.name || '-') + '</div>';
                contentHtml += '<div class="col-sm-6"><strong>Sobrenome:</strong> ' + (agentData.surname || '-') + '</div>';
                contentHtml += '</div>';
                contentHtml += '<div class="row mb-2">';
                contentHtml += '<div class="col-sm-6"><strong>E-mail:</strong> ' + (agentData.email || '-') + '</div>';
                contentHtml += '<div class="col-sm-6"><strong>CPF:</strong> ' + (agentData.cpf || '-') + '</div>';
                contentHtml += '</div>';
                contentHtml += '<div class="row mb-2">';
                contentHtml += '<div class="col-sm-6"><strong>Cargo:</strong> ' + (agentData.cargo || '-') + '</div>';
                contentHtml += '<div class="col-sm-6"><strong>Telefone:</strong> ' + (agentData.telefone || '-') + '</div>';
                contentHtml += '</div>';

                if (agentData.extraFields && typeof agentData.extraFields === 'object') {
                    contentHtml += '<hr><h6>Campos Adicionais</h6><ul>';
                    for (const key in agentData.extraFields) {
                        contentHtml += `<li><strong>${key}:</strong> ${agentData.extraFields[key]}</li>`;
                    }
                    contentHtml += '</ul>';
                }

                contentHtml += '</div>';
                document.getElementById('member-details-content').innerHTML = contentHtml;

                const modalElement = document.getElementById('memberDetailsModal');
                const modalInstance = bootstrap?.Modal
                    ? new bootstrap.Modal(modalElement)
                    : new window.bootstrap.Modal(modalElement);

                modalInstance.show();

            } catch (err) {
                alert("Erro ao carregar dados do membro. Verifique o console.");
            }
        });
    });
});

