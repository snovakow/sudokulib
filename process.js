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

export { puzzleGridHex, puzzleHexGrid };
