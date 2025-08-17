import { ref } from 'vue';

export function useTransactionCategoryFilter(rawCategories, includeAllOption = false) {
  const baseCategories = rawCategories.map(item => ({
    value: item.id,
    label: item.name
  }));

  const categories = includeAllOption
    ? [{ value: 'all', label: 'Semua' }, ...baseCategories]
    : baseCategories;

  const filteredCategories = ref([...categories]);

  const filterCategories = (val, update) => {
    const search = val.toLowerCase();
    update(() => {
      filteredCategories.value = categories.filter(item =>
        item.label.toLowerCase().includes(search)
      );
    });
  };

  return {
    filteredCategories,
    filterCategories,
    categories // jika butuh juga yang belum difilter
  };
}
