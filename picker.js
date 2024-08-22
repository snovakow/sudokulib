const picker = document.createElement('canvas');
const pickerMarker = document.createElement('canvas');

picker.style.position = 'fixed';
picker.style.width = '192px';
picker.style.height = '192px';
picker.style.bottom = '0px';
picker.style.left = '0px';

pickerMarker.style.position = 'fixed';
pickerMarker.style.width = '192px';
pickerMarker.style.height = '192px';
pickerMarker.style.bottom = '0px';
pickerMarker.style.right = '0px';

const LINE_THIN = 2;

export const pixAlign = (val) => {
	return Math.round(val) + 0.5;
};

export const canvasDraw = (font, canvas) => {
	canvas.width = 64 * 3 * window.devicePixelRatio;
	canvas.height = 64 * 3 * window.devicePixelRatio;

	const symbols = [
		[1, 2, 3],
		[4, 5, 6],
		[7, 8, 9],
	];

	const sizeTotal = canvas.width;
	const unitSize = sizeTotal / 3;
	const inc = unitSize;

	const off = unitSize * 0.5;

	const ctx = canvas.getContext("2d");
	// ctx.clearRect(0, 0, canvas.width, canvas.height);

	ctx.clearRect(0, 0, canvas.width, canvas.height);

	ctx.lineWidth = LINE_THIN * window.devicePixelRatio;

	ctx.beginPath();
	for (let base = 0; base <= sizeTotal; base += inc) {
		const pix = pixAlign(base);
		ctx.moveTo(pix, 0);
		ctx.lineTo(pix, sizeTotal);
		// ctx.stroke();

		ctx.moveTo(0, pix);
		ctx.lineTo(sizeTotal, pix);

	}
	ctx.stroke();

	ctx.strokeStyle = 'black';
	ctx.font = font;

	ctx.textAlign = 'center';
	ctx.textBaseline = 'bottom';
	ctx.fillStyle = 'black';

	const measure = ctx.measureText("0");

	let roff = off;
	for (let x = 0; x < 3; x++, roff += inc) {
		let coff = off;
		for (let y = 0; y < 3; y++, coff += inc) {
			ctx.fillText(symbols[x][y], pixAlign(coff), pixAlign(roff + (measure.actualBoundingBoxAscent * 0.5 - measure.actualBoundingBoxDescent * 0.5)));
		}
	}
};
export const pickerDraw = (font) => {
	canvasDraw(font, picker);
	canvasDraw(font, pickerMarker);
};

export { picker, pickerMarker };
