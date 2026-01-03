import { Controller } from '@hotwired/stimulus';
import SignaturePad from 'signature_pad';

export default class extends Controller {
    static targets = ["canvas", "sign"];

    connect() {
	this.signaturePad = new SignaturePad(this.canvasTarget, {
	    penColor: "rgb(0, 0, 0)",
	    backgroundColor: 'rgb(255, 255, 255)',
	});
    }

    clear() {
        this.signaturePad.clear();
    }

    capture() {
	if (!this.signaturePad.isEmpty()) {
	    this.signTarget.value = this.signaturePad.toDataURL('image/svg+xml');
	}
    }
}
