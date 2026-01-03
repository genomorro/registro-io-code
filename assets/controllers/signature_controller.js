import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["canvas"];

    connect() {
        this.canvas = this.canvasTarget;
        this.ctx = this.canvas.getContext('2d');
        this.painting = false;
        
        this.ctx.lineWidth = 2;
        this.ctx.lineCap = 'round';
        this.ctx.strokeStyle = 'blue';

        // Bind methods to ensure 'this' refers to the controller
        this.boundDraw = this.draw.bind(this);
        this.boundStopPainting = this.stopPainting.bind(this);
    }

    startPainting(e) {
        this.painting = true;
        
        document.addEventListener('mousemove', this.boundDraw);
        document.addEventListener('mouseup', this.boundStopPainting);
        
        // Also handle touch events
        document.addEventListener('touchmove', this.boundDraw, { passive: false }); // passive: false to allow preventDefault
        document.addEventListener('touchend', this.boundStopPainting);
        
        this.draw(e); // Draw the first point
    }

    stopPainting() {
        this.painting = false;
        this.ctx.beginPath();

        document.removeEventListener('mousemove', this.boundDraw);
        document.removeEventListener('mouseup', this.boundStopPainting);
        
        document.removeEventListener('touchmove', this.boundDraw);
        document.removeEventListener('touchend', this.boundStopPainting);
    }
    
    getCoordinates(e) {
        const rect = this.canvas.getBoundingClientRect();
        if (e.touches && e.touches.length > 0) {
            return [e.touches[0].clientX - rect.left, e.touches[0].clientY - rect.top];
        }
        return [e.clientX - rect.left, e.clientY - rect.top];
    }

    draw(e) {
        if (!this.painting) return;

        // Prevent scrolling on touch devices
        if (e.type === 'touchmove') {
            e.preventDefault();
        }
        
        const [x, y] = this.getCoordinates(e);
        
        this.ctx.lineTo(x, y);
        this.ctx.stroke();
        this.ctx.beginPath();
        this.ctx.moveTo(x, y);
    }
    
    clear() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
    }
}
