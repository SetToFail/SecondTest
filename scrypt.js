function showForm(type = null, id = null) {
    const modal = document.getElementById('modal');
    const formType = document.getElementById('form-type');
    const formId = document.getElementById('form-id');
    const modalTitle = document.getElementById('modal-title');
    const dealFields = document.getElementById('deal-fields');
    const contactFields = document.getElementById('contact-fields');
    
    if (type) {
        formType.value = type;
        formId.value = id || '';
        modalTitle.textContent = id ? 'Редактировать' : 'Добавить';
        
        if (type === 'deal') {
            dealFields.style.display = 'block';
            contactFields.style.display = 'none';
        } else {
            dealFields.style.display = 'none';
            contactFields.style.display = 'block';
        }
    }
    
    modal.style.display = 'block';
}

function hideForm() {
    document.getElementById('modal').style.display = 'none';
    document.getElementById('item-form').reset();
}

function editItem(type, id) {
    showForm(type, id);
    // Здесь можно добавить загрузку данных в форму
}

// Закрытие модального окна при клике вне его
window.onclick = function(event) {
    const modal = document.getElementById('modal');
    if (event.target === modal) {
        hideForm();
    }
}

// Для выбора типа при добавлении
document.querySelector('.add-btn').addEventListener('click', function() {
    const currentMenu = new URLSearchParams(window.location.search).get('menu');
    showForm(currentMenu === 'deals' ? 'deal' : 'contact');
});