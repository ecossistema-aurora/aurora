document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.tags-selector').forEach(selector => {
        initializeTagSelector(selector);
    });
});

function initializeTagSelector(selector) {
    const inputName = selector.dataset.inputName;
    const tagsContainer = selector.querySelector(`#tags-container-${inputName}`);
    const dropdownMenu = selector.querySelector('.dropdown-menu');
    const searchInput = selector.querySelector('.custom-search-input');
    const newTagItem = selector.querySelector('.new-tag-item');
    const newTagButton = newTagItem.querySelector('button');
    const newTagSpan = newTagButton.querySelector('span')
    const errorMessage = document.getElementById('span-message');

    const addTag = (label, value) => {
        if (!label || !label.trim()) return;

        const existingTag = tagsContainer.querySelector(`.area-tag[data-value="${value}"]`);
        if (existingTag) return;

        const tagElement = document.createElement('div');
        tagElement.className = 'area-tag';
        tagElement.dataset.value = value;
        tagElement.innerHTML = `
            <span>${label}</span>
            <input type="hidden" name="${inputName}[]" value="${value}"/>
            <button type="button" class="remove-tag m-0 p-0 px-1 border-0 bg-transparent">x</button>
        `;
        tagsContainer.appendChild(tagElement);

        const optionInList = dropdownMenu.querySelector(`li > button[data-value="${value}"]`);
        if (optionInList) {
            optionInList.parentElement.classList.add('d-none');
        }
        searchInput.value = '';
        handleFilter();
    };

    const removeTag = (tagElement) => {
        const value = tagElement.dataset.value;
        tagElement.remove();

        const optionInList = dropdownMenu.querySelector(`li > button[data-value="${value}"]`);
        if (optionInList) {
            optionInList.parentElement.classList.remove('d-none');
        }
    };

    const handleFilter = () => {
        const query = searchInput.value.toLowerCase().trim();
        const allOptions = dropdownMenu.querySelectorAll('li > button[data-value]');
        let exactMatchFound = false;

        allOptions.forEach(button => {
            const label = button.dataset.label.toLowerCase();
            const parentLi = button.parentElement;

            const isVisible = label.includes(query);
            parentLi.style.display = isVisible ? 'block' : 'none';

            if (isVisible && label === query) {
                exactMatchFound = true;
            }
        });

        const alreadySelected = !!tagsContainer.querySelector(`.area-tag[data-value="${searchInput.value.trim()}"]`);
        const canShowNewTagButton = query && !exactMatchFound && !alreadySelected;

        newTagItem.classList.add('d-none');

        if (exactMatchFound && searchInput.value.trim()) {
            errorMessage.classList.remove('d-none');
            return;
        }
        
        errorMessage.classList.add('d-none');

        if (canShowNewTagButton) {
            newTagSpan.textContent = `Adicionar "${searchInput.value.trim()}"`;
            newTagItem.classList.remove('d-none');
            errorMessage.classList.add('d-none');
        }
    };

    searchInput.addEventListener('input', handleFilter);

    dropdownMenu.addEventListener('click', (event) => {
        const target = event.target;

        if (newTagButton.contains(target)) {
            const newLabel = searchInput.value.trim();
            const newValue = newLabel;
            addTag(newLabel, newValue);
            return;
        }

        const dropdownItem = target.closest('.dropdown-item[data-value]');
        if (dropdownItem) {
            addTag(dropdownItem.dataset.label, dropdownItem.dataset.value);
        }
    });

    tagsContainer.addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-tag')) {
            const tagElement = event.target.closest('.area-tag');
            removeTag(tagElement);
        }
    });
}