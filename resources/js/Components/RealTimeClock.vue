<template>
  <div class="bg-gray-50 p-4 rounded-lg">
    <div class="text-center">
      <div class="text-3xl font-bold text-blue-600 mb-1">{{ currentTime }}</div>
      <div class="text-sm text-gray-600">{{ currentDate }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import dayjs from 'dayjs';
import 'dayjs/locale/id';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.locale('id');

const currentTime = ref('');
const currentDate = ref('');

const updateDateTime = () => {
  const now = dayjs().tz('Asia/Jakarta');
  currentTime.value = now.format('HH:mm:ss');
  currentDate.value = now.format('dddd, D MMMM YYYY');
};

let timer;

onMounted(() => {
  updateDateTime();
  timer = setInterval(updateDateTime, 1000);
});

onUnmounted(() => {
  clearInterval(timer);
});
</script> 