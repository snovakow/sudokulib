import {
	candidates, simpleHidden, simpleOmissions, simpleNaked2, simpleNaked3, simpleNaked,
	visibleOmissions, visibleNaked2, visibleNaked, hiddenSingles, NakedHiddenGroups, omissions, uniqueRectangle,
	yWing, xyzWing, xWing, swordfish, jellyfish, superposition, aCells, bCells,
	simpleHiddenValid, simpleNakedValid,
} from "./solver.js";

const consoleOut = (result) => {
	const lines = [];
	lines.push("Solved: " + (result.solved ? "Yes" : "No"));
	lines.push("Simple: " + (result.simple ? "Yes" : "No"));
	lines.push("Visual: " + (result.candidateVisible ? "Yes" : "No"));
	lines.push("Simple Hidden: " + result.hiddenSimple);
	lines.push("Simple Omission: " + result.omissionSimple);
	lines.push("Simple Naked 2: " + result.naked2Simple);
	lines.push("Simple Naked 3: " + result.naked3Simple);
	lines.push("Simple Naked: " + result.nakedSimple);

	lines.push("Visual Omission: " + result.omissionVisible);
	lines.push("Visual Naked2: " + result.naked2Visible);
	lines.push("Visual Naked: " + result.nakedVisible);

	lines.push("Naked 2: " + result.naked2);
	lines.push("Naked 3: " + result.naked3);
	lines.push("Naked 4: " + result.naked4);
	lines.push("Hidden 1: " + result.hidden1);
	lines.push("Hidden 2: " + result.hidden2);
	lines.push("Hidden 3: " + result.hidden3);
	lines.push("Hidden 4: " + result.hidden4);
	lines.push("Omissions: " + result.omissions);
	lines.push("Deadly Pattern (Unique Rectangle): " + result.uniqueRectangle);
	lines.push("Y Wing: " + result.yWing);
	lines.push("XYZ Wing: " + result.xyzWing);
	lines.push("X Wing: " + result.xWing);
	lines.push("Swordfish: " + result.swordfish);
	lines.push("Jellyfish: " + result.jellyfish);

	lines.push("Superposition Rank: " + result.superRank);
	lines.push("Superposition Size: " + result.superSize);
	lines.push("Superposition Type: " + result.superType);
	lines.push("Superposition Depth: " + result.superDepth);
	lines.push("Superposition Count: " + result.superCount);

	return lines;
}

const isFinished = (cells) => {
	for (let i = 0; i < 81; i++) {
		const cell = cells[i];
		if (cell.symbol === 0) return false;
	}
	return true;
}
const emptyCount = (cells) => {
	let count = 0;
	for (let i = 0; i < 81; i++) {
		const cell = cells[i];
		if (cell.symbol === 0) count++;
	}
	return count;
}

let VAL = 0;
const STRATEGY = {
	SIMPLE_HIDDEN: VAL++,
	SIMPLE_INTERSECTION: VAL++,
	SIMPLE_NAKED2: VAL++,
	SIMPLE_NAKED3: VAL++,
	SIMPLE_NAKED: VAL++,

	VISIBLE_INTERSECTION: VAL++,
	VISIBLE_NAKED2: VAL++,
	VISIBLE_NAKED: VAL++,

	NAKED_2: VAL++,
	NAKED_3: VAL++,
	NAKED_4: VAL++,
	HIDDEN_1: VAL++,
	HIDDEN_2: VAL++,
	HIDDEN_3: VAL++,
	HIDDEN_4: VAL++,
	INTERSECTION_REMOVAL: VAL++,
	DEADLY_PATTERN: VAL++,
	Y_WING: VAL++,
	XYZ_WING: VAL++,
	X_WING: VAL++,
	SWORDFISH: VAL++,
	JELLYFISH: VAL++,
};
Object.freeze(STRATEGY);

const fillSolve = (cells, simples, visibles, strategies, superpositions) => {
	simples = simples ?? [
		STRATEGY.SIMPLE_HIDDEN,
		STRATEGY.SIMPLE_INTERSECTION,
		STRATEGY.SIMPLE_NAKED2,
		STRATEGY.SIMPLE_NAKED3,
		STRATEGY.SIMPLE_NAKED,
	];
	visibles = visibles ?? [
		STRATEGY.VISIBLE_INTERSECTION,
		STRATEGY.VISIBLE_NAKED2,
		STRATEGY.VISIBLE_NAKED,
	];
	strategies = strategies ?? [
		STRATEGY.NAKED_2,
		STRATEGY.NAKED_3,
		STRATEGY.NAKED_4,
		STRATEGY.HIDDEN_1,
		STRATEGY.HIDDEN_2,
		STRATEGY.HIDDEN_3,
		STRATEGY.HIDDEN_4,
		STRATEGY.INTERSECTION_REMOVAL,
		STRATEGY.DEADLY_PATTERN,
		STRATEGY.Y_WING,
		STRATEGY.XYZ_WING,
		STRATEGY.X_WING,
		STRATEGY.SWORDFISH,
		STRATEGY.JELLYFISH,
	];

	let hiddenSimple = 0;
	let omissionSimple = 0;
	let naked2Simple = 0;
	let naked3Simple = 0;
	let nakedSimple = 0;

	const solveSimple = () => {
		let remaining = emptyCount(cells);
		while (remaining > 0) {
			let found = false;
			for (const simple of simples) {
				if (simple === STRATEGY.SIMPLE_HIDDEN && simpleHidden(cells)) {
					hiddenSimple++;
					found = true;
					break;
				}
				if (simple === STRATEGY.SIMPLE_INTERSECTION && simpleOmissions(cells)) {
					omissionSimple++;
					found = true;
					break;
				}
				if (simple === STRATEGY.SIMPLE_NAKED2 && simpleNaked2(cells)) {
					naked2Simple++;
					found = true;
					break;
				}
				if (simple === STRATEGY.SIMPLE_NAKED3 && simpleNaked3(cells)) {
					naked3Simple++;
					found = true;
					break;
				}
				if (simple === STRATEGY.SIMPLE_NAKED && simpleNaked(cells)) {
					nakedSimple++;
					found = true;
					break;
				}
			}
			if (found) remaining--;
			else break;
		}
		return remaining > 0;
	}

	let omissionVisible = 0;
	let naked2Visible = 0;
	let nakedVisible = 0;

	let naked2Reduced = 0;
	let naked3Reduced = 0;
	let naked4Reduced = 0;
	let hidden1Reduced = 0;
	let hidden2Reduced = 0;
	let hidden3Reduced = 0;
	let hidden4Reduced = 0;
	let omissionsReduced = 0;
	let uniqueRectangleReduced = 0;
	let yWingReduced = 0;
	let xyzWingReduced = 0;
	let xWingReduced = 0;
	let swordfishReduced = 0;
	let jellyfishReduced = 0;
	let nakedHidden = null;
	// 0 none
	// 1 reduced
	// 2 placed
	const solveVisiblePriority = (strategy) => {
		if (strategy === STRATEGY.VISIBLE_INTERSECTION && visibleOmissions(cells)) {
			omissionVisible++;
			return 1;
		}
		if (strategy === STRATEGY.VISIBLE_NAKED2 && visibleNaked2(cells)) {
			naked2Visible++;
			return 1;
		}
		if (strategy === STRATEGY.VISIBLE_NAKED && visibleNaked(cells)) {
			nakedVisible++;
			return 2;
		}
		return 0;
	}
	const solvePriority = (strategy) => {
		if (!nakedHidden) {
			if (strategy === STRATEGY.NAKED_2 ||
				strategy === STRATEGY.NAKED_3 ||
				strategy === STRATEGY.NAKED_4 ||
				strategy === STRATEGY.HIDDEN_2 ||
				strategy === STRATEGY.HIDDEN_3 ||
				strategy === STRATEGY.HIDDEN_4
			) nakedHidden = new NakedHiddenGroups(cells);
		}
		if (strategy === STRATEGY.NAKED_2 && nakedHidden.nakedPair()) {
			naked2Reduced++;
			return 1;
		}
		if (strategy === STRATEGY.NAKED_3 && nakedHidden.nakedTriple()) {
			naked3Reduced++;
			return 1;
		}
		if (strategy === STRATEGY.NAKED_4 && nakedHidden.nakedQuad()) {
			naked4Reduced++;
			return 1;
		}
		if (strategy === STRATEGY.HIDDEN_1 && hiddenSingles(cells)) {
			hidden1Reduced++;
			return 2;
		}
		if (strategy === STRATEGY.HIDDEN_2 && nakedHidden.hiddenPair()) {
			hidden2Reduced++;
			return 1;
		}
		if (strategy === STRATEGY.HIDDEN_3 && nakedHidden.hiddenTriple()) {
			hidden3Reduced++;
			return 1;
		}
		if (strategy === STRATEGY.HIDDEN_4 && nakedHidden.hiddenQuad()) {
			hidden4Reduced++;
			return 1;
		}
		if (strategy === STRATEGY.INTERSECTION_REMOVAL && omissions(cells)) {
			omissionsReduced++;
			return 1;
		}
		if (strategy === STRATEGY.DEADLY_PATTERN && uniqueRectangle(cells)) {
			uniqueRectangleReduced++;
			return 1;
		}
		if (strategy === STRATEGY.Y_WING && yWing(cells)) {
			yWingReduced++;
			return 1;
		}
		if (strategy === STRATEGY.XYZ_WING && xyzWing(cells)) {
			xyzWingReduced++;
			return 1;
		}
		if (strategy === STRATEGY.X_WING && xWing(cells)) {
			xWingReduced++;
			return 1;
		}
		if (strategy === STRATEGY.SWORDFISH && swordfish(cells)) {
			swordfishReduced++;
			return 1;
		}
		if (strategy === STRATEGY.JELLYFISH && jellyfish(cells)) {
			jellyfishReduced++;
			return 1;
		}
		return 0;
	}

	let simple = true;
	let candidateVisible = true;
	let solved = true;

	let superRank = 0;
	let superSize = 0;
	let superType = 0;
	let superDepth = 0;
	let superCount = 0;

	do {
		const remaining = solveSimple();
		if (!remaining) break;

		simple = false;
		candidates(cells);

		let progress = 0;
		do {
			for (const strategy of visibles) {
				progress = solveVisiblePriority(strategy);
				if (progress > 0) break;
			}
			if (progress === 0) {
				candidateVisible = false;

				for (const strategy of strategies) {
					progress = solvePriority(strategy);
					if (progress > 0) break;
				}
				nakedHidden = null;
			}
		} while (progress === 1);

		if (progress === 0 && superpositions) {
			const result = superposition(cells);
			if (result.rank > 0) {
				superRank = Math.max(superRank, result.rank);
				superSize = Math.max(superSize, result.size);
				superType = Math.max(superType, result.type);
				superDepth = Math.max(superDepth, result.depth);
				superCount++;
				progress = 2;
			}
		}

		if (progress === 0) solved = false;
	} while (solved);

	if (simple) candidateVisible = false;

	if (superCount > 0) solved = false;

	return {
		solved,
		simple,
		candidateVisible,
		hiddenSimple,
		omissionSimple,
		naked2Simple,
		naked3Simple,
		nakedSimple,
		omissionVisible,
		naked2Visible,
		nakedVisible,
		naked2: naked2Reduced,
		naked3: naked3Reduced,
		naked4: naked4Reduced,
		hidden1: hidden1Reduced,
		hidden2: hidden2Reduced,
		hidden3: hidden3Reduced,
		hidden4: hidden4Reduced,
		omissions: omissionsReduced,
		uniqueRectangle: uniqueRectangleReduced,
		yWing: yWingReduced,
		xyzWing: xyzWingReduced,
		xWing: xWingReduced,
		swordfish: swordfishReduced,
		jellyfish: jellyfishReduced,
		superRank,
		superSize,
		superType,
		superDepth,
		superCount,
	};
}
const makeArray = (size) => {
	const array = new Uint8Array(size);
	for (let i = 0; i < size; i++) array[i] = i;
	return array;
}
const randomize = (array, degree = 1) => {
	for (let i = array.length - 1; i > 0; i--) {
		if (degree < 1 && Math.random() < degree) continue;

		const randomi = Math.floor(Math.random() * (i + 1));
		const tmp = array[i];
		array[i] = array[randomi];
		array[randomi] = tmp;
	}
}

function isValidCell(board, row, col, x) {
	for (let i = 0; i < 9; i++) {
		if (board[row * 9 + i] === x || board[i * 9 + col] === x) return false;
		const m = 3 * Math.floor(row / 3) + Math.floor(i / 3);
		const n = 3 * Math.floor(col / 3) + i % 3;
		if (board[m * 9 + n] === x) return false;
	}
	return true;
}

const sodokoSolver = (grid) => {
	const rndx = makeArray(9);
	for (let i = 0; i < 81; i++) {
		const index = i;
		if (grid[index] === 0) {
			randomize(rndx);
			for (let x = 0; x < 9; x++) {
				const symbol = rndx[x] + 1;
				if (isValidCell(grid, Math.floor(index / 9), index % 9, symbol)) {
					grid[index] = symbol;
					if (sodokoSolver(grid)) {
						return true;
					} else {
						grid[index] = 0;
					}
				}
			}
			return false;
		}
	}
	return true;
}

const solutionCount = (grid, baseIndex, baseSymbol) => {
	/*
	for (let i = 0; i < 81; i++) {
		if (grid[i] !== 0) continue;
		const index = i;
		for (let x = 1; x <= 9; x++) {
			const symbol = x;
			if (baseIndex === index && baseSymbol === symbol) continue;
			if (isValidCell(grid, Math.floor(index / 9), index % 9, symbol)) {
				grid[index] = symbol;
				if (solutionCount(grid, baseIndex, baseSymbol)) return true;
				else grid[index] = 0;
			}
		}
		return false;
	}
	*/
	for (let index = 0; index < 81; index++) {
		if (grid[index] !== 0) continue;
		for (let x = 1; x <= 9; x++) {
			const symbol = x;
			if (baseIndex === index && baseSymbol === symbol) continue;
			if (isValidCell(grid, Math.floor(index / 9), index % 9, symbol)) {
				grid[index] = symbol;

				let results = [index];
				let progress = false;
				do {
					const [filled, remaining] = simpleNakedValid(grid, baseIndex, baseSymbol);
					if (remaining === 0) {
						return true;
					}
					if (filled.length > 0) {
						results.push(...filled);
						progress = true;
					} else {
						progress = false;
					}
				} while (progress);

				if (solutionCount(grid, baseIndex, baseSymbol)) return true;
				else for (const i of results) grid[i] = 0;
			}
		}
		return false;
	}

	return true;
}

const sudokuGenerator = (cells, target = 0) => {
	const grid = new Uint8Array(81);
	if (target === -1) {
		for (let i = 0; i < 81; i++) grid[i] = cells[i].symbol;
	} else {
		for (let i = 0; i < 81; i++) grid[i] = 0;
		for (let i = 0; i < 9; i++) grid[i] = i + 1;
		sodokoSolver(grid);
	}

	const savedGrid = new Uint8Array(81);
	const puzzleFilled = new Uint8Array(81);
	puzzleFilled.set(grid);
	const rndi = makeArray(81);
	randomize(rndi);

	if (target === 0) {
		for (let i = 0; i < 81; i++) {
			const index = rndi[i];

			const symbol = grid[index];
			if (symbol === 0) continue;
			grid[index] = 0;

			savedGrid.set(grid);
			/*
			const result = solutionCount(savedGrid, index, symbol);
			if (result) grid[index] = symbol;
			*/
			let complete = false;
			let progress = false;
			do {
				const [filled, remaining] = simpleNakedValid(savedGrid, index, symbol);
				if (remaining === 0) complete = true;
				progress = (filled.length > 0);
				if (remaining > 0) {
					const filled = simpleHiddenValid(savedGrid, index, symbol);
					if (filled.length > 0) progress = true;
					if (filled.length === remaining) complete = true;
				}
			} while (progress && !complete);
			if (complete) {
				grid[index] = symbol;
			} else {
				const result = solutionCount(savedGrid, index, symbol);
				if (result) grid[index] = symbol;
			}
		}
	} else {
		if (target === 1) {
			const edge = new Set();

			let number = Math.floor(Math.random() * 27);
			const type = Math.floor(number / 9);
			const x = number % 9;

			if (type === 0) {
				for (const cell of cells) if (cell.row === x && Math.random() > 0.1) edge.add(cell.index);
			}
			if (type === 1) {
				for (const cell of cells) if (cell.col === x && Math.random() > 0.1) edge.add(cell.index);
			}
			if (type === 2) {
				for (const cell of cells) if (cell.box === x && Math.random() > 0.1) edge.add(cell.index);
			}

			for (const index of edge) {
				const symbol = grid[index];
				if (symbol === 0) continue;
				grid[index] = 0;

				savedGrid.set(grid);

				const result = solutionCount(grid);
				grid.set(savedGrid);
				if (result !== 1) {
					grid[index] = symbol;
				}
			}

			for (let i = 0; i < 81; i++) {
				const index = rndi[i];

				if (edge.has(index)) continue;

				// const index = i;
				const symbol = grid[index];
				if (symbol === 0) continue;
				grid[index] = 0;

				savedGrid.set(grid);

				const result = solutionCount(grid);
				// console.log(result)
				grid.set(savedGrid);
				if (result !== 1) {
					grid[index] = symbol;
				}
			}
		}
		if (target === 2) {
			randomize(rndi);

			const rnd = [];
			const rnda = [];
			const rndb = [];
			for (const cell of aCells) rnda.push(cell);
			for (const cell of bCells) rndb.push(cell);

			randomize(rnda);
			randomize(rndb);

			if (Math.random() < 0.5) {
				rnd.push(...rnda);
				rnd.push(...rndb);
			} else {
				rnd.push(...rnda);
				rnd.push(...rndb);
			}

			const rndSet = new Set(rnd);

			randomize(rnd, Math.random());

			for (let i = 0; i < 81; i++) {
				const index = rndi[i];
				if (rndSet.has(index)) continue;

				const symbol = grid[index];
				if (symbol === 0) continue;
				grid[index] = 0;

				savedGrid.set(grid);

				const result = solutionCount(grid);
				grid.set(savedGrid);
				if (result !== 1) {
					grid[index] = symbol;
				}
			}

			for (const i of rnd) {
				const index = rnd[i];
				const symbol = grid[index];
				if (symbol === 0) continue;
				grid[index] = 0;

				savedGrid.set(grid);

				const result = solutionCount(grid);
				grid.set(savedGrid);
				if (result !== 1) {
					grid[index] = symbol;
				}
			}
		}
	}

	let clueCount = 0;
	for (let i = 0; i < 81; i++) {
		if (grid[i] !== 0) {
			clueCount++;
		}
	}

	for (let i = 0; i < 81; i++) {
		const cell = cells[i];
		cell.setSymbol(grid[i]);
	}

	return [clueCount, puzzleFilled];
}

const swapCell = (array, i1, i2) => {
	const tmp = array[i1];
	array[i1] = array[i2];
	array[i2] = tmp;
}
const swapRow = (array, i1, i2) => {
	if (i1 === i2) return;
	const rowi1 = i1 * 9;
	const rowi2 = i2 * 9;
	for (let i = 0; i < 9; i++) {
		swapCell(array, rowi1 + i, rowi2 + i);
	}
}
const swapCol = (array, i1, i2) => {
	if (i1 === i2) return;
	for (let i = 0; i < 9; i++) {
		const rowi = i * 9;
		swapCell(array, rowi + i1, rowi + i2);
	}
}

const generateTransform = () => {
	const triple = makeArray(3);
	const row = new Uint8Array(9);
	const col = new Uint8Array(9);
	const box = new Uint8Array(3);
	box[0] = 0;
	box[1] = 3;
	box[2] = 6;

	const swapBoxGroup = (group) => {
		randomize(box);
		randomize(triple);
		for (let i = 0; i < 3; i++) group[i] = triple[i] + box[0];
		randomize(triple);
		for (let i = 0; i < 3; i++) group[i + 3] = triple[i] + box[1];
		randomize(triple);
		for (let i = 0; i < 3; i++) group[i + 6] = triple[i] + box[2];
	}

	swapBoxGroup(row);
	swapBoxGroup(col);

	const symbols = makeArray(9);
	randomize(symbols);

	const data = {
		rotation: Math.floor(Math.random() * 4),
		reflection1: Math.floor(Math.random() * 2),
		reflection2: Math.floor(Math.random() * 2),
		reflection3: Math.floor(Math.random() * 2),
		reflection4: Math.floor(Math.random() * 2),
		row,
		col,
		symbols
	};

	return data;
}
const generateFromSeed = (puzzleString, transform) => {
	const puzzle = new Uint8Array(81);

	for (let i = 0; i < 81; i++) puzzle[i] = parseInt(puzzleString[i]);

	const tmp = new Uint8Array(81);
	const rotation = transform.rotation;
	if (rotation === 1) { // 90° cw x=y y=-x
		tmp.set(puzzle);
		for (let row = 0; row < 9; row++) {
			for (let col = 0; col < 9; col++) {
				const colInv = 8 - col;
				puzzle[row * 9 + col] = tmp[colInv * 9 + row];
			}
		}
	}
	if (rotation === 2) { // 180° x=-x y=-y
		tmp.set(puzzle);
		for (let row = 0; row < 9; row++) {
			for (let col = 0; col < 9; col++) {
				const rowInv = 8 - row;
				const colInv = 8 - col;
				puzzle[row * 9 + col] = tmp[rowInv * 9 + colInv];
			}
		}
	}
	if (rotation === 3) { // 90° ccw // x=-y y=x
		tmp.set(puzzle);
		for (let row = 0; row < 9; row++) {
			for (let col = 0; col < 9; col++) {
				const rowInv = 8 - row;
				puzzle[row * 9 + col] = tmp[col * 9 + rowInv];
			}
		}
	}

	const half = 8 / 2;
	const reflection1 = transform.reflection1; // | x=-x
	if (reflection1 === 1) {
		for (let row = 0; row < 9; row++) {
			for (let col = 0; col < half; col++) {
				const colInv = 8 - col;
				const rowi = row * 9;
				swapCell(puzzle, rowi + col, rowi + colInv);
			}
		}
	}
	const reflection2 = transform.reflection2; // - y=-y
	if (reflection2 === 1) {
		for (let row = 0; row < half; row++) {
			for (let col = 0; col < 9; col++) {
				const rowInv = 8 - row;
				swapCell(puzzle, row * 9 + col, rowInv * 9 + col);
			}
		}
	}
	const reflection3 = transform.reflection3; // \ x=y y=x
	if (reflection3 === 1) {
		for (let row = 0; row < 9; row++) {
			for (let col = 0; col < row; col++) {
				swapCell(puzzle, row * 9 + col, col * 9 + row);
			}
		}
	}
	const reflection4 = transform.reflection4; // / x=-y y=-x
	if (reflection4 === 1) {
		for (let col = 0; col < 9; col++) {
			for (let row = 0; row < col; row++) {
				const rowInv = 8 - row;
				const colInv = 8 - col;
				swapCell(puzzle, row * 9 + colInv, col * 9 + rowInv);
			}
		}
	}

	const row = transform.row;
	const col = transform.col;
	for (let i = 0; i < 9; i++) swapRow(puzzle, i, row[i]);
	for (let i = 0; i < 9; i++) swapCol(puzzle, i, col[i]);

	const symbols = transform.symbols;
	for (let i = 0; i < 81; i++) {
		const symbol = puzzle[i];
		const swap = (symbol === 0 ? 0 : symbols[symbol - 1] + 1);
		puzzle[i] = swap;
	}

	return puzzle;
};

export { generateFromSeed, generateTransform, STRATEGY };
export { sudokuGenerator, fillSolve, consoleOut };