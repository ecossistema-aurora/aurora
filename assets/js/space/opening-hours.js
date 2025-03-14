document.addEventListener('DOMContentLoaded', function() {

    const daysOfWeek = [
        { value: 'sunday', label: 'Domingo' },
        { value: 'monday', label: 'Segunda-feira' },
        { value: 'tuesday', label: 'Terça-feira' },
        { value: 'wednesday', label: 'Quarta-feira' },
        { value: 'thursday', label: 'Quinta-feira' },
        { value: 'friday', label: 'Sexta-feira' },
        { value: 'saturday', label: 'Sábado' }
    ];

    function populateDropdown(selectElement) {
        selectElement.innerHTML = '';

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.disabled = true;
        defaultOption.selected = true;
        defaultOption.textContent = 'Selecione o dia';
        selectElement.appendChild(defaultOption);

        daysOfWeek.forEach(day => {
            const option = document.createElement('option');
            option.value = day.value;
            option.textContent = day.label;
            selectElement.appendChild(option);
        });
    }

    document.querySelectorAll('select.week_days').forEach(select => {
        populateDropdown(select);
    });

    const addButton = document.getElementById('add-opening-hours');
    const container = document.getElementById('opening-hours-container');
    const templateRow = document.querySelector('.opening-hours-row');

    if (addButton && container && templateRow) {
        addButton.addEventListener('click', function(event) {
            event.preventDefault();

            const newRow = templateRow.cloneNode(true);

            const newSelect = newRow.querySelector('select.week_days');
            if (newSelect) {
                populateDropdown(newSelect);
            }

            const opensInput = newRow.querySelector('.opens_at');
            if (opensInput) {
                opensInput.value = '';
            }
            const closesInput = newRow.querySelector('.closes_at');
            if (closesInput) {
                closesInput.value = '';
            }

            const addButtonRow = addButton.closest('.row.mt-4');
            container.insertBefore(newRow, addButtonRow);
        });
    }
});
