<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ChartComponent from '@/Components/ChartComponent.vue';

const props = defineProps({
    salesData: Object,      // { daily: {labels:[], values:[]}, monthly:{...}, recentOrders:[] }
    productsData: Array,    // [{ name: 'Carne', sales: 40 }, ...]
    revenueData: Object,    // { daily: 1234, monthly: 5678 }
    topProducts: Array,      // [{ name: 'Bife', sales: 25 }, ...]
    uniqueCustomers: Number // número de clientes únicos atendidos
});

// Filtros
const chartType = ref('bar');
const timeRange = ref('daily');
const selectedProduct = ref('all');

// Data para el gráfico (formato Chart.js)
const chartData = computed(() => {
    const source = timeRange.value === 'daily'
        ? props.salesData.daily
        : props.salesData.monthly;

    // Paleta de colores (puedes ampliarla si tienes más datos)
    const colors = [
        'rgba(239, 68, 68, 0.7)',   // rojo
        'rgba(59, 130, 246, 0.7)',  // azul
        'rgba(16, 185, 129, 0.7)',  // verde
        'rgba(245, 158, 11, 0.7)',  // amarillo
        'rgba(139, 92, 246, 0.7)',  // violeta
        'rgba(236, 72, 153, 0.7)',  // rosa
        'rgba(34, 197, 94, 0.7)'    // verde esmeralda
    ];

    return {
        labels: source.labels ?? [],
        datasets: [
            {
                label: timeRange.value === 'daily' ? 'Ventas Diarias' : 'Ventas Mensuales',
                data: source.values ?? [],
                backgroundColor: source.values?.map((_, i) => colors[i % colors.length]),
                borderColor: source.values?.map((_, i) => colors[i % colors.length].replace('0.7', '1')),
                borderWidth: 2
            }
        ]
    };
});

// Opciones para el gráfico
const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    scales: chartType.value === 'pie' ? {} : {
        y: { beginAtZero: true }
    }
}));

// Totales
const totalRevenue = computed(() => {
    return timeRange.value === 'daily'
        ? props.revenueData.daily ?? 0
        : props.revenueData.monthly ?? 0;
});

const totalProductsSold = computed(() => {
    return props.productsData.reduce((sum, p) => sum + parseFloat(p.sales ?? 0), 0);
});

const recentOrders = computed(() => props.salesData.recentOrders ?? []);
</script>


<template>

    <Head title="Dashboard - Carnicería" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Dashboard de Ventas
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats cards -->
                <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Ingresos -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-red-500">
                        <div class="p-6 flex items-center">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <span class="text-red-600 text-lg">💰</span>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Ingresos {{ timeRange === 'daily' ? 'Diarios' : 'Mensuales' }}
                                </p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    ${{ totalRevenue.toLocaleString() }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Productos Vendidos -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-red-400">
                        <div class="p-6 flex items-center">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <span class="text-red-600 text-lg">📦</span>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Productos Vendidos</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ totalProductsSold }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Clientes Atendidos -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-red-300">
                        <div class="p-6 flex items-center">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <span class="text-red-600 text-lg">👥</span>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Clientes Atendidos</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ props.uniqueCustomers ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Crecimiento -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-red-600">
                        <div class="p-6 flex items-center">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <span class="text-red-600 text-lg">📈</span>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Crecimiento</p>
                                <p class="text-2xl font-bold text-green-600">+12.5%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart + Ventas por Producto -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Main Chart -->
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Estadísticas de Ventas
                                </h3>
                                <div class="flex space-x-2">
                                    <select v-model="chartType"
                                        class="text-sm border-gray-300 rounded-md focus:border-red-500 focus:ring-red-500">
                                        <option value="bar">Barras</option>
                                        <option value="line">Líneas</option>
                                        <option value="pie">Pizza</option>
                                    </select>
                                    <select v-model="timeRange"
                                        class="text-sm border-gray-300 rounded-md focus:border-red-500 focus:ring-red-500">
                                        <option value="daily">Diario</option>
                                        <option value="monthly">Mensual</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Chart real -->
                            <div class="h-64">
                                <ChartComponent :type="chartType" :data="chartData" :options="chartOptions" />
                            </div>
                        </div>
                    </div>


                    <!-- Product Sales Breakdown -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ventas por Producto
                            </h3>
                            <div class="space-y-3">
                                <div v-for="product in productsData" :key="product.name"
                                    class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ product.name }}</span>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-16 bg-gray-200 rounded-full h-2">
                                            <div class="bg-gradient-to-r from-red-400 to-red-600 h-2 rounded-full"
                                                :style="`width: ${(product.sales / totalProductsSold) * 100}%`"></div>
                                        </div>
                                        <span class="text-sm font-semibold text-red-600">{{ product.sales }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent orders table -->
                <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pedidos Recientes</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-red-50 dark:bg-red-900">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wider">
                                            Cliente</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wider">
                                            Productos</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wider">
                                            Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="order in recentOrders" :key="order.id"
                                        class="hover:bg-red-50 dark:hover:bg-red-900 transition-colors">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ order.customer }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ order.items }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600">
                                            ${{ order.total }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
