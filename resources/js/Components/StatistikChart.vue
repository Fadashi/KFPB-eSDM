<script setup>
import { Pie } from 'vue-chartjs'
import { Chart, ArcElement, Tooltip, Legend } from 'chart.js'

Chart.register(ArcElement, Tooltip, Legend)

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
})

const chartData = {
  labels: ['Hadir', 'Terlambat', 'Izin', 'Cuti'],
  datasets: [
    {
      backgroundColor: ['#22c55e', '#facc15', '#3b82f6', '#f87171'],
      data: [props.data.hadir, props.data.terlambat, props.data.izin, props.data.cuti]
    }
  ]
}
</script>

<template>
  <Pie :data="chartData" :options="{ responsive: true, plugins: { legend: { position: 'bottom' } } }" />
</template> 