import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["canvas", "clearButton", "sign"];

    connect() {
        this.ctx = this.canvasTarget.getContext("2d");
        this.painting = false;
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Mouse events
        this.canvasTarget.addEventListener("mousedown", this.startPosition.bind(this));
        this.canvasTarget.addEventListener("mouseup", this.finishedPosition.bind(this));
        this.canvasTarget.addEventListener("mousemove", this.draw.bind(this));

        // Touch events
        this.canvasTarget.addEventListener("touchstart", this.startPosition.bind(this));
        this.canvasTarget.addEventListener("touchend", this.finishedPosition.bind(this));
        this.canvasTarget.addEventListener("touchmove", this.draw.bind(this));

        // Clear button
        this.clearButtonTarget.addEventListener("click", this.clearCanvas.bind(this));
    }

    startPosition(e) {
        e.preventDefault();
        this.painting = true;
        this.draw(e);
    }

    finishedPosition() {
        this.painting = false;
        this.ctx.beginPath();
    }

    draw(e) {
        if (!this.painting) return;

        e.preventDefault();

        this.ctx.lineWidth = 1;
        this.ctx.lineCap = "round";
        this.ctx.strokeStyle = "blue";

        let rect = this.canvasTarget.getBoundingClientRect();
        let x, y;

        if (e.touches) {
            x = e.touches[0].clientX - rect.left;
            y = e.touches[0].clientY - rect.top;
        } else {
            x = e.clientX - rect.left;
            y = e.clientY - rect.top;
        }

        this.ctx.lineTo(x, y);
        this.ctx.stroke();
        this.ctx.beginPath();
        this.ctx.moveTo(x, y);
    }

    clearCanvas() {
        this.ctx.clearRect(0, 0, this.canvasTarget.width, this.canvasTarget.height);
    }

    save() {
        const dataURL = this.canvasTarget.toDataURL("image/png");
        this.signTarget.value = dataURL;
    }
}
