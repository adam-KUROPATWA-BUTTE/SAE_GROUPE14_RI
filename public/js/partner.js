document.addEventListener("DOMContentLoaded", () => {
    const addPartnerBtn = document.querySelector('.btn-add-partner');
    const partnerForm = document.getElementById('partner-form-container');
    const cancelBtn = document.querySelector('.btn-cancel');

    if (addPartnerBtn && partnerForm && cancelBtn) {
        addPartnerBtn.addEventListener('click', () => {
            addPartnerBtn.style.display = 'none';
            partnerForm.classList.remove('hidden');
        });

        cancelBtn.addEventListener('click', () => {
            partnerForm.classList.add('hidden');
            addPartnerBtn.style.display = 'inline-flex';
            // Reset form if needed
            const form = partnerForm.querySelector('form');
            if(form) form.reset();
        });
    }
});