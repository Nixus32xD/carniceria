<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

import ChartComponent from '@/Components/ChartComponent.vue';

const props = defineProps({
    analyticsData: Object,
    trends: Object,
    comparisons: Object
});

const selectedMetric = ref('revenue');
const comparisonPeriod = ref('last_month');
const chartType = ref('line');
const timeFrame = ref('daily');

// Métricas y productos más vendidos desde backend
const metrics = computed(() => props.analyticsData.metrics);
const topProducts = computed(() => props.analyticsData.topProducts);

// Comparaciones dinámicas según período seleccionado
const currentComparison = computed(() => props.comparisons[comparisonPeriod.value]);

// Preparar datos para el gráfico
const chartData = computed(() => {
    const trendData = props.trends[timeFrame.value] || [];

    if (trendData.length === 0) {
        return { labels: [], datasets: [] };
    }

    const labels = trendData.map(item =>
        timeFrame.value === 'daily'
            ? new Date(item.date).toLocaleDateString()
            : item.period
    );

    const isBar = chartType.value === 'bar';

    // Convertir valores a números puros
    return {
        labels,
        datasets: [
            {
                label: 'Ingresos',
                data: trendData.map(item => Number(item.revenue)),
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: isBar ? 'rgb(239, 68, 68)' : 'rgba(239, 68, 68, 0.2)',
                tension: 0.3,
                fill: !isBar,
                yAxisID: 'y' // eje principal
            },
            {
                label: 'Pedidos',
                data: trendData.map(item => Number(item.orders)),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: isBar ? 'rgb(59, 130, 246)' : 'rgba(59, 130, 246, 0.2)',
                tension: 0.3,
                fill: !isBar,
                yAxisID: 'y1' // eje secundario
            }
        ]
    };
});

// Opciones del gráfico con eje Y secundario
const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: `Tendencias de ${timeFrame.value === 'daily' ? 'Últimos 30 Días' : 'Último Año'}`
        }
    },
    scales: {
        y: {
            type: 'linear',
            position: 'left',
            beginAtZero: true,
            ticks: {
                callback: function (value) {
                    return '$' + value.toLocaleString();
                }
            }
        },
        y1: {
            type: 'linear',
            position: 'right',
            beginAtZero: true,
            grid: { drawOnChartArea: false },
            ticks: { stepSize: 1 } // opcional, para que los pedidos se vean claramente
        }
    }
}));
</script>


<template>

    <Head title="Estadísticas Avanzadas - Carnicería" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Estadísticas Avanzadas
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Métricas generales -->
                <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
                    <div v-for="(metric, key) in metrics" :key="key"
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4"
                        :class="`border-${metric.color}-500`">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ metric.label }}
                                    </p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ key === 'revenue' || key === 'avgOrder' ? '$' : '' }}{{
                                            metric.value.toLocaleString()
                                        }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-semibold text-green-600">{{ metric.change }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos y análisis -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Top Products -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Productos Más Vendidos
                            </h3>
                            <div class="space-y-4">
                                <div v-for="(product, index) in topProducts" :key="product.name"
                                    class="flex items-center space-x-4">
                                    <div
                                        class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 font-bold text-sm">{{ index + 1 }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ product.name }}
                                        </p>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                                <div class="bg-gradient-to-r from-red-400 to-red-600 h-2 rounded-full"
                                                    :style="`width: ${(product.sales / 100) * 100}%`"></div>
                                            </div>
                                            <span class="text-xs text-gray-500">{{ product.sales }} ventas</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-red-600">${{ product.revenue }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Análisis de Tendencias
                                </h3>
                                <div class="flex space-x-2">
                                    <select v-model="timeFrame"
                                        class="text-sm border-gray-300 rounded-md focus:border-red-500 focus:ring-red-500">
                                        <option value="daily">Últimos 30 días</option>
                                        <option value="monthly">Último año</option>
                                    </select>
                                    <select v-model="chartType"
                                        class="text-sm border-gray-300 rounded-md focus:border-red-500 focus:ring-red-500">
                                        <option value="line">Línea</option>
                                        <option value="bar">Barras</option>
                                    </select>
                                </div>
                            </div>
                            <div class="h-64">
                                <ChartComponent :type="chartType" :data="chartData" :options="chartOptions" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comparaciones -->
                <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Análisis Detallado por
                                Período</h3>
                            <select v-model="comparisonPeriod"
                                class="text-sm border-gray-300 rounded-md focus:border-red-500 focus:ring-red-500">
                                <option value="last_week">Última Semana</option>
                                <option value="last_month">Último Mes</option>
                                <option value="last_quarter">Último Trimestre</option>
                            </select>
                        </div>

                        <div v-if="currentComparison" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center p-4 bg-red-50 dark:bg-red-900 rounded-lg">
                                <div class="text-2xl font-bold text-red-600">${{
                                    currentComparison.revenue.toLocaleString() }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">Ingresos Totales</div>
                                <div class="text-xs text-green-600 mt-1">↗ {{ currentComparison.revenueChange }}% vs
                                    período anterior</div>
                            </div>
                            <div class="text-center p-4 bg-red-50 dark:bg-red-900 rounded-lg">
                                <div class="text-2xl font-bold text-red-600">{{ currentComparison.orders }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">Pedidos Procesados</div>
                            </div>
                            <div class="text-center p-4 bg-red-50 dark:bg-red-900 rounded-lg">
                                <div class="text-2xl font-bold text-red-600">
                                    ${{ currentComparison.avgOrder.toFixed(2) }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">Ticket Promedio</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
