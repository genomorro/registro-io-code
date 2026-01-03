import { Controller } from '@hotwired/stimulus';
import SignaturePad from 'signature_pad';

export default class extends Controller {
    static targets = ['canvas', 'clearButton', 'sign', 'form'];

    connect() {
        this.signaturePad = new SignaturePad(this.canvasTarget, {
            backgroundColor: 'rgb(255, 255, 255)',
        });

        this.clearButtonTarget.addEventListener('click', () => {
            this.signaturePad.clear();
        });

        this.formTarget.addEventListener('submit', (event) => {
            if (!this.signaturePad.isEmpty()) {
		this.signTarget.value = this.signaturePad.toDataURL('image/svg+xml');
            }
        });

        window.addEventListener('resize', this.resizeCanvas.bind(this));
        this.resizeCanvas();
    }

    resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        this.canvasTarget.width = this.canvasTarget.offsetWidth * ratio;
        this.canvasTarget.height = this.canvasTarget.offsetHeight * ratio;
        this.canvasTarget.getContext('2d').scale(ratio, ratio);
        this.signaturePad.clear();
    }
}
