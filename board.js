import { Cell, CellCandidate, Grid } from "../sudokulib/Grid.js";
import * as SudokuProcess from "../sudokulib/process.js";

const pixAlign = (val) => {
	return Math.round(val) + 0.5;
};

const canvas = document.createElement('canvas');

const LINE_THICK = 8;
const LINE_THICK_HALF = LINE_THICK * 0.5;
const LINE_THIN = 2;

const FONT = "sans-serif";

const BOX_SIDE = 3;
const GRID_SIDE = BOX_SIDE * BOX_SIDE;

class Board {
	constructor() {
		this.canvas = canvas;

		this.cells = new Grid();
		for (let i = 0; i < 81; i++) this.cells[i] = new CellCandidate(i);

		this.startCells = new Grid();
		for (let i = 0; i < 81; i++) this.startCells[i] = new Cell(i);

		this.puzzleSolved = new Uint8Array(81);

		this.errorCells = new Set();

		Object.seal(this);
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
		this.errorCells.clear();
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

				if (cell.symbol > 0 && this.errorCells.has(index)) ctx.fillStyle = 'HSL(9 100% 50%)';
				else ctx.fillStyle = 'Black';

				if (cell.symbol === 0) {
					ctx.font = pixAlign(unitSize * 0.7 * 1 / 3) + "px " + FONT;

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
					const startCell = this.startCells[index];
					const fontSize = pixAlign(unitSize * 0.7);
					ctx.font = fontSize + "px " + FONT;

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

const saveGrid = (metadata, clues) => {
	const data = JSON.stringify(metadata);
	const encoded = SudokuProcess.puzzleGridBase64(board, clues);
	// window.location.hash = encoded;
	history.replaceState(null, "", "#" + encoded);
	sessionStorage.setItem("saveData", data);
};
const loadGrid = (hash, clues) => {
	if (!hash) {
		sessionStorage.removeItem("saveData");
		return { coded: false, metadata: null };
	}
	try {
		SudokuProcess.puzzleBase64Grid(board, hash, clues);
	} catch (error) {
		console.log(error);
		sessionStorage.removeItem("saveData");
		return { coded: false, metadata: null };
	}
	try {
		const data = sessionStorage.getItem("saveData");
		return { coded: true, metadata: JSON.parse(data) };
	} catch (error) {
		return { coded: true, metadata: null };
	}
};

const clearGrid = () => {
	sessionStorage.removeItem("saveData");
};

export { board, FONT, loadGrid, saveGrid, clearGrid };
