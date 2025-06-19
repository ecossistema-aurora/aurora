import {
    trans,
    ZIP_CODE_NOT_FOUND,
    ZIP_CODE_CITY_NOT_FOUND,
    ZIP_CODE_GENERIC_ERROR,
    ZIP_CODE_INVALID,
    ZIP_CODE_STATE_NOT_FOUND
} from "../translator.js";

document.addEventListener('DOMContentLoaded', () => {
    const cepInput = document.getElementById('cep');
    const streetInput = document.getElementById('street');
    const numberInput = document.getElementById('number');
    const neighborhoodInput = document.getElementById('neighborhood');
    const addressComplementInput = document.getElementById('address_complement');

    const stateSelectInstance = document.getElementById('state').tomselect;
    const citySelectInstance = document.getElementById('city').tomselect;

    const mapIframe = document.querySelector('.ratio iframe');

    let errorElement = document.getElementById('error');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.id = 'cep-error-message';
        cepInput.parentNode.insertBefore(errorElement, cepInput.nextSibling);
    }

    function showError(message) {
        errorElement.textContent = message;
        errorElement.classList.add('text-danger', 'mt-2');
        errorElement.style.display = 'block';
    }

    function clearError() {
        errorElement.textContent = '';
        errorElement.classList.remove('text-danger', 'mt-2');
        errorElement.style.display = 'none';
    }

    cepInput.addEventListener('blur', async () => {
        const cep = cepInput.value.replace(/\D/g, '');
        clearError();

        if (cep.length === 8) {
            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();

                if (!data.erro) {
                    streetInput.value = data.logradouro || '';
                    neighborhoodInput.value = data.bairro || '';
                    addressComplementInput.value = data.complemento || '';

                    if (stateSelectInstance) {
                        const ufFromViaCep = data.uf;
                        const stateNameFromViaCep = data.estado;
                        let selectedStateValue = '';

                        for (const optionId in stateSelectInstance.options) {
                            const option = stateSelectInstance.options[optionId];
                            if (option.value === ufFromViaCep || option.text.toUpperCase() === stateNameFromViaCep.toUpperCase()) {
                                selectedStateValue = option.value;
                                break;
                            }
                        }

                        if (selectedStateValue) {
                            stateSelectInstance.setValue(selectedStateValue);
                            stateSelectInstance.trigger('change', selectedStateValue);
                        } else {
                            stateSelectInstance.setValue('');
                            citySelectInstance.clearOptions();
                            citySelectInstance.clear();
                            showError(trans(ZIP_CODE_STATE_NOT_FOUND));
                        }
                    }

                    setTimeout(() => {
                        if (citySelectInstance) {
                            const cityNameFromViaCep = data.localidade;
                            let cityFound = false;
                            let selectedCityValue = '';

                            for (const optionId in citySelectInstance.options) {
                                const option = citySelectInstance.options[optionId];
                                if (option.text.toUpperCase() === cityNameFromViaCep.toUpperCase()) {
                                    selectedCityValue = option.value;
                                    cityFound = true;
                                    break;
                                }
                            }

                            if (cityFound) {
                                citySelectInstance.setValue(selectedCityValue);
                            } else {
                                citySelectInstance.clearOptions();
                                citySelectInstance.clear();
                                showError(trans(ZIP_CODE_CITY_NOT_FOUND));
                            }
                        }
                    }, 600);

                    const fullAddress = `${data.logradouro || ''}, ${data.bairro || ''}, ${data.localidade || ''} - ${data.uf || ''}, ${data.cep || ''}, Brasil`;
                    const encodedAddress = encodeURIComponent(fullAddress);
                    mapIframe.src = `https://maps.google.com/maps?q=${encodedAddress}&t=&z=15&ie=UTF8&iwloc=&output=embed`;

                } else {
                    showError(trans(ZIP_CODE_NOT_FOUND));
                    clearAddressFields();
                }
            } catch (error) {
                showError(trans(ZIP_CODE_GENERIC_ERROR));
                clearAddressFields();
            }
        } else if (cep.length > 0) {
            showError(trans(ZIP_CODE_INVALID));
            clearAddressFields();
        }
    });

    function clearAddressFields() {
        streetInput.value = '';
        numberInput.value = '';
        neighborhoodInput.value = '';
        addressComplementInput.value = '';
        if (stateSelectInstance) stateSelectInstance.setValue('');
        if (citySelectInstance) {
            citySelectInstance.clearOptions();
            citySelectInstance.clear();
        }
        mapIframe.src = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d75897.0345148201!2d-38.627535961048046!3d-3.704056341712097!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x7c749006cf93087%3A0x203684bff0b2f209!2sEd.%20S.%20Pedro%20(marco%20hist%C3%B3rico%20demolido)!5e1!3m2!1spt-BR!2sbr!4v1739989008210!5m2!1spt-BR!2sbr';
    }
});
