<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    type: { type: String, default: 'line' },
    data: { type: Object, required: true },
    options: { type: Object, default: () => ({}) }
});

const chartRef = ref(null);
let chartInstance = null;

onMounted(() => {
    renderChart();
});

onUnmounted(() => {
    if (chartInstance) chartInstance.destroy();
});

watch(() => props.data, (newData) => {
    if (chartInstance) {
        chartInstance.data = newData;
        chartInstance.update();
    }
}, { deep: true });

watch(() => props.type, () => {
    if (chartInstance) {
        chartInstance.destroy();
        renderChart();
    }
});

const renderChart = () => {
    if (chartRef.value) {
        chartInstance = new Chart(chartRef.value, {
            type: props.type,
            data: props.data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: !!props.options.title, text: props.options.title || '' }
                },
                ...props.options
            }
        });
    }
};
</script>

<template>
  <div style="height:250px;">
    <canvas ref="chartRef"></canvas>
  </div>
</template>
