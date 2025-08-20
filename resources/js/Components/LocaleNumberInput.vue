<template>
  <q-input
    :model-value="displayValue"
    :label="props.label"
    :outlined="props.outlined"
    @update:model-value="onInput"
    @blur="onBlur"
    @keydown="filterInput"
    :lazy-rules="lazyRules"
    :disable="disable"
    :error="error"
    :rules="rules"
    :error-message="errorMessage"
  />
</template>

<script setup>
import { ref, watch } from "vue";

// Props
const props = defineProps({
  modelValue: { type: Number, required: true, default: 0 }, // Always expect a Number
  label: { type: String, default: "" },
  locale: { type: String, default: "id-ID" },
  outlined: { type: Boolean, default: false },
  allowNegative: { type: Boolean, default: false },
  maxDecimals: { type: Number, default: 0 },
  lazyRules: { type: String },
  disable: { type: Boolean, default: false },
  error: { type: Boolean, default: false },
  errorMessage: { type: String, default: "" },
  rules: { type: Array, default: [] },
});

// Emit events
const emit = defineEmits(["update:modelValue"]);

// Internal state for display value
const displayValue = ref("");

// Detect locale's decimal and thousand separators
const getLocaleSeparators = (locale) => {
  const sampleNumber = 1234567.89;
  const formatted = new Intl.NumberFormat(locale).format(sampleNumber);
  const [integerPart, decimalPart] = formatted.split(".");

  const decimalSeparator = decimalPart
    ? formatted[formatted.indexOf(decimalPart[0])]
    : ".";
  const thousandSeparator = formatted[integerPart.length] === "," ? "," : ".";

  return { decimalSeparator, thousandSeparator };
};

const { decimalSeparator, thousandSeparator } = getLocaleSeparators(
  props.locale
);

// Format a number according to the locale
const formatNumber = (value) => {
  let number = value;

  if (number === null || number === undefined || isNaN(number)) {
    number = 0;
  }

  return new Intl.NumberFormat(props.locale, {
    minimumFractionDigits: props.maxDecimals,
    maximumFractionDigits: props.maxDecimals,
  }).format(number);
};

displayValue.value = formatNumber(props.modelValue);

// Watch for changes in modelValue and sync displayValue
watch(
  () => props.modelValue,
  (newValue) => {
    displayValue.value = formatNumber(newValue);
  },
  { immediate: true }
);

// Sanitize input and convert to a valid number
const sanitizeInput = (value) => {
  // Regex for valid input
  const regex = props.allowNegative
    ? /^-?[0-9]+([.,][0-9]*)?$/
    : /^[0-9]+([.,][0-9]*)?$/;

  // Remove invalid characters and normalize decimal separator
  const sanitized = value
    .replace(/[^0-9.,-]+/g, "") // Remove unwanted characters
    .replace(new RegExp(`\\${thousandSeparator}`, "g"), "") // Remove thousand separator
    .replace(new RegExp(`\\${decimalSeparator}`, "g"), "."); // Replace decimal separator

  if (!regex.test(sanitized)) {
    return props.modelValue || 0; // Fallback to current modelValue if input is invalid
  }

  const parsed = parseFloat(sanitized);
  return isNaN(parsed) ? 0 : parseFloat(parsed.toFixed(props.maxDecimals));
};

const emitUpdateModelValue = () => {
  const sanitizedValue = sanitizeInput(displayValue.value);
  emit("update:modelValue", sanitizedValue); // Emit as Number
};

// Update display value on input
const onInput = (value) => {
  displayValue.value = value; // Update the input field
  emitUpdateModelValue();
};

// Emit sanitized value on blur
const onBlur = () => {
  displayValue.value = formatNumber(sanitizeInput(displayValue.value)); // Format the input
  emitUpdateModelValue();
};

// Filter keyboard input
const filterInput = (event) => {
  const allowedKeys = [
    "Backspace",
    "Delete",
    "Tab",
    "ArrowLeft",
    "ArrowRight",
    "Home",
    "End",
  ];

  if (event.ctrlKey || event.metaKey) return;
  if (event.key >= "0" && event.key <= "9") return;
  if (
    (event.key === decimalSeparator || event.key === thousandSeparator) &&
    !displayValue.value.includes(decimalSeparator)
  )
    return;
  if (
    props.allowNegative &&
    event.key === "-" &&
    event.target.selectionStart === 0
  )
    return;
  if (allowedKeys.includes(event.key)) return;

  event.preventDefault(); // Block other keys
};
</script>
