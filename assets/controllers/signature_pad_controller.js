import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["canvas", "sign"];
    drawing = false;
    context = null;

    connect() {
        this.context = this.canvasTarget.getContext('2d');
        this.context.lineWidth = 2;
        this.context.strokeStyle = 'black';
        this.context.lineCap = 'round';

        this.canvasTarget.addEventListener('mousedown', this.startDrawing.bind(this));
        this.canvasTarget.addEventListener('mouseup', this.stopDrawing.bind(this));
        this.canvasTarget.addEventListener('mousemove', this.draw.bind(this));
        this.canvasTarget.addEventListener('touchstart', this.startDrawing.bind(this), { passive: false });
        this.canvasTarget.addEventListener('touchend', this.stopDrawing.bind(this), { passive: false });
        this.canvasTarget.addEventListener('touchmove', this.draw.bind(this), { passive: false });
    }

    startDrawing(event) {
        this.drawing = true;
        this.context.beginPath();
        const pos = this.getMousePos(this.canvasTarget, event);
        this.context.moveTo(pos.x, pos.y);
    }

    stopDrawing() {
        this.drawing = false;
    }

    draw(event) {
        if (!this.drawing) return;
        event.preventDefault();
        const pos = this.getMousePos(this.canvasTarget, event);
        this.context.lineTo(pos.x, pos.y);
        this.context.stroke();
    }

    getMousePos(canvas, evt) {
        const rect = canvas.getBoundingClientRect();
        let clientX, clientY;
        if (evt.touches && evt.touches.length > 0) {
            clientX = evt.touches[0].clientX;
            clientY = evt.touches[0].clientY;
        } else {
            clientX = evt.clientX;
            clientY = evt.clientY;
        }
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }

    clear() {
        this.context.clearRect(0, 0, this.canvasTarget.width, this.canvasTarget.height);
    }

    async captureSign(event) {
        const signField = this.signTarget;

        if (this.isCanvasBlank()) {
            signField.value = '';
            return;
        }

        if (signField) {
            const context = this.canvasTarget.getContext('2d');
            const now = new Date();
            const timestamp = now.toLocaleString();

            context.font = "10px Arial";
            context.fillStyle = "rgba(0, 0, 0, 0.8)";
            context.fillText(timestamp, 10, this.canvasTarget.height - 10);

            const logo = await this.loadLogo(this.logoUrl);

            const logoWidth = this.canvasTarget.width * 0.10;
            const logoHeight = (logo.height / logo.width) * logoWidth;
            const x = this.canvasTarget.width - logoWidth - 10;
            const y = 10;

            context.globalAlpha = 0.7;
            context.drawImage(logo, x, y, logoWidth, logoHeight);
            context.globalAlpha = 1;

            const dataURL = this.canvasTarget.toDataURL('image/png');
            signField.value = dataURL;
        } else {
            console.error('Sign field not found in the form.');
        }
    }

    isCanvasBlank() {
        const context = this.canvasTarget.getContext('2d');
        const pixelBuffer = new Uint32Array(
            context.getImageData(0, 0, this.canvasTarget.width, this.canvasTarget.height).data.buffer
        );
        return !pixelBuffer.some(color => color !== 0);
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
