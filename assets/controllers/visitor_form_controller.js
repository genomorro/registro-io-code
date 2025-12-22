import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['dniField', 'dniOtherField'];

    connect() {
        this.toggleDniOther();
    }

    toggleDniOther() {
        if (this.dniFieldTarget.value === 'Otro') {
            this.dniOtherFieldTarget.classList.remove('d-none');
            this.dniOtherFieldTarget.querySelector('input').required = true;
        } else {
            this.dniOtherFieldTarget.classList.add('d-none');
            this.dniOtherFieldTarget.querySelector('input').required = false;
        }
    }
}
