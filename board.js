import { Cell, CellCandidate, Grid } from "../sudokulib/Grid.js";
const pixAlign = (val) => {
	return Math.round(val) + 0.5;
};

const canvas = document.createElement('canvas');

const LINE_THICK = 8;
const LINE_THICK_HALF = LINE_THICK * 0.5;
const LINE_THIN = 2;

const FONT = {};
FONT.REGULAR = "REGULAR, Hauss, sans-serif";
FONT.COMIC = "COMIC, 'Comic Sans MS', 'Comic Sans', cursive";
FONT.marker = FONT.REGULAR;
FONT.default = FONT.REGULAR;
FONT.initialized = false;
Object.freeze(FONT.REGULAR);
Object.freeze(FONT.COMIC);
Object.freeze(FONT.default);

const setMarkerFont = (markerFont) => {
	if (markerFont) FONT.marker = FONT.COMIC;
	else FONT.marker = FONT.REGULAR;
};

const BOX_SIDE = 3;
const GRID_SIDE = BOX_SIDE * BOX_SIDE;

class Board {
	constructor() {
		this.canvas = canvas;

		this.cells = new Grid();
		for (let i = 0; i < 81; i++) this.cells[i] = new CellCandidate(i);

		this.startCells = new Grid();
		for (let i = 0; i < 81; i++) this.startCells[i] = new Cell(i);
	}
	setGrid(cells) {
		for (let i = 0; i < 81; i++) {
			this.startCells[i].fromStore(cells[i]);
		}
		for (const startCell of this.startCells) {
			const cell = this.cells[startCell.index];
			cell.setSymbol(startCell.symbol);
		}
		for (const cell of this.cells) {
			const startCell = this.startCells[cell.index];
			cell.setSymbol(startCell.symbol);
		}
	}
	resetGrid() {
		for (const cell of this.cells) {
			cell.setSymbol(this.startCells[cell.index].symbol);
		}
	}
	hitDetect(x, y, sizeTotal) {
		const size = sizeTotal - LINE_THICK;

		const r = Math.floor((y - LINE_THICK_HALF) / size * GRID_SIDE);
		const c = Math.floor((x - LINE_THICK_HALF) / size * GRID_SIDE);
		if (r < 0 || c < 0 || r >= GRID_SIDE || c >= GRID_SIDE) return [-1, -1];
		return [r, c];
	}
	draw(pickerVisible, selectedRow, selectedCol) {
		const ctx = canvas.getContext("2d");

		ctx.fillStyle = 'GhostWhite'; // GhostWhite White WhiteSmoke Gainsboro
		ctx.fillRect(0, 0, canvas.width, canvas.height);
		// ctx.clearRect(0, 0, canvas.width, canvas.height);

		ctx.strokeStyle = 'Black';

		const sizeTotal = canvas.width;
		const size = sizeTotal - LINE_THICK * window.devicePixelRatio;
		const boxSize = size / BOX_SIDE;
		const unitSize = size / GRID_SIDE;

		let base;
		base = LINE_THICK_HALF * window.devicePixelRatio;
		const pixBase = pixAlign(base);
		if (pickerVisible) {
			ctx.fillStyle = 'rgba(127,255,255)';
			ctx.fillRect(size * selectedCol / 9 + pixBase, pixAlign(size * selectedRow / 9) + pixBase, pixAlign(unitSize), pixAlign(unitSize));
		}

		// for (let r = 0; r < GRID_SIDE; r++) {
		// 	for (let c = 0; c < GRID_SIDE; c++) {
		// 		const cell = this.startCells[r * 9 + c];
		// 		if (cell.symbol === 0) continue;
		// 		ctx.fillStyle = 'WhiteSmoke';
		// 		ctx.fillRect(size * c / 9 + pixBase, pixAlign(size * r / 9) + pixBase, pixAlign(unitSize), pixAlign(unitSize));
		// 	}
		// }

		ctx.beginPath();
		ctx.lineWidth = LINE_THICK * window.devicePixelRatio;
		for (let i = 0; i <= BOX_SIDE; i++, base += boxSize) {
			const pix = pixAlign(base);

			ctx.moveTo(pix, 0);
			ctx.lineTo(pix, sizeTotal);

			ctx.moveTo(0, pix);
			ctx.lineTo(sizeTotal, pix);
		}
		ctx.stroke();

		ctx.beginPath();
		ctx.lineWidth = LINE_THIN * window.devicePixelRatio;
		base = LINE_THICK_HALF * window.devicePixelRatio + unitSize;
		const nextBox = unitSize * 2;
		for (let i = 0; i < BOX_SIDE; i++, base += nextBox) {
			const off = 0.5;
			let pix = Math.round(base) + off;
			ctx.moveTo(pix, 0);
			ctx.lineTo(pix, sizeTotal);

			ctx.moveTo(0, pix);
			ctx.lineTo(sizeTotal, pix);

			base += unitSize;
			pix = Math.floor(base) + off;

			ctx.moveTo(pix, 0);
			ctx.lineTo(pix, sizeTotal);

			ctx.moveTo(0, pix);
			ctx.lineTo(sizeTotal, pix);
		}
		ctx.stroke();

		if (!FONT.initialized) return;

		ctx.textAlign = 'center';
		ctx.textBaseline = 'bottom';
		ctx.fillStyle = 'Black'; // DimGray Black

		let measureClue = null;
		let measure = null;
		let measureCandidate = null;

		base = LINE_THICK_HALF * window.devicePixelRatio + unitSize * 0.5;
		let roff = base;
		for (let r = 0; r < GRID_SIDE; r++, roff += unitSize) {
			let coff = base;
			for (let c = 0; c < GRID_SIDE; c++, coff += unitSize) {
				const index = r * 9 + c;
				const cell = this.cells[index];
				if (cell.symbol === 0) {
					ctx.font = pixAlign(unitSize * 0.7 * 1 / 3) + "px " + FONT.marker;

					if (!measureCandidate) measureCandidate = ctx.measureText("0");

					for (let x = 0; x < 3; x++) {
						for (let y = 0; y < 3; y++) {
							const symbol = y * 3 + x + 1;
							if (cell.has(symbol)) {
								const spacing = 3.5;
								const xx = coff + unitSize * (x - 1) / spacing;
								const yy = roff + (measureCandidate.actualBoundingBoxAscent * 0.5 - measureCandidate.actualBoundingBoxDescent * 0.5) + unitSize * (y - 1) / spacing;
								ctx.fillText(symbol, xx, yy);
							}
						}
					}
				} else {
					const startCell = board.startCells[index];
					const font = startCell.symbol > 0 ? FONT.default : FONT.marker;
					const fontSize = pixAlign(unitSize * 0.7);
					ctx.font = fontSize + "px " + font;

					let measured = measure;
					if (startCell.symbol === 0) {
						if (!measure) measure = ctx.measureText("0");
						measured = measure;
					} else {
						if (!measureClue) measureClue = ctx.measureText("0");
						measured = measureClue;
					}

					const x = pixAlign(coff);
					const y = pixAlign(roff + (measured.actualBoundingBoxAscent * 0.5 - measured.actualBoundingBoxDescent * 0.5));
					ctx.fillText(cell.symbol, x, y);
				}
			}
		}
	}
}
const board = new Board();

const storageToCells = (data) => {
	const dataCells = data.grid;
	if (!dataCells) return null;
	for (let i = 0; i < 81; i++) {
		const dataCell = dataCells[i];
		const startCell = board.startCells[i];
		const cell = board.cells[i];
		if (dataCell.clue) {
			startCell.symbol = dataCell.symbol;
			cell.symbol = dataCell.symbol;
		} else {
			startCell.symbol = 0;
			cell.setSymbol(dataCell.symbol);
			if (dataCell.symbol === 0) cell.mask = dataCell.mask;
		}
	}
	return data.metadata;
}
const cellsToStorage = (metadata) => {
	const dataCells = [];
	for (let i = 0; i < 81; i++) {
		const startCell = board.startCells[i];
		const data = {};
		if (startCell.symbol === 0) {
			const cell = board.cells[i];
			data.clue = false;
			data.symbol = cell.symbol;
			data.mask = cell.mask;
		} else {
			data.clue = true;
			data.symbol = startCell.symbol;
			data.mask = 0x0000;
		}
		dataCells.push(data);
	}
	return {
		grid: dataCells,
		metadata
	}
}

const saveGrid = (metadata) => {
	window.name = JSON.stringify(cellsToStorage(metadata));
};
const loadGrid = () => {
	const data = window.name;
	if (!data) return null;
	let metadata = null;
	try {
		metadata = storageToCells(JSON.parse(data));
	} catch (err) {
		console.log("Window name data error", err);
	}
	return metadata;
};

export { board, FONT, loadGrid, saveGrid, setMarkerFont };
