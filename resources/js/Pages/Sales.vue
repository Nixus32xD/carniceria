<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { reactive, computed, ref, onMounted } from 'vue';

const props = defineProps({
    products: Array,
    customers: Array,
    message: String
});

const saleModal = ref(false);
const message = ref('');
const salesList = ref([]);
const productList = ref([...props.products]);
const customerList = ref([...props.customers]);

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

const paymentMethods = [
    { value: 'efectivo', label: 'Efectivo' },
    { value: 'tarjeta', label: 'Tarjeta' },
    { value: 'transferencia', label: 'Transferencia' }
];

const totalSale = computed(() => {
    return form.items.reduce((sum, item) => sum + item.subtotal, 0);
});

function addItem() {
    if (newItem.product_id && (newItem.quantity > 0 || newItem.weight > 0)) {
        const product = productList.value.find(p => p.id === newItem.product_id);
        const price = product.isOffer ? product.offerPrice : product.price;
        const quantity = newItem.weight || newItem.quantity;

        form.items.push({
            product_id: newItem.product_id,
            product_name: product.name,
            quantity: quantity,
            unit_price: price,
            subtotal: price * quantity
        });

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

async function submitSale() {
    form.total = totalSale.value;

    try {
        const response = await axios.post('/dashboard/sales', form);
        message.value = response.data.message;
        salesList.value.unshift(response.data.sale);
        closeSaleModal();
        setTimeout(() => (message.value = ''), 3000);
    } catch (error) {
        message.value = 'Error al procesar la venta';
        setTimeout(() => (message.value = ''), 3000);
    }
}

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

onMounted(async () => {
    try {
        const response = await axios.get('/dashboard/sales');
        salesList.value = response.data.sales;
    } catch (error) {
        console.error('Error loading sales:', error);
    }
});
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
                                <p class="text-2xl font-bold">{{ salesList.filter(s => new Date(s.created_at).toDateString() === new Date().toDateString()).length }}</p>
                            </div>
                            <div class="bg-gradient-to-r from-red-600 to-red-700 p-4 rounded-lg text-white">
                                <h3 class="text-sm font-medium">Ingresos Hoy</h3>
                                <p class="text-2xl font-bold">${{ salesList.filter(s => new Date(s.created_at).toDateString() === new Date().toDateString()).reduce((sum, s) => sum + s.total, 0).toFixed(2) }}</p>
                            </div>
                            <div class="bg-gradient-to-r from-red-700 to-red-800 p-4 rounded-lg text-white">
                                <h3 class="text-sm font-medium">Total Ventas</h3>
                                <p class="text-2xl font-bold">{{ salesList.length }}</p>
                            </div>
                            <div class="bg-gradient-to-r from-red-800 to-red-900 p-4 rounded-lg text-white">
                                <h3 class="text-sm font-medium">Promedio Venta</h3>
                                <p class="text-2xl font-bold">${{ salesList.length ? (salesList.reduce((sum, s) => sum + s.total, 0) / salesList.length).toFixed(2) : '0.00' }}</p>
                            </div>
                        </div>

                         <!-- Botón Nueva Venta -->
                        <div class="mb-4">
                            <button @click="openSaleModal()"
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Nueva Venta
                            </button>
                        </div>

                         <!-- Tabla de Ventas -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gradient-to-r from-red-50 to-red-100 dark:from-gray-700 dark:to-gray-600">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">Cliente</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">Método Pago</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    <tr v-for="sale in salesList" :key="sale.id" class="hover:bg-red-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">#{{ sale.id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ sale.customer_name || 'Cliente General' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ new Date(sale.created_at).toLocaleDateString() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600 dark:text-red-400">${{ sale.total }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 capitalize">{{ sale.payment_method }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 mr-3">Ver Detalle</button>
                                            <button class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300">Imprimir</button>
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
            <div v-if="saleModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50" @click.self="closeSaleModal">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                    <h2 class="text-2xl font-bold mb-6 text-red-800 dark:text-red-400">Nueva Venta</h2>

                    <form @submit.prevent="submitSale">
                         <!-- Información del Cliente -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Nombre del Cliente</label>
                                <input v-model="form.customer_name" type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="Nombre del cliente">
                            </div>
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Teléfono</label>
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
                                        <option v-for="product in productList" :key="product.id" :value="product.id">
                                            {{ product.name }} - ${{ product.isOffer ? product.offerPrice : product.price }}/kg
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <input v-model.number="newItem.weight" @input="updateItemPrice" type="number" step="0.1"
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
                            <h3 class="text-lg font-semibold mb-4 text-red-700 dark:text-red-400">Productos en la Venta</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Producto</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cantidad</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Precio</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Subtotal</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                        <tr v-for="(item, index) in form.items" :key="index">
                                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ item.product_name }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ item.quantity }} kg</td>
                                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">${{ item.unit_price }}</td>
                                            <td class="px-4 py-2 text-sm font-semibold text-red-600 dark:text-red-400">${{ item.subtotal.toFixed(2) }}</td>
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
                                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Método de Pago</label>
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
                                    <p class="text-3xl font-bold text-red-600 dark:text-red-400">${{ totalSale.toFixed(2) }}</p>
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
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
