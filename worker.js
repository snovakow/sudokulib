import { CellCandidate, Grid } from "../sudokulib/Grid.js";
import { sudokuGenerator, fillSolve, STRATEGY } from "../sudokulib/generator.js";

const cells = new Grid();
for (let i = 0; i < 81; i++) cells[i] = new CellCandidate(i);

let puzzleString = null;
let puzzleStrings = null;

let stepMode = 0; // 1=row 2=phist
const step = () => {
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

	const data = {
		id,
		puzzle: cells.string(),
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

	if (simple) data.simple = 1;
	if (result.bruteForceFill) data.bruteForce = 1;
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

	if (!simple) {
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
	}

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
