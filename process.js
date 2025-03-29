// Compressing to Hex
const byteToHex = [];
for (let n = 0; n <= 0xff; n++) byteToHex.push(n.toString(16).padStart(2, "0"));
const bin2hex = (buff) => {
	const hexOctets = new Array(buff.length);
	for (let i = 0; i < buff.length; i++) hexOctets[i] = byteToHex[buff[i]];
	return hexOctets.join("");
}

const binaryFill = (filled) => {
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
	return binaryFilled;
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
	const binaryFilled = binaryFill(filled);

	const gridLength = 56;
	const bitLength = gridLength * 3; // 168
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

const base64chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_";
const base64index = new Map();
for (let i = 0; i < base64chars.length; i++) base64index.set(base64chars[i], i);

class Possibilty {
	constructor() {
		this.mask = 0x01FF;
		this.remaining = 9;
	}
	bitCount() {
		switch (this.remaining) {
			case 9: return 4;
			case 8: return 3;
			case 7: return 3;
			case 6: return 3;
			case 5: return 3;
			case 4: return 2;
			case 3: return 2;
			case 2: return 1;
		}
		return 0;
	}
	compress(symbol) {
		const symbolIndex = symbol - 1;
		let compressed = symbolIndex;
		for (let i = 0; i < symbolIndex; i++) {
			const bit = (this.mask >>> i) & 0x1;
			if (bit === 0x0) compressed--;
		}
		return compressed;
	}
	decompress(compressed) {
		let ones = -1;
		for (let i = 0; i < 9; i++) {
			const bit = (this.mask >>> i) & 0x1;
			if (bit === 0x1) ones++;
			if (ones === compressed) return i + 1;
		}
		return 0;
	}
	bitFill(symbol, binary) {
		const bitIndex = this.bitCount() - 1;
		if (bitIndex < 0) return;
		const symbolIndex = this.compress(symbol);
		for (let shift = bitIndex; shift >= 0; shift--) {
			binary.push((symbolIndex >>> shift) & 0x1);
		}
	}
	reduce(symbol) {
		const symbolIndex = symbol - 1;

		const mask = this.mask;
		this.mask &= ~(0x1 << symbolIndex);
		if (mask === this.mask) return;

		this.remaining--;
	}
}
const reducePossibility = (symbol, index, possibilities) => {
	const row = Math.floor(index / 9);
	const col = index % 9;
	for (let i = row + 1; i < 9; i++) {
		const group = possibilities[i * 9 + col];
		group.reduce(symbol);
	}
	for (let i = col + 1; i < 9; i++) {
		const group = possibilities[row * 9 + i];
		group.reduce(symbol);
	}
	const boxRow = 3 * Math.floor(row / 3);
	const boxCol = 3 * Math.floor(col / 3);
	const boxi = (row - boxRow) * 3 + (col - boxCol);
	for (let i = boxi + 1; i < 9; i++) {
		const m = boxRow + Math.floor(i / 3);
		const n = boxCol + i % 3;
		const boxIndex = m * 9 + n;
		if (boxIndex <= index) continue;
		const group = possibilities[boxIndex];
		group.reduce(symbol);
	}
}
const compressGrid = (grid) => {
	const possibilities = [];
	for (let i = 0; i < 81; i++) {
		possibilities.push(new Possibilty());
	}

	const binary = [];
	for (let index = 0; index < 81; index++) {
		const symbol = grid[index];
		const possibility = possibilities[index];
		possibility.bitFill(symbol, binary);

		reducePossibility(symbol, index, possibilities);
	}

	return binary;
}

const throwError = "Board data decompress error!";
const decompressGrid = (binary) => {
	const possibilities = [];
	for (let i = 0; i < 81; i++) {
		possibilities.push(new Possibilty());
	}

	let bitIndex = 0;
	const grid = new Uint8Array(81);
	for (let index = 0; index < 81; index++) {
		const possibility = possibilities[index];
		const bitCount = possibility.bitCount();
		let compressed = 0x0;
		for (let shift = bitCount - 1; shift >= 0; shift--) {
			if (bitIndex === binary.length) throw new Error(throwError);
			const bit = binary[bitIndex];
			compressed |= bit << shift;
			bitIndex++;
		}
		const symbol = possibility.decompress(compressed);
		if (symbol === 0) throw new Error(throwError);
		grid[index] = symbol;
		reducePossibility(symbol, index, possibilities);
	}
	binary.splice(0, bitIndex);

	return grid;
}

const binaryToBase64 = (binaryArray) => {
	const base = 6;
	const bitLength = binaryArray.length;
	const baseLength = Math.ceil(bitLength / base);
	let baseFilled = "";
	let index = 0;
	const shiftBit = 0x1 << (base - 1);
	for (let i = 0; i < baseLength; i++) {
		let char = 0x0;
		for (let offset = 0; offset < base; offset++) {
			if (index === binaryArray.length) break;
			if (binaryArray[index] !== 0) char |= shiftBit >>> offset;
			index++;
		}
		baseFilled += base64chars[char];
	}
	return baseFilled;
}

// 00 clue: 2 bit
// 01 4 bit symbol: 6 bit
// 10 empty mask: 2 bit
// 11 9 bit mask: 11 bit
const puzzleGridBase64 = (board, clues = false) => {
	const binaryData = clues ? [] : compressGrid(board.puzzleSolved);

	for (let i = 0; i < 81; i++) {
		const startCell = board.startCells[i];
		if (startCell.symbol > 0) {
			binaryData.push(0, 0);
			if (clues) {
				const symbolBase = startCell.symbol - 1;
				for (let offset = 3; offset >= 0; offset--) {
					binaryData.push((symbolBase >>> offset) & 0x1);
				}
			}
			continue;
		}

		const cell = board.cells[i];
		if (cell.symbol > 0) {
			binaryData.push(0, 1);
			const symbolBase = cell.symbol - 1;
			for (let offset = 3; offset >= 0; offset--) {
				binaryData.push((symbolBase >>> offset) & 0x1);
			}
			continue;
		}

		if (cell.mask === 0x0) {
			binaryData.push(1, 0);
			continue;
		}

		binaryData.push(1, 1);
		const maskBase = cell.mask >>> 1;
		for (let offset = 8; offset >= 0; offset--) {
			binaryData.push((maskBase >>> offset) & 0x1);
		}
	}

	return binaryToBase64(binaryData);
}

const puzzleBase64Grid = (board, puzzleDataBase64, clues = false) => {
	// min = 81*2/6 = 27
	if (!puzzleDataBase64 || puzzleDataBase64.length < 27) throw new Error(throwError);

	const bits = [];
	for (let i = 0; i < puzzleDataBase64.length; i++) {
		const char = puzzleDataBase64[i];
		const base64 = base64index.get(char);

		for (let offset = 5; offset >= 0; offset--) {
			bits.push((base64 >>> offset) & 0x1);
		}
	}

	const puzzleSolved = new Uint8Array(81);
	if (!clues) {
		const gridSeed = decompressGrid(bits);
		for (let i = 0; i < 81; i++) puzzleSolved[i] = gridSeed[i];
	}

	const startCells = new Uint8Array(81);
	const cellSymbols = new Uint8Array(81);
	const cellMasks = new Uint16Array(81);

	let bitIndex = 0;
	for (let i = 0; i < 81; i++) {
		const requiredRemaining = (81 - i) * 2;
		if (bitIndex > bits.length - requiredRemaining) throw new Error(throwError);

		const bit1 = bits[bitIndex++];
		const bit2 = bits[bitIndex++];
		if (bit1 === 0 && bit2 === 0) {
			if (clues) {
				if (bitIndex > bits.length - 4) throw new Error(throwError);
				let symbol = 0;
				for (let i = 3; i >= 0; i--) {
					const bit = bits[bitIndex++];
					symbol |= bit << i;
				}
				symbol++;
				puzzleSolved[i] = symbol;
			}
			startCells[i] = puzzleSolved[i];
			cellSymbols[i] = puzzleSolved[i];
			cellMasks[i] = 0x0000;
			continue;
		}

		startCells[i] = 0;
		if (bit1 === 0 && bit2 === 1) {
			if (bitIndex > bits.length - 4) throw new Error(throwError);
			let symbol = 0;
			for (let i = 3; i >= 0; i--) {
				const bit = bits[bitIndex++];
				symbol |= bit << i;
			}
			symbol++;
			cellSymbols[i] = symbol;
			cellMasks[i] = 0x0000;
			continue;
		}

		cellSymbols[i] = 0;
		if (bit1 === 1 && bit2 === 0) {
			cellMasks[i] = 0x0000;
			continue;
		}

		// if (bit1===1 && bit2===1) 
		if (bitIndex > bits.length - 9) throw new Error(throwError);

		let mask = 0;
		for (let i = 8; i >= 0; i--) {
			const bit = bits[bitIndex++];
			mask |= bit << i;
		}
		mask <<= 1;
		cellMasks[i] = mask;
	}

	for (let i = 0; i < 81; i++) {
		board.puzzleSolved[i] = puzzleSolved[i];
		board.startCells[i].symbol = startCells[i];
		board.cells[i].symbol = cellSymbols[i];
		board.cells[i].mask = cellMasks[i];
	}
}

export { puzzleGridHex, puzzleHexGrid, puzzleGridBase64, puzzleBase64Grid };
