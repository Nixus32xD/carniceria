<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { reactive, computed, ref } from 'vue';

// ========== PROPS Y DATA INICIAL ==========
const props = defineProps({
    products: Array,
    customers: Array,
    sales: Array,
    message: String
});

// Referencias reactivas
const saleModal = ref(false);
const detailModal = ref(false);
const message = ref('');
const selectedSale = ref(null);

// Listas de datos
const salesList = ref([...props.sales]);
const productList = ref([...props.products]);
const customerList = ref([...props.customers]);

// Filtros de búsqueda
const searchDate = ref('');
const searchPaymentMethod = ref('');
const startDate = ref('');
const endDate = ref('');

// Formularios
const form = reactive({
    customer_id: null,
    customer_name: '',
    customer_phone: '',
    items: [],
    total: 0,
    payment_method: 'efectivo',
    notes: ''
});

const newItem = reactive({
    product_id: null,
    quantity: 0,
    weight: 0,
    unit_price: 0,
    subtotal: 0
});

// Constantes
const paymentMethods = [
    { value: 'efectivo', label: 'Efectivo' },
    { value: 'tarjeta', label: 'Tarjeta' },
    { value: 'transferencia', label: 'Transferencia' }
];

// ========== COMPUTED PROPERTIES ==========

// Datos filtrados y validados
const validSales = computed(() => {
    return salesList.value.filter(s => s !== null && s !== undefined && s.id !== undefined);
});

const filteredSales = computed(() => {
    let filtered = validSales.value;

    // Filtrar por método de pago
    if (searchPaymentMethod.value) {
        filtered = filtered.filter(sale => sale.payment_method === searchPaymentMethod.value);
    }

    // Filtrar por fecha específica
    if (searchDate.value) {
        filtered = filtered.filter(sale => {
            const saleDate = new Date(sale.created_at).toISOString().split('T')[0];
            return saleDate === searchDate.value;
        });
    }

    // Filtrar por rango de fechas
    if (startDate.value && endDate.value) {
        filtered = filtered.filter(sale => {
            const saleDate = new Date(sale.created_at);
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            end.setHours(23, 59, 59);
            return saleDate >= start && saleDate <= end;
        });
    }

    return filtered;
});

const uniquePaymentMethods = computed(() => {
    const methods = [...new Set(validSales.value.map(sale => sale.payment_method))];
    return methods.filter(method => method);
});

// Estadísticas
const todaySales = computed(() => {
    const today = new Date().toDateString();
    const todaySales = filteredSales.value.filter(s => {
        if (!s.created_at) return false;
        return new Date(s.created_at).toDateString() === today;
    });

    return {
        count: todaySales.length,
        total: todaySales.reduce((sum, s) => sum + (parseFloat(s.total) || 0), 0)
    };
});

const allSales = computed(() => {
    return {
        count: filteredSales.value.length,
        total: filteredSales.value.reduce((sum, s) => sum + (parseFloat(s.total) || 0), 0)
    };
});

const averageSale = computed(() => {
    return allSales.value.count > 0 ? allSales.value.total / allSales.value.count : 0;
});

// Total de la venta actual
const totalSale = computed(() => {
    return form.items.reduce((sum, item) => sum + item.subtotal, 0);
});

// ========== FUNCIONES DE VENTAS ==========

// Gestión de items de venta
function addItem() {
    if (newItem.product_id && (newItem.quantity > 0 || newItem.weight > 0)) {
        const product = productList.value.find(p => p.id === newItem.product_id);
        const price = product.isOffer ? product.offerPrice : product.price;
        const quantity = newItem.weight || newItem.quantity;

        // Validar stock
        const stockCheck = checkStock(newItem.product_id, quantity);
        if (!stockCheck.valid) {
            message.value = stockCheck.message;
            setTimeout(() => (message.value = ''), 3000);
            return;
        }

        // Verificar si el producto ya está en la venta
        const existingItemIndex = form.items.findIndex(item => item.product_id === newItem.product_id);

        if (existingItemIndex !== -1) {
            // Actualizar item existente
            const newQuantity = form.items[existingItemIndex].quantity + quantity;
            const stockCheckUpdate = checkStock(newItem.product_id, newQuantity);

            if (!stockCheckUpdate.valid) {
                message.value = stockCheckUpdate.message;
                setTimeout(() => (message.value = ''), 3000);
                return;
            }

            form.items[existingItemIndex].quantity = newQuantity;
            form.items[existingItemIndex].subtotal = newQuantity * price;
        } else {
            // Agregar nuevo item
            form.items.push({
                product_id: newItem.product_id,
                product_name: product.name,
                quantity: quantity,
                unit_price: price,
                subtotal: price * quantity
            });
        }

        // Reset new item
        Object.assign(newItem, {
            product_id: null,
            quantity: 0,
            weight: 0,
            unit_price: 0,
            subtotal: 0
        });
    }
}

function removeItem(index) {
    form.items.splice(index, 1);
}

function updateItemPrice() {
    if (newItem.product_id) {
        const product = productList.value.find(p => p.id === newItem.product_id);
        newItem.unit_price = product.isOffer ? product.offerPrice : product.price;
        const quantity = newItem.weight || newItem.quantity;
        newItem.subtotal = newItem.unit_price * quantity;
    }
}

// Validación de stock
const checkStock = (productId, quantity) => {
    const product = productList.value.find(p => p.id === productId);
    if (!product) return { valid: false, message: 'Producto no encontrado' };

    if (product.stock < quantity) {
        return {
            valid: false,
            message: `Stock insuficiente. Disponible: ${product.stock} kg`
        };
    }

    return { valid: true };
};

const validateCartStock = () => {
    for (const item of form.items) {
        const stockCheck = checkStock(item.product_id, item.quantity);
        if (!stockCheck.valid) {
            return stockCheck;
        }
    }
    return { valid: true };
};

// Procesamiento de venta
async function submitSale() {
    form.total = totalSale.value;

    // Validar stock final antes de enviar
    const stockValidation = validateCartStock();
    if (!stockValidation.valid) {
        message.value = stockValidation.message;
        setTimeout(() => (message.value = ''), 3000);
        return;
    }

    try {
        const response = await axios.post('/dashboard/sales', form);
        message.value = response.data.message;

        // Actualizar stock localmente
        updateLocalStock(form.items);

        salesList.value.unshift(response.data.sale);
        closeSaleModal();
        setTimeout(() => (message.value = ''), 3000);
    } catch (error) {
        if (error.response?.data?.message) {
            message.value = 'Error: ' + error.response.data.message;
        } else {
            message.value = 'Error al procesar la venta';
        }
        setTimeout(() => (message.value = ''), 3000);
    }
}

const updateLocalStock = (items) => {
    items.forEach(item => {
        const productIndex = productList.value.findIndex(p => p.id === item.product_id);
        if (productIndex !== -1) {
            productList.value[productIndex].stock -= item.quantity;
        }
    });
};

// ========== GESTIÓN DE MODALES ==========

function resetForm() {
    Object.assign(form, {
        customer_id: null,
        customer_name: '',
        customer_phone: '',
        items: [],
        total: 0,
        payment_method: 'efectivo',
        notes: ''
    });
}

function openSaleModal() {
    resetForm();
    saleModal.value = true;
}

function closeSaleModal() {
    saleModal.value = false;
}

async function openDetailModal(saleId) {
    try {
        const response = await axios.get(`/dashboard/sales/${saleId}`);
        selectedSale.value = response.data.sale;
        detailModal.value = true;
    } catch (error) {
        message.value = 'Error al cargar los detalles de la venta';
        setTimeout(() => (message.value = ''), 3000);
    }
}

function closeDetailModal() {
    detailModal.value = false;
    selectedSale.value = null;
}

// ========== FUNCIONES DE FILTRADO ==========

const clearFilters = () => {
    searchDate.value = '';
    searchPaymentMethod.value = '';
    startDate.value = '';
    endDate.value = '';
};

const exportToCSV = () => {
    const headers = ['ID', 'Cliente', 'Fecha', 'Total', 'Método de Pago'];
    const csvData = filteredSales.value.map(sale => [
        sale.id,
        getCustomerName(sale),
        formatDate(sale.created_at),
        sale.total,
        sale.payment_method
    ]);

    const csvContent = [
        headers.join(','),
        ...csvData.map(row => row.join(','))
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', `ventas_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

// ========== FUNCIONES UTilitarias ==========

function printSale(saleId) {
    window.open(`/dashboard/sales/${saleId}/print`, '_blank');
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
};

const formatDateArgentina = (dateString) => {
    return new Date(dateString).toLocaleString("es-AR", {
        timeZone: "America/Argentina/Buenos_Aires",
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit"
    });
};

const getCustomerName = (sale) => {
    if (sale.customer_name) return sale.customer_name;

    if (sale.customer_id) {
        const customer = customerList.value.find(c => c.id === sale.customer_id);
        return customer ? customer.name : 'Cliente General';
    }

    return 'Cliente General';
};
</script>
<template>

    <Head title="Ventas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Gestión de Ventas
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">

                        <!-- Mensaje de éxito/error -->
                        <div v-if="message" class="mt-4 mb-4 p-3 rounded text-sm" :class="message.includes('Error')
                            ? 'bg-red-100 border border-red-400 text-red-700'
                            : 'bg-green-100 border border-green-400 text-green-700'">
                            {{ message }}
                        </div>

                        <!-- Estadísticas rápidas -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-gradient-to-r from-red-500 to-red-600 p-4 rounded-lg text-white">
                                <h3 class="text-sm font-medium">Ventas Hoy</h3>
                                <p class="text-2xl font-bold">{{ todaySales.count }}</p>
                            </div>
                            <div class="bg-gradient-to-r from-red-600 to-red-700 p-4 rounded-lg text-white">
                                <h3 class="text-sm font-medium">Ingresos Hoy</h3>
                                <p class="text-2xl font-bold">${{ todaySales.total.toFixed(2) }}</p>
                            </div>
                            <div class="bg-gradient-to-r from-red-700 to-red-800 p-4 rounded-lg text-white">
                                <h3 class="text-sm font-medium">Total Ventas</h3>
                                <p class="text-2xl font-bold">{{ allSales.count }}</p>
                            </div>
                            <div class="bg-gradient-to-r from-red-800 to-red-900 p-4 rounded-lg text-white">
                                <h3 class="text-sm font-medium">Promedio Venta</h3>
                                <p class="text-2xl font-bold">${{ averageSale.toFixed(2) }}</p>
                            </div>
                        </div>


                        <!-- Después de las estadísticas y antes del botón Nueva Venta -->

                        <!-- Filtros de Búsqueda -->
                        <div class="mb-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Filtrar Ventas</h3>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <!-- Filtro por Método de Pago -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Método de Pago
                                    </label>
                                    <select v-model="searchPaymentMethod"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                        <option value="">Todos los métodos</option>
                                        <option v-for="method in uniquePaymentMethods" :key="method" :value="method">
                                            {{ method.charAt(0).toUpperCase() + method.slice(1) }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Filtro por Fecha Específica -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Fecha específica
                                    </label>
                                    <input v-model="searchDate" type="date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                </div>

                                <!-- Filtro por Rango de Fechas - Start -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Desde
                                    </label>
                                    <input v-model="startDate" type="date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                </div>

                                <!-- Filtro por Rango de Fechas - End -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Hasta
                                    </label>
                                    <input v-model="endDate" type="date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="flex justify-end space-x-3 mt-4">
                                <button @click="clearFilters"
                                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 text-sm">
                                    Limpiar Filtros
                                </button>
                                <button v-if="filteredSales.length > 0" @click="exportToCSV"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
                                    Exportar CSV
                                </button>
                            </div>

                            <!-- Resumen de filtros aplicados -->
                            <div v-if="searchPaymentMethod || searchDate || startDate"
                                class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-semibold">Filtros aplicados:</span>
                                <span v-if="searchPaymentMethod"
                                    class="ml-2 bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                    Método: {{ searchPaymentMethod }}
                                </span>
                                <span v-if="searchDate"
                                    class="ml-2 bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                                    Fecha: {{ searchDate }}
                                </span>
                                <span v-if="startDate && endDate"
                                    class="ml-2 bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">
                                    Rango: {{ startDate }} - {{ endDate }}
                                </span>
                                <span class="ml-2 bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">
                                    Resultados: {{ filteredSales.length }}
                                </span>
                            </div>
                        </div>

                        <!-- Botón Nueva Venta -->
                        <div class="mb-4">
                            <button @click="openSaleModal()"
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Nueva Venta
                            </button>
                        </div>

                        <!-- Tabla de Ventas -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead
                                    class="bg-gradient-to-r from-red-50 to-red-100 dark:from-gray-700 dark:to-gray-600">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">
                                            ID</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">
                                            Cliente</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">
                                            Fecha</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">
                                            Total</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">
                                            Método Pago</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    <tr v-for="sale in filteredSales" :key="sale.id"
                                        class="hover:bg-red-50 dark:hover:bg-gray-700 transition-colors">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            #{{ sale.id }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{
                                                getCustomerName(sale) }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ new
                                                Date(sale.created_at).toLocaleDateString() }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600 dark:text-red-400">
                                            ${{ sale.total }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 capitalize">
                                            {{ sale.payment_method }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button @click="openDetailModal(sale.id)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 mr-3">Ver
                                                Detalle</button>
                                            <button @click="printSale(sale.id)"
                                                class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300">
                                                Imprimir
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Nueva Venta -->
        <transition name="fade" mode="out-in">
            <div v-if="saleModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50"
                @click.self="closeSaleModal">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                    <h2 class="text-2xl font-bold mb-6 text-red-800 dark:text-red-400">Nueva Venta</h2>

                    <form @submit.prevent="submitSale">
                        <!-- Información del Cliente -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Nombre del
                                    Cliente</label>
                                <input v-model="form.customer_name" type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="Nombre del cliente">
                            </div>
                            <div>
                                <label
                                    class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Teléfono</label>
                                <input v-model="form.customer_phone" type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="Teléfono del cliente">
                            </div>
                        </div>

                        <!-- Agregar Productos -->
                        <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 mb-6">
                            <h3 class="text-lg font-semibold mb-4 text-red-700 dark:text-red-400">Agregar Productos</h3>
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                                <div>
                                    <select v-model="newItem.product_id" @change="updateItemPrice"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="">Seleccionar producto</option>
                                        <option v-for="product in productList" :key="product.id" :value="product.id"
                                            :disabled="product.stock <= 0">
                                            {{ product.name }} - ${{ product.isOffer ? product.offerPrice :
                                                product.price }}/kg
                                            (Stock: {{ product.stock }} kg)
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <input v-model.number="newItem.weight" @input="updateItemPrice" type="number"
                                        step="0.1"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="Peso (kg)">
                                </div>
                                <div>
                                    <input v-model.number="newItem.unit_price" readonly
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 dark:bg-gray-600 dark:border-gray-600 dark:text-white"
                                        placeholder="Precio unitario">
                                </div>
                                <div>
                                    <input v-model.number="newItem.subtotal" readonly
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 dark:bg-gray-600 dark:border-gray-600 dark:text-white"
                                        placeholder="Subtotal">
                                </div>
                                <div>
                                    <button type="button" @click="addItem"
                                        class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        Agregar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Productos Agregados -->
                        <div v-if="form.items.length > 0" class="mb-6">
                            <h3 class="text-lg font-semibold mb-4 text-red-700 dark:text-red-400">Productos en la Venta
                            </h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th
                                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                Producto</th>
                                            <th
                                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                Cantidad</th>
                                            <th
                                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                Precio</th>
                                            <th
                                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                Subtotal</th>
                                            <th
                                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                        <tr v-for="(item, index) in form.items" :key="index">
                                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                                item.product_name
                                            }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                                item.quantity }}
                                                kg</td>
                                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">${{
                                                item.unit_price
                                            }}</td>
                                            <td class="px-4 py-2 text-sm font-semibold text-red-600 dark:text-red-400">
                                                ${{
                                                    item.subtotal.toFixed(2) }}</td>
                                            <td class="px-4 py-2">
                                                <button type="button" @click="removeItem(index)"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Método de Pago y Total -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Método de
                                    Pago</label>
                                <select v-model="form.payment_method"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option v-for="method in paymentMethods" :key="method.value" :value="method.value">
                                        {{ method.label }}
                                    </option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <div class="text-right">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Total de la Venta</p>
                                    <p class="text-3xl font-bold text-red-600 dark:text-red-400">${{
                                        totalSale.toFixed(2) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="mb-6">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Notas</label>
                            <textarea v-model="form.notes" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Notas adicionales sobre la venta"></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end space-x-4">
                            <button type="button" @click="closeSaleModal"
                                class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.items.length === 0"
                                class="px-6 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                Procesar Venta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>

        <!-- Modal Ver Detalle -->
        <transition name="fade" mode="out-in">
            <div v-if="detailModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50"
                @click.self="closeDetailModal">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto">
                    <h2 class="text-2xl font-bold mb-6 text-red-800 dark:text-red-400">Detalle de Venta</h2>

                    <div v-if="selectedSale">
                        <!-- Datos generales -->
                        <!-- Datos generales -->
                        <div
                            class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow-sm">
                            <div class="flex flex-col space-y-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400 uppercase font-semibold">Cliente</p>
                                <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    {{ getCustomerName(selectedSale) }}
                                </p>
                            </div>

                            <div class="flex flex-col space-y-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400 uppercase font-semibold">Teléfono</p>
                                <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    {{ selectedSale.customer?.phone || '-' }}
                                </p>
                            </div>

                            <div class="flex flex-col space-y-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400 uppercase font-semibold">Fecha</p>
                                <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    {{ formatDateArgentina(selectedSale.created_at) }}
                                </p>
                            </div>

                            <div class="flex flex-col space-y-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400 uppercase font-semibold">Método de
                                    Pago</p>
                                <p class="text-lg font-medium capitalize text-gray-900 dark:text-gray-100">
                                    {{ selectedSale.payment_method }}
                                </p>
                            </div>

                            <div class="md:col-span-2 flex flex-col space-y-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400 uppercase font-semibold">Notas</p>
                                <p class="text-base text-gray-900 dark:text-gray-100 italic">
                                    {{ selectedSale.notes || 'Sin notas adicionales' }}
                                </p>
                            </div>
                        </div>


                        <!-- Lista de productos -->
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Producto</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Cantidad</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Precio</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    <tr v-for="item in selectedSale.items" :key="item.id">
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{
                                            item.product_name }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ item.quantity
                                            }} kg
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">${{
                                            item.unit_price }}
                                        </td>
                                        <td class="px-4 py-2 text-sm font-semibold text-red-600 dark:text-red-400">${{
                                            item.subtotal }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Total -->
                        <div class="text-right">
                            <p class="text-lg font-bold text-red-600 dark:text-red-400">
                                Total: ${{ parseFloat(selectedSale.total).toFixed(2) }}
                            </p>
                        </div>
                    </div>

                    <!-- Botón cerrar -->
                    <div class="flex justify-end mt-6">
                        <button @click="closeDetailModal"
                            class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </transition>

    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
