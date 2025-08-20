<template>
  <q-input
    v-model="pickedDatetime"
    :label="props.label"
    :readonly="props.readonly"
    :disable="props.disable"
    :error="props.error"
    :rules="[...props.rules]"
    :error-message="errorMessage"
    :mask="props.mask"
  >
    <template v-slot:append>
      <!-- Date Picker Icon -->
      <q-icon name="event" class="cursor-pointer">
        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
          <q-date
            v-model="dateValue"
            mask="YYYY-MM-DD"
            @update:model-value="updateDateTime"
          >
            <div class="row items-center justify-end">
              <q-btn v-close-popup label="Close" color="primary" flat />
            </div>
          </q-date>
        </q-popup-proxy>
      </q-icon>

      <!-- Time Picker Icon -->
      <q-icon name="access_time" class="cursor-pointer">
        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
          <q-time
            v-model="timeValue"
            mask="HH:mm"
            format24h
            @update:model-value="updateDateTime"
          >
            <div class="row items-center justify-end">
              <q-btn v-close-popup label="Close" color="primary" flat />
            </div>
          </q-time>
        </q-popup-proxy>
      </q-icon>
    </template>
  </q-input>
</template>

<script setup>
import { ref, watch } from "vue";

// Props passed from the parent
const props = defineProps({
  modelValue: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    default: "",
  },
  readonly: {
    type: Boolean,
    default: false,
  },
  disable: {
    type: Boolean,
    default: false,
  },
  error: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: "",
  },
  rules: {
    type: Array,
    default: [],
  },
  mask: {
    type: String,
    default: "####-##-## ##:##",
  },
});

// Emits to update parent value
const emit = defineEmits(["update:modelValue"]);

// Internal states for date and time
const dateValue = ref("");
const timeValue = ref("00:00");

// Watch for changes to modelValue (pickedDatetime) and sync with internal states
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue) {
      // Gunakan objek Date untuk mengurai string, termasuk yang memiliki timezone
      const dateObj = new Date(newValue);

      // Dapatkan tanggal dalam format YYYY-MM-DD
      const year = dateObj.getFullYear();
      const month = String(dateObj.getMonth() + 1).padStart(2, "0");
      const day = String(dateObj.getDate()).padStart(2, "0");
      dateValue.value = `${year}-${month}-${day}`;

      // Dapatkan waktu dalam format HH:mm
      const hours = String(dateObj.getHours()).padStart(2, "0");
      const minutes = String(dateObj.getMinutes()).padStart(2, "0");
      timeValue.value = `${hours}:${minutes}`;
    }
  }
);

// Use the combined pickedDatetime for q-input value
const pickedDatetime = ref(props.modelValue);

// Update modelValue when either date or time changes
const updateDateTime = () => {
  let val = "";
  if (dateValue.value && timeValue.value) {
    val = `${dateValue.value} ${timeValue.value}`;
  } else if (dateValue.value) {
    val = `${dateValue.value} 00:00`;
  }

  emit("update:modelValue", val);
  pickedDatetime.value = val;
};
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>
