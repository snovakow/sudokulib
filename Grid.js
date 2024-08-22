// 00 01 02|03 04 05|06 07 08
// 09 10 11|12 13 14|15 16 17
// 18 19 20|21 22 23|24 25 26
// --------|--------|--------
// 27 28 29|30 31 32|33 34 35
// 36 37 38|39 40 41|42 43 44
// 45 46 47|48 49 50|51 52 53
// --------|--------|--------
// 54 55 56|57 58 59|60 61 62
// 63 64 65|66 67 68|69 70 71
// 72 73 74|75 76 77|78 79 80

const indices = Uint8Array.of(
	0, 1, 2, 3, 4, 5, 6, 7, 8,
	9, 10, 11, 12, 13, 14, 15, 16, 17,
	18, 19, 20, 21, 22, 23, 24, 25, 26,
	27, 28, 29, 30, 31, 32, 33, 34, 35,
	36, 37, 38, 39, 40, 41, 42, 43, 44,
	45, 46, 47, 48, 49, 50, 51, 52, 53,
	54, 55, 56, 57, 58, 59, 60, 61, 62,
	63, 64, 65, 66, 67, 68, 69, 70, 71,
	72, 73, 74, 75, 76, 77, 78, 79, 80
);

const row1 = Uint8Array.of(0, 1, 2, 3, 4, 5, 6, 7, 8);
const row2 = Uint8Array.of(9, 10, 11, 12, 13, 14, 15, 16, 17);
const row3 = Uint8Array.of(18, 19, 20, 21, 22, 23, 24, 25, 26);
const row4 = Uint8Array.of(27, 28, 29, 30, 31, 32, 33, 34, 35);
const row5 = Uint8Array.of(36, 37, 38, 39, 40, 41, 42, 43, 44);
const row6 = Uint8Array.of(45, 46, 47, 48, 49, 50, 51, 52, 53);
const row7 = Uint8Array.of(54, 55, 56, 57, 58, 59, 60, 61, 62);
const row8 = Uint8Array.of(63, 64, 65, 66, 67, 68, 69, 70, 71);
const row9 = Uint8Array.of(72, 73, 74, 75, 76, 77, 78, 79, 80);

const col1 = Uint8Array.of(0, 9, 18, 27, 36, 45, 54, 63, 72);
const col2 = Uint8Array.of(1, 10, 19, 28, 37, 46, 55, 64, 73);
const col3 = Uint8Array.of(2, 11, 20, 29, 38, 47, 56, 65, 74);
const col4 = Uint8Array.of(3, 12, 21, 30, 39, 48, 57, 66, 75);
const col5 = Uint8Array.of(4, 13, 22, 31, 40, 49, 58, 67, 76);
const col6 = Uint8Array.of(5, 14, 23, 32, 41, 50, 59, 68, 77);
const col7 = Uint8Array.of(6, 15, 24, 33, 42, 51, 60, 69, 78);
const col8 = Uint8Array.of(7, 16, 25, 34, 43, 52, 61, 70, 79);
const col9 = Uint8Array.of(8, 17, 26, 35, 44, 53, 62, 71, 80);

const box1 = Uint8Array.of(0, 1, 2, 9, 10, 11, 18, 19, 20);
const box2 = Uint8Array.of(3, 4, 5, 12, 13, 14, 21, 22, 23);
const box3 = Uint8Array.of(6, 7, 8, 15, 16, 17, 24, 25, 26);
const box4 = Uint8Array.of(27, 28, 29, 36, 37, 38, 45, 46, 47);
const box5 = Uint8Array.of(30, 31, 32, 39, 40, 41, 48, 49, 50);
const box6 = Uint8Array.of(33, 34, 35, 42, 43, 44, 51, 52, 53);
const box7 = Uint8Array.of(54, 55, 56, 63, 64, 65, 72, 73, 74);
const box8 = Uint8Array.of(57, 58, 59, 66, 67, 68, 75, 76, 77);
const box9 = Uint8Array.of(60, 61, 62, 69, 70, 71, 78, 79, 80);

const groupRows = [row1, row2, row3, row4, row5, row6, row7, row8, row9];
const groupCols = [col1, col2, col3, col4, col5, col6, col7, col8, col9];
const groupBoxs = [box1, box2, box3, box4, box5, box6, box7, box8, box9];

const groupTypes = [...groupRows, ...groupCols, ...groupBoxs];

class BaseCell {
	constructor(index) {
		this.index = index;

		this.row = Math.floor(index / 9);
		this.col = index % 9;
		this.box = Math.floor(this.row / 3) * 3 + Math.floor(this.col / 3);

		const rowSet = new Set();
		const colSet = new Set();
		const boxSet = new Set();
		const groupSet = new Set();
		for (const i of groupRows[this.row]) {
			rowSet.add(i);
			groupSet.add(i);
		}
		for (const i of groupCols[this.col]) {
			colSet.add(i);
			groupSet.add(i);
		}
		for (const i of groupBoxs[this.box]) {
			boxSet.add(i);
			groupSet.add(i);
		}
		rowSet.delete(index);
		colSet.delete(index);
		boxSet.delete(index);
		groupSet.delete(index);
		this.groupRow = [...rowSet];
		this.groupCol = [...colSet];
		this.groupBox = [...boxSet];
		this.groupType = [rowSet, colSet, boxSet];
		this.group = [...groupSet];
		this.groupSet = groupSet;

		Object.freeze(this.index);
		Object.freeze(this.row);
		Object.freeze(this.col);
		Object.freeze(this.box);

		Object.freeze(this.rowGroup);
		Object.freeze(this.colGroup);
		Object.freeze(this.boxGroup);
		Object.freeze(this.groupType);
		Object.freeze(this.group);
		Object.freeze(this.groupSet);
	}
}

const baseCells = [];
for (const index of indices) {
	const cell = new BaseCell(index);

	Object.freeze(cell);
	baseCells[index] = cell;
}
Object.freeze(baseCells);

export class Cell {
	constructor(index) {
		this.index = index;
		this.symbol = 0;
	}

	fromStore(x) {
		this.symbol = parseInt(x);
	}

	toStorage() {
		return this.symbol;
	}
	fromStorage(data) {
		this.symbol = data;
	}
}

export class CellMarker extends Cell {
	constructor(index) {
		super(index);

		this.reset();
		this.show = false;

		const baseCell = baseCells[index];
		this.row = baseCell.row;
		this.col = baseCell.col;
		this.box = baseCell.box;

		this.groupRow = baseCell.groupRow;
		this.groupCol = baseCell.groupCol;
		this.groupBox = baseCell.groupBox;
		this.group = baseCell.group;
		this.groupSet = baseCell.groupSet;
	}

	toData() {
		return {
			symbol: this.symbol,
			mask: this.mask,
			size: this.size,
			remainder: this.remainder,
		};
	}
	fromData(data) {
		this.symbol = data.symbol;
		this.mask = data.mask;
		this.size = data.size;
		this.remainder = data.remainder;
	}
	toStorage() {
		return {
			symbol: this.symbol,
			mask: this.mask,
			size: this.size,
			remainder: this.remainder,
			show: this.show
		};
	}
	fromStorage(data) {
		this.symbol = data.symbol;
		this.mask = data.mask;
		this.size = data.size;
		this.remainder = data.remainder;
		this.show = data.show;
	}

	setSymbol(symbol) {
		this.symbol = symbol;
		if (symbol === 0) {
			this.mask = 0x03fe;
			this.size = 9;
			this.remainder = 45;
		} else {
			this.mask = 0x0;
			this.size = 0;
			this.remainder = 0;
		}
	}
	has(value) {
		return ((this.mask >> value) & 0x1) === 0x1;
	}
	delete(value) {
		const mask = this.mask;
		this.mask &= ~(0x1 << value);
		if (mask === this.mask) return false;
		this.size--;
		this.remainder -= value;
		return true;
	}
	clear() {
		this.mask = 0x0;
		this.size = 0;
		this.remainder = 0;
	}
	reset() {
		this.symbol = 0;
		this.mask = 0x03fe; // 0000 0011 1111 1110
		this.size = 9;
		this.remainder = 45; // 1+2+3+4+5+6+7+8+9	
	}
	add(value) {
		this.mask |= 0x1 << value;
		this.size++;
		this.remainder += value;
	}
}

class Marker {
	constructor() {
		this.clear();
	}
	clear() {
		this.mask = 0x03fe; // 0000 0011 1111 1110
		this.size = 9;
		this.remainder = 45; // 1+2+3+4+5+6+7+8+9	
	}

	has(symbol) {
		return ((this.mask >> symbol) & 0x0001) === 0x0001;
	}
	delete(symbol) {
		const mask = this.mask;
		this.mask &= ~(0x0001 << symbol);
		if (mask === this.mask) return false;
		this.size--;
		this.remainder -= symbol;
		return true;
	}
	add(symbol) {
		const mask = this.mask;
		this.mask |= 0x0001 << symbol;
		if (mask === this.mask) return false;
		this.size++;
		this.remainder += symbol;
		return true;
	}
}

class GridCell {
	static indices = indices;

	static groupRows = groupRows;
	static groupCols = groupCols;
	static groupBoxs = groupBoxs;
	static groupTypes = groupTypes;

	constructor(symbol) {
		if (symbol !== undefined) this.setSymbol(symbol);
	}
	setSymbol(symbol) {
		this.symbol = symbol;
		if (symbol === 0) {
			this.clear();
		} else {
			this.symbol = symbol;
			// this.mask = 0x0000;
			// this.size = 0;
			// this.remainder = 0;

			// this.mask = 0x0001 << symbol;
			// this.size = 1;
			// this.remainder = symbol;
		}
	}
	has(symbol) {
		return ((this.mask >> symbol) & 0x0001) === 0x0001;
	}
	delete(symbol) {
		const mask = this.mask;
		this.mask &= ~(0x0001 << symbol);
		if (mask === this.mask) return false;
		this.size--;
		this.remainder -= symbol;
		return true;
	}
	add(symbol) {
		const mask = this.mask;
		this.mask |= 0x0001 << symbol;
		if (mask === this.mask) return false;
		this.size++;
		this.remainder += symbol;
		return true;
	}
	clear() {
		this.symbol = 0;
		this.mask = 0x03fe; // 0000 0011 1111 1110
		this.size = 9;
		this.remainder = 45; // 1+2+3+4+5+6+7+8+9	
	}
}

class Grid extends Array {
	static indices = indices;

	static groupRows = groupRows;
	static groupCols = groupCols;
	static groupBoxs = groupBoxs;
	static groupTypes = groupTypes;

	constructor() {
		super(81);
	}

	string() {
		let string = "";
		for (const cell of this) string += cell.symbol;
		return string;
	}

	toStorage() {
		const data = [];
		for (const cell of this) data[cell.index] = cell.toStorage();
		return JSON.stringify(data);
	}
	fromStorage(json) {
		const data = JSON.parse(json);
		for (const cell of this) cell.fromStorage(data[cell.index]);
	}

	toData() {
		const data = [];
		for (const cell of this) data[cell.index] = cell.toData();
		return data;
	}
	fromData(data) {
		for (const cell of this) cell.fromData(data[cell.index]);
	}
	fromString(string) {
		for (let index = 0; index < 81; index++) {
			const char = string[index];
			const symbol = parseInt(char);
			const cell = this[index];
			cell.setSymbol(symbol);
		}
	}
}

export { Grid, GridCell, Marker };
