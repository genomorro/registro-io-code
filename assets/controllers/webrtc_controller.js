import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["video", "canvas", "form", "evidence"];

    connect() {
	if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
	    console.error('Browser API navigator.mediaDevices.getUserMedia not available');
	    return;
	}

	navigator.mediaDevices.getUserMedia({ video: true, audio: false })
	    .then(stream => {
		this.videoTarget.srcObject = stream;
		this.videoTarget.play();
	    })
	    .catch(err => {
		console.log("An error occurred: " + err);
	    });
    }

    capture(event) {
	event.preventDefault();

	if (!this.videoTarget.srcObject) {
	    console.error('Video stream not available');
	    return;
	}

	const context = this.canvasTarget.getContext('2d');
	this.canvasTarget.width = 270;
	this.canvasTarget.height = 203;
	context.drawImage(this.videoTarget, 0, 0, this.canvasTarget.width, this.canvasTarget.height);

        const now = new Date();
        const timestamp = now.toLocaleString();

        context.font = "10px Arial";
        context.fillStyle = "rgba(255, 255, 255, 0.8)";
        context.fillText(timestamp, 10, this.canvasTarget.height - 10);

	const dataURL = this.canvasTarget.toDataURL('image/png');
	this.evidenceTarget.value = dataURL;

	this.formTarget.submit();
    }
}
