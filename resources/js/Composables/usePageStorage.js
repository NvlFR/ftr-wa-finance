// resources/js/helpers/usePageStorage.js
export function usePageStorage(prefix) {
  const makeKey = (key) => `${prefix}.${key}`;

  return {
    get(key, fallback = null) {
      try {
        const value = localStorage.getItem(makeKey(key));
        return value ? JSON.parse(value) : fallback;
      } catch (e) {
        return fallback;
      }
    },
    set(key, value) {
      localStorage.setItem(makeKey(key), JSON.stringify(value));
    },
    clear() {
      Object.keys(localStorage)
        .filter(k => k.startsWith(`${prefix}.`))
        .forEach(k => localStorage.removeItem(k));
    }
  }
}
