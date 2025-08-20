import dayjs from 'dayjs';

export const formatNumber = (value, maxDecimals = 0, locale = 'id-ID') => {
  let number = value;

  if (number === null || number === undefined || isNaN(number)) {
    number = 0;
  }

  return new Intl.NumberFormat(locale, {
    minimumFractionDigits: maxDecimals,
    maximumFractionDigits: maxDecimals,
  }).format(number);
};

export function dateTimeFromNow(date) {
  return dayjs(date).fromNow();
}

export function plusMinusSymbol(num) {
  return num > 0 ? '+' : '';
}

export function formatNumberWithSymbol(num) {
  return plusMinusSymbol(num) + formatNumber(num);
}

export function formatDateForEditing(val) {
  return formatDate(val, 'YYYY-MM-DD');
}

export function formatDateTimeForEditing(val) {
  return formatDate(val, 'YYYY-MM-DD HH:mm:ss');
}

export function formatDateTime(val, fmt = 'DD/MM/YYYY HH:mm:ss', locale = 'id-ID') {
  let date;
  if (val instanceof Date) {
    date = val;
  }
  else if (typeof (val) === 'string') {
    date = new Date(val);
  }
  else {
    throw new Error('val must be string or Date object');
  }

  return dayjs(date).format(fmt);
}

export function formatDate(val, fmt = 'DD/MM/YYYY', locale = 'id-ID') {
  return formatDateTime(val, fmt, locale);
}

export function formatTime(val, fmt = 'HH:mm:ss', locale = 'id-ID') {
  return formatDateTime(val, fmt, locale);
}
