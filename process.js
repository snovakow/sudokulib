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

class StrategyCounter {
	constructor() {
		this.totalPuzzles = 0;
		this.clueCounter = new Map();

		this.simples = 0;
		this.candidates = 0;
		this.bruteForceFill = 0;

		this.setNaked2 = 0;
		this.setNaked3 = 0;
		this.setNaked4 = 0;
		this.setHidden2 = 0;
		this.setHidden3 = 0;
		this.setHidden4 = 0;
		this.omissionsReduced = 0;
		this.yWingReduced = 0;
		this.xyzWingReduced = 0;
		this.xWingReduced = 0;
		this.swordfishReduced = 0;
		this.jellyfishReduced = 0;
		this.uniqueRectangleReduced = 0;

		this.startTime = performance.now();
		this.totalTime = 0;
	}
	addData(data) {
		this.totalPuzzles++;

		this.simples += data.simple;
		if (data.simple === 0 && data.bruteForce === 0) this.candidates++;

		this.setNaked2 += data.naked2;
		this.setNaked3 += data.naked3;
		this.setNaked4 += data.naked4;
		this.setHidden2 += data.hidden2;
		this.setHidden3 += data.hidden3;
		this.setHidden4 += data.hidden4;
		this.omissionsReduced += data.omissions;
		this.yWingReduced += data.yWing;
		this.xyzWingReduced += data.xyzWing;
		this.xWingReduced += data.xWing;
		this.swordfishReduced += data.swordfish;
		this.jellyfishReduced += data.jellyfish;
		this.uniqueRectangleReduced += data.uniqueRectangle;
		this.bruteForceFill += data.bruteForce;

		let candidateTotal = 0;
		candidateTotal += this.setNaked2;
		candidateTotal += this.setNaked3;
		candidateTotal += this.setNaked4;
		candidateTotal += this.setHidden2;
		candidateTotal += this.setHidden3;
		candidateTotal += this.setHidden4;
		candidateTotal += this.omissionsReduced;
		candidateTotal += this.yWingReduced;
		candidateTotal += this.xyzWingReduced;
		candidateTotal += this.xWingReduced;
		candidateTotal += this.swordfishReduced;
		candidateTotal += this.jellyfishReduced;
		candidateTotal += this.uniqueRectangleReduced;

		const clueValue = this.clueCounter.get(data.clueCount);
		if (clueValue) this.clueCounter.set(data.clueCount, clueValue + 1);
		else this.clueCounter.set(data.clueCount, 1)

		// let superTotal = 0;
		// for (const value of superpositionReduced.values()) superTotal += value;

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

		this.totalTime = performance.now() - this.startTime;
	}
	lines() {
		const res = 10000;
		const percent = (val, total = this.totalPuzzles) => {
			return Math.ceil(100 * res * val / total) / res + "%";
		}

		let candidateTotal = 0;
		candidateTotal += this.setNaked2;
		candidateTotal += this.setNaked3;
		candidateTotal += this.setNaked4;
		candidateTotal += this.setHidden2;
		candidateTotal += this.setHidden3;
		candidateTotal += this.setHidden4;
		candidateTotal += this.omissionsReduced;
		candidateTotal += this.yWingReduced;
		candidateTotal += this.xyzWingReduced;
		candidateTotal += this.xWingReduced;
		candidateTotal += this.swordfishReduced;
		candidateTotal += this.jellyfishReduced;
		candidateTotal += this.uniqueRectangleReduced;

		// let superTotal = 0;
		// for (const value of superpositionReduced.values()) superTotal += value;

		const printLine = (title, val, total) => {
			lines.push(title + ": " + percent(val, total) + " - " + val);
		};

		const lines = [];

		const clues = [...this.clueCounter.entries()];
		clues.sort((a, b) => {
			return a[0] - b[0];
		});

		lines.push("--- Clues");
		for (const clue of clues) printLine(clue[0], clue[1], this.totalPuzzles);

		if (candidateTotal > 0) {
			lines.push("--- Candidates");
			printLine("Naked2", this.setNaked2, candidateTotal);
			printLine("Naked3", this.setNaked3, candidateTotal);
			printLine("Naked4", this.setNaked4, candidateTotal);
			printLine("Hidden2", this.setHidden2, candidateTotal);
			printLine("Hidden3", this.setHidden3, candidateTotal);
			printLine("Hidden4", this.setHidden4, candidateTotal);

			printLine("Omissions", this.omissionsReduced, candidateTotal);
			printLine("UniqueRectangle", this.uniqueRectangleReduced, candidateTotal);
			printLine("yWing", this.yWingReduced, candidateTotal);
			printLine("xyzWing", this.xyzWingReduced, candidateTotal);
			printLine("xWing", this.xWingReduced, candidateTotal);
			printLine("Swordfish", this.swordfishReduced, candidateTotal);
			printLine("Jellyfish", this.jellyfishReduced, candidateTotal);
		}

		lines.push("--- Totals");
		lines.push("Simples: " + percent(this.simples) + " - " + this.simples);
		lines.push("Candidates: " + percent(this.candidates) + " - " + this.candidates);
		// lines.push("Superpositions: " + percent(superpositions) + " - " + superpositions);
		lines.push("BruteForceFill: " + percent(this.bruteForceFill) + " - " + this.bruteForceFill);

		const timeAvg = this.totalTime / 1000 / this.totalPuzzles;
		const timeAvgInv = 1 / timeAvg;
		lines.push("Time Avg: " + timeAvg.toFixed(3));
		lines.push("Per Second: " + timeAvgInv.toFixed(1));

		lines.push("Puzzles: " + this.totalPuzzles);
		return lines;
	}
}
export { StrategyCounter, puzzleGridHex, puzzleHexGrid };
