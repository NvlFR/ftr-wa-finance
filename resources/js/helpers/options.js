export function createYearOptions(startYear, endYear) {
  const years = [];
  for (let i = startYear; i <= endYear; i++) {
    years.push({ value: i, label: i });
  }
  return years;
}


export function createMonthOptions() {
  return [
    { value: 1, label: "Januari" },
    { value: 2, label: "Februari" },
    { value: 3, label: "Maret" },
    { value: 4, label: "April" },
    { value: 5, label: "Mei" },
    { value: 6, label: "Juni" },
    { value: 7, label: "Juli" },
    { value: 8, label: "Agustus" },
    { value: 9, label: "September" },
    { value: 10, label: "Oktober" },
    { value: 11, label: "November" },
    { value: 12, label: "Desember" },
  ];
}

export function createOptions(data) {
  return Object.entries(data)
    .map(([key, value]) => ({ 'value': key, 'label': value }));
}

export function createBillPeriodOptions(includeAllOptions = false) {
  if (includeAllOptions) {
    return [
      { value: "all", label: "Semua" },
      ...createOptions(window.CONSTANTS.PRODUCT_BILL_PERIODS),
    ];
  }

  return createOptions(window.CONSTANTS.PRODUCT_BILL_PERIODS);
}
