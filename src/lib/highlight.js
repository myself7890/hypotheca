const escapeHtml = (s) =>
  s.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
const escapeRe = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

/**
 * Texte échappé avec le terme en gras à sa première occurrence ;
 * si le texte ne le contient pas, le terme est ajouté en tête.
 */
export function highlightTerm(text, term) {
  const html = escapeHtml(text ?? '');
  if (!term) return html;
  const t = escapeHtml(term);
  const re = new RegExp(`(${escapeRe(t)})`, 'i');
  return re.test(html) ? html.replace(re, '<strong>$1</strong>') : `<strong>${t}</strong> : ${html}`;
}
