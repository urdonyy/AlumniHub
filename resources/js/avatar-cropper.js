// Reusable square avatar cropper with zoom + drag-to-pan.
//
// Usage:
//   const cropper = window.createAvatarCropper({ canvas, zoomInput, outputSize: 512 });
//   cropper.setImage(dataUrlOrObjectUrl);   // loads + centers the image
//   cropper.toBlob(blob => { ... });        // exports the visible crop
//
// The crop window is the full canvas (a square). The image is always scaled to
// "cover" the canvas at zoom = 1, and panning is clamped so no empty edges show.
window.createAvatarCropper = function ({ canvas, zoomInput, outputSize = 512 }) {
    const ctx = canvas.getContext('2d');
    const size = canvas.width; // preview canvas is square (width === height)

    let img = null;
    let zoom = 1;
    let offsetX = 0;
    let offsetY = 0;
    let dragging = false;
    let lastX = 0;
    let lastY = 0;

    function drawnSize() {
        const coverScale = size / Math.min(img.naturalWidth, img.naturalHeight);
        const scale = coverScale * zoom;
        return { drawW: img.naturalWidth * scale, drawH: img.naturalHeight * scale };
    }

    function clamp() {
        const { drawW, drawH } = drawnSize();
        offsetX = Math.min(0, Math.max(size - drawW, offsetX));
        offsetY = Math.min(0, Math.max(size - drawH, offsetY));
    }

    function render() {
        if (!img) return;
        clamp();
        const { drawW, drawH } = drawnSize();
        ctx.clearRect(0, 0, size, size);
        ctx.drawImage(img, 0, 0, img.naturalWidth, img.naturalHeight, offsetX, offsetY, drawW, drawH);
    }

    function setImage(source) {
        img = new Image();
        img.onload = () => {
            zoom = Number(zoomInput?.value || 100) / 100;
            const { drawW, drawH } = drawnSize();
            offsetX = (size - drawW) / 2;
            offsetY = (size - drawH) / 2;
            render();
        };
        img.src = source;
    }

    // Zoom anchored on the canvas center so the focal point stays put.
    zoomInput?.addEventListener('input', () => {
        if (!img) return;
        const prevZoom = zoom;
        zoom = Number(zoomInput.value) / 100;
        const center = size / 2;
        offsetX = center - (center - offsetX) * (zoom / prevZoom);
        offsetY = center - (center - offsetY) * (zoom / prevZoom);
        render();
    });

    // Drag-to-pan via pointer events (mouse + touch).
    const onDown = (e) => {
        if (!img) return;
        dragging = true;
        lastX = e.clientX;
        lastY = e.clientY;
        canvas.setPointerCapture?.(e.pointerId);
    };
    const onMove = (e) => {
        if (!dragging) return;
        offsetX += e.clientX - lastX;
        offsetY += e.clientY - lastY;
        lastX = e.clientX;
        lastY = e.clientY;
        render();
    };
    const onUp = (e) => {
        dragging = false;
        canvas.releasePointerCapture?.(e.pointerId);
    };

    canvas.style.cursor = 'grab';
    canvas.addEventListener('pointerdown', (e) => { onDown(e); canvas.style.cursor = 'grabbing'; });
    canvas.addEventListener('pointermove', onMove);
    canvas.addEventListener('pointerup', (e) => { onUp(e); canvas.style.cursor = 'grab'; });
    canvas.addEventListener('pointercancel', (e) => { onUp(e); canvas.style.cursor = 'grab'; });

    // Export the current crop at a higher resolution for crisp avatars.
    function toBlob(cb) {
        if (!img) { cb(null); return; }
        const out = document.createElement('canvas');
        out.width = out.height = outputSize;
        const octx = out.getContext('2d');
        const ratio = outputSize / size;
        const { drawW, drawH } = drawnSize();
        octx.drawImage(
            img, 0, 0, img.naturalWidth, img.naturalHeight,
            offsetX * ratio, offsetY * ratio, drawW * ratio, drawH * ratio
        );
        out.toBlob(cb, 'image/png', 0.92);
    }

    function reset() {
        zoom = 1;
        if (zoomInput) zoomInput.value = '100';
    }

    return { setImage, render, toBlob, reset, hasImage: () => !!img };
};
