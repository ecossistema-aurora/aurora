function applyMask(input, maskFunction) {
    if (input.value) {
        input.value = maskFunction(input.value);
    }

    input.addEventListener("input", () => {
        input.value = maskFunction(input.value);
    });
}

function cpfMask(value) {
    return value
        .replace(/\D/g, '')
        .substring(0, 11)
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
}

function phoneMask(value) {
    const digits = value.replace(/\D/g, '');

    if (digits.length <= 10) {
        return digits
            .replace(/(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{4})(\d{1,4})/, '$1-$2');
    }

    return digits
        .replace(/(\d{2})(\d)/, '($1) $2')
        .replace(/(\d{1})(\d{4})(\d{1,4})/, '$1 $2-$3')
        .substring(0, 16);
}

function cnpjMask(value) {
    return value
        .replace(/\D/g, '')
        .substring(0, 14)
        .replace(/^(\d{2})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1/$2')
        .replace(/(\d{4})(\d{1,2})$/, '$1-$2');
}

function cepMask(value) {
    return value
        .replace(/\D/g, '')
        .replace(/(\d{5})(\d)/, '$1-$2')
        .substring(0, 9);
}

document.addEventListener("DOMContentLoaded", () => {
    const cpfInput = document.querySelector('[data-mask="cpf"]');
    const phoneInputs = document.querySelectorAll('[data-mask="phone"]');
    const cnpjInput = document.querySelector('[data-mask="cnpj"]');
    const cepInput = document.querySelector('[data-mask="cep"]');

    if (cpfInput) applyMask(cpfInput, cpfMask);
    if (cnpjInput) applyMask(cnpjInput, cnpjMask);
    if (cepInput) applyMask(cepInput, cepMask);

    phoneInputs.forEach(input => applyMask(input, phoneMask));

    if (cepInput) {
        applyMask(cepInput, cepMask);

        cepInput.form?.addEventListener('submit', () => {
            cepInput.value = cepInput.value.replace(/\D/g, '');
        });
    }
});
