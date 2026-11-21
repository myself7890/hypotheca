// Calculs hypothécaires canadiens : intérêts composés semi-annuellement (Loi sur l'intérêt).

export const FREQUENCIES = [
  { id: 'monthly', label: 'Mensuel', n: 12 },
  { id: 'semiMonthly', label: 'Bimensuel', n: 24 },
  { id: 'biweekly', label: '2 semaines', n: 26 },
  { id: 'accBiweekly', label: '2 sem. accéléré', n: 26, split: 2 },
  { id: 'weekly', label: 'Semaine', n: 52 },
  { id: 'accWeekly', label: 'Sem. accéléré', n: 52, split: 4 },
];

export const AMORTIZATIONS = [5, 10, 15, 20, 25, 30];

const round2 = (x) => Math.round(x * 100) / 100;

/** Taux par période équivalent à un taux annuel composé deux fois par année. */
export function periodicRate(annualPct, perYear) {
  return Math.pow(1 + annualPct / 100 / 2, 2 / perYear) - 1;
}

function annuity(principal, rate, periods) {
  if (periods <= 0) return 0;
  if (rate === 0) return principal / periods;
  return (principal * rate) / (1 - Math.pow(1 + rate, -periods));
}

/**
 * Paiement périodique pour un montant d'hypothèque donné.
 * Accéléré : paiement mensuel divisé par 2 (aux 2 semaines) ou 4 (chaque semaine).
 */
export function mortgagePayment({ loan, ratePct, years, frequency }) {
  const freq = FREQUENCIES.find((f) => f.id === frequency) ?? FREQUENCIES[0];
  const l = Math.max(0, Number(loan) || 0);
  const r = Math.max(0, Number(ratePct) || 0);
  const y = Math.max(1, Number(years) || 25);
  if (l === 0) return 0;
  if (freq.split) return round2(annuity(l, periodicRate(r, 12), y * 12) / freq.split);
  return round2(annuity(l, periodicRate(r, freq.n), y * freq.n));
}
