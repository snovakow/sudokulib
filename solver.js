import { Grid, GridCell, Marker } from "./Grid.js";

let reduce_i = 0;
const REDUCE = {
	Naked_2: reduce_i++,
	Naked_3: reduce_i++,
	Naked_4: reduce_i++,
	Hidden_2: reduce_i++,
	Hidden_3: reduce_i++,
	Hidden_4: reduce_i++,
	UniqueRectangle: reduce_i++,
	X_Wing: reduce_i++,
	Y_Wing: reduce_i++,
	XYZ_Wing: reduce_i++,
	Swordfish: reduce_i++,
	Jellyfish: reduce_i++,
	Phistomefel: reduce_i++,
	Brute_Force: reduce_i++,
};

const openSingles = (grid) => {
	let marker = new Marker();
	for (const group of Grid.groupTypes) {
		let gridIndex = -1;
		marker.clear();
		for (const index of group) {
			const symbol = grid[index];
			if (symbol === 0) {
				if (gridIndex === -1) { gridIndex = index; }
				else { gridIndex = -1; break; }
			} else {
				marker.delete(symbol);
			}
		}
		if (gridIndex !== -1) {
			assert(marker.size === 1, "Invalid remainder for marker size " + marker.size);
			grid[gridIndex] = marker.remainder;
			return true;
		}
	}
	return false;
}

const candidates = (cells) => {
	for (const cell of cells) {
		const symbol = cell.symbol;
		if (symbol === 0) continue;

		for (const i of cell.group) {
			const linked = cells[i];
			if (linked.symbol === 0) linked.delete(symbol);
		}
	}
}

const loneSingles = (cells) => {
	let reduced = false;
	for (const cell of cells) {
		if (cell.symbol !== 0 || cell.size !== 1) continue;
		cell.setSymbol(cell.remainder);
		reduced = true;
	}
	return reduced;
}

const hiddenSingles = (cells) => {
	for (let x = 1; x <= 9; x++) {
		for (const group of Grid.groupTypes) {
			let symbolCell = null;
			for (const index of group) {
				const cell = cells[index];
				if (cell.symbol !== 0) continue;
				if (!cell.has(x)) continue;
				if (symbolCell === null) symbolCell = cell;
				else { symbolCell = null; break; }
			}
			if (symbolCell !== null) {
				symbolCell.setSymbol(x);
				return true;
			}
		}
	}
	return false;
}

const omissions = (cells) => {
	const groupInGroup = (x, srcGroups, srcGroupType, dstGroups, dstGroupType) => {
		let groupIndex = 0;
		for (const group of srcGroups) {
			let groupForGroup = -1;
			for (const index of group) {
				const cell = cells[index];
				if (cell.symbol !== 0) continue;
				if (!cell.has(x)) continue;

				const typeIndex = cell[srcGroupType];
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
					if (cell[dstGroupType] === groupIndex) continue;
					const had = cell.delete(x);
					if (had) reduced = true;
				}
			}

			if (reduced) return true;

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

class SetUnion {
	constructor(mask = 0x0000) {
		this.mask = mask;
	}
	has(x) {
		return ((this.mask >> x) & 0x0001) === 0x0001;
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
			if (len <= 2) continue;

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
			if (len <= 3) continue;

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
			if (len <= 4) continue;

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
	hiddenPair() {
		const union = new SetUnion();
		for (const sets of this.groupSets) {
			const len = sets.length;
			if (len <= 2) continue;

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
			if (len <= 3) continue;

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
			if (len <= 4) continue;

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
		// 9  4 4 = 8 53
		// 8  4 3 = 7 52
		// 7  4 2 = 6
		// 6  4 0 = 4 41
		// 5  3 0 = 3 41
		// 4  2 0 = 2 31
		// 3  0 0 = 0 21

		let reduced;
		reduced = this.nakedPair();
		if (reduced) return { hidden: false, size: 2, ...reduced };
		reduced = this.nakedTriple();
		if (reduced) return { hidden: false, size: 3, ...reduced };
		reduced = this.nakedQuad();
		if (reduced) return { hidden: false, size: 4, ...reduced };

		reduced = this.hiddenPair();
		if (reduced) return { hidden: true, size: 2, ...reduced };
		reduced = this.hiddenTriple();
		if (reduced) return { hidden: true, size: 3, ...reduced };
		reduced = this.hiddenQuad();
		if (reduced) return { hidden: true, size: 4, ...reduced };
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
	constructor(symbol, cells, xyz) {
		this.strategy = xyz ? REDUCE.XYZ_Wing : REDUCE.Y_Wing;
		this.symbol = symbol;
		this.cells = cells;
	}
}
const bentWings = (cells) => {
	class Pair {
		constructor(cell, s1, s2) {
			this.cell = cell;
			this.s1 = s1;
			this.s2 = s2;
		}
	}
	class Triple {
		constructor(cell, s1, s2, s3) {
			this.cell = cell;
			this.s1 = s1;
			this.s2 = s2;
			this.s3 = s3;
		}
	}

	const results = [];

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
			pairCells.push(new Pair(cell, s1, s2));
		} else {
			tripleCells.push(new Triple(cell, s1, s2, s3));
		}
	}

	const pairLen_0 = pairCells.length;
	const pairLen_1 = pairLen_0 - 1;
	const processWing = (pairs, triples = null) => {
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
					if (reduced.length > 0) {
						if (triples) results.push(new BentWingResult(common, reduced, true));
						else results.push(new BentWingResult(common, reduced, false));
					}
				}
			}
		}
	}

	processWing(pairCells);
	processWing(pairCells, tripleCells);

	for (const result of results) {
		for (const cell of result.cells) {
			cell.delete(result.symbol);
		}
	}
	return results;
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

const solve = (cells, pairs) => {
	let progress = false;
	do {
		candidates(cells);

		progress = loneSingles(cells);
		if (progress) continue;

		progress = hiddenSingles(cells);
		if (progress) continue;

		progress = omissions(cells);
		if (progress) continue;

		if (pairs) {
			const nakedHidden = new NakedHiddenGroups(cells);
			progress = nakedHidden.nakedPair();
			if (progress) continue;
		}

		// progress = nakedHidden.nakedTriple();
		// if (progress) continue;

		// progress = nakedHidden.nakedQuad();
		// if (progress) continue;

		// progress = nakedHidden.hiddenPair();
		// if (progress) continue;
		// progress = nakedHidden.hiddenTriple();
		// if (progress) continue;
		// progress = nakedHidden.hiddenQuad();
		// if (progress) continue;

		// const nakedHiddenResult = new NakedHiddenGroups(cells).nakedHiddenSets();
		// if (nakedHiddenResult) {
		// 	progress = true;
		// 	continue;
		// }

		// const bentWingResults = bentWings(cells);
		// if (bentWingResults.length > 0) {
		// 	progress = true;
		// 	continue;
		// }

		// progress = xWing(cells);
		// if (progress) { continue; }

		// progress = swordfish(cells);
		// if (progress) { continue; }

		// progress = jellyfish(cells);
		// if (progress) { continue; }

		// progress = uniqueRectangle(cells);
		// if (progress) { continue; }
	} while (progress);
};
const superposition = (cells) => {
	const startBoard = cells.toData();

	class Result {
		constructor(type, symbol, cell, size) {
			this.type = type;
			this.symbol = symbol;
			this.cell = cell;
			this.size = size;
		}
	}

	const checkCells = (type, cells, supers, size) => {
		const reduced = [];
		for (const checkCell of cells) {
			if (checkCell.symbol !== 0) continue;

			let symbolSet = new Set();
			for (const result of supers) {
				const resultCell = result[checkCell.index];
				if (resultCell.symbol === 0) {
					// if (resultCell.size === 0) {
					// 	if (size === 2) {
					// 		let super1 = supers[0];
					// 		let super2 = supers[1];
					// 		const valid = result === super1 ? super2 : super1;

					// 		for (let i = 0; i < 81; i++) {
					// 			const cell = cells[i];
					// 			if (cell.symbol !== 0) continue;
					// 			const validCell = valid[i];

					// 			let reduction = false;
					// 			if (validCell.symbol === 0) {
					// 				for (let x = 1; x <= 9; x++) {
					// 					if (cell.has(x) && ((validCell.mask >> x) & 0x0001 === 0x0000)) {
					// 						console.log("!!!1");
					// 						reduced.push(new Result(type, x, cell, size));
					// 						reduction = true;
					// 					}
					// 				}
					// 			} else {
					// 				for (let x = 1; x <= 9; x++) {
					// 					if (x !== validCell.symbol && cell.has(x)) {
					// 						reduced.push(new Result(type, x, cell, size));
					// 						reduction = true;
					// 					}
					// 				}
					// 			}

					// 			if (reduction) return reduced;
					// 		}
					// 	}
					// }
					for (let x = 1; x <= 9; x++) {
						if ((resultCell.mask >> x) & 0x0001 === 0x001) {
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

				reduced.push(new Result(type, x, checkCell, size));
			}
		}
		return reduced;
	};

	const masterMarkers2 = [];
	const masterSymbols2 = [];

	const results = [];
	const superMarkers = (targetSize, pairs) => {
		const masterMarkers = [];
		for (let index = 0; index < 81; index++) {
			const cell = cells[index];
			if (cell.symbol !== 0) continue;
			if (cell.size !== targetSize) continue;

			const supers = [];
			for (let x = 1; x <= 9; x++) {
				if (!cell.has(x)) continue;

				cell.setSymbol(x);
				solve(cells, pairs);
				const result = cells.toData();
				supers.push(result);

				cells.fromData(startBoard);
			}
			masterMarkers.push(supers);
		}
		for (const supers of masterMarkers) {
			const title = pairs ? "Cell Markers Pair" : "Cell Markers";
			const reduced = checkCells(title, cells, supers, supers.length);
			if (reduced.length > 0) results.push(...reduced);
		}
	};

	const superSymbols = (targetSize, pairs) => {
		const masterSymbols = [];
		for (let x = 1; x <= 9; x++) {
			for (const group of GridCell.groupTypes) {
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
				for (const cell of symbolCells) {
					cell.setSymbol(x);
					solve(cells, pairs);
					const result = cells.toData();
					supers.push(result);

					cells.fromData(startBoard);
				}

				masterSymbols.push(supers);
			}
		}
		for (const supers of masterSymbols) {
			const title = pairs ? "Group Symbol Pair" : "Group Symbol";
			const reduced = checkCells(title, cells, supers, targetSize);
			if (reduced.length > 0) results.push(...reduced);
		}
	}

	for (let target = 2; target <= 9; target++) {
		superMarkers(target, false);
		if (results.length > 0) break;
		superSymbols(target, false);
		if (results.length > 0) break;
		superMarkers(target, true);
		if (results.length > 0) break;
		superSymbols(target, true);
		if (results.length > 0) break;
	}
	// if (results.length === 0) tri();


	for (const result of results) {
		result.cell.delete(result.symbol);
	}

	return results;
}

export const aCells = new Set();
export const bCells = new Set();
const phistomefel = (cells) => {
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

	let reduced = false;
	let filled = false;

	for (let x = 1; x <= 9; x++) {
		let aCount = 0;
		let aMarkers = 0;
		let aFull = true;
		for (const aIndex of aCells) {
			const aCell = cells[aIndex];
			if (aCell.symbol === 0) {
				if (aCell.has(x)) {
					aFull = false;
					aMarkers++;
				}
			} else {
				if (aCell.symbol === x) aCount++;
			}
		}

		let bCount = 0;
		let bMarkers = 0;
		let bFull = true;
		for (const bIndex of bCells) {
			const bCell = cells[bIndex];
			if (bCell.symbol === 0) {
				if (bCell.has(x)) {
					bFull = false;
					bMarkers++;
				}
			} else {
				if (bCell.symbol === x) bCount++;
			}
		}

		if (aFull) {
			if (aCount === bCount && bMarkers > 0) {
				for (const bIndex of bCells) {
					const bCell = cells[bIndex];
					if (bCell.symbol !== 0) continue;
					if (bCell.delete(x)) reduced = true;
				}
			}
			if (aCount === bCount + bMarkers) {
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
			if (bCount === aCount && aMarkers > 0) {
				for (const aIndex of aCells) {
					const aCell = cells[aIndex];
					if (aCell.symbol !== 0) continue;
					if (aCell.delete(x)) reduced = true;
				}
			}
			if (bCount === aCount + aMarkers) {
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
	REDUCE, generate, candidates, loneSingles, hiddenSingles, omissions, NakedHiddenGroups, bentWings, xWing, swordfish, jellyfish,
	uniqueRectangle, superposition, phistomefel, bruteForce
};
