// resources/js/Composables/useFormatter.js

// Composable adalah fungsi yang bisa kita ekspor
export function useFormatter() {

    const formatCurrency = (value) => {
        if (value === null || value === undefined) return 'Rp 0';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(value);
    };

    const formatDecimal = (value) => {
        if (value === null || value === undefined) return '0';
        return parseFloat(value).toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 8
        });
    };

    // Kembalikan semua fungsi yang ingin kita gunakan
    return { formatCurrency, formatDecimal };
}
