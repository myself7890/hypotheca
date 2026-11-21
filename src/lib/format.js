const money0 = new Intl.NumberFormat('fr-CA', { style: 'currency', currency: 'CAD', maximumFractionDigits: 0 });
const money2 = new Intl.NumberFormat('fr-CA', { style: 'currency', currency: 'CAD', minimumFractionDigits: 2, maximumFractionDigits: 2 });
const pct2 = new Intl.NumberFormat('fr-CA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const pct1 = new Intl.NumberFormat('fr-CA', { minimumFractionDigits: 1, maximumFractionDigits: 1 });

/** 400 000 $ */
export const money = (x) => money0.format(x);
/** 2 326,42 $ */
export const moneyCents = (x) => money2.format(x);
/** 4,79 % */
export const percent = (x, digits = 2) => `${(digits === 1 ? pct1 : pct2).format(x)} %`;
