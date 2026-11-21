// Calculs hypothécaires canadiens : intérêts composés semi-annuellement (Loi sur l'intérêt).

export const FREQUENCIES = [
  { id: 'monthly', label: 'Mensuelle', per: 'par mois', n: 12 },
  { id: 'semiMonthly', label: 'Bimensuelle (2 fois par mois)', per: 'deux fois par mois', n: 24 },
  { id: 'biweekly', label: 'Aux deux semaines', per: 'aux deux semaines', n: 26 },
  { id: 'accBiweekly', label: 'Aux deux semaines accélérée', per: 'aux deux semaines', n: 26, split: 2 },
  { id: 'weekly', label: 'Hebdomadaire', per: 'par semaine', n: 52 },
  { id: 'accWeekly', label: 'Hebdomadaire accélérée', per: 'par semaine', n: 52, split: 4 },
];

export const AMORTIZATIONS = [5, 10, 15, 20, 25, 30];

// Prix au-delà duquel l'assurance prêt hypothécaire n'est plus offerte (20 % de mise de fonds requis).
export const INSURED_PRICE_CAP = 1_500_000;

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

/** Mise de fonds minimale au Canada selon le prix. */
export function minimumDownPayment(price) {
  if (price >= INSURED_PRICE_CAP) return price * 0.2;
  if (price <= 500_000) return price * 0.05;
  return 25_000 + (price - 500_000) * 0.1;
}

/** Taux de prime d'assurance (SCHL) selon la mise de fonds, en fraction du prêt. */
export function insurancePremiumRate(price, down, years) {
  if (price <= 0 || down / price >= 0.2 || price >= INSURED_PRICE_CAP) return 0;
  const ratio = down / price;
  const base = ratio >= 0.15 ? 0.028 : ratio >= 0.1 ? 0.031 : 0.04;
  // Supplément de 0,20 % pour un amortissement de plus de 25 ans.
  return base + (years > 25 ? 0.002 : 0);
}

/**
 * @returns {{ payment, principal, premium, loan, totalInterest, totalCost, insured, minDown }}
 */
export function computeMortgage({ price, down, ratePct, years, frequency }) {
  const freq = FREQUENCIES.find((f) => f.id === frequency) ?? FREQUENCIES[0];
  const p = Math.max(0, Number(price) || 0);
  const d = Math.min(p, Math.max(0, Number(down) || 0));
  const r = Math.max(0, Number(ratePct) || 0);
  const y = Math.max(1, Number(years) || 25);

  const principal = p - d;
  const premiumRate = insurancePremiumRate(p, d, y);
  const premium = Math.round(principal * premiumRate);
  const loan = principal + premium;

  let payment;
  let periods;
  if (freq.split) {
    // Accéléré : paiement mensuel divisé (2 ou 4), versé 26 ou 52 fois par année.
    payment = round2(annuity(loan, periodicRate(r, 12), y * 12) / freq.split);
    const i = periodicRate(r, freq.n);
    periods = i === 0 ? loan / payment : -Math.log(1 - (loan * i) / payment) / Math.log(1 + i);
  } else {
    periods = y * freq.n;
    payment = round2(annuity(loan, periodicRate(r, freq.n), periods));
  }

  const totalPaid = loan > 0 ? payment * periods : 0;
  const totalInterest = Math.max(0, Math.round(totalPaid - loan));

  return {
    frequency: freq,
    payment: loan > 0 ? payment : 0,
    principal,
    premium,
    loan,
    totalInterest,
    totalCost: loan + totalInterest,
    insured: premiumRate > 0,
    minDown: minimumDownPayment(p),
    years: round2(periods / freq.n),
  };
}
