import { CellCandidate, Grid } from "../sudokulib/Grid.js";
import { sudokuGenerator, fillSolve, totalPuzzles, STRATEGY } from "../sudokulib/generator.js";

const cells = new Grid();
for (let i = 0; i < 81; i++) cells[i] = new CellCandidate(i);

let simples = 0;

let candidates = 0;

let setNaked2 = 0;
let setNaked3 = 0;
let setNaked4 = 0;
let setHidden2 = 0;
let setHidden3 = 0;
let setHidden4 = 0;
let omissionsReduced = 0;
let yWingReduced = 0;
let xyzWingReduced = 0;
let xWingReduced = 0;
let swordfishReduced = 0;
let jellyfishReduced = 0;
let uniqueRectangleReduced = 0;
let bruteForceFill = 0;

let maxTime = 0;
let totalTime = 0;

let puzzleString = null;
let puzzleStrings = null;

const clueCounter = new Map();

let stepMode = 0; // 1=row 2=phist
const step = () => {
	const time = performance.now();

	let mode = stepMode;
	if (puzzleString) {
		cells.fromString(puzzleString);
		mode = -1;
	}

	let id = 0;
	if (puzzleStrings) {
		const puzzleData = puzzleStrings.shift();
		if (!puzzleData) return false;
		const puzzle = puzzleData.puzzleClues;
		if (!puzzle) return false;
		id = puzzleData.id;
		cells.fromString(puzzle);
		mode = -1;
	}
	const [clueCount, puzzleFilled] = sudokuGenerator(cells, mode);

	const clueValue = clueCounter.get(clueCount);
	if (clueValue) {
		clueCounter.set(clueCount, clueValue + 1)
	} else {
		clueCounter.set(clueCount, 1)
	}

	const data = {
		id,
		puzzle: cells.string(),
		totalPuzzles: totalPuzzles,
		cells: cells.toData(),
		message: null
	};

	for (const cell of cells) if (cell.symbol === 0) cell.fill();
	const save = cells.toData();

	const result = fillSolve(cells, STRATEGY.ALL);

	data.puzzleClues = data.puzzle;
	data.puzzleFilled = puzzleFilled.join('');
	data.clueCount = clueCount;

	data.simple = 0;
	data.naked2 = 0;
	data.naked3 = 0;
	data.naked4 = 0;
	data.hidden2 = 0;
	data.hidden3 = 0;
	data.hidden4 = 0;
	data.omissions = 0;
	data.yWing = 0;
	data.xyzWing = 0;
	data.xWing = 0;
	data.swordfish = 0;
	data.jellyfish = 0;
	data.uniqueRectangle = 0;
	data.bruteForce = 0;

	if (result.bruteForceFill) {
		data.bruteForce = 1;
		bruteForceFill++;
	}

	let simple = true;
	simple &&= result.naked2Reduced === 0;
	simple &&= result.naked3Reduced === 0;
	simple &&= result.naked4Reduced === 0;
	simple &&= result.hidden2Reduced === 0;
	simple &&= result.hidden3Reduced === 0;
	simple &&= result.hidden4Reduced === 0;
	simple &&= result.omissionsReduced === 0;
	simple &&= result.yWingReduced === 0;
	simple &&= result.xyzWingReduced === 0;
	simple &&= result.xWingReduced === 0;
	simple &&= result.swordfishReduced === 0;
	simple &&= result.jellyfishReduced === 0;
	simple &&= result.uniqueRectangleReduced === 0;
	simple &&= !result.bruteForceFill;

	data.simple = simple ? 1 : 0;

	data.has_naked2 = 0;
	data.has_naked3 = 0;
	data.has_naked4 = 0;
	data.has_hidden2 = 0;
	data.has_hidden3 = 0;
	data.has_hidden4 = 0;
	data.has_omissions = 0;
	data.has_uniqueRectangle = 0;
	data.has_yWing = 0;
	data.has_xyzWing = 0;
	data.has_xWing = 0;
	data.has_swordfish = 0;
	data.has_jellyfish = 0;

	if (simple) simples++;
	else {
		const processStrategy = (resultProperty, dataProperty, strategy) => {
			if (result[resultProperty] === 0) return;

			cells.fromData(save);
			const strategyResult = fillSolve(cells, strategy, false);
			const strategyResultValue = strategyResult[resultProperty];

			result[resultProperty] = strategyResultValue;
			if (strategyResultValue > 0) {
				cells.fromData(save);
				const resultIsolated = fillSolve(cells, strategy, true);

				if (!resultIsolated.bruteForceFill) {
					const isolatedValue = resultIsolated[resultProperty];
					if (isolatedValue <= strategyResultValue) data[dataProperty] = isolatedValue;
				}
			}
		}
		const processSets = () => {
			const strategies = ['naked2Reduced', 'naked3Reduced', 'naked4Reduced', 'hidden2Reduced', 'hidden3Reduced', 'hidden4Reduced'];
			const hasMap = ['has_naked2', 'has_naked3', 'has_naked4', 'has_hidden2', 'has_hidden3', 'has_hidden4'];
			const strategType = [STRATEGY.NAKED_2, STRATEGY.NAKED_3, STRATEGY.NAKED_4, STRATEGY.HIDDEN_2, STRATEGY.HIDDEN_3, STRATEGY.HIDDEN_4];
			const maxSetIndex = (result) => {
				let i = strategies.length - 1;
				do {
					if (result[strategies[i]] > 0) return i;
					i--;
				} while (i >= 0);
				return i;
			}

			if (maxSetIndex(result) < 0) return;

			cells.fromData(save);
			const strategyResult = fillSolve(cells, STRATEGY.NAKED_HIDDEN, false);

			for (const strategy of strategies) result[strategy] = strategyResult[strategy];

			const maxStrategy = maxSetIndex(result);
			if (maxStrategy < 0) return;

			cells.fromData(save);
			const resultIsolated = fillSolve(cells, strategType[maxStrategy], true);
			if (resultIsolated.bruteForceFill) return;

			const maxIsolated = maxSetIndex(resultIsolated);
			for (const i in strategies) {
				const strategy = strategies[i];
				const isolatedValue = resultIsolated[strategy];
				if (isolatedValue > 0) {
					if (i < maxIsolated) break;
					if (isolatedValue <= strategyResult[strategy]) data[hasMap[i]] = isolatedValue;
				}
			}
		}
		processSets();
		processStrategy('omissionsReduced', 'has_omissions', STRATEGY.INTERSECTION_REMOVAL);
		processStrategy('uniqueRectangleReduced', 'has_uniqueRectangle', STRATEGY.DEADLY_PATTERN);
		processStrategy('yWingReduced', 'has_yWing', STRATEGY.Y_WING);
		processStrategy('xyzWingReduced', 'has_xyzWing', STRATEGY.XYZ_WING);
		processStrategy('xWingReduced', 'has_xWing', STRATEGY.X_WING);
		processStrategy('swordfishReduced', 'has_swordfish', STRATEGY.SWORDFISH);
		processStrategy('jellyfishReduced', 'has_jellyfish', STRATEGY.JELLYFISH);

		setNaked2 += result.naked2Reduced;
		setNaked3 += result.naked3Reduced;
		setNaked4 += result.naked4Reduced;
		setHidden2 += result.hidden2Reduced;
		setHidden3 += result.hidden3Reduced;
		setHidden4 += result.hidden4Reduced;
		omissionsReduced += result.omissionsReduced;
		yWingReduced += result.yWingReduced;
		xyzWingReduced += result.xyzWingReduced;
		xWingReduced += result.xWingReduced;
		swordfishReduced += result.swordfishReduced;
		jellyfishReduced += result.jellyfishReduced;
		uniqueRectangleReduced += result.uniqueRectangleReduced;

		data.naked2 += result.naked2Reduced;
		data.naked3 += result.naked3Reduced;
		data.naked4 += result.naked4Reduced;
		data.hidden2 += result.hidden2Reduced;
		data.hidden3 += result.hidden3Reduced;
		data.hidden4 += result.hidden4Reduced;
		data.omissions += result.omissionsReduced;
		data.yWing += result.yWingReduced;
		data.xyzWing += result.xyzWingReduced;
		data.xWing += result.xWingReduced;
		data.swordfish += result.swordfishReduced;
		data.jellyfish += result.jellyfishReduced;
		data.uniqueRectangle += result.uniqueRectangleReduced;

		// if (result.superpositionReduced.length > 0) {
		// 	const once = new Set();
		// 	for (const superpositionResult of result.superpositionReduced) {
		// 		const key = superpositionResult.type + " " + superpositionResult.size;
		// 		if (once.has(key)) continue;

		// 		once.add(key);

		// 		const count = superpositionReduced.get(key);
		// 		if (count) {
		// 			superpositionReduced.set(key, count + 1);
		// 		} else {
		// 			superpositionReduced.set(key, 1);
		// 		}
		// 	}
		// 	superpositions++;
		// 	data.superpositions++;
		// }

		if (!result.bruteForceFill) candidates++;
	}

	const res = 10000;
	const percent = (val, total = totalPuzzles) => {
		return Math.ceil(100 * res * val / total) / res + "%";
	}

	let candidateTotal = 0;
	candidateTotal += setNaked2;
	candidateTotal += setNaked3;
	candidateTotal += setNaked4;
	candidateTotal += setHidden2;
	candidateTotal += setHidden3;
	candidateTotal += setHidden4;
	candidateTotal += omissionsReduced;
	candidateTotal += yWingReduced;
	candidateTotal += xyzWingReduced;
	candidateTotal += xWingReduced;
	candidateTotal += swordfishReduced;
	candidateTotal += jellyfishReduced;
	candidateTotal += uniqueRectangleReduced;

	// let superTotal = 0;
	// for (const value of superpositionReduced.values()) superTotal += value;

	const printLine = (title, val, total) => {
		lines.push(title + ": " + percent(val, total) + " - " + val);
	};

	const lines = [];

	const clues = [...clueCounter.entries()];
	clues.sort((a, b) => {
		return a[0] - b[0];
	});

	lines.push("--- Clues");
	for (const clue of clues) {
		printLine(clue[0], clue[1], totalPuzzles);
	}

	// if (superTotal > 0) {
	// 	lines.push("--- Superpositions");
	// 	const ordered = [];
	// 	const entries = superpositionReduced.entries();
	// 	for (const [key, value] of entries) {
	// 		ordered.push({ key, value });
	// 	}
	// 	ordered.sort((a, b) => {
	// 		return b.value - a.value;
	// 	});
	// 	for (const result of ordered) {
	// 		printLine(result.key, result.value, superTotal);
	// 	}
	// }
	if (candidateTotal > 0) {
		lines.push("--- Candidates");
		printLine("Naked2", setNaked2, candidateTotal);
		printLine("Naked3", setNaked3, candidateTotal);
		printLine("Naked4", setNaked4, candidateTotal);
		printLine("Hidden2", setHidden2, candidateTotal);
		printLine("Hidden3", setHidden3, candidateTotal);
		printLine("Hidden4", setHidden4, candidateTotal);

		printLine("Omissions", omissionsReduced, candidateTotal);
		printLine("UniqueRectangle", uniqueRectangleReduced, candidateTotal);
		printLine("yWing", yWingReduced, candidateTotal);
		printLine("xyzWing", xyzWingReduced, candidateTotal);
		printLine("xWing", xWingReduced, candidateTotal);
		printLine("Swordfish", swordfishReduced, candidateTotal);
		printLine("Jellyfish", jellyfishReduced, candidateTotal);
	}

	const elapsed = performance.now() - time;
	if (maxTime === 0) {
		maxTime = elapsed;
	} else {
		if (elapsed > maxTime) {
			maxTime = elapsed;
		}
	}

	totalTime += elapsed;

	lines.push("--- Totals");
	lines.push("Simples: " + percent(simples) + " - " + simples);
	lines.push("Candidates: " + percent(candidates) + " - " + candidates);
	// lines.push("Superpositions: " + percent(superpositions) + " - " + superpositions);
	lines.push("BruteForceFill: " + percent(bruteForceFill) + " - " + bruteForceFill);

	const timeAvg = totalTime / 1000 / totalPuzzles;
	const timeMax = maxTime / 1000;
	lines.push("Time Avg: " + timeAvg.toFixed(3) + " Max: " + timeMax.toFixed(3));

	lines.push("Puzzles: " + totalPuzzles);

	data.message = lines;

	postMessage(data);

	return true;
};

onmessage = (event) => {
	puzzleString = event.data.grid ?? null;
	stepMode = 0; // (searchParams.get("table") == "phistomefel") ? 2 : 0;
	if (event.data.grids) {
		if (!puzzleStrings) puzzleStrings = [];
		for (const data of event.data.grids) {
			puzzleStrings.push(data);
		}
	}
	while (step());
};
