import { Controller } from '@hotwired/stimulus';
import C2S from 'canvas2svg';

export default class extends Controller {
    static targets = ["canvas", "sign", "checkInAt"];

    connect() {
        this.canvas = this.canvasTarget;
        this.initializeContexts();
        this.painting = false;
        
        this.boundDraw = this.draw.bind(this);
        this.boundStopPainting = this.stopPainting.bind(this);
    }

    initializeContexts() {
        this.visibleCtx = this.canvas.getContext('2d');
        this.svgCtx = new C2S(this.canvas.width, this.canvas.height);

        const contexts = [this.visibleCtx, this.svgCtx];
        contexts.forEach(ctx => {
            ctx.fillStyle = '#98989A';
            ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#611232';
        });
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
        this.visibleCtx.beginPath();
        this.svgCtx.beginPath();

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
        
        this.visibleCtx.lineTo(x, y);
        this.svgCtx.lineTo(x, y);
        
        this.visibleCtx.stroke();
        this.svgCtx.stroke();
        
        this.visibleCtx.beginPath();
        this.svgCtx.beginPath();
        
        this.visibleCtx.moveTo(x, y);
        this.svgCtx.moveTo(x, y);
    }
    
    clear() {
        this.visibleCtx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        this.initializeContexts();
    }

    async save() {
        const logoUrl = this.element.dataset.webrtcLogoUrl;
        const checkInValue = this.checkInAtTarget.value;

        // Convert logo to Data URL to embed it in the SVG
        const logoDataUrl = await this._imageToDataURL(logoUrl);

        const addWatermarks = (ctx, logoSrc) => {
            return new Promise((resolve) => {
                const logo = new Image();
                logo.src = logoSrc;
                logo.onload = () => {
                    ctx.font = '12px Arial';
                    ctx.fillStyle = 'black';
                    
                    // Date watermark
                    ctx.fillText(checkInValue, 5, this.canvas.height - 5);

                    // Logo watermark
                    const logoWidth = 50;
                    const logoHeight = (logo.height / logo.width) * logoWidth;
                    ctx.drawImage(logo, this.canvas.width - logoWidth - 5, 5, logoWidth, logoHeight);
                    
                    resolve();
                };
                logo.onerror = () => {
                    console.warn('Could not load logo for watermark.');
                    resolve();
                };
            });
        };

        await Promise.all([
            addWatermarks(this.visibleCtx, logoUrl), // Use original URL for visible canvas
            addWatermarks(this.svgCtx, logoDataUrl)      // Use Data URL for SVG context
        ]);
        
        const svg = this.svgCtx.getSerializedSvg();
        this.signTarget.value = 'data:image/svg+xml;base64,' + btoa(svg);
    }

    _imageToDataURL(url) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = 'Anonymous';
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.height = img.naturalHeight;
                canvas.width = img.naturalWidth;
                ctx.drawImage(img, 0, 0);
                resolve(canvas.toDataURL('image/png'));
            };
            img.onerror = () => {
                reject(new Error('Failed to load image for Data URL conversion.'));
            };
            img.src = url;
        });
    }
}
