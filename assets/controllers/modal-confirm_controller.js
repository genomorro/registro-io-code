import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        message: String,
        title: String
    }

    confirm(event) {
        if (this.element.dataset.confirmed === "true") {
            // If already confirmed, let the event continue (or let the next submit call go through)
            return;
        }

        // Prevent submission and other listeners (like webrtc capture)
        event.preventDefault();
        event.stopImmediatePropagation();

        const modalElement = document.getElementById('confirmModal');
        if (!modalElement) {
            // Fallback to native confirm if modal is not in DOM
            if (confirm(this.messageValue || "Are you sure?")) {
                this.element.dataset.confirmed = "true";
                this.element.requestSubmit();
            }
            return;
        }

        const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);
        
        const titleElement = modalElement.querySelector('.modal-title');
        if (titleElement) {
            titleElement.textContent = this.titleValue || titleElement.getAttribute('data-default-title') || "Confirmation";
        }

        const bodyElement = modalElement.querySelector('.modal-body');
        if (bodyElement) {
            bodyElement.textContent = this.messageValue || "Are you sure?";
        }
        
        const confirmBtn = modalElement.querySelector('.btn-confirm');
        if (confirmBtn) {
            // Replace the button to clear previous event listeners
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            newConfirmBtn.addEventListener('click', () => {
                modal.hide();
                this.element.dataset.confirmed = "true";
                // requestSubmit() triggers the submit event again, 
                // but this time the first check (confirmed === "true") will pass.
                this.element.requestSubmit();
            });
        }

        modal.show();
    }
}
