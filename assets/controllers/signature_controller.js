import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["canvas", "clearButton", "sign"];

    connect() {
        this.ctx = this.canvasTarget.getContext("2d");
        this.painting = false;

        this.ctx.lineWidth = 1;
        this.ctx.lineCap = "round";
        this.ctx.strokeStyle = "blue";

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
        this.canvasTarget.addEventListener("touchmove", this.draw.bind(this), { passive: false });

        // Clear button
        this.clearButtonTarget.addEventListener("click", this.clearCanvas.bind(this));
    }

    getCoordinates(e) {
        const rect = this.canvasTarget.getBoundingClientRect();
        const touch = e.touches ? e.touches[0] : null;
        return {
            x: (touch || e).clientX - rect.left,
            y: (touch || e).clientY - rect.top,
        };
    }

    startPosition(e) {
        e.preventDefault();
        this.painting = true;
        const { x, y } = this.getCoordinates(e);
        this.ctx.beginPath();
        this.ctx.moveTo(x, y);
    }

    finishedPosition() {
        this.painting = false;
    }

    draw(e) {
        if (!this.painting) return;
        e.preventDefault();

        const { x, y } = this.getCoordinates(e);
        this.ctx.lineTo(x, y);
        this.ctx.stroke();
    }

    clearCanvas() {
        this.ctx.clearRect(0, 0, this.canvasTarget.width, this.canvasTarget.height);
    }

    save() {
        const dataURL = this.canvasTarget.toDataURL("image/png");
        this.signTarget.value = dataURL;
    }
}
