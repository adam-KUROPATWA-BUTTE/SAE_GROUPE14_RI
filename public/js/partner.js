/**
 * Class representing the partner institution management logic.
 * Handles displaying and hiding the add partner form.
 */
class PartnerManager {
    constructor() {
        this.addPartnerBtn = document.querySelector('.btn-add-partner');
        this.partnerForm = document.getElementById('partner-form-container');
        this.cancelBtn = document.querySelector('.btn-cancel');
        
        this.initEvents();
    }

    /**
     * Initializes click events for opening and closing the form.
     */
    initEvents() {
        if (this.addPartnerBtn && this.partnerForm && this.cancelBtn) {
            this.addPartnerBtn.addEventListener('click', () => this.showForm());
            this.cancelBtn.addEventListener('click', () => this.hideForm());
        }
    }

    /**
     * Shows the partner creation form and hides the 'Add' button.
     */
    showForm() {
        this.addPartnerBtn.style.display = 'none';
        this.partnerForm.classList.remove('hidden');
    }

    /**
     * Hides the partner creation form, resets its content, and shows the 'Add' button.
     */
    hideForm() {
        this.partnerForm.classList.add('hidden');
        this.addPartnerBtn.style.display = 'inline-flex';
        
        const form = this.partnerForm.querySelector('form');
        if(form) form.reset();
    }
}

// Instantiate globally
document.addEventListener('DOMContentLoaded', () => {
    window.partnerManager = new PartnerManager();
});