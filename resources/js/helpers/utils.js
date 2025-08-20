import { usePage } from "@inertiajs/vue3";

export const getQueryParams = (...args) => {
  const page = usePage();
  let queryString = page.url;
  if (queryString.indexOf("?") === -1) {
    return {};
  }
  queryString = queryString.substring(queryString.indexOf("?") + 1);
  return Object.assign(Object.fromEntries(new URLSearchParams(queryString)), ...args);
}

export function plusMinusSymbol(amount) {
  return amount > 0 ? '+' : '';
}

export async function scrollToFirstErrorField(ref) {
  const element = ref.getNativeElement();
  if (element) {
    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    element.focus();
  }
}
