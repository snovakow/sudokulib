const picker = document.createElement('canvas');
const pickerMarker = document.createElement('canvas');

export const cellSize = 56;
export const cellsSize = cellSize * 3;

const LINE_THIN = 2;

export const pixAlign = (val) => {
	return Math.round(val) + 0.5;
};

const canvasDraw = (selected, font, marker) => {
	const canvas = marker ? pickerMarker : picker;
	canvas.width = cellSize * 3 * window.devicePixelRatio;
	canvas.height = cellSize * 3 * window.devicePixelRatio;

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

	{
		ctx.fillStyle = 'rgba(127,255,255)';
		let roff = off;
		for (let x = 0; x < 3; x++, roff += inc) {
			let coff = off;
			for (let y = 0; y < 3; y++, coff += inc) {
				if (!selected.has(x * 3 + y + 1)) continue;
				// ctx.fillText(symbols[x][y], pixAlign(coff), pixAlign(roff + (measure.actualBoundingBoxAscent * 0.5 - measure.actualBoundingBoxDescent * 0.5)));
				ctx.fillRect(unitSize * y + 0, pixAlign(unitSize * x) + 0, pixAlign(unitSize), pixAlign(unitSize));
			}
		}
	}

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

	if (!font) return;

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
			const xoff = marker ? (y - 1) * unitSize * 0.2 : 0;
			const yoff = marker ? (x - 1) * unitSize * 0.2 : 0;
			ctx.fillText(
				symbols[x][y],
				pixAlign(coff + xoff),
				pixAlign(roff + yoff + (measure.actualBoundingBoxAscent * 0.5 - measure.actualBoundingBoxDescent * 0.5))
			);
		}
	}
};
export const pickerDraw = (selected, font, fontMarker) => {
	canvasDraw(selected, font, false);
	canvasDraw(selected, fontMarker, true);
};

export { picker, pickerMarker };
