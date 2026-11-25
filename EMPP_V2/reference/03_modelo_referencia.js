/* EMPP — modelo de referência da simulação de filtração (teoria de fibra única), versão 2.
   Unidades de entrada: T °C | PmmHg mmHg | df µm | L µm | eps fração (0–1) | v m/s | rhop kg/m³
                        dmin, dmax µm (faixa da curva fracionária)
                        cmd µm, gsd: aerossol lognormal dado pelo diâmetro mediano em NÚMERO, como nas normas
                        (ou mmd µm, gsd, se a mediana em massa for conhecida); mono: true = ensaio no MPPS (EN 1822).
   Opções: legacy (planilha), inertial: true soma o termo de Forchheimer estimado (padrão: só Darcy com escorregamento).
   opts.legacy = true reproduz a planilha "Filter simulation - Fiber mat - Rachel-Everton - 2025.xlsx" (para conferência).

   Diferenças da versão 2 em relação à planilha:
   M1  π no expoente da penetração: P = exp(-4·α·ηT·L / (π·(1-α)·df))                 [Payet et al. 1992, eq. 1-2]
   M2  difusão com o fator C'd de Payet, que mantém ηD < 1                              [Payet et al. 1992, eq. 11-12]
   M3  perda de carga com escorregamento na fibra: k1 = k1(Davies) / fator de arraste   [Davies 1973; Pich 1966 / Brown 1993]
   M4  aerossol lognormal (CMD, GSD); MMD = CMD·exp(3·ln²GSD) (Hatch-Choate); peso de cada classe = diferença
       da função acumulada entre os limites da classe; eficiência global em massa (pen) e em número (penN)
   M5  d10, d50, d90 em massa calculados da própria distribuição
   M6  perda de carga de Darcy (linear na velocidade); termo inercial só como opção
   M7  ePM1, ePM2,5 e ePM10 pela ponderação da ISO 16890-1 (emppEPM)                      */
function emppErf(x) { var s = x < 0 ? -1 : 1; x = Math.abs(x); var t = 1 / (1 + 0.3275911 * x);
  return s * (1 - (((((1.061405429 * t - 1.453152027) * t) + 1.421413741) * t - 0.284496736) * t + 0.254829592) * t * Math.exp(-x * x)); }
function emppAir(Tc, PmmHg) {
  var T = Tc + 273, P = PmmHg / 760 * 101325, mu = 1.73e-5 * Math.pow(T / 273, 1.5) * (398 / (Tc + 398));
  return { T: T, P: P, mu: mu, rho: P * 0.028965 / (8.314 * T), lam: 21.2255 * mu * Math.sqrt(T) / P };
}
function emppSimulate(i, opts) {
  opts = opts || {}; var legacy = !!opts.legacy;
  var kB = 1.38065e-23, air = emppAir(i.T, i.PmmHg), T = air.T, mu = air.mu, rho = air.rho, lam = air.lam;
  var df = i.df * 1e-6, L = i.L * 1e-6, e = i.eps, a = 1 - e;
  var Ku = -Math.log(a) / 2 - 0.75 + a - a * a / 4, Knf = 2 * lam / df;
  var k1Davies = df * df / (64 * Math.pow(a, 1.5) * (1 + 56 * a * a * a));           // Davies, regime contínuo
  var slip = (1 + 1.996 * Knf) * Ku / (Ku + 1.996 * Knf * (-0.5 * Math.log(a) - 0.25 + 0.25 * a * a)); // Δp(com escorregamento)/Δp(sem)
  var k1 = legacy ? k1Davies : k1Davies / slip;
  var k2 = Math.exp(-1.71588 * Math.pow(k1Davies, -0.08093));                        // termo inercial (correlação empírica)
  var n = i.classes || 100, rows = [], j, sum = 0, lo = Math.log(i.dmin), hi = Math.log(i.dmax), outside = 0;
  if (legacy) {                                                                      // Rosin-Rammler definida pela faixa, pesos com Δd constante
    var arr = Math.log(1e8 / 1.00000001) / Math.log(i.dmax / i.dmin), dm = i.dmax / Math.pow(Math.log(1e8), 1 / arr);
    for (j = 0; j <= n; j++) { var d0 = Math.exp(lo + j * (hi - lo) / n), w0 = arr / dm * Math.pow(d0 / dm, arr - 1) * Math.exp(-Math.pow(d0 / dm, arr)); rows.push({ d: d0, w: w0 }); if (j < n) sum += w0; }
  } else {                                                                           // lognormal, n classes com limites em escala log
    var lg = Math.log(i.gsd), mmd = i.mmd || i.cmd * Math.exp(3 * lg * lg), cmd = i.cmd || mmd * Math.exp(-3 * lg * lg), sumN = 0;
    var F = function (d, med) { return 0.5 * (1 + emppErf(Math.log(d / med) / (Math.SQRT2 * lg))); };
    for (j = 0; j < n; j++) { var x0 = lo + j * (hi - lo) / n, x1 = lo + (j + 1) * (hi - lo) / n, w = F(Math.exp(x1), mmd) - F(Math.exp(x0), mmd), wn = F(Math.exp(x1), cmd) - F(Math.exp(x0), cmd); rows.push({ d: Math.exp((x0 + x1) / 2), w: w, wn: wn }); sum += w; sumN += wn; }
    outside = 1 - sum;                                                               // fração mássica fora da faixa dmin–dmax
  }
  var geo = 4 * a / (1 - a) * L / df / (legacy ? 1 : Math.PI);
  var pen = 0, penN = 0, cum = 0, Emin = 2, mpps = 0, mi = 0;
  rows.forEach(function (r, idx) {
    r.w /= sum; cum += r.w; r.cum = cum; if (!legacy) r.wn /= sumN;
    var dp = r.d * 1e-6;
    r.Fs = 1 + lam / dp * (2.33 + 0.966 * Math.exp(-0.4985 * dp / lam));             // fator de escorregamento da partícula
    r.D = kB * T * r.Fs / (3 * Math.PI * mu * dp);
    r.Pe = df * i.v / r.D;
    r.Cd = 1 + 0.388 * Knf * Math.cbrt(e * r.Pe / Ku);
    var x = 1.6 * Math.cbrt(e / Ku) * Math.pow(r.Pe, -2 / 3) * r.Cd;
    r.nD = legacy ? x : x / (1 + x);                                                 // M2
    r.R = dp / df; r.Cr = 1 + 1.996 * Knf / r.R;
    r.nR = Math.min(1, 0.6 * (e / Ku) * r.R * r.R / (1 + r.R) * r.Cr);
    r.Stk = i.rhop * i.v * r.Fs * dp * dp / (9 * mu * df);
    r.nI = Math.pow(r.Stk, 3) / (Math.pow(r.Stk, 3) + 0.77 * r.Stk * r.Stk + 0.22);
    r.nT = 1 - (1 - r.nD) * (1 - r.nR) * (1 - r.nI);
    r.pen = Math.exp(-geo * r.nT); r.E = 1 - r.pen;                                  // guardar a penetração preserva a precisão perto de 100 %
    pen += r.pen * r.w; penN += r.pen * (legacy ? r.w : r.wn);
    if (r.E < Emin) { Emin = r.E; mpps = r.d; mi = idx; }
  });
  var q = function (p) { for (var k = 0; k < rows.length; k++) if (rows[k].cum >= p) return rows[k].d; return rows[rows.length - 1].d; };
  var useInert = legacy || opts.inertial === true;
  var dPv = mu / k1 * i.v * L, dPi = useInert ? rho / k2 * i.v * i.v * L : 0, dP = dPv + dPi;
  if (i.mono) { pen = rows[mi].pen; penN = pen; }                                    // ensaio no MPPS: eficiência integral = eficiência no MPPS
  var p03 = null; rows.forEach(function (r) { if (p03 === null && r.d >= 0.3) p03 = r.pen; });
  var Ref = rho * i.v * df / mu;                                                     // Reynolds da fibra
  return {
    pen: pen, E: 1 - pen, penN: penN, pen03: p03, QF03: p03 === null ? null : -Math.log(Math.max(p03, 1e-300)) / dP, penMin: rows[mi].pen, Emin: Emin, mpps: mpps, mppsAtEdge: mi === 0 || mi === rows.length - 1,
    dP: dP, viscShare: dPv / dP, QF: -Math.log(Math.max(pen, 1e-300)) / dP, QFmpps: -Math.log(Math.max(rows[mi].pen, 1e-300)) / dP,
    Ref: Ref, k1: k1, k1Davies: k1Davies, slipFactor: slip, k2: k2, Ku: Ku, Knf: Knf, mu: mu, rho: rho, lam: lam, alpha: a,
    d10: q(0.1), d50: q(0.5), d90: q(0.9), massOutside: outside, rows: rows
  };
}

/* Alertas de validade. Devolve [{level:'info'|'warn', code, text}] para exibir na tela e no relatório. */
function emppAlerts(i, r, opts) {
  var A = [], a = 1 - i.eps, add = function (level, code, text) { A.push({ level: level, code: code, text: text }); };
  if (opts && opts.legacy) add('warn', 'LEGACY', 'Legacy spreadsheet equations in use: no π in the penetration exponent, no slip in the pressure drop, size distribution set by the range. Use only to check against the spreadsheet.');
  if (a > 0.3 || a < 0.006) add('warn', 'ALPHA', 'Packing density ' + a.toFixed(3) + ' is outside the range of the Davies permeability correlation (0.006–0.3).');
  if (r.Knf > 0.25) add('info', 'SLIP', 'Fibre Knudsen number ' + r.Knf.toFixed(2) + ': slip flow at the fibre. Pressure drop is reduced by a slip factor of ' + r.slipFactor.toFixed(2) + ' relative to the continuum value.');
  if (r.Knf > 2) add('warn', 'KNF', 'Fibre Knudsen number above 2: transition regime, beyond the range of the slip corrections used.');
  if (i.L / i.df < 20) add('warn', 'THIN', 'The membrane is only ' + (i.L / i.df).toFixed(0) + ' fibre diameters thick. A uniform-bed model is less reliable for so few fibre layers; local thin spots will raise the real penetration.');
  if (r.mppsAtEdge) add('warn', 'EDGE', 'The lowest efficiency is at the edge of the size range, so the true MPPS may lie outside it. Widen the range before reading a class.');
  if (r.mpps > i.df) add('info', 'R1', 'The MPPS (' + (r.mpps * 1000).toFixed(0) + ' nm) is larger than the fibre diameter. The interception correlation was fitted for particles smaller than the fibre and is extrapolated here.');
  if (r.massOutside > 0.01) add('warn', 'MASS', (r.massOutside * 100).toFixed(1) + ' % of the aerosol mass lies outside the size range and is left out of the overall efficiency.');
  if (r.penMin < 1e-9) add('info', 'SAT', 'Penetration below 10⁻⁹ at every size: differences at this level are beyond what the model, or a measurement, can resolve.');
  if (r.viscShare < 0.9) add('warn', 'INERT', 'The estimated inertial (Forchheimer) term is ' + ((1 - r.viscShare) * 100).toFixed(0) + ' % of the pressure drop at a fibre Reynolds number of ' + r.Ref.toExponential(1) + '. It comes from an empirical k₁–k₂ correlation extrapolated to nanofibre media. Confirm with Δp measured at several velocities (Validation).');
  if (r.Ref > 0.5) add('warn', 'RE', 'Fibre Reynolds number ' + r.Ref.toFixed(2) + ': inertial losses may no longer be negligible and the Darcy pressure drop can underestimate the real one.');
  else if (r.viscShare >= 0.999) add('info', 'DARCY', 'Pressure drop is linear in velocity (Darcy flow with slip). Fibre Reynolds number ' + r.Ref.toExponential(1) + ', far below 1.');
  add('info', 'BASIS', 'Clean, uniform, uncharged filter: no electrostatic capture, no dust loading, no particle bounce, one mean fibre diameter. Real membranes with a wide diameter distribution or uneven thickness usually show lower efficiency and lower pressure drop.');
  return A;
}

/* Classes EN 1822-1 / ISO 29463: penetração máxima no MPPS (valor integral). */
var EMPP_EN1822 = [
  { cls: 'E10', group: 'EPA', minEff: 85, maxPen: 0.15 }, { cls: 'E11', group: 'EPA', minEff: 95, maxPen: 0.05 },
  { cls: 'E12', group: 'EPA', minEff: 99.5, maxPen: 0.005 }, { cls: 'H13', group: 'HEPA', minEff: 99.95, maxPen: 5e-4 },
  { cls: 'H14', group: 'HEPA', minEff: 99.995, maxPen: 5e-5 }, { cls: 'U15', group: 'ULPA', minEff: 99.9995, maxPen: 5e-6 },
  { cls: 'U16', group: 'ULPA', minEff: 99.99995, maxPen: 5e-7 }, { cls: 'U17', group: 'ULPA', minEff: 99.999995, maxPen: 5e-8 }
];
function emppClassEN1822(penAtMpps) { var k = -1; EMPP_EN1822.forEach(function (c, i) { if (penAtMpps <= c.maxPen) k = i; }); return k; }
/* Níveis de eficiência de respiradores (somente o critério de eficiência do meio filtrante). */
var EMPP_RESP = [
  { name: 'FFP1', std: 'EN 149', minEff: 80 }, { name: 'FFP2', std: 'EN 149', minEff: 94 }, { name: 'N95', std: 'NIOSH 42 CFR 84', minEff: 95 },
  { name: 'FFP3', std: 'EN 149', minEff: 99 }, { name: 'N99', std: 'NIOSH 42 CFR 84', minEff: 99 }, { name: 'N100', std: 'NIOSH 42 CFR 84', minEff: 99.97 }
];
/* ISO 16890-1: eficiências ePMx ponderadas pelas distribuições volumétricas padronizadas (urbana: ePM1 e ePM2,5; rural: ePM10).
   Exige curva fracionária cobrindo 0,3–10 µm em classes de largura logarítmica constante (é o caso de emppSimulate). */
var EMPP_ISO16890 = { urban: { dA: 0.3, sA: 2.2, dB: 10, sB: 3.1, y: 0.45 }, rural: { dA: 0.25, sA: 2.2, dB: 11, sB: 4.0, y: 0.18 } };
function emppEPM(rows) {
  var f = function (d, d50, s) { var l = Math.log(s); return Math.exp(-Math.pow(Math.log(d / d50), 2) / (2 * l * l)) / (l * Math.sqrt(2 * Math.PI)); };
  var q = function (d, p) { return p.y * f(d, p.dA, p.sA) + (1 - p.y) * f(d, p.dB, p.sB); };
  var e = function (xmax, p) { var a = 0, b = 0; rows.forEach(function (r) { if (r.d >= 0.3 && r.d <= xmax) { var w = q(r.d, p); a += (1 - r.pen) * w; b += w; } }); return b > 0 ? 100 * a / b : null; };
  var o = { ePM1: e(1, EMPP_ISO16890.urban), ePM25: e(2.5, EMPP_ISO16890.urban), ePM10: e(10, EMPP_ISO16890.rural) };
  var down5 = function (x) { return Math.min(95, Math.floor(x / 5) * 5); };
  if (o.ePM1 === null || o.ePM10 === null) o.group = null;
  else if (o.ePM1 > 99) o.group = 'Above the scope of ISO 16890 (ePM1 > 99 %)';
  else if (o.ePM1 >= 50) o.group = 'ISO ePM1 ' + down5(o.ePM1) + ' %';
  else if (o.ePM25 >= 50) o.group = 'ISO ePM2,5 ' + down5(o.ePM25) + ' %';
  else if (o.ePM10 >= 50) o.group = 'ISO ePM10 ' + down5(o.ePM10) + ' %';
  else o.group = 'ISO Coarse';
  return o;
}
if (typeof module !== 'undefined') module.exports = { emppEPM: emppEPM, emppSimulate: emppSimulate, emppAlerts: emppAlerts, emppAir: emppAir, EMPP_EN1822: EMPP_EN1822, emppClassEN1822: emppClassEN1822, EMPP_RESP: EMPP_RESP };
