import assert from "node:assert/strict";

const historyLimit = 100;
const quoteIds = Array.from({ length: 265 }, (_, index) => `quote-${index + 1}`);

function selectNext(history, randomIndex) {
	const blocked = new Set(history.slice(-historyLimit));
	const available = quoteIds.filter((id) => !blocked.has(id));
	const selected = available[randomIndex % available.length];
	return {
		selected,
		history: [...history, selected].slice(-historyLimit),
	};
}

let state = { history: [] };
const shown = [];

for (let login = 0; login < 1000; login += 1) {
	state = selectNext(state.history, (login * 73) + 19);
	const recentBeforeCurrent = shown.slice(-historyLimit);
	assert.equal(
		recentBeforeCurrent.includes(state.selected),
		false,
		`quote repeated inside the 100-quote window at login ${login + 1}`,
	);
	shown.push(state.selected);
	assert.ok(state.history.length <= historyLimit);
}

console.log("Rotation model passed 1,000 simulated logins.");
