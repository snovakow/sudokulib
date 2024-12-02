// Compressing to Hex
const byteToHex = [];
for (let n = 0; n <= 0xff; n++) byteToHex.push(n.toString(16).padStart(2, "0"));
const bin2hex = (buff) => {
	const hexOctets = new Array(buff.length);
	for (let i = 0; i < buff.length; i++) hexOctets[i] = byteToHex[buff[i]];
	return hexOctets.join("");
}

const puzzleCluesHex = (clues) => {
	const hexClues = new Uint8Array(11);
	if (clues[0] === "0") hexClues[0] = 0x00;
	else hexClues[0] = 0x01;
	let index = 1;
	for (let i = 1; i < 11; i++) {
		let char = 0x00;
		for (let offset = 0; offset < 8; offset++) {
			if (clues[index] !== "0") char |= 0x80 >>> offset;
			index++;
		}
		hexClues[i] = char;
	}
	return bin2hex(hexClues);
}
const puzzleGridHex = (clues, filled) => {
	const binaryFilled = [];
	for (let row = 1; row < 8; row++) {
		for (let i = 0; i < 8; i++) {
			const index = row * 9 + i;
			const symbol = parseInt(filled[index]) - 1;

			let encode = symbol;
			if (i < encode) encode--;

			const symbolBit1 = encode % 2;
			encode = Math.floor(encode / 2);
			const symbolBit2 = encode % 2;
			encode = Math.floor(encode / 2);
			const symbolBit3 = encode % 2;

			binaryFilled.push(symbolBit3);
			binaryFilled.push(symbolBit2);
			binaryFilled.push(symbolBit1);
		}
	}
	const gridLength = 56;
	const bitLength = gridLength * 3;
	const byteLength = bitLength / 8; // 21
	const hexFilled = new Uint8Array(byteLength);
	let index = 0;
	for (let i = 0; i < byteLength; i++) {
		let char = 0x00;
		for (let offset = 0; offset < 8; offset++) {
			if (binaryFilled[index] !== 0) char |= 0x80 >>> offset;
			index++;
		}
		hexFilled[i] = char;
	}
	return puzzleCluesHex(clues) + bin2hex(hexFilled);
}

// Decompressing from Hex
const hexClues = (hexString, grid) => {
	const clues = [];
	if (hexString[1] === "0") clues[0] = 0;
	else clues[0] = grid[0];
	let index = 1;
	for (let i = 2; i < 22; i++) {
		const hex = parseInt(hexString[i], 16);

		let shift = 3;
		for (let offset = 0; offset < 4; offset++) {
			const clue = (hex >>> shift) & 0x1;
			if (clue === 0x1) clues[index] = grid[index];
			else clues[index] = 0;
			shift--;
			index++;
		}
	}
	return clues.join('');
}
const remainingTotal = 36; // 0+1+2+3+4+5+6+7+8
const hexGrid = (hexString) => {
	const bits = [];
	for (let i = 0; i < 42; i++) {
		const hex = parseInt(hexString[i], 16);

		let shift = 3;
		for (let offset = 0; offset < 4; offset++) {
			bits.push((hex >>> shift) & 0x01);
			shift--;
		}
	}
	const grid = [0, 1, 2, 3, 4, 5, 6, 7, 8];
	let index = 0;
	for (let row = 1; row < 8; row++) {
		let remaining = remainingTotal;
		for (let i = 0; i < 8; i++) {
			const bit3 = bits[index];
			const bit2 = bits[index + 1];
			const bit1 = bits[index + 2];

			const symbol = bit1 | (bit2 << 1) | (bit3 << 2);
			let encode = symbol;
			if (i <= encode) encode++;
			grid.push(encode);
			remaining -= encode;
			index += 3;
		}
		grid.push(remaining);
	}
	for (let col = 0; col < 9; col++) {
		let remaining = remainingTotal;
		for (let i = col; i < 72; i += 9) remaining -= grid[i];
		grid.push(remaining);
	}
	for (let i = 0; i < 81; i++) grid[i]++;
	return grid.join('');
}
const puzzleHexGrid = (puzzleDataHex) => {
	const gridSeed = hexGrid(puzzleDataHex.substring(22));
	const puzzle = hexClues(puzzleDataHex.substring(0, 22), gridSeed);

	const grid = new Uint8Array(81);
	for (let i = 0; i < 81; i++) grid[i] = parseInt(gridSeed[i]);

	return [puzzle, grid];
}

class SimpleCounter {
	constructor() {
		this.nakedSimple = 0;
		this.hiddenSimple = 0;
		this.omissionSimple = 0;
		this.count = 0;
	}
	addData(data) {
		this.nakedSimple += data.nakedSimple > 0 ? 1 : 0;
		this.hiddenSimple += data.hiddenSimple > 0 ? 1 : 0;
		this.omissionSimple += data.omissionSimple > 0 ? 1 : 0;
		this.count++;
	}
}
class SimpleMinimalCounter {
	constructor() {
		this.hiddenSimple = 0;
		this.omissionSimple = 0;
		this.nakedSimple = 0;
		this.allSimple = 0;
		this.count = 0;
	}
	addData(data) {
		if (data.hiddenSimple > 0 && data.omissionSimple === 0 && data.nakedSimple === 0) this.hiddenSimple++;
		if (data.hiddenSimple > 0 && data.omissionSimple > 0 && data.nakedSimple === 0) this.omissionSimple++;
		if (data.hiddenSimple > 0 && data.omissionSimple === 0 && data.nakedSimple > 0) this.nakedSimple++;
		if (data.hiddenSimple > 0 && data.omissionSimple > 0 && data.nakedSimple > 0) this.allSimple++;
		this.count++;
	}
}
class VisibleCounter extends SimpleCounter {
	constructor() {
		super();
		this.nakedVisible = 0;
		this.omissionVisible = 0;
	}
	addData(data) {
		super.addData(data);
		this.nakedVisible += data.nakedVisible > 0 ? 1 : 0;
		this.omissionVisible += data.omissionVisible > 0 ? 1 : 0;
	}
}
class CandidateCounter extends VisibleCounter {
	constructor() {
		super();
		this.naked2 = 0;
		this.naked3 = 0;
		this.naked4 = 0;
		this.hidden1 = 0;
		this.hidden2 = 0;
		this.hidden3 = 0;
		this.hidden4 = 0;
		this.omissions = 0;
		this.uniqueRectangle = 0;
		this.yWing = 0;
		this.xyzWing = 0;
		this.xWing = 0;
		this.swordfish = 0;
		this.jellyfish = 0;
	}
	addData(data) {
		super.addData(data);
		this.naked2 += data.naked2 > 0 ? 1 : 0;
		this.naked3 += data.naked3 > 0 ? 1 : 0;
		this.naked4 += data.naked4 > 0 ? 1 : 0;
		this.hidden1 += data.hidden1 > 0 ? 1 : 0;
		this.hidden2 += data.hidden2 > 0 ? 1 : 0;
		this.hidden3 += data.hidden3 > 0 ? 1 : 0;
		this.hidden4 += data.hidden4 > 0 ? 1 : 0;
		this.omissions += data.omissions > 0 ? 1 : 0;
		this.uniqueRectangle += data.uniqueRectangle > 0 ? 1 : 0;
		this.yWing += data.yWing > 0 ? 1 : 0;
		this.xyzWing += data.xyzWing > 0 ? 1 : 0;
		this.xWing += data.xWing > 0 ? 1 : 0;
		this.swordfish += data.swordfish > 0 ? 1 : 0;
		this.jellyfish += data.jellyfish > 0 ? 1 : 0;
	}
}
class StrategyCounter {
	constructor() {
		this.totalPuzzles = 0;
		this.clueCounter = new Map();

		this.simples = new SimpleCounter();
		this.simplesMinimal = new SimpleCounter();
		this.simplesIsolated = new SimpleMinimalCounter();
		this.candidatesVisible = new VisibleCounter();
		this.candidates = new CandidateCounter();
		this.candidatesMinimal = new CandidateCounter();
		this.unsolvable = new CandidateCounter();

		this.startTime = performance.now();
		this.totalTime = 0;
	}
	addData(data) {
		this.totalPuzzles++;

		if (data.solveType === 0 || data.solveType === 1) this.simples.addData(data);
		if (data.solveType === 1) this.simplesMinimal.addData(data);
		if (data.solveType === 1) this.simplesIsolated.addData(data);

		if (data.solveType === 2) this.candidatesVisible.addData(data);
		if (data.solveType === 3 || data.solveType === 4) this.candidates.addData(data);
		if (data.solveType === 4) this.candidatesMinimal.addData(data);

		if (data.solveType === 5) this.unsolvable.addData(data);

		const clueValue = this.clueCounter.get(data.clueCount);
		if (clueValue) this.clueCounter.set(data.clueCount, clueValue + 1);
		else this.clueCounter.set(data.clueCount, 1)

		this.totalTime = performance.now() - this.startTime;
	}
	lines() {
		// const res = 10000;
		// const percent = (val, total = this.totalPuzzles) => {
		// 	return ((Math.ceil(100 * res * val / total) / res).toFixed(3) + "%").padStart(7, "0");
		// }
		// const makeLineSimple = (title, val, total) => {
		// 	return title + ": " + percent(val, total);
		// };
		// const makeLine = (title, val, total) => {
		// 	return title + ": " + percent(val, total) + " - " + val.toLocaleString();
		// };
		// const printLine = (title, val, total) => {
		// 	lines.push(makeLine(title, val, total));
		// };

		// const lines = [];

		// const clues = [...this.clueCounter.entries()];
		// clues.sort((a, b) => {
		// 	return a[0] - b[0];
		// });

		// lines.push("--- Clues");
		// for (const clue of clues) printLine(clue[0], clue[1], this.totalPuzzles);

		// if (this.simplesMinimal.count > 0) {
		// 	lines.push("");
		// 	lines.push("--- Simples Minimal");
		// 	const printStrategy = (title, property) => {
		// 		let line = makeLineSimple(title, this.simplesIsolated[property], this.simplesMinimal.count);
		// 		line += " - " + this.simplesIsolated[property].toLocaleString();
		// 		lines.push(line);
		// 	}
		// 	printStrategy("Hidden", 'hiddenSimple');
		// 	printStrategy("Omission", 'omissionSimple');
		// 	printStrategy("Naked", 'nakedSimple');
		// 	printStrategy("All", 'allSimple');
		// }

		// if (this.candidatesMinimal.count) {
		// 	lines.push("");
		// 	lines.push("--- Candidates Minimal");

		// 	const printStrategy = (title, property) => {
		// 		const line = makeLineSimple(title, this.candidatesMinimal[property], this.candidatesMinimal.count);
		// 		lines.push(line + " - " + this.candidatesMinimal[property].toLocaleString());
		// 	}
		// 	printStrategy("Naked2", 'naked2');
		// 	printStrategy("Naked3", 'naked3');
		// 	printStrategy("Naked4", 'naked4');
		// 	printStrategy("Hidden1", 'hidden1');
		// 	printStrategy("Hidden2", 'hidden2');
		// 	printStrategy("Hidden3", 'hidden3');
		// 	printStrategy("Hidden4", 'hidden4');
		// 	printStrategy("Omissions", 'omissions');
		// 	printStrategy("Unique Rectangle", 'uniqueRectangle');
		// 	printStrategy("Y-Wing", 'yWing');
		// 	printStrategy("XYZ-Wing", 'xyzWing');
		// 	printStrategy("X-Wing", 'xWing');
		// 	printStrategy("Swordfish", 'swordfish');
		// 	printStrategy("Jellyfish", 'jellyfish');
		// }

		// const candidateCount = this.candidates.count + this.candidatesVisible.count;

		// lines.push("");
		// lines.push("--- Totals " + this.totalPuzzles.toLocaleString());

		// let line = "Simples: " + percent(this.simples.count);
		// if (this.simples.count > 0) line += " (" + percent(this.simplesMinimal.count, this.simples.count) + " minimal)";
		// lines.push(line);

		// line = "Strategies: " + percent(this.candidates.count);
		// if (this.candidates.count > 0) line += " (" + percent(this.candidatesMinimal.count, this.candidates.count) + " minimal)";
		// lines.push(line);

		// line = "Candidates: " + percent(candidateCount);
		// if (candidateCount > 0) line += " (" + percent(this.candidatesVisible.count, candidateCount) + " visible)";
		// lines.push(line);

		// lines.push("Unsolvable: " + percent(this.unsolvable.count));

		// lines.push("");
		// lines.push("--- Rate");
		const timeAvg = this.totalTime / 1000 / this.totalPuzzles;
		// const timeAvgInv = 1 / timeAvg;
		// lines.push("Time Avg: " + timeAvg.toFixed(3));
		// lines.push("Per Second: " + timeAvgInv.toFixed(1));

		return timeAvg;
	}
}

export { StrategyCounter, puzzleGridHex, puzzleHexGrid };
