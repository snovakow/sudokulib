import {
	generate, candidates, nakedSingles, hiddenSingles, omissions, NakedHiddenGroups, uniqueRectangle, bentWings, xWing, swordfish, jellyfish, bruteForce, phistomefel,
	REDUCE, aCells, bCells, superposition,
} from "./solver.js";

const consoleOut = (result) => {
	const lines = [];
	const phistomefelReduced = result.phistomefelReduced;
	const phistomefelFilled = result.phistomefelFilled;
	lines.push("Naked Hidden Sets: " + result.nakedHiddenSetsReduced.length);
	for (const nakedHiddenSet of result.nakedHiddenSetsReduced) {
		if (nakedHiddenSet.hidden) lines.push("    Hidden " + nakedHiddenSet.size);
		else lines.push("    Naked " + nakedHiddenSet.size);
	}
	let yWings = 0;
	let xyzWings = 0;
	for (const reduced of result.bentWingsReduced) {
		if (reduced.strategy === REDUCE.Y_Wing) yWings++;
		if (reduced.strategy === REDUCE.XYZ_Wing) xyzWings++;
	}
	lines.push("Omissions: " + result.omissions);
	lines.push("Y Wing: " + yWings);
	lines.push("XYZ Wing: " + xyzWings);
	lines.push("X Wing: " + result.xWingReduced);
	lines.push("Swordfish: " + result.swordfishReduced);
	lines.push("Jellyfish: " + result.jellyfishReduced);
	lines.push("Deadly Pattern Unique Rectangle: " + result.uniqueRectangleReduced);
	// lines.push("Phistomefel: " + phistomefelReduced + (phistomefelFilled > 0 ? " + " + phistomefelFilled + " filled" : ""));
	// if (result.superpositionReduced.length > 0) {
	// 	const once = new Set();
	// 	for (const superpositionResult of result.superpositionReduced) {
	// 		const key = superpositionResult.type + " " + superpositionResult.size;
	// 		if (once.has(key)) continue;

	// 		once.add(key);

	// 		lines.push("Superposition: " + key);
	// 	}
	// }
	lines.push("Brute Force: " + result.bruteForceFill);
	return lines;
}

const isFinished = (cells) => {
	for (let i = 0; i < 81; i++) {
		const cell = cells[i];
		if (cell.symbol === 0) return false;
	}
	return true;
}

const fillSolve = (cells, search) => {

	let nakedHiddenSetsReduced = [];
	let omissionsReduced = 0;
	let bentWingsReduced = [];
	let xWingReduced = 0;
	let swordfishReduced = 0;
	let jellyfishReduced = 0;

	let uniqueRectangleReduced = 0;
	let phistomefelReduced = 0;
	let phistomefelFilled = 0;
	let superpositionReduced = [];

	let bruteForceFill = false;

	let progress = false;
	do {
		candidates(cells);

		progress = nakedSingles(cells);
		if (progress) continue;

		progress = hiddenSingles(cells);
		if (progress) continue;

		if (search === "?candidates" || search.startsWith("?strategy=")) continue;

		const nakedHiddenResult = new NakedHiddenGroups(cells).nakedHiddenSets();
		if (nakedHiddenResult) {
			progress = true;
			nakedHiddenSetsReduced.push(nakedHiddenResult);
			continue;
		}

		progress = omissions(cells);
		if (progress) { omissionsReduced++; continue; }

		const bentWingResults = bentWings(cells);
		if (bentWingResults.length > 0) {
			progress = true;
			bentWingsReduced.push(...bentWingResults);
			continue;
		}

		progress = xWing(cells);
		if (progress) { xWingReduced++; continue; }

		progress = swordfish(cells);
		if (progress) { swordfishReduced++; continue; }

		progress = jellyfish(cells);
		if (progress) { jellyfishReduced++; continue; }

		progress = uniqueRectangle(cells);
		if (progress) { uniqueRectangleReduced++; continue; }

		if (search === "?dbphistomefel" || search === "?phistomefel") {
			const { reduced, filled } = phistomefel(cells);
			progress = reduced > 0 || filled > 0;
			if (progress) {
				if (reduced > 0) phistomefelReduced++;
				if (filled > 0) phistomefelFilled++;
				continue;
			}
		}

		if (!bruteForceFill) bruteForceFill = !isFinished(cells);

		// const superpositionResults = superposition(cells);
		// if (superpositionResults.length > 0) {
		// 	progress = true;
		// 	superpositionReduced.push(...superpositionResults);
		// 	continue;
		// }

		// if (bruteForceFill) bruteForce(cells);
	} while (progress);

	return {
		nakedHiddenSetsReduced,
		omissionsReduced,
		bentWingsReduced,
		xWingReduced,
		swordfishReduced,
		jellyfishReduced,
		uniqueRectangleReduced,
		phistomefelReduced,
		phistomefelFilled,
		superpositionReduced,
		bruteForceFill
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

let totalPuzzles = 0;

const grid = new Uint8Array(81);

function isValidCell(board, row, col, x) {
	for (let i = 0; i < 9; i++) {
		const m = 3 * Math.floor(row / 3) + Math.floor(i / 3);
		const n = 3 * Math.floor(col / 3) + i % 3;
		if (board[row * 9 + i] == x || board[i * 9 + col] == x || board[m * 9 + n] == x) {
			return false;
		}
	}
	return true;
}

const isValidGrid = (grid) => {
	let symbols = 0;
	for (let i = 0; i < 81; i++) {
		if (grid[i] !== 0) symbols++;
	}
	if (symbols < 17) return false;

	for (let row = 0; row < 9; row++) {
		for (let x = 1; x <= 9; x++) {
			let rowCount = 0;
			let colCount = 0;
			let boxCount = 0;

			for (let i = 0; i < 9; i++) {
				if (grid[row * 9 + i] === x) {
					rowCount++;
					if (rowCount === 2) return false;
				}
				if (grid[i * 9 + row] === x) {
					colCount++;
					if (colCount === 2) return false;
				}

				const m = 3 * Math.floor(row / 3) + Math.floor(i / 3);
				const n = 3 * Math.floor(row / 3) + i % 3;

				if (grid[m * 9 + n] === x) {
					boxCount++;
					if (boxCount === 2) return false;
				}
			}

		}
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
	return true;
}

const savedGrid = new Uint8Array(81);
const rndi = makeArray(81);

const sudokuGenerator = (cells, target = 0) => {
	if (target === -1) {
		for (let i = 0; i < 81; i++) grid[i] = cells[i].symbol;
	} else {
		for (let i = 0; i < 81; i++) grid[i] = 0;
		for (let i = 0; i < 9; i++) grid[i] = i + 1;
		sodokoSolver(grid);
	}

	if (!isValidGrid(grid)) {
		console.log("INVALID!");
		return;
	}

	randomize(rndi);

	if (target === 0) {
		for (let i = 0; i < 81; i++) {
			const index = rndi[i];

			const symbol = grid[index];
			if (symbol === 0) continue;
			grid[index] = 0;

			savedGrid.set(grid);

			const result = solutionCount(grid, index, symbol);
			grid.set(savedGrid);
			if (result) grid[index] = symbol;
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
	totalPuzzles++;

	for (let i = 0; i < 81; i++) {
		const cell = cells[i];
		cell.setSymbol(grid[i]);
	}

	return clueCount;
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
		for (let i = 0; i < 3; i++) group[i + 0] = triple[i] + box[0];
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

export { totalPuzzles, generateFromSeed, generateTransform };
export { sudokuGenerator, fillSolve, consoleOut };