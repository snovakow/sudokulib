import { CellCandidate, Grid } from "../sudokulib/Grid.js";
import { sudokuGenerator, fillSolve, STRATEGY } from "../sudokulib/generator.js";

const cells = new Grid();
for (let i = 0; i < 81; i++) cells[i] = new CellCandidate(i);

let puzzleString = null;
let puzzleStrings = null;

let stepMode = 0; // 1=row 2=phist

const allArray = [];

class StrategyData {
	constructor(data, strategy, result) {
		this.strategy = strategy;
		this.data = result;
		data.push(this);
		allArray.push(this);
	}
}

const simpleDataArray = [];
const visibleDataArray = [];
const strategyDataArray = [];

new StrategyData(simpleDataArray, STRATEGY.SIMPLE_HIDDEN, 'hiddenSimple');
new StrategyData(simpleDataArray, STRATEGY.SIMPLE_INTERSECTION, 'omissionSimple');
new StrategyData(simpleDataArray, STRATEGY.SIMPLE_NAKED, 'nakedSimple');

new StrategyData(visibleDataArray, STRATEGY.VISIBLE_NAKED, 'nakedVisible');
new StrategyData(visibleDataArray, STRATEGY.VISIBLE_INTERSECTION, 'omissionVisible');

new StrategyData(strategyDataArray, STRATEGY.NAKED_2, 'naked2');
new StrategyData(strategyDataArray, STRATEGY.NAKED_3, 'naked3');
new StrategyData(strategyDataArray, STRATEGY.NAKED_4, 'naked4');
new StrategyData(strategyDataArray, STRATEGY.HIDDEN_1, 'hidden1');
new StrategyData(strategyDataArray, STRATEGY.HIDDEN_2, 'hidden2');
new StrategyData(strategyDataArray, STRATEGY.HIDDEN_3, 'hidden3');
new StrategyData(strategyDataArray, STRATEGY.HIDDEN_4, 'hidden4');
new StrategyData(strategyDataArray, STRATEGY.INTERSECTION_REMOVAL, 'omissions');
new StrategyData(strategyDataArray, STRATEGY.DEADLY_PATTERN, 'uniqueRectangle');
new StrategyData(strategyDataArray, STRATEGY.Y_WING, 'yWing');
new StrategyData(strategyDataArray, STRATEGY.XYZ_WING, 'xyzWing');
new StrategyData(strategyDataArray, STRATEGY.X_WING, 'xWing');
new StrategyData(strategyDataArray, STRATEGY.SWORDFISH, 'swordfish');
new StrategyData(strategyDataArray, STRATEGY.JELLYFISH, 'jellyfish');

const strategies = [...strategyDataArray];
for (const strategy of strategyDataArray) strategies.push(strategy.strategy);

const simples = [...simpleDataArray];
for (const strategy of simpleDataArray) simples.push(strategy.strategy);

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
		cells: cells.toData()
	};

	for (const cell of cells) if (cell.symbol === 0) cell.fill();
	const save = cells.toData();

	const result = fillSolve(cells, simples, strategies);
	data.puzzleClues = data.puzzle;
	data.puzzleFilled = puzzleFilled.join('');
	data.clueCount = clueCount;

	for (const strategy of allArray) data[strategy.data] = 0;

	// solveType
	// 0 Simple
	// 1 Simple Minimal
	// 2 Candidate
	// 3 Candidate Visible
	// 3 Candidate Minimal
	// 4 Incomplete
	data.solveType = 0;
	if (!result.simple) {
		if (result.solved) {
			data.solveType = 2;
		} else {
			data.solveType = 5;
		}
	}

	const orderedSolveSimple = () => {
		// simples = [STRATEGY.SIMPLE_HIDDEN, STRATEGY.SIMPLE_INTERSECTION, STRATEGY.SIMPLE_NAKED];
		if (result.nakedSimple === 0) {
			data.solveType = 1;
			return;
		}

		cells.fromData(save);
		const nakedResult = fillSolve(cells, [STRATEGY.SIMPLE_NAKED], []);
		if (nakedResult.solved) {
			data.solveType = 1;
			return;
		}

		cells.fromData(save);
		const simples2 = [STRATEGY.SIMPLE_HIDDEN, STRATEGY.SIMPLE_NAKED, STRATEGY.SIMPLE_INTERSECTION];
		const orderedResult = fillSolve(cells, simples2, []);

		// "X0X" = if simples2 "XX0" then simples
		// "X0X" = if simples2 "XXX" then simples min
		// "XXX" = if simples2 "XX0" then simples2 min
		// "XXX" = if simples2 "XXX" then simples min
		if (result.omissionSimple > 0) {
			if (orderedResult.nakedSimple > 0 && orderedResult.omissionSimple > 0) {
				data.solveType = 1;
			} else {
				for (const strategy of simpleDataArray) data[strategy.data] = orderedResult[strategy.data];
				data.solveType = 1;
			}
		} else {
			if (orderedResult.omissionSimple > 0) {
				data.solveType = 1;
			}
		}
	}
	const orderedSolve = () => {
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
			data.solveType = 4;
			for (const strategy of allArray) data[strategy.data] = minimalResult[strategy.data];
		}
	}

	for (const strategy of allArray) data[strategy.data] = result[strategy.data];
	if (data.solveType === 0) {
		orderedSolveSimple();
	}
	if (data.solveType === 2) {
		if (!result.candidateVisible) {
			data.solveType = 3;
			orderedSolve();
		}
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
