import { Grid } from "./Grid.js";

const candidates = (cells) => {
	/*
	for (const cell of cells) {
		const symbol = cell.symbol;
		if (symbol === 0) continue;

		for (const i of cell.group) {
			const linked = cells[i];
			if (linked.symbol === 0) linked.delete(symbol);
		}
	}
	*/
	for (let index = 0; index < 81; index++) {
		const cell = cells[index];
		const symbol = cell.symbol;
		if (symbol === 0) continue;

		const row = Math.floor(index / 9);
		const col = index % 9;
		for (let i = 0; i < 9; i++) {
			const linkedRow = cells[row * 9 + i];
			if (linkedRow.symbol === 0) linkedRow.mask &= ~(0x0001 << symbol);
			const linkedCol = cells[i * 9 + col];
			if (linkedCol.symbol === 0) linkedCol.mask &= ~(0x0001 << symbol);

			const m = 3 * Math.floor(row / 3) + Math.floor(i / 3);
			const n = 3 * Math.floor(col / 3) + i % 3;
			const linkedBox = cells[m * 9 + n];
			if (linkedBox.symbol === 0) linkedBox.mask &= ~(0x0001 << symbol);
		}
	}
}

const simpleNakedCell = (cells, index) => {
	const cell = cells[index];

	if (cell.symbol !== 0) return false;
	let set = 0x0000;
	/*
	for (const i of cell.group) {
		const symbol = cells[i].symbol;
		if (symbol === 0) continue;
		set |= (0x0001 << symbol);
	}
	*/
	const row = Math.floor(index / 9);
	const col = index % 9;
	for (let i = 0; i < 9; i++) {
		const linkedRow = cells[row * 9 + i];
		if (linkedRow.symbol !== 0) set |= (0x0001 << linkedRow.symbol);
		const linkedCol = cells[i * 9 + col];
		if (linkedCol.symbol !== 0) set |= (0x0001 << linkedCol.symbol);

		const m = 3 * Math.floor(row / 3) + Math.floor(i / 3);
		const n = 3 * Math.floor(col / 3) + i % 3;
		const linkedBox = cells[m * 9 + n];
		if (linkedBox.symbol !== 0) set |= (0x0001 << linkedBox.symbol);
	}

	let remainder = 0;
	for (let x = 1; x <= 9; x++) {
		if (((set >>> x) & 0x001) === 0x000) {
			if (remainder === 0) {
				remainder = x;
			} else {
				remainder = 0;
				break;
			}
		}
	}
	if (remainder > 0) {
		// cell.setSymbol(remainder);
		cell.symbol = remainder;
		cell.mask = 0x0000;
		return true;
	}
	return false;
}

// 1-0 simple
// 1-1/8 visibleNaked

// 2-0 naked2 visual
// 2-1 naked2 visual omission
// 2-2/7 naked2

// 3-0 naked3 visual
// 3-1 naked3 visual omission3
// 3-2/6 naked3

// 4-1
// 4-2/5 naked4 true

// 5-1 hidden
// 5-2
// 5-3
// 5-4

// 6-1 hidden1 true
// 6-2 hidden2 true
// 6-3 hidden3 true

// 7-1 hidden1 true
// 7-2 hidden2 true

// 8-1 hidden1 true

// simpleHidden
// simpleOmissions
// simpleNaked

// visibleOmissions
// visibleNaked2
// visibleNaked

const simpleNaked2 = (cells) => {
	for (const group of Grid.groupBoxs) {
		let cell1 = null;
		let cell2 = null;
		for (const i of group) {
			const cell = cells[i];
			if (cell.symbol !== 0) continue;
			if (cell.size > 2) {
				cell2 = null;
				break;
			}
			if (cell1) {
				if (cell2) {
					cell2 = null;
					break;
				} else {
					cell2 = cell;
				}
			} else {
				cell1 = cell;
			}
		}
		if (!cell2) continue;

		const union = cell1.mask | cell2.mask;

		let symbol1 = 0;
		let symbol2 = 0;
		for (let x = 1; x <= 9; x++) {
			if (((union >>> x) & 0x0001) === 0x0001) {
				if (symbol1 === 0) symbol1 = x;
				else if (symbol2 === 0) symbol2 = x;
				else {
					symbol2 = 0;
					break;
				}
			}
		}

		if (symbol2 === 0) continue;

		let reduceGroup = null;
		if (cell1.row === cell2.row) {
			reduceGroup = Grid.groupRows[cell1.row];
		}
		if (cell1.col === cell2.col) {
			reduceGroup = Grid.groupCols[cell1.col];
		}
		if (!reduceGroup) return false;

		for (const i of reduceGroup) {
			if (i === cell1.index) continue;
			if (i === cell2.index) continue;
			if (simpleNakedCell(cells, i)) return true;
		}
	}

	const groups = [...Grid.groupRows, ...Grid.groupCols];
	for (const group of groups) {
		let cell1 = null;
		let cell2 = null;
		for (const i of group) {
			const cell = cells[i];
			if (cell.symbol !== 0) continue;
			if (cell.size > 2) {
				cell2 = null;
				break;
			}
			if (cell1 === null) {
				cell1 = cell;
			} else {
				if (cell2) {
					cell2 = null;
					break;
				} else {
					if (cell.box === cell1.box) cell2 = cell;
					else break;
				}
			}
		}
		if (!cell2) continue;

		const union = cell1.mask | cell2.mask;

		let symbol1 = 0;
		let symbol2 = 0;
		for (let x = 1; x <= 9; x++) {
			if (((union >>> x) & 0x0001) === 0x0001) {
				if (symbol1 === 0) symbol1 = x;
				else if (symbol2 === 0) symbol2 = x;
				else {
					symbol2 = 0;
					break;
				}
			}
		}

		if (symbol2 === 0) continue;

		const reduceGroup = Grid.groupBoxs[cell1.box];
		for (const i of reduceGroup) {
			if (i === cell1.index) continue;
			if (i === cell2.index) continue;
			if (simpleNakedCell(cells, i)) return true;
		}
	}
	return false;
}

const visibleNaked2 = (cells) => {
	for (const group of Grid.groupBoxs) {
		const cellGroup = [];
		for (const i of group) {
			const cell = cells[i];
			if (cell.symbol !== 0) continue;
			cellGroup.push(cell);
		}
		const len = cellGroup.length;
		if (len < 2) continue;

		const len_1 = len - 1;
		for (let i1 = 0; i1 < len_1; i1++) {
			const cell1 = cellGroup[i1];
			for (let i2 = i1 + 1; i2 < len; i2++) {
				const cell2 = cellGroup[i2];
				if (cell1.row !== cell2.row && cell1.col !== cell2.col) continue;

				const union = cell1.mask | cell2.mask;
				let symbol1 = 0;
				let symbol2 = 0;
				for (let x = 1; x <= 9; x++) {
					if (((union >>> x) & 0x001) === 0x001) {
						if (symbol1 === 0) symbol1 = x;
						else if (symbol2 === 0) symbol2 = x;
						else {
							symbol2 = 0;
							break;
						}
					}
				}
				if (symbol2 === 0) continue;

				let reduced = false;
				if (len > 2) {
					for (let i = 0; i < len; i++) {
						if (i === i1 || i === i2) continue;

						const cell = cellGroup[i];
						if (cell.delete(symbol1)) reduced = true;
						if (cell.delete(symbol2)) reduced = true;
					}
				}

				let crossGroup = null;
				if (cell1.row === cell2.row) crossGroup = Grid.groupRows[cell1.row];
				else if (cell1.col === cell2.col) crossGroup = Grid.groupCols[cell1.col];

				if (crossGroup) {
					for (const i of crossGroup) {
						const cell = cells[i];
						if (cell.box === cell1.box) continue;
						if (cell.delete(symbol1)) reduced = true;
						if (cell.delete(symbol2)) reduced = true;
					}
				}

				if (reduced) return true;
			}
		}
	}
	return false;
}

const simpleNaked = (cells) => {
	// for (const cell of cells) {
	// 	if (simpleNakedCell(cells, cell)) return true;
	for (let index = 0; index < 81; index++) {
		if (simpleNakedCell(cells, index)) return true;
	}
	return false;
}

const simpleNaked3 = (cells) => {
	for (const group of Grid.groupBoxs) {
		let cell1 = null;
		let cell2 = null;
		let cell3 = null;
		for (const i of group) {
			const cell = cells[i];
			if (cell.symbol !== 0) continue;
			if (cell.size > 3) {
				cell3 = null;
				break;
			}
			if (cell1) {
				if (cell2) {
					if (cell3) {
						cell3 = null;
						break;
					} else {
						cell3 = cell;
					}
				} else {
					cell2 = cell;
				}
			} else {
				cell1 = cell;
			}
		}
		if (!cell3) continue;

		const union = cell1.mask | cell2.mask | cell3.mask;

		let symbol1 = 0;
		let symbol2 = 0;
		let symbol3 = 0;
		for (let x = 1; x <= 9; x++) {
			if (((union >>> x) & 0x0001) === 0x0001) {
				if (symbol1 === 0) symbol1 = x;
				else if (symbol2 === 0) symbol2 = x;
				else if (symbol3 === 0) symbol3 = x;
				else {
					symbol3 = 0;
					break;
				}
			}
		}

		if (symbol3 === 0) continue;

		let reduceGroup = null;
		if (cell1.row === cell2.row && cell2.row === cell3.row) {
			reduceGroup = Grid.groupRows[cell1.row];
		}
		if (cell1.col === cell2.col && cell2.col === cell3.col) {
			reduceGroup = Grid.groupCols[cell1.col];
		}
		if (!reduceGroup) return false;

		for (const i of reduceGroup) {
			if (i === cell1.index) continue;
			if (i === cell2.index) continue;
			if (i === cell3.index) continue;
			if (simpleNakedCell(cells, i)) return true;
		}
	}

	const groups = [...Grid.groupRows, ...Grid.groupCols];
	for (const group of groups) {
		let cell1 = null;
		let cell2 = null;
		let cell3 = null;
		for (const i of group) {
			const cell = cells[i];
			if (cell.symbol !== 0) continue;
			if (cell.size > 3) {
				cell3 = null;
				break;
			}
			if (cell1) {
				if (cell2) {
					if (cell3) {
						cell3 = null;
						break;
					} else {
						if (cell.box === cell1.box) cell3 = cell;
						else break;
					}
				} else {
					if (cell.box === cell1.box) cell2 = cell;
					else break;
				}
			} else {
				cell1 = cell;
			}
		}
		if (!cell3) continue;

		const union = cell1.mask | cell2.mask | cell3.mask;

		let symbol1 = 0;
		let symbol2 = 0;
		let symbol3 = 0;
		for (let x = 1; x <= 9; x++) {
			if (((union >>> x) & 0x0001) === 0x0001) {
				if (symbol1 === 0) symbol1 = x;
				else if (symbol2 === 0) symbol2 = x;
				else if (symbol3 === 0) symbol3 = x;
				else {
					symbol3 = 0;
					break;
				}
			}
		}

		if (symbol3 === 0) continue;

		for (const i of Grid.groupBoxs[cell1.box]) {
			if (i === cell1.index) continue;
			if (i === cell2.index) continue;
			if (i === cell3.index) continue;
			if (simpleNakedCell(cells, i)) return true;
		}
	}
	return false;
}

const visibleNaked = (cells) => {
	// for (const cell of cells) {
	for (let index = 0; index < 81; index++) {
		const cell = cells[index];
		if (cell.symbol !== 0) continue;
		const remainder = cell.remainder;
		if (remainder === 0) continue;
		// cell.setSymbol(remainder);
		cell.symbol = remainder;
		cell.mask = 0x0000;
		return true;
	}
	return false;
}

const simpleHidden = (cells) => {
	for (let x = 1; x <= 9; x++) {
		for (const group of Grid.groupTypes) {
			let symbolCell = null;
			for (const index of group) {
				const cell = cells[index];
				if (cell.symbol !== 0) continue;

				let valid = true;

				/*
				for (const i of cell.group) {
					const symbol = cells[i].symbol;
					if (symbol === 0) continue;
					if (x === symbol) {
						valid = false;
						break;
					}
				}
				*/
				const row = Math.floor(index / 9);
				const col = index % 9;
				for (let i = 0; i < 9; i++) {
					const linkedRow = cells[row * 9 + i];
					if (x === linkedRow.symbol) { valid = false; break; }

					const linkedCol = cells[i * 9 + col];
					if (x === linkedCol.symbol) { valid = false; break; }

					const m = 3 * Math.floor(row / 3) + Math.floor(i / 3);
					const n = 3 * Math.floor(col / 3) + i % 3;
					const linkedBox = cells[m * 9 + n];
					if (x === linkedBox.symbol) { valid = false; break; }
				}

				if (!valid) continue;

				if (symbolCell === null) symbolCell = cell;
				else { symbolCell = null; break; }
			}
			if (symbolCell !== null) {
				// symbolCell.setSymbol(x);
				symbolCell.symbol = x;
				symbolCell.mask = 0x0000;
				return true;
			}
		}
	}
	return false;
}

const simpleHiddenValid = (grid, indexFill, symbolFill) => {
	const filled = [];
	for (let x = 1; x <= 9; x++) {
		for (const group of Grid.groupTypes) {
			let symbolIndex = -1;
			for (const index of group) {
				const symbol = grid[index];
				if (symbol !== 0) continue;
				let valid = true;

				const row = Math.floor(index / 9);
				const col = index % 9;
				for (let i = 0; i < 9; i++) {
					if (x === grid[row * 9 + i] || x === grid[i * 9 + col]) { valid = false; break; }
					const m = 3 * Math.floor(row / 3) + Math.floor(i / 3);
					const n = 3 * Math.floor(col / 3) + i % 3;
					if (x === grid[m * 9 + n]) { valid = false; break; }
				}

				if (!valid) continue;

				if (symbolIndex === -1) symbolIndex = index;
				else { symbolIndex = -1; break; }

			}
			if (symbolIndex !== -1) {
				if (indexFill === symbolIndex && symbolFill === x) continue;

				grid[symbolIndex] = x;
				filled.push(symbolIndex);
			}
		}
	}
	return filled;
}
const simpleNakedValid = (grid, indexFill, symbolFill) => {
	const filled = [];
	let remaining = 0;
	for (let index = 0; index < 81; index++) {
		const symbol = grid[index];
		if (symbol !== 0) continue;
		remaining++;
		let set = 0x0000;

		const row = Math.floor(index / 9);
		const col = index % 9;
		for (let i = 0; i < 9; i++) {
			const symbolRow = grid[row * 9 + i];
			if (symbolRow !== 0) set |= (0x0001 << symbolRow);
			const symbolCol = grid[i * 9 + col];
			if (symbolCol !== 0) set |= (0x0001 << symbolCol);

			const m = 3 * Math.floor(row / 3) + Math.floor(i / 3);
			const n = 3 * Math.floor(col / 3) + i % 3;
			const symbolBox = grid[m * 9 + n];
			if (symbolBox !== 0) set |= (0x0001 << symbolBox);
		}

		let remainder = 0;
		for (let x = 1; x <= 9; x++) {
			if (((set >>> x) & 0x001) === 0x000) {
				if (remainder === 0) {
					remainder = x;
				} else {
					remainder = 0;
					break;
				}
			}
		}
		if (remainder > 0) {
			if (indexFill === index && symbolFill === remainder) continue;

			grid[index] = remainder;
			filled.push(index);
			remaining--;
		}
	}

	return [filled, remaining];
}

const hiddenSingles = (cells) => {
	for (let x = 1; x <= 9; x++) {
		for (const group of Grid.groupTypes) {
			let symbolCell = null;
			for (const index of group) {
				const cell = cells[index];
				if (cell.symbol !== 0) continue;
				if ((cell.mask & (0x0001 << x)) === 0x0000) continue;
				// if (!cell.has(x)) continue;
				if (symbolCell === null) symbolCell = cell;
				else { symbolCell = null; break; }
			}
			if (symbolCell !== null) {
				// symbolCell.setSymbol(x);
				symbolCell.symbol = x;
				symbolCell.mask = 0x0000;
				return true;
			}
		}
	}
	return false;
}

const simpleHiddenSymbol = (cells, x, reduced) => {
	for (const group of Grid.groupTypes) {
		let symbolCell = null;
		for (const index of group) {
			const cell = cells[index];
			if (cell.symbol !== 0) continue;

			let valid = true;

			/*
			for (const i of cell.group) {
				const symbol = cells[i].symbol;
				if (symbol === 0) continue;
				if (x === symbol) {
					valid = false;
					break;
				}
			}
			*/
			const row = Math.floor(index / 9);
			const col = index % 9;
			for (let i = 0; i < 9; i++) {
				const linkedRow = cells[row * 9 + i];
				if (x === linkedRow.symbol) { valid = false; break; }

				const linkedCol = cells[i * 9 + col];
				if (x === linkedCol.symbol) { valid = false; break; }

				const m = 3 * Math.floor(row / 3) + Math.floor(i / 3);
				const n = 3 * Math.floor(col / 3) + i % 3;
				const linkedBox = cells[m * 9 + n];
				if (x === linkedBox.symbol) { valid = false; break; }
			}

			if (!valid) continue;

			if (reduced.has(index)) continue;

			if (symbolCell === null) symbolCell = cell;
			else { symbolCell = null; break; }
		}
		if (symbolCell !== null) {
			// symbolCell.setSymbol(x);
			symbolCell.symbol = x;
			symbolCell.mask = 0x0000;
			return true;
		}
	}
	return false;
}
// type
// 0 = simple
// 1 = visible
// 2 = candidate
const omissions = (cells, type = 2) => {
	const groupInGroup = (x, srcGroups, srcGroupType, dstGroups, dstGroupType) => {
		let groupIndex = 0;
		for (const group of srcGroups) {
			let groupForGroup = -1;
			for (const index of group) {
				const cell = cells[index];
				if (cell.symbol !== 0) continue;

				if (type === 2) {
					// if (!cell.has(x)) continue;
					if ((cell.mask & (0x0001 << x)) === 0x0000) continue;
				} else {
					let valid = true;

					/*
					for (const i of cell.group) {
						const symbol = cells[i].symbol;
						if (symbol === 0) continue;
						if (x === symbol) {
							valid = false;
							break;
						}
					}
					*/
					const row = Math.floor(index / 9);
					const col = index % 9;
					for (let i = 0; i < 9; i++) {
						const linkedRow = cells[row * 9 + i];
						if (x === linkedRow.symbol) { valid = false; break; }

						const linkedCol = cells[i * 9 + col];
						if (x === linkedCol.symbol) { valid = false; break; }

						const m = 3 * Math.floor(row / 3) + Math.floor(i / 3);
						const n = 3 * Math.floor(col / 3) + i % 3;
						const linkedBox = cells[m * 9 + n];
						if (x === linkedBox.symbol) { valid = false; break; }
					}

					if (!valid) continue;
				}

				let typeIndex = 0;
				if (srcGroupType === 'row') {
					typeIndex = Math.floor(index / 9);
				}
				if (srcGroupType === 'col') {
					typeIndex = index % 9;
				}
				if (srcGroupType === 'box') {
					const row = Math.floor(index / 9);
					const col = index % 9;
					const box = Math.floor(row / 3) * 3 + Math.floor(col / 3);
					typeIndex = box;
				}
				// const typeIndex = cell[srcGroupType];

				if (groupForGroup === -1) {
					groupForGroup = typeIndex;
				} else if (groupForGroup !== typeIndex) {
					groupForGroup = -1;
					break;
				}
			}

			let reduced = false;
			if (groupForGroup !== -1) {
				for (const index of dstGroups[groupForGroup]) {
					const cell = cells[index];
					if (cell.symbol !== 0) continue;

					let typeIndex = 0;
					if (dstGroupType === 'row') {
						typeIndex = Math.floor(index / 9);
					}
					if (dstGroupType === 'col') {
						typeIndex = index % 9;
					}
					if (dstGroupType === 'box') {
						const row = Math.floor(index / 9);
						const col = index % 9;
						const box = Math.floor(row / 3) * 3 + Math.floor(col / 3);
						typeIndex = box;
					}
					if (typeIndex === groupIndex) continue;
					// if (cell[dstGroupType] === groupIndex) continue;

					if (type === 0) {
						if (!reduced) reduced = new Set();
						reduced.add(index);
					} else {
						// const had = cell.delete(x);
						// if (had) reduced = true;
						const mask = cell.mask;
						cell.mask &= ~(0x0001 << x);
						if (mask !== cell.mask) reduced = true;
					}
				}
			}

			if (type === 0) {
				if (reduced && reduced.size > 0) {
					if (simpleHiddenSymbol(cells, x, reduced)) return true;
				}
			} else {
				if (reduced) return true;
			}

			groupIndex++;
		}
		return false;
	}
	const groupInBox = (x, groups, groupProperty) => {
		return groupInGroup(x, groups, 'box', Grid.groupBoxs, groupProperty);
	}
	const boxInGroup = (x, groups, groupProperty) => {
		return groupInGroup(x, Grid.groupBoxs, groupProperty, groups, 'box');
	}

	for (let x = 1; x <= 9; x++) {
		if (groupInBox(x, Grid.groupRows, 'row')) return true;
		if (groupInBox(x, Grid.groupCols, 'col')) return true;

		if (boxInGroup(x, Grid.groupRows, 'row')) return true;
		if (boxInGroup(x, Grid.groupCols, 'col')) return true;
	}

	return false;
}
const simpleOmissions = (cells) => {
	return omissions(cells, 0);
}
const visibleOmissions = (cells) => {
	return omissions(cells, 1);
}

class SetUnion {
	constructor(mask = 0x0000) {
		this.mask = mask;
	}
	has(x) {
		return ((this.mask >>> x) & 0x0001) === 0x0001;
	}
	set(mask) {
		this.mask = mask;
	}
	clear() {
		this.mask = 0x0000;
	}
	get size() {
		let size = 0;
		for (let x = 1; x <= 9; x++) {
			if (this.has(x)) size++;
		}
		return size;
	}
}

class NakedHiddenResult {
	constructor(size, hidden, max) {
		if (hidden) {
			this.nakedSize = max - size;
			this.hiddenSize = size;
		} else {
			this.nakedSize = size;
			this.hiddenSize = max - size;
		}
		this.size = size;
		this.hidden = hidden;
		this.max = max;
	}
}

class NakedHiddenGroups {
	constructor(cells) {
		this.groupSets = [];
		this.cells = cells;
		for (const groupType of Grid.groupTypes) {
			const sets = [];
			for (const index of groupType) {
				const cell = cells[index];
				if (cell.symbol !== 0) continue;
				sets.push(cell);
			}
			if (sets.length >= 3) this.groupSets.push(sets);
		}
	}
	nakedPair() {
		const union = new SetUnion();
		for (const sets of this.groupSets) {
			const len = sets.length;
			if (len < 2) continue;

			const len_1 = len - 1;

			for (let i1 = 0; i1 < len_1; i1++) {
				const mask1 = sets[i1].mask;
				for (let i2 = i1 + 1; i2 < len; i2++) {
					const mask2 = sets[i2].mask;

					union.set(mask1 | mask2);
					if (union.size !== 2) continue;

					let reduced = false;
					for (let i = 0; i < len; i++) {
						if (i === i1 || i === i2) continue;

						const cell = sets[i];
						for (let x = 1; x <= 9; x++) {
							if (!union.has(x)) continue;
							if (cell.delete(x)) reduced = true;
						}
					}
					if (reduced) return new NakedHiddenResult(2, false, len);
				}
			}
		}
		return null;
	}
	nakedTriple() {
		const union = new SetUnion();
		for (const sets of this.groupSets) {
			const len = sets.length;
			if (len < 3) continue;

			const len_1 = len - 1;
			const len_2 = len - 2;

			for (let i1 = 0; i1 < len_2; i1++) {
				const mask1 = sets[i1].mask;
				for (let i2 = i1 + 1; i2 < len_1; i2++) {
					const mask2 = sets[i2].mask;
					for (let i3 = i2 + 1; i3 < len; i3++) {
						const mask3 = sets[i3].mask;

						union.set(mask1 | mask2 | mask3);
						if (union.size !== 3) continue;

						let reduced = false;
						for (let i = 0; i < len; i++) {
							if (i === i1 || i === i2 || i === i3) continue;

							const cell = sets[i];
							for (let x = 1; x <= 9; x++) {
								if (!union.has(x)) continue;
								if (cell.delete(x)) reduced = true;
							}
						}
						if (reduced) return new NakedHiddenResult(3, false, len);
					}
				}
			}
		}
		return null;
	}
	nakedQuad() {
		const union = new SetUnion();
		for (const sets of this.groupSets) {
			const len = sets.length;
			if (len < 5) continue;

			const len_1 = len - 1;
			const len_2 = len - 2;
			const len_3 = len - 3;

			for (let i1 = 0; i1 < len_3; i1++) {
				const mask1 = sets[i1].mask;
				for (let i2 = i1 + 1; i2 < len_2; i2++) {
					const mask2 = sets[i2].mask;
					for (let i3 = i2 + 1; i3 < len_1; i3++) {
						const mask3 = sets[i3].mask;
						for (let i4 = i3 + 1; i4 < len; i4++) {
							const mask4 = sets[i4].mask;

							union.set(mask1 | mask2 | mask3 | mask4);
							if (union.size !== 4) continue;

							let reduced = false;
							for (let i = 0; i < len; i++) {
								if (i === i1 || i === i2 || i === i3 || i === i4) continue;

								const cell = sets[i];
								for (let x = 1; x <= 9; x++) {
									if (!union.has(x)) continue;
									if (cell.delete(x)) reduced = true;
								}
							}
							if (reduced) return new NakedHiddenResult(4, false, len);
						}
					}
				}
			}
		}
		return null;
	}
	nakedQuint() {
		const union = new SetUnion();
		for (const sets of this.groupSets) {
			const len = sets.length;
			if (len < 7) continue;

			const len_1 = len - 1;
			const len_2 = len - 2;
			const len_3 = len - 3;
			const len_4 = len - 4;

			for (let i1 = 0; i1 < len_4; i1++) {
				const mask1 = sets[i1].mask;
				for (let i2 = i1 + 1; i2 < len_3; i2++) {
					const mask2 = sets[i2].mask;
					for (let i3 = i2 + 1; i3 < len_2; i3++) {
						const mask3 = sets[i3].mask;
						for (let i4 = i3 + 1; i4 < len_1; i4++) {
							const mask4 = sets[i4].mask;
							for (let i5 = i4 + 1; i5 < len; i5++) {
								const mask5 = sets[i5].mask;

								union.set(mask1 | mask2 | mask3 | mask4 | mask5);
								if (union.size !== 5) continue;

								let reduced = false;
								for (let i = 0; i < len; i++) {
									if (i === i1 || i === i2 || i === i3 || i === i4 || i === i5) continue;

									const cell = sets[i];
									for (let x = 1; x <= 9; x++) {
										if (!union.has(x)) continue;
										if (cell.delete(x)) reduced = true;
									}
								}
								if (reduced) return new NakedHiddenResult(5, false, len);
							}
						}
					}
				}
			}
		}
		return null;
	}
	hiddenPair() {
		const union = new SetUnion();
		for (const sets of this.groupSets) {
			const len = sets.length;
			if (len < 7) continue;

			const len_1 = len - 1;

			for (let i1 = 0; i1 < len_1; i1++) {
				for (let i2 = i1 + 1; i2 < len; i2++) {
					union.clear();
					for (let i = 0; i < len; i++) {
						if (i === i1 || i === i2) continue;
						union.mask |= sets[i].mask;
					}

					if (union.size !== len - 2) continue;

					const cell1 = sets[i1];
					const cell2 = sets[i2];
					let reduced = false;
					for (let x = 1; x <= 9; x++) {
						if (!union.has(x)) continue;
						reduced = cell1.delete(x) || reduced;
						reduced = cell2.delete(x) || reduced;
					}
					if (reduced) return new NakedHiddenResult(2, true, len);
				}
			}
		}
		return null;
	}
	hiddenTriple() {
		const union = new SetUnion();
		for (const sets of this.groupSets) {
			const len = sets.length;
			if (len < 8) continue;

			const len_1 = len - 1;
			const len_2 = len - 2;

			for (let i1 = 0; i1 < len_2; i1++) {
				for (let i2 = i1 + 1; i2 < len_1; i2++) {
					for (let i3 = i2 + 1; i3 < len; i3++) {
						union.clear();
						for (let i = 0; i < len; i++) {
							if (i === i1 || i === i2 || i === i3) continue;
							union.mask |= sets[i].mask;
						}

						if (union.size !== len - 3) continue;

						const cell1 = sets[i1];
						const cell2 = sets[i2];
						const cell3 = sets[i3];
						let reduced = false;
						for (let x = 1; x <= 9; x++) {
							if (!union.has(x)) continue;
							reduced = cell1.delete(x) || reduced;
							reduced = cell2.delete(x) || reduced;
							reduced = cell3.delete(x) || reduced;
						}
						if (reduced) return new NakedHiddenResult(3, true, len);
					}
				}
			}
		}
		return null;
	}
	hiddenQuad() {
		const union = new SetUnion();
		for (const sets of this.groupSets) {
			const len = sets.length;
			if (len < 9) continue;

			const len_1 = len - 1;
			const len_2 = len - 2;
			const len_3 = len - 3;

			for (let i1 = 0; i1 < len_3; i1++) {
				for (let i2 = i1 + 1; i2 < len_2; i2++) {
					for (let i3 = i2 + 1; i3 < len_1; i3++) {
						for (let i4 = i3 + 1; i4 < len; i4++) {
							union.clear();
							for (let i = 0; i < len; i++) {
								if (i === i1 || i === i2 || i === i3 || i === i4) continue;
								union.mask |= sets[i].mask;
							}

							if (union.size !== len - 4) continue;

							const cell1 = sets[i1];
							const cell2 = sets[i2];
							const cell3 = sets[i3];
							const cell4 = sets[i4];
							let reduced = false;
							for (let x = 1; x <= 9; x++) {
								if (!union.has(x)) continue;
								reduced = cell1.delete(x) || reduced;
								reduced = cell2.delete(x) || reduced;
								reduced = cell3.delete(x) || reduced;
								reduced = cell4.delete(x) || reduced;
							}
							if (reduced) return new NakedHiddenResult(4, true, len);
						}
					}
				}
			}
		}
		return null;
	}
	nakedHiddenSets() {
		let reduced;
		reduced = this.nakedPair();
		if (reduced) return reduced;
		reduced = this.nakedTriple();
		if (reduced) return reduced;
		reduced = this.nakedQuad();
		if (reduced) return reduced;
		reduced = this.hiddenPair();
		if (reduced) return reduced;
		reduced = this.hiddenTriple();
		if (reduced) return reduced;
		reduced = this.hiddenQuad();
		if (reduced) return reduced;
		return null;
	}
}

const xWing = (cells) => {
	class GroupPair {
		constructor(x, i1, i2) {
			this.x = x;
			this.i1 = i1;
			this.i2 = i2;
		}
	}

	const xWingOrientation = (flip) => {
		for (let i = 1; i <= 9; i++) {
			const pairs = [];
			for (let x = 0; x < 9; x++) {
				let y1 = -1;
				let y2 = -1;
				for (let y = 0; y < 9; y++) {
					const index = flip ? x * 9 + y : y * 9 + x;
					const cell = cells[index];
					if (cell.symbol !== 0) continue;
					if (cell.has(i)) {
						if (y1 === -1) y1 = y;
						else if (y2 === -1) y2 = y;
						else { y2 = -1; break; }
					}
				}
				if (y2 >= 0) pairs.push(new GroupPair(x, y1, y2));
			}

			const len = pairs.length;
			for (let p1 = 0, last = len - 1; p1 < last; p1++) {
				const pair1 = pairs[p1];
				for (let p2 = p1 + 1; p2 < len; p2++) {
					const pair2 = pairs[p2];
					if (pair1.i1 === pair2.i1 && pair1.i2 === pair2.i2) {
						let reduced = false;
						for (let x = 0; x < 9; x++) {
							if (x === pair1.x || x === pair2.x) continue;

							const index1 = flip ? x * 9 + pair1.i1 : pair1.i1 * 9 + x;
							const cell1 = cells[index1];
							if (cell1.symbol === 0) {
								const had = cell1.delete(i);
								if (had) {
									reduced = true;
									// console.log("X-Wing");
								}
							}

							const index2 = flip ? x * 9 + pair1.i2 : pair1.i2 * 9 + x;
							const cell2 = cells[index2];
							if (cell2.symbol === 0) {
								const had = cell2.delete(i);
								if (had) {
									reduced = true;
									// console.log("X-Wing");
								}
							}
						}
						if (reduced) return true;
					}
				}
			}
		}
		return false;
	}

	if (xWingOrientation(true)) return true;
	if (xWingOrientation(false)) return true;

	return false;
}

const swordfish = (cells) => {
	class GroupPair {
		constructor(x, i1, i2, i3) {
			this.x = x;
			this.i1 = i1;
			this.i2 = i2;
			this.i3 = i3;
		}
	}

	const set = new Set();

	const swordfishOrientation = (flip) => {
		for (let i = 1; i <= 9; i++) {
			const pairs = [];
			for (let x = 0; x < 9; x++) {
				let y1 = -1;
				let y2 = -1;
				let y3 = -1;
				for (let y = 0; y < 9; y++) {
					const index = flip ? x * 9 + y : y * 9 + x;
					const cell = cells[index];
					if (cell.symbol !== 0) continue;
					if (cell.has(i)) {
						if (y1 === -1) y1 = y;
						else if (y2 === -1) y2 = y;
						else if (y3 === -1) y3 = y;
						else { y2 = -1; break; }
					}
				}
				if (y2 >= 0) pairs.push(new GroupPair(x, y1, y2, y3));
			}

			const len_0 = pairs.length;
			const len_1 = len_0 - 1;
			const len_2 = len_0 - 2;
			for (let p1 = 0; p1 < len_2; p1++) {
				const pair1 = pairs[p1];
				for (let p2 = p1 + 1; p2 < len_1; p2++) {
					const pair2 = pairs[p2];
					for (let p3 = p2 + 1; p3 < len_0; p3++) {
						const pair3 = pairs[p3];

						set.clear();

						set.add(pair1.i1);
						set.add(pair1.i2);
						if (pair1.i3 !== -1) set.add(pair1.i3);

						set.add(pair2.i1);
						set.add(pair2.i2);
						if (pair2.i3 !== -1) set.add(pair2.i3);

						set.add(pair3.i1);
						set.add(pair3.i2);
						if (pair3.i3 !== -1) set.add(pair3.i3);

						if (set.size !== 3) continue;

						let reduced = false;
						for (let x = 0; x < 9; x++) {
							if (x === pair1.x || x === pair2.x || x === pair3.x) continue;

							for (const pairi of [...set]) {
								const index = flip ? x * 9 + pairi : pairi * 9 + x;
								const cell = cells[index];
								if (cell.symbol === 0) {
									if (cell.delete(i)) reduced = true;
								}
							}
						}
						if (reduced) return true;
					}
				}
			}
		}
		return false;
	}
	if (swordfishOrientation(true)) return true;
	if (swordfishOrientation(false)) return true;

	return false;
}

const jellyfish = (cells) => {
	class GroupPair {
		constructor(x, i1, i2, i3, i4) {
			this.x = x;
			this.i1 = i1;
			this.i2 = i2;
			this.i3 = i3;
			this.i4 = i4;
		}
	}

	const set = new Set();

	const jellyfishOrientation = (flip) => {
		for (let i = 1; i <= 9; i++) {
			const pairs = [];
			for (let x = 0; x < 9; x++) {
				let y1 = -1;
				let y2 = -1;
				let y3 = -1;
				let y4 = -1;
				for (let y = 0; y < 9; y++) {
					const index = flip ? x * 9 + y : y * 9 + x;
					const cell = cells[index];
					if (cell.symbol !== 0) continue;
					if (cell.has(i)) {
						if (y1 === -1) y1 = y;
						else if (y2 === -1) y2 = y;
						else if (y3 === -1) y3 = y;
						else if (y4 === -1) y4 = y;
						else { y2 = -1; break; }
					}
				}
				if (y2 >= 0) pairs.push(new GroupPair(x, y1, y2, y3, y4));
			}

			const len_0 = pairs.length;
			const len_1 = len_0 - 1;
			const len_2 = len_0 - 2;
			const len_3 = len_0 - 3;
			for (let p1 = 0; p1 < len_3; p1++) {
				const pair1 = pairs[p1];
				for (let p2 = p1 + 1; p2 < len_2; p2++) {
					const pair2 = pairs[p2];
					for (let p3 = p2 + 1; p3 < len_1; p3++) {
						const pair3 = pairs[p3];
						for (let p4 = p3 + 1; p4 < len_0; p4++) {
							const pair4 = pairs[p4];

							set.clear();

							set.add(pair1.i1);
							set.add(pair1.i2);
							if (pair1.i3 !== -1) set.add(pair1.i3);
							if (pair1.i4 !== -1) set.add(pair1.i4);

							set.add(pair2.i1);
							set.add(pair2.i2);
							if (pair2.i3 !== -1) set.add(pair2.i3);
							if (pair2.i4 !== -1) set.add(pair2.i4);

							set.add(pair3.i1);
							set.add(pair3.i2);
							if (pair3.i3 !== -1) set.add(pair3.i3);
							if (pair3.i4 !== -1) set.add(pair3.i4);

							set.add(pair4.i1);
							set.add(pair4.i2);
							if (pair4.i3 !== -1) set.add(pair4.i3);
							if (pair4.i4 !== -1) set.add(pair4.i4);

							if (set.size !== 4) continue;

							let reduced = false;
							for (let x = 0; x < 9; x++) {
								if (x === pair1.x || x === pair2.x || x === pair3.x || x === pair4.x) continue;

								for (const pairi of [...set]) {
									const index = flip ? x * 9 + pairi : pairi * 9 + x;
									const cell = cells[index];
									if (cell.symbol === 0) {
										if (cell.delete(i)) reduced = true;
									}
								}
							}
							if (reduced) return true;
						}
					}
				}
			}
		}
		return false;
	}
	if (jellyfishOrientation(true)) return true;
	if (jellyfishOrientation(false)) return true;

	return false;
}

class BentWingResult {
	constructor(symbol, cells) {
		this.symbol = symbol;
		this.cells = cells;
	}
}
class BentWingPair {
	constructor(cell, s1, s2) {
		this.cell = cell;
		this.s1 = s1;
		this.s2 = s2;
	}
}
const processBentWing = (cells, pairs, triples = null) => {
	const results = [];

	const pairLen_0 = pairs.length;
	const pairLen_1 = pairLen_0 - 1;

	for (let i1 = 0; i1 < pairLen_1; i1++) {
		const pair1 = pairs[i1];
		for (let i2 = i1 + 1; i2 < pairLen_0; i2++) {
			const pair2 = pairs[i2];

			if (pair1.s1 === pair2.s1 && pair1.s2 === pair2.s2) continue; // same

			let s1 = -1;
			let s2 = -1;
			let common = -1;
			if (pair1.s1 === pair2.s1) {
				common = pair1.s1;
				s1 = pair1.s2;
				s2 = pair2.s2;
			} else if (pair1.s1 === pair2.s2) {
				common = pair1.s1;
				s1 = pair1.s2;
				s2 = pair2.s1;
			} else if (pair1.s2 === pair2.s1) {
				common = pair1.s2;
				s1 = pair1.s1;
				s2 = pair2.s2;
			} else if (pair1.s2 === pair2.s2) {
				common = pair1.s2;
				s1 = pair1.s1;
				s2 = pair2.s1;
			}

			if (common === -1) continue;

			const overlaps = new Set();
			for (const i of pair1.cell.group) {
				const cell = cells[i];
				if (cell.symbol !== 0) continue;
				if (pair2.cell.groupSet.has(i)) overlaps.add(i);
			}
			if (overlaps.size > 0) {
				const reduced = [];
				const cellGroups = triples ?? pairs;
				for (const pair of cellGroups) {
					if (triples && !pair.cell.has(common)) continue;
					if (!pair.cell.has(s1)) continue;
					if (!pair.cell.has(s2)) continue;
					if (!overlaps.has(pair.cell.index)) continue;

					if (triples) {
						for (const i of overlaps) {
							if (pair.cell.groupSet.has(i)) continue;
							overlaps.delete(i);
						}
					}
					overlaps.delete(pair.cell.index);

					for (const i of overlaps) {
						const cell = cells[i];
						if (cell.has(common)) reduced.push(cell);
					}
				}
				if (reduced.length > 0) results.push(new BentWingResult(common, reduced));
			}
		}
	}
	return results;
}
const yWing = (cells) => {
	const pairCells = [];
	for (const cell of cells) {
		if (cell.symbol !== 0) continue;
		let s1 = 0;
		let s2 = 0;
		for (let s = 1; s <= 9; s++) {
			if (!cell.has(s)) continue;
			if (s1 === 0) {
				s1 = s;
			} else if (s2 === 0) {
				s2 = s;
			} else {
				s2 = 0;
				break;
			}
		}
		if (s2 === 0) continue;
		pairCells.push(new BentWingPair(cell, s1, s2));
	}

	if (pairCells.length < 3) return false;

	const results = processBentWing(cells, pairCells);
	for (const result of results) {
		for (const cell of result.cells) cell.delete(result.symbol);
		return true;
	}
	return false;
}
const xyzWing = (cells) => {
	class Triple {
		constructor(cell, s1, s2, s3) {
			this.cell = cell;
			this.s1 = s1;
			this.s2 = s2;
			this.s3 = s3;
		}
	}

	const pairCells = [];
	const tripleCells = [];
	for (const cell of cells) {
		if (cell.symbol !== 0) continue;
		let s1 = 0;
		let s2 = 0;
		let s3 = 0;
		for (let s = 1; s <= 9; s++) {
			if (!cell.has(s)) continue;
			if (s1 === 0) {
				s1 = s;
			} else if (s2 === 0) {
				s2 = s;
			} else if (s3 === 0) {
				s3 = s;
			} else {
				s2 = 0;
				break;
			}
		}
		if (s2 === 0) continue;
		if (s3 === 0) {
			pairCells.push(new BentWingPair(cell, s1, s2));
		} else {
			tripleCells.push(new Triple(cell, s1, s2, s3));
		}
	}

	if (pairCells.length < 2 || tripleCells.length < 1) return false;

	const results = processBentWing(cells, pairCells, tripleCells);
	for (const result of results) {
		for (const cell of result.cells) cell.delete(result.symbol);
		return true;
	}
	return false;
}

// Deadly Pattern: Unique Rectangle
const uniqueRectangle = (cells) => {
	const pairs = [];
	for (let i = 0; i < 81; i++) {
		const cell = cells[i];
		if (cell.symbol !== 0) continue;
		if (cell.size !== 2) continue;
		pairs.push(cell);
	}
	for (let i = 0, leni = pairs.length - 2; i < leni; i++) {
		for (let j = i + 1, lenj = pairs.length - 1; j < lenj; j++) {
			for (let k = j + 1, lenk = pairs.length; k < lenk; k++) {
				const cell1 = pairs[i];
				const cell2 = pairs[j];
				const cell3 = pairs[k];

				if (cell1.mask !== cell2.mask || cell2.mask !== cell3.mask) continue;

				let rowCount = 1;
				if (cell2.row !== cell1.row) rowCount++;
				if (cell3.row !== cell1.row && cell3.row !== cell2.row) rowCount++;
				if (rowCount !== 2) continue;

				let colCount = 1;
				if (cell2.col !== cell1.col) colCount++;
				if (cell3.col !== cell1.col && cell3.col !== cell2.col) colCount++;
				if (colCount !== 2) continue;

				let boxCount = 1;
				if (cell2.box !== cell1.box) boxCount++;
				if (cell3.box !== cell1.box && cell3.box !== cell2.box) boxCount++;
				if (boxCount !== 2) continue;

				let row = -1;
				if (cell1.row === cell2.row) row = cell3.row;
				// if (cell1.row === cell3.row) row = cell2.row; // cells are in order so the 1st and 3rd can't be on the same row
				if (cell2.row === cell3.row) row = cell1.row;

				if (row === -1) continue;

				let col = -1;
				if (cell1.col === cell2.col) col = cell3.col;
				if (cell1.col === cell3.col) col = cell2.col;
				if (cell2.col === cell3.col) col = cell1.col;

				if (col === -1) continue;

				let reduced = false;
				const cell = cells[row * 9 + col];
				for (let x = 1; x <= 9; x++) {
					if (cell1.has(x)) {
						if (cell.delete(x)) reduced = true;
					}
				}
				return reduced;
			}
		}
	}
}

const superpositionSolve = (cells, limit = 81) => {
	let remaining = 0;
	for (let i = 0; i < 81; i++) {
		const cell = cells[i];
		if (cell.symbol === 0) remaining++;
	}

	const progress = remaining;
	while (remaining > 0 && (progress - remaining < limit)) {
		if (simpleHidden(cells)) {
			remaining--;
			continue;
		}
		if (simpleOmissions(cells)) {
			remaining--;
			continue;
		}
		if (simpleNaked(cells)) {
			remaining--;
			continue;
		}
		break;
	}

	// 0 = solved
	// 1 = incomplete
	// 2 = invalid
	let result = 0;
	if (remaining > 0) {
		result = 1;

		if (progress > remaining) {
			candidates(cells);

			for (const cell of cells) {
				if (cell.symbol === 0) {
					if (cell.size === 0) {
						result = 2;
						break;
					}
				}
			}
		}
	}
	// if(result===0) {
	// 	for (const group of Grid.groupTypes) {
	// 		let set = 0x0000;
	// 		for (const i of group) {
	// 			const cell = cells[i];
	// 			set |= 0x0001 << cell.symbol;
	// 		}
	// 		if (set !== 0x03FE) {
	// 			result = 2;
	// 			console.log("INVALID!!!");
	// 			break;
	// 		}
	// 	}	
	// }

	const depth = progress - remaining;
	return [result, depth];
};

const superposition = (cells) => {
	const startBoard = cells.toData();

	class SuperpositionResult {
		constructor(type, symbol, cell, size, depth, complete = false) {
			this.type = type; // 0 = Cell Candidates, 1 = Group Candidate
			this.symbol = symbol;
			this.cell = cell;
			this.size = size;
			this.depth = depth;
			this.complete = complete;
		}
	}

	const checkCells = (type, cells, supers, size, depth) => {
		const reduced = [];
		for (const checkCell of cells) {
			if (checkCell.symbol !== 0) continue;

			let symbolSet = new Set();
			for (const result of supers) {
				const resultCell = result[checkCell.index];
				if (resultCell.symbol === 0) {
					for (let x = 1; x <= 9; x++) {
						if (((resultCell.mask >>> x) & 0x0001) === 0x001) {
							symbolSet.add(x);
						}
					}
				} else {
					symbolSet.add(resultCell.symbol);
				}
			}
			for (let x = 1; x <= 9; x++) {
				if (!checkCell.has(x)) continue;
				if (symbolSet.has(x)) continue;

				reduced.push(new SuperpositionResult(type, x, checkCell, size, depth));
			}
		}
		return reduced;
	};

	const superCandidates = (targetSize) => {
		const results = [];
		for (let index = 0; index < 81; index++) {
			const cell = cells[index];
			if (cell.symbol !== 0) continue;
			if (cell.size !== targetSize) continue;

			const supers = [];
			let maxDepth = 0;
			for (let x = 1; x <= 9; x++) {
				if (!cell.has(x)) continue;

				cell.setSymbol(x);
				const [state, depth] = superpositionSolve(cells);
				if (state === 0) {
					return [new SuperpositionResult(0, x, cell, targetSize, depth, true)];
				}
				if (state === 1) {
					const result = cells.toData();
					supers.push(result);
					cells.fromData(startBoard);
					maxDepth = Math.max(maxDepth, depth);
				}
				if (state === 2) {
					cells.fromData(startBoard);
					results.push(new SuperpositionResult(0, x, cell, targetSize, depth));
					return results;
				}
			}
			if (supers.length === 1) {
				const result = supers[0];
				cells.fromData(result);
				return [new SuperpositionResult(0, 0, cell, targetSize, maxDepth, true)];
			}

			const reduced = checkCells(0, cells, supers, supers.length, maxDepth);
			if (reduced.length > 0) {
				results.push(...reduced);
				return results;
			}
		}
		return results;
	};

	const superSymbols = (targetSize) => {
		const results = [];
		for (let x = 1; x <= 9; x++) {
			for (const group of Grid.groupTypes) {
				const symbolCells = [];
				for (const i of group) {
					const cell = cells[i];
					if (cell.symbol === x) {
						symbolCells.length = 0;
						break;
					}
					if (cell.symbol !== 0) continue;
					if (cell.has(x)) symbolCells.push(cell);
				}

				if (symbolCells.length !== targetSize) continue;

				const supers = [];
				let maxDepth = 0;
				for (const cell of symbolCells) {
					cell.setSymbol(x);
					const [state, depth] = superpositionSolve(cells);
					if (state === 0) {
						return [new SuperpositionResult(1, x, cell, targetSize, depth, true)];
					}
					if (state === 1) {
						const result = cells.toData();
						supers.push(result);
						cells.fromData(startBoard);
						maxDepth = Math.max(maxDepth, depth);
					}
					if (state === 2) {
						cells.fromData(startBoard);
						results.push(new SuperpositionResult(1, x, cell, targetSize, depth));
						return results;
					}
				}

				if (supers.length === 1) {
					const result = supers[0];
					cells.fromData(result);
					return [new SuperpositionResult(1, x, null, targetSize, maxDepth, true)];
				}

				const reduced = checkCells(1, cells, supers, targetSize, maxDepth);
				if (reduced.length > 0) {
					results.push(...reduced);
					return results;
				}
			}
		}
		return results;
	}

	const results = [];

	for (let target = 2; target <= 9; target++) {
		results.push(...superCandidates(target));
		if (results.length > 0) break;
		results.push(...superSymbols(target));
		if (results.length > 0) break;
	}

	if (results.length === 0) {
		return {
			type: 0,
			rank: 0,
			size: 0,
			depth: 0,
		};
	}

	const result = results[0];
	if (results.length === 1 && result.complete) {
		return {
			type: result.type,
			rank: 1,
			size: result.size,
			depth: result.depth,
		};
	}

	let maxType = 0;
	let maxDepth = 0;
	let maxSize = 0;
	for (const result of results) {
		maxType = Math.max(maxType, result.type);
		maxDepth = Math.max(maxDepth, result.depth);
		maxSize = Math.max(maxSize, result.size);
		result.cell.delete(result.symbol);
	}
	return {
		type: maxType,
		rank: 2,
		size: maxSize,
		depth: maxDepth,
	};
}

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

// A symbols = B symbols
// AA.|...|.AA
// AA.|...|.AA
// ..B|BBB|B..
// ---|---|---
// ..B|...|B..
// ..B|...|B..
// ..B|...|B..
// ---|---|---
// ..B|BBB|B..
// AA.|...|.AA
// AA.|...|.AA

// A1.|...|.A2
// A1.|...|.A2
// ..5|B1B|5..
// ---|---|---
// ..B|...|B..
// ..4|...|2..
// ..B|...|B..
// ---|---|---
// ..5|B3B|5..
// A3.|...|.A4
// A3.|...|.A4

export const aCells = new Set();
export const bCells = new Set();

const a1Cells = new Set();
a1Cells.add(0);
a1Cells.add(1);
a1Cells.add(9);
a1Cells.add(10);

const a2Cells = new Set();
a2Cells.add(7);
a2Cells.add(8);
a2Cells.add(16);
a2Cells.add(17);

const a3Cells = new Set();
a3Cells.add(63);
a3Cells.add(64);
a3Cells.add(72);
a3Cells.add(73);

const a4Cells = new Set();
a4Cells.add(70);
a4Cells.add(71);
a4Cells.add(79);
a4Cells.add(80);

const b1Cells = new Set();
b1Cells.add(21);
b1Cells.add(22);
b1Cells.add(23);
const b1OuterCells = new Set();
b1OuterCells.add(20);
b1OuterCells.add(21);
b1OuterCells.add(22);
b1OuterCells.add(23);
b1OuterCells.add(24);

const b2Cells = new Set();
b2Cells.add(33);
b2Cells.add(42);
b2Cells.add(51);
const b2OuterCells = new Set();
b2OuterCells.add(24);
b2OuterCells.add(33);
b2OuterCells.add(42);
b2OuterCells.add(51);
b2OuterCells.add(60);

const b3Cells = new Set();
b3Cells.add(57);
b3Cells.add(58);
b3Cells.add(59);
const b3OuterCells = new Set();
b3OuterCells.add(56);
b3OuterCells.add(57);
b3OuterCells.add(58);
b3OuterCells.add(59);
b3OuterCells.add(60);

const b4Cells = new Set();
b4Cells.add(29);
b4Cells.add(38);
b4Cells.add(47);
const b4OuterCells = new Set();
b4OuterCells.add(20);
b4OuterCells.add(29);
b4OuterCells.add(38);
b4OuterCells.add(47);
b4OuterCells.add(56);

const b5Cells = new Set();
b5Cells.add(20);
b5Cells.add(24);
b5Cells.add(60);
b5Cells.add(56);

for (const i of a1Cells) aCells.add(i);
for (const i of a2Cells) aCells.add(i);
for (const i of a3Cells) aCells.add(i);
for (const i of a4Cells) aCells.add(i);

for (const i of b1Cells) bCells.add(i);
for (const i of b2Cells) bCells.add(i);
for (const i of b3Cells) bCells.add(i);
for (const i of b4Cells) bCells.add(i);
for (const i of b5Cells) bCells.add(i);

const phistomefel = (cells) => {
	let reduced = false;
	let filled = false;

	for (let x = 1; x <= 9; x++) {
		let aCount = 0;
		let aCandidates = 0;
		let aFull = true;
		for (const aIndex of aCells) {
			const aCell = cells[aIndex];
			if (aCell.symbol === 0) {
				if (aCell.has(x)) {
					aFull = false;
					aCandidates++;
				}
			} else {
				if (aCell.symbol === x) aCount++;
			}
		}

		let bCount = 0;
		let bCandidates = 0;
		let bFull = true;
		for (const bIndex of bCells) {
			const bCell = cells[bIndex];
			if (bCell.symbol === 0) {
				if (bCell.has(x)) {
					bFull = false;
					bCandidates++;
				}
			} else {
				if (bCell.symbol === x) bCount++;
			}
		}

		if (aFull) {
			if (aCount === bCount && bCandidates > 0) {
				for (const bIndex of bCells) {
					const bCell = cells[bIndex];
					if (bCell.symbol !== 0) continue;
					if (bCell.delete(x)) reduced = true;
				}
			}
			if (aCount === bCount + bCandidates) {
				for (const bIndex of bCells) {
					const bCell = cells[bIndex];
					if (bCell.symbol !== 0) continue;
					if (bCell.has(x)) {
						bCell.setSymbol(x);
						filled = true;
					}
				}
			}
		}
		if (bFull) {
			if (bCount === aCount && aCandidates > 0) {
				for (const aIndex of aCells) {
					const aCell = cells[aIndex];
					if (aCell.symbol !== 0) continue;
					if (aCell.delete(x)) reduced = true;
				}
			}
			if (bCount === aCount + aCandidates) {
				for (const aIndex of aCells) {
					const aCell = cells[aIndex];
					if (aCell.symbol !== 0) continue;

					if (aCell.has(x)) {
						aCell.setSymbol(x);
						filled = true;
					}
				}
			}
		}
	}

	return { reduced, filled };
}

const bruteForce = (cells) => {
	function isValid(cell, x) {
		const row = Math.floor(cell.index / 9);
		const col = cell.index % 9;
		for (let i = 0; i < 9; i++) {
			const m = 3 * Math.floor(row / 3) + Math.floor(i / 3);
			const n = 3 * Math.floor(col / 3) + i % 3;
			const rowCell = cells[row * 9 + i];
			const colCell = cells[i * 9 + col];
			const boxCell = cells[m * 9 + n];
			if (rowCell.symbol === x || colCell.symbol === x || boxCell.symbol === x) {
				return false;
			}
		}
		return true;
	}

	const makeRand = (size) => {
		const rnd = new Uint8Array(size);
		for (let i = 0; i < size; i++) rnd[i] = i;

		for (let i = 0; i < size; i++) {
			const position = Math.floor(Math.random() * size);
			if (position !== i) {
				const tmp = rnd[position];
				rnd[position] = rnd[i];
				rnd[i] = tmp;
			}
		}
		return rnd;
	}
	const rnd = makeRand(81);
	function sodokoSolver() {
		for (let index = 0; index < 81; index++) {
			const cell = cells[rnd[index]];
			if (cell.symbol === 0) {

				const rndx = makeRand(9);
				for (let x = 0; x < 9; x++) {
					const symbol = rndx[x] + 1;
					if (!cell.has(symbol)) continue;

					const state = cell.toData();

					if (isValid(cell, symbol)) {
						cell.setSymbol(symbol);
						if (sodokoSolver()) {
							return true;
						} else {
							cell.fromData(state);
							// cell.setSymbol(null);
						}
					}
				}
				return false;
			}
		}
		return true;
	}

	return sodokoSolver();
}

const indices = new Uint8Array(81);
for (let i = 0; i < 81; i++) indices[i] = i;

const generate = (cells) => {
	if (!cells) {
		for (let i = 0; i < 81; i++) {
			const position = Math.floor(Math.random() * 81);
			if (position !== i) {
				const tmp = indices[position];
				indices[position] = indices[i];
				indices[i] = tmp;
			}
		}
		return;
	}

	for (let i = 0; i < 81; i++) {
		const index = indices[i];
		const cell = cells[index];
		if (cell.symbol !== 0) continue;
		let found = -1;

		const random = [1, 2, 3, 4, 5, 6, 7, 8, 9];
		for (let i = 0; i < 9; i++) {
			const position = Math.floor(Math.random() * 9);
			if (position !== i) {
				const tmp = random[position];
				random[position] = random[i];
				random[i] = tmp;
			}
		}

		for (const x of random) {
			if (cell.has(x)) {
				if (found >= 0) {
					cell.setSymbol(x);
					return true;
				}
				found = x;
			}
		}
	}
	return false;
}

export {
	generate, candidates, simpleHidden, simpleOmissions, simpleNaked2, simpleNaked3, simpleNaked,
	visibleOmissions, visibleNaked2, visibleNaked, hiddenSingles, NakedHiddenGroups, omissions, uniqueRectangle,
	yWing, xyzWing, xWing, swordfish, jellyfish,
	superposition, phistomefel, bruteForce, simpleHiddenValid, simpleNakedValid
};
