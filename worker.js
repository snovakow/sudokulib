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
new StrategyData(simpleDataArray, STRATEGY.SIMPLE_NAKED2, 'naked2Simple');
new StrategyData(simpleDataArray, STRATEGY.SIMPLE_NAKED3, 'naked3Simple');
new StrategyData(simpleDataArray, STRATEGY.SIMPLE_NAKED, 'nakedSimple');

new StrategyData(visibleDataArray, STRATEGY.VISIBLE_INTERSECTION, 'omissionVisible');
new StrategyData(visibleDataArray, STRATEGY.VISIBLE_NAKED2, 'naked2Visible');
new StrategyData(visibleDataArray, STRATEGY.VISIBLE_NAKED, 'nakedVisible');

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

const simples = [...simpleDataArray];
for (const strategy of simpleDataArray) simples.push(strategy.strategy);

const visibles = [...visibleDataArray];
for (const strategy of visibleDataArray) visibles.push(strategy.strategy);

const strategies = [...strategyDataArray];
for (const strategy of strategyDataArray) strategies.push(strategy.strategy);

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

	const result = fillSolve(cells, simples, visibles, strategies, true);
	data.puzzleClues = data.puzzle;
	data.puzzleFilled = puzzleFilled.join('');
	data.clueCount = clueCount;
	if (puzzleStrings) {
		data.remaining = puzzleStrings.length;
		data.id = puzzleId;
	}

	for (const strategy of allArray) data[strategy.data] = 0;

	// solveType
	// 0 Simple
	// 1 Visible
	// 2 Candidate
	// 3 Candidate Minimal
	// 4 Incomplete
	data.solveType = 0;
	data.superRank = 0;
	data.superSize = 0;
	data.superType = 0;
	data.superDepth = 0;
	data.superCount = 0;
	if (!result.simple) {
		if (result.solved) {
			data.solveType = 1;
		} else {
			data.solveType = 4;
			data.superRank = result.superRank;
			data.superSize = result.superSize;
			data.superType = result.superType;
			data.superDepth = result.superDepth;
			data.superCount = result.superCount;
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
			const priorityResult = fillSolve(cells, simples, visibles, priority);
			if (!priorityResult.solved) minimal.push(used.strategy);
		}
		minimal.push(usedData[usedData.length - 1].strategy);

		cells.fromData(save);
		const minimalResult = fillSolve(cells, simples, visibles, minimal);
		if (minimalResult.solved) {
			data.solveType = 3;
			for (const strategy of allArray) data[strategy.data] = minimalResult[strategy.data];
		}
	}

	for (const strategy of allArray) data[strategy.data] = result[strategy.data];
	if (data.solveType === 1) {
		if (!result.candidateVisible) {
			data.solveType = 2;
			orderedSolve();
		}
	}
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
