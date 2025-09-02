<script setup>
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    salesData: Object,
    productsData: Array,
    revenueData: Object,
    topProducts: Array
});

// Chart configuration
const chartType = ref('bar');
const timeRange = ref('daily');
const selectedProduct = ref('all');

// Sample data structure (will be replaced by Laravel props)
const sampleData = ref({
    dailySales: [120, 150, 180, 200, 170, 190, 220],
    monthlySales: [3500, 4200, 3800, 4500, 5200, 4800, 5500],
    productSales: {
        'Carne de Res': 45,
        'Carne de Cerdo': 30,
        'Pollo': 35,
        'Embutidos': 25,
        'Cortes Premium': 15
    },
    recentOrders: [
        { id: 1, customer: 'Juan Pérez', total: 85.50, items: 'Bife de chorizo, Vacío' },
        { id: 2, customer: 'María García', total: 120.00, items: 'Asado, Chorizo, Morcilla' },
        { id: 3, customer: 'Carlos López', total: 65.75, items: 'Pollo entero, Milanesas' }
    ]
});

// Computed properties for chart data
const chartData = computed(() => {
    if (timeRange.value === 'daily') {
        return {
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            data: sampleData.value.dailySales
        };
    } else {
        return {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul'],
            data: sampleData.value.monthlySales
        };
    }
});

const totalRevenue = computed(() => {
    return timeRange.value === 'daily'
        ? sampleData.value.dailySales.reduce((a, b) => a + b, 0)
        : sampleData.value.monthlySales.reduce((a, b) => a + b, 0);
});

// Chart rendering function (simplified - would use Chart.js or similar)
const renderChart = () => {
    // This would integrate with Chart.js or another charting library
    console.log('[v0] Rendering chart with type:', chartType.value);
};

onMounted(() => {
    renderChart();
});
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
                 <!-- Stats cards with red accent colors -->
                <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-red-500">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 text-lg">💰</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ingresos {{ timeRange === 'daily' ? 'Diarios' : 'Mensuales' }}</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ totalRevenue.toLocaleString() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-red-400">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 text-lg">📦</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Productos Vendidos</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ Object.values(sampleData.productSales).reduce((a, b) => a + b, 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-red-300">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 text-lg">👥</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Clientes Atendidos</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ sampleData.recentOrders.length }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-red-600">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 text-lg">📈</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Crecimiento</p>
                                    <p class="text-2xl font-bold text-green-600">+12.5%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- Chart controls and visualization -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                     <!-- Main Chart -->
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Estadísticas de Ventas</h3>
                                <div class="flex space-x-2">
                                    <select v-model="chartType" class="text-sm border-gray-300 rounded-md focus:border-red-500 focus:ring-red-500">
                                        <option value="bar">Barras</option>
                                        <option value="line">Líneas</option>
                                        <option value="pie">Pizza</option>
                                    </select>
                                    <select v-model="timeRange" class="text-sm border-gray-300 rounded-md focus:border-red-500 focus:ring-red-500">
                                        <option value="daily">Diario</option>
                                        <option value="monthly">Mensual</option>
                                    </select>
                                </div>
                            </div>

                             <!-- Chart placeholder - would integrate with Chart.js -->
                            <div class="h-64 bg-gray-50 dark:bg-gray-700 rounded-lg flex items-center justify-center border-2 border-dashed border-red-200">
                                <div class="text-center">
                                    <div class="text-red-500 text-4xl mb-2">📊</div>
                                    <p class="text-gray-500 dark:text-gray-400">Gráfico {{ chartType }} - {{ timeRange === 'daily' ? 'Ventas Diarias' : 'Ventas Mensuales' }}</p>
                                    <p class="text-sm text-gray-400 mt-2">Integrar con Chart.js o similar</p>
                                </div>
                            </div>
                        </div>
                    </div>

                     <!-- Product Sales Breakdown -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ventas por Producto</h3>
                            <div class="space-y-3">
                                <div v-for="(sales, product) in sampleData.productSales" :key="product" class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ product }}</span>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-16 bg-gray-200 rounded-full h-2">
                                            <div class="bg-gradient-to-r from-red-400 to-red-600 h-2 rounded-full" :style="`width: ${(sales/50)*100}%`"></div>
                                        </div>
                                        <span class="text-sm font-semibold text-red-600">{{ sales }}</span>
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wider">Cliente</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wider">Productos</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="order in sampleData.recentOrders" :key="order.id" class="hover:bg-red-50 dark:hover:bg-red-900 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ order.customer }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ order.items }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600">${{ order.total }}</td>
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
