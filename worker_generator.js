import { CellCandidate, Grid } from "./Grid.js";
import { sudokuGenerator, fillSolve, STRATEGY } from "./generator.js";

const cells = new Grid();
for (let i = 0; i < 81; i++) cells[i] = new CellCandidate(i);

let puzzleString = null;
let puzzleStrings = null;

let puzzleCount = 0;

let stepMode = 0; // 1=row 2=phist

const allArray = [];

class StrategyData {
	constructor(strategyArray, strategy, result, thisArray = null) {
		this.strategy = strategy;
		this.data = result;
		if (strategyArray) strategyArray.push(strategy);
		if (thisArray) thisArray.push(this);
		allArray.push(this);
	}
}

const simples = [];
new StrategyData(simples, STRATEGY.SIMPLE_HIDDEN, 'hiddenSimple');
new StrategyData(simples, STRATEGY.SIMPLE_NAKED, 'nakedSimple');
new StrategyData(simples, STRATEGY.SIMPLE_INTERSECTION, 'omissionSimple');

new StrategyData(null, STRATEGY.VISIBLE_INTERSECTION, 'omissionVisible');
new StrategyData(null, STRATEGY.VISIBLE_NAKED, 'nakedVisible');

const strategyDataArray = [];
const strategies = [];
new StrategyData(strategies, STRATEGY.NAKED_2, 'naked2', strategyDataArray);
new StrategyData(strategies, STRATEGY.NAKED_3, 'naked3', strategyDataArray);
new StrategyData(strategies, STRATEGY.NAKED_4, 'naked4', strategyDataArray);
new StrategyData(strategies, STRATEGY.HIDDEN_1, 'hidden1', strategyDataArray);
new StrategyData(strategies, STRATEGY.HIDDEN_2, 'hidden2', strategyDataArray);
new StrategyData(strategies, STRATEGY.HIDDEN_3, 'hidden3', strategyDataArray);
new StrategyData(strategies, STRATEGY.HIDDEN_4, 'hidden4', strategyDataArray);
new StrategyData(strategies, STRATEGY.INTERSECTION_REMOVAL, 'omissions', strategyDataArray);
new StrategyData(strategies, STRATEGY.DEADLY_PATTERN, 'uniqueRectangle', strategyDataArray);
new StrategyData(strategies, STRATEGY.Y_WING, 'yWing', strategyDataArray);
new StrategyData(strategies, STRATEGY.XYZ_WING, 'xyzWing', strategyDataArray);
new StrategyData(strategies, STRATEGY.X_WING, 'xWing', strategyDataArray);
new StrategyData(strategies, STRATEGY.SWORDFISH, 'swordfish', strategyDataArray);
new StrategyData(strategies, STRATEGY.JELLYFISH, 'jellyfish', strategyDataArray);

const step = () => {
	let mode = stepMode;
	if (puzzleString) {
		cells.fromString(puzzleString);
		mode = -1;
	}

	let puzzleId = "";
	if (puzzleStrings) {
		const puzzleData = puzzleStrings.shift();
		if (!puzzleData) return false;
		const puzzle = puzzleData.puzzleClues;
		if (!puzzle) return false;
		puzzleId = puzzleData.id;
		cells.fromString(puzzle);
		mode = -1;
	}
	const [clueCount, puzzleFilled] = sudokuGenerator(cells, mode);

	const data = {
		puzzle: cells.string(),
		cells: cells.toData()
	};

	for (const cell of cells) if (cell.symbol === 0) cell.fill();
	const save = cells.toData();

	const result = fillSolve(cells, simples, strategies);
	data.puzzleClues = data.puzzle;
	data.puzzleFilled = puzzleFilled.join('');
	data.clueCount = clueCount;
	if (puzzleStrings) {
		data.remaining = puzzleStrings.length;
		data.id = puzzleId;
	}

	for (const strategy of allArray) data[strategy.data] = 0;

	// solveType
	// 0 hiddenSimple
	// 1 nakedSimple
	// 2 omissionSimple
	// 3 omissionVisible
	// 4 Candidate
	// 5 Candidate Minimal
	// 6 Incomplete
	data.solveType = 0;

	if (result.simple) {
		if (result.nakedSimple > 0 && result.omissionSimple === 0) {
			data.solveType = 1;
		}
		if (result.omissionSimple > 0) {
			data.solveType = 2;
		}
	}
	if (result.visible) {
		data.solveType = 3;
	}
	if (!result.simple && !result.visible) {
		if (result.solved) data.solveType = 4;
		else data.solveType = 6;
	}

	for (const strategy of allArray) data[strategy.data] = result[strategy.data];

	if (data.solveType === 4) {
		const usedData = [];
		for (const strategy of strategyDataArray) {
			if (result[strategy.data] > 0) usedData.push(strategy);
		}
		const minimal = [];
		for (let index = 0; index < usedData.length - 1; index++) {
			const used = usedData[index];

			const priority = [];
			for (const strategy of usedData) {
				if (used.strategy === strategy.strategy) continue;
				priority.push(strategy.strategy);
			}

			cells.fromData(save);
			const priorityResult = fillSolve(cells, simples, priority);
			if (!priorityResult.solved) minimal.push(used.strategy);
		}
		minimal.push(usedData[usedData.length - 1].strategy);

		cells.fromData(save);
		const minimalResult = fillSolve(cells, simples, minimal);
		if (minimalResult.solved) {
			data.solveType = 5;
			for (const strategy of allArray) data[strategy.data] = minimalResult[strategy.data];
		}
	}

	puzzleCount++;

	postMessage(data);

	if (puzzleStrings) return puzzleStrings.length > 0;
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
