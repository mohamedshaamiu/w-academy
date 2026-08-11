@props(['inputName' => 'signature_image'])

<div
    x-data="{
        drawing: false,
        hasDrawn: false,
        ctx: null,
        init() {
            const canvas = this.$refs.canvas;
            canvas.width = canvas.offsetWidth * 2;
            canvas.height = canvas.offsetHeight * 2;
            this.ctx = canvas.getContext('2d');
            this.ctx.scale(2, 2);
            this.ctx.lineWidth = 2;
            this.ctx.lineCap = 'round';
            this.ctx.strokeStyle = '#0B1F3A';
        },
        pos(e) {
            const rect = this.$refs.canvas.getBoundingClientRect();
            const point = e.touches ? e.touches[0] : e;
            return { x: point.clientX - rect.left, y: point.clientY - rect.top };
        },
        start(e) {
            this.drawing = true;
            this.hasDrawn = true;
            const p = this.pos(e);
            this.ctx.beginPath();
            this.ctx.moveTo(p.x, p.y);
        },
        move(e) {
            if (!this.drawing) return;
            const p = this.pos(e);
            this.ctx.lineTo(p.x, p.y);
            this.ctx.stroke();
        },
        end() {
            this.drawing = false;
            this.$refs.input.value = this.$refs.canvas.toDataURL('image/png');
        },
        clear() {
            this.ctx.clearRect(0, 0, this.$refs.canvas.width, this.$refs.canvas.height);
            this.hasDrawn = false;
            this.$refs.input.value = '';
        },
    }"
    class="space-y-2"
>
    <canvas
        x-ref="canvas"
        class="h-40 w-full touch-none rounded-md border border-navy-200 bg-white"
        @mousedown="start($event)"
        @mousemove="move($event)"
        @mouseup="end()"
        @mouseleave="drawing && end()"
        @touchstart.prevent="start($event)"
        @touchmove.prevent="move($event)"
        @touchend.prevent="end()"
    ></canvas>

    <input type="hidden" name="{{ $inputName }}" x-ref="input">

    <button type="button" @click="clear()" class="text-sm font-medium text-navy-400 underline hover:text-navy">
        {{ __('agreement.page.signature_pad_clear') }}
    </button>
</div>
