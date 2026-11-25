/*
 * Builds tests/fixtures/reference-cases.json from the reference model
 * (reference/03_modelo_referencia.js, model v2.1 by the scientific reviewer).
 * The PHP port must reproduce these numbers; ReferenceParityTest checks it.
 *
 *   node tests/fixtures/generate-reference.js
 *
 * Regenerate only when the reference model itself changes.
 */
const path = require('path');
const fs = require('fs');
const m = require(path.join(__dirname, '..', '..', 'reference', '03_modelo_referencia.js'));

const base = { T: 25, PmmHg: 715, df: 0.30, L: 12, eps: 0.841, dmin: 0.01, dmax: 10, classes: 100 };
const cases = [
  // the published cases (instructions, section 6)
  ['spreadsheet legacy', { ...base, df: 50, L: 1000, eps: 0.7438, v: 0.053, rhop: 912, dmin: 0.01, dmax: 100 }, { legacy: true }],
  ['EN 1822', { ...base, v: 0.053, rhop: 912, mono: true, cmd: 0.2, gsd: 1.5 }, {}],
  ['NIOSH N', { ...base, v: 0.142, rhop: 2165, cmd: 0.075, gsd: 1.86 }, {}],
  ['NIOSH P', { ...base, v: 0.142, rhop: 985, cmd: 0.185, gsd: 1.60 }, {}],
  ['NIOSH N inertial', { ...base, v: 0.142, rhop: 2165, cmd: 0.075, gsd: 1.86 }, { inertial: true }],
  ['ISO 16890', { ...base, df: 5, L: 500, eps: 0.92, v: 0.053, rhop: 912, mono: true, cmd: 0.2, gsd: 1.5 }, {}],
  // extra cases that trigger every alert at least once
  ['thin, MPPS at edge', { ...base, L: 3, dmin: 0.06, v: 0.053, rhop: 912, cmd: 0.2, gsd: 1.5 }, {}],
  ['dense, Knudsen > 2', { ...base, df: 0.05, eps: 0.6, v: 0.053, rhop: 912, cmd: 0.2, gsd: 1.5 }, {}],
  ['mass outside range', { ...base, df: 0.1, dmax: 1, v: 0.053, rhop: 912, cmd: 0.5, gsd: 2.5 }, {}],
  ['MPPS above fibre', { ...base, df: 0.03, eps: 0.95, L: 5, v: 0.053, rhop: 912, cmd: 0.2, gsd: 1.5 }, {}],
  ['high Reynolds', { ...base, df: 20, L: 20000, eps: 0.9, v: 1.0, rhop: 912, cmd: 0.2, gsd: 1.5 }, {}],
  ['mass median given', { ...base, v: 0.053, rhop: 1000, mmd: 0.8, gsd: 2.0 }, {}],
  ['sea level legacy', { ...base, PmmHg: 760, T: 20, v: 0.1, rhop: 1000, dmin: 0.02, dmax: 5 }, { legacy: true }],
];

const scalars = ['pen', 'E', 'penN', 'pen03', 'QF03', 'penMin', 'Emin', 'mpps', 'mppsAtEdge', 'dP', 'viscShare', 'QF', 'QFmpps',
  'Ref', 'k1', 'k1Davies', 'slipFactor', 'k2', 'Ku', 'Knf', 'mu', 'rho', 'lam', 'alpha', 'd10', 'd50', 'd90', 'massOutside'];
const rowKeys = ['d', 'w', 'wn', 'cum', 'Fs', 'D', 'Pe', 'Cd', 'nD', 'R', 'Cr', 'nR', 'Stk', 'nI', 'nT', 'pen', 'E'];

const out = cases.map(([name, input, options]) => {
  const r = m.emppSimulate(input, options);
  const k = m.emppClassEN1822(r.penMin);
  const result = {};
  scalars.forEach((s) => { result[s] = r[s]; });
  return {
    name,
    input,
    options,
    result,
    rows: r.rows.map((row) => rowKeys.map((key) => (row[key] === undefined ? null : row[key]))),
    alerts: m.emppAlerts(input, r, options).map((a) => ({ level: a.level, code: a.code, text: a.text })),
    en1822: k < 0 ? null : m.EMPP_EN1822[k].cls,
    epm: m.emppEPM(r.rows),
  };
});

fs.writeFileSync(path.join(__dirname, 'reference-cases.json'),
  JSON.stringify({ generatedFrom: 'reference/03_modelo_referencia.js', rowKeys, cases: out }, null, 1) + '\n');
console.log('wrote ' + out.length + ' cases');
