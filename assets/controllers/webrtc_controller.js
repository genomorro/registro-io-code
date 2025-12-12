import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["video", "canvas"];
    stream = null;

    connect() {
	if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
	    console.error('Browser API navigator.mediaDevices.getUserMedia not available');
	    return;
	}

	navigator.mediaDevices.getUserMedia({ video: true, audio: false })
	    .then(stream => {
		this.stream = stream;
		if (this.hasVideoTarget) {
		    this.videoTarget.srcObject = stream;
		    this.videoTarget.play();
		}
	    })
	    .catch(err => {
		console.log("An error occurred: " + err);
	    });
    }

    disconnect() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
        }
    }

    async capture(event) {
	event.preventDefault();
	const form = event.currentTarget;

	if (this.hasVideoTarget && this.videoTarget.srcObject) {
	    const evidenceField = form.querySelector('input[name="evidence"], input[name$="[evidence]"]');

	    if (evidenceField) {
		const context = this.canvasTarget.getContext('2d');
		this.canvasTarget.width = 270;
		this.canvasTarget.height = 203;
		context.drawImage(this.videoTarget, 0, 0, this.canvasTarget.width, this.canvasTarget.height);

		const now = new Date();
		const timestamp = now.toLocaleString();

		context.font = "10px Arial";
		context.fillStyle = "rgba(255, 255, 255, 0.8)";
		context.fillText(timestamp, 10, this.canvasTarget.height - 10);

		const logo = await this.loadLogo(this.logoUrl);

		const logoWidth = this.canvasTarget.width * 0.10; // 27
		const logoHeight = (logo.height / logo.width) * logoWidth; //195/165 * 27 = 31.9
		const x = this.canvasTarget.width - logoWidth - 10;
		const y = this.canvasTarget.height - logoHeight - 161;

		context.globalAlpha = 0.7;
		context.drawImage(logo, x, y, logoWidth, logoHeight);
		context.globalAlpha = 1;

		const dataURL = this.canvasTarget.toDataURL('image/png');
		evidenceField.value = dataURL;
	    } else {
		console.error('Evidence field not found in the form.');
	    }
	}

	const confirmationMessage = form.dataset.confirmation;
	if (confirmationMessage && !confirm(confirmationMessage)) {
	    return;
	}

	form.submit();
    }

    loadLogo(src) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = src;
        });
    }

    get logoUrl() {
	return this.data.get("logoUrl");
    }
}
