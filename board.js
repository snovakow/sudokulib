import { Cell, CellMarker, Grid } from "../sudokulib/Grid.js";
const pixAlign = (val) => {
	return Math.round(val) + 0.5;
};

const canvas = document.createElement('canvas');

const LINE_THICK = 8;
const LINE_THICK_HALF = LINE_THICK * 0.5;
const LINE_THIN = 2;

const FONT = "Hauss,sans-serif";

const BOX_SIDE = 3;
const GRID_SIDE = BOX_SIDE * BOX_SIDE;

class Board {
	constructor() {
		this.canvas = canvas;

		this.cells = new Grid();
		for (const index of Grid.indices) this.cells[index] = new CellMarker(index);

		this.startCells = new Grid();
		for (const index of Grid.indices) this.startCells[index] = new Cell(index);
	}
	setGrid(cells) {
		for (let r = 0, index = 0; r < 9; r++) {
			const row = cells[r];
			for (let c = 0; c < 9; c++, index++) {
				this.startCells[index].fromStore(row[c]);
			}
		}
		for (const startCell of this.startCells) {
			const cell = this.cells[startCell.index];
			cell.setSymbol(startCell.symbol);
		}
		for (const cell of this.cells) {
			cell.show = false;

			const startCell = this.startCells[cell.index];
			cell.setSymbol(startCell.symbol);
		}
	}
	resetGrid() {
		for (const cell of this.cells) {
			cell.show = false;
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

		ctx.fillStyle = 'WhiteSmoke'; // GhostWhite White WhiteSmoke Gainsboro
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
			ctx.fillStyle = 'cyan';
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

		let measure = null;
		let measureMarker = null;

		base = LINE_THICK_HALF * window.devicePixelRatio + unitSize * 0.5;
		let roff = base;
		for (let r = 0; r < GRID_SIDE; r++, roff += unitSize) {
			let coff = base;
			for (let c = 0; c < GRID_SIDE; c++, coff += unitSize) {
				const index = r * 9 + c;
				const cell = this.cells[index];
				if (cell.size > 0 && cell.show) {
					ctx.font = "100 " + pixAlign(unitSize * 0.7 * 1 / 3) + "px " + FONT;
					// ctx.font = "100 " + unitSize * 0.75 * 1 / 3 + "px " + FONTS[fontCurrent];
					// ctx.font = "100 " + unitSize * 0.8 * 1 / 3 + "px " + FONTS[fontCurrent];

					if (!measureMarker) measureMarker = ctx.measureText("0");

					for (let x = 0; x < 3; x++) {
						for (let y = 0; y < 3; y++) {
							const symbol = y * 3 + x + 1;
							if (cell.has(symbol)) {
								const spacing = 3.5;
								const xx = coff + unitSize * (x - 1) / spacing;
								const yy = roff + (measureMarker.actualBoundingBoxAscent * 0.5 - measureMarker.actualBoundingBoxDescent * 0.5) + unitSize * (y - 1) / spacing;
								ctx.fillText(symbol, xx, yy);
							}
						}
					}
				} else {
					const symbol = cell.symbol;
					if (symbol === 0) continue;

					const fontSize = pixAlign(unitSize * 0.7);
					ctx.font = "100 " + fontSize + "px " + FONT;
					// ctx.font = "100 " + unitSize * 0.75 + "px " + FONTS[fontCurrent];
					// ctx.font = "100 " + unitSize * 0.8 + "px " + FONTS[fontCurrent];

					if (!measure) measure = ctx.measureText("0");

					const x = pixAlign(coff);
					const y = pixAlign(roff + (measure.actualBoundingBoxAscent * 0.5 - measure.actualBoundingBoxDescent * 0.5));
					ctx.fillText(symbol, x, y);
				}
			}
		}
	}
}
const board = new Board();

const storageToCells = (data) => {
	const dataCells = data.split(",");
	for (let i = 0; i < 81; i++) {
		const dataCell = dataCells[i];
		const startCell = board.startCells[i];
		const cell = board.cells[i];
		const type = dataCell[0];
		const value = dataCell.substring(1);
		if (type == "0") {
			startCell.symbol = parseInt(value);
			cell.setSymbol(parseInt(value));
		}
		if (type == "1") {
			startCell.symbol = 0;
			cell.setSymbol(parseInt(value));
		}
		if (type == "2") {
			startCell.symbol = 0;
			cell.setSymbol(0);
			const dataMarkers = value.split("");
			for (let x = 0; x < 9; x++) {
				const dataMark = dataMarkers[x];
				if (dataMark == "0") cell.delete(x + 1);
			}
		}
	}
}
const cellsToStorage = () => {
	const dataCells = [];
	for (let i = 0; i < 81; i++) {
		const startCell = board.startCells[i];
		let data = [];
		if (startCell.symbol === 0) {
			const cell = board.cells[i];
			if (cell.symbol === 0) {
				data += "2";
				for (let x = 1; x <= 9; x++) {
					if (cell.has(x)) {
						data += "1";
					} else {
						data += "0";
					}
				}
			} else {
				data += "1" + cell.symbol;
			}
		} else {
			data += "0" + startCell.symbol;
		}
		dataCells.push(data);
	}
	return dataCells.join(",");
}

const saveGrid = () => {
	window.name = cellsToStorage();
};
const loadGrid = () => {
	if (window.name) storageToCells(window.name);
};

export { board, FONT, loadGrid, saveGrid };
