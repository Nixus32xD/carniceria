<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    analyticsData: Object,
    trends: Object,
    comparisons: Object
});

const selectedMetric = ref('revenue');
const comparisonPeriod = ref('last_month');

const metrics = ref({
    revenue: { label: 'Ingresos', value: 15420, change: '+8.2%', color: 'red' },
    orders: { label: 'Pedidos', value: 342, change: '+12.1%', color: 'red' },
    customers: { label: 'Clientes', value: 156, change: '+5.7%', color: 'red' },
    avgOrder: { label: 'Pedido Promedio', value: 45.12, change: '+3.4%', color: 'red' }
});

const topProducts = ref([
    { name: 'Bife de Chorizo', sales: 89, revenue: 2670 },
    { name: 'Asado', sales: 76, revenue: 2280 },
    { name: 'Vacío', sales: 65, revenue: 1950 },
    { name: 'Pollo Entero', sales: 54, revenue: 1080 },
    { name: 'Chorizo Parrillero', sales: 43, revenue: 860 }
]);
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
                 <!-- Advanced metrics grid -->
                <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
                    <div v-for="(metric, key) in metrics" :key="key"
                         class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4"
                         :class="`border-${metric.color}-500`">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ metric.label }}</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ key === 'revenue' || key === 'avgOrder' ? '$' : '' }}{{ metric.value.toLocaleString() }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-semibold text-green-600">{{ metric.change }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- Advanced charts and analytics -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                     <!-- Top Products Chart -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Productos Más Vendidos</h3>
                            <div class="space-y-4">
                                <div v-for="(product, index) in topProducts" :key="product.name" class="flex items-center space-x-4">
                                    <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <span class="text-red-600 font-bold text-sm">{{ index + 1 }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ product.name }}</p>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                                <div class="bg-gradient-to-r from-red-400 to-red-600 h-2 rounded-full"
                                                     :style="`width: ${(product.sales/100)*100}%`"></div>
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

                     <!-- Trends Analysis -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Análisis de Tendencias</h3>
                            <div class="h-64 bg-gray-50 dark:bg-gray-700 rounded-lg flex items-center justify-center border-2 border-dashed border-red-200">
                                <div class="text-center">
                                    <div class="text-red-500 text-4xl mb-2">📈</div>
                                    <p class="text-gray-500 dark:text-gray-400">Gráfico de Tendencias</p>
                                    <p class="text-sm text-gray-400 mt-2">Análisis temporal de ventas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- Detailed analytics table -->
                <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Análisis Detallado por Período</h3>
                            <select v-model="comparisonPeriod" class="text-sm border-gray-300 rounded-md focus:border-red-500 focus:ring-red-500">
                                <option value="last_week">Última Semana</option>
                                <option value="last_month">Último Mes</option>
                                <option value="last_quarter">Último Trimestre</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center p-4 bg-red-50 dark:bg-red-900 rounded-lg">
                                <div class="text-2xl font-bold text-red-600">$12,450</div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">Ingresos Totales</div>
                                <div class="text-xs text-green-600 mt-1">↗ +15.2% vs período anterior</div>
                            </div>
                            <div class="text-center p-4 bg-red-50 dark:bg-red-900 rounded-lg">
                                <div class="text-2xl font-bold text-red-600">276</div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">Pedidos Procesados</div>
                                <div class="text-xs text-green-600 mt-1">↗ +8.7% vs período anterior</div>
                            </div>
                            <div class="text-center p-4 bg-red-50 dark:bg-red-900 rounded-lg">
                                <div class="text-2xl font-bold text-red-600">$45.11</div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">Ticket Promedio</div>
                                <div class="text-xs text-green-600 mt-1">↗ +6.1% vs período anterior</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
