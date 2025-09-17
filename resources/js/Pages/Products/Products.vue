<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { reactive, computed, ref, watch } from 'vue';
import Category from './Category.vue';
import Cuts from './Cuts.vue';

const props = defineProps({
    products: Array, // Assuming you pass products as a prop
    message: String,
    categories: Array,
    cuts: Array,
});
const categories = ref([...props.categories])
const cutsMutable = ref([...props.cuts])
const createModal = ref(false);

const message = ref('');
const productList = ref([...props.products])
const isEditMode = ref(false)
const editingProductId = ref(null)

const searchTerm = ref('');
const filterStatus = ref('');
const filterOffer = ref('');

const form = reactive({
    name: '',
    description: '',
    isActive: false,   // por defecto inactivo
    isOffer: false,    // por defecto sin oferta
    stock: 0,
    price: null,
    offerPrice: null,  // este se calcula si hay descuento
    discount: null,
    cut_id: null,       // nuevo
    category_id: null,  // nuevo
})
//watch para controlar el checkbox de isActive segun el stock
watch(() => form.stock, (newStock, oldStock) => {
    //Si antes era 0 y ahora es mayor, activar isActive
    if (oldStock == 0 && newStock > 0) {
        form.isActive = true;
    }

    //Si vuelve a 0, desactivar isActive
    if (newStock == 0) {
        form.isActive = false;
    }
})

const finalPrice = computed(() => {
    if (form.isOffer && form.discount) {
        return form.price - (form.price * (form.discount / 100))
    }
    return form.price;
})

const filteredProducts = computed(() => {
    let filtered = productList.value;

    if (searchTerm.value) {
        const term = searchTerm.value.toLowerCase();
        filtered = filtered.filter(product =>
            (product.name || '').toLowerCase().includes(term) ||
            (product.description || '').toLowerCase().includes(term)
        );
    }

    if (filterStatus.value === 'active') {
        filtered = filtered.filter(product => product.isActive);
    } else if (filterStatus.value === 'inactive') {
        filtered = filtered.filter(product => !product.isActive);
    }

    if (filterOffer.value === 'offer') {
        filtered = filtered.filter(product => product.isOffer);
    } else if (filterOffer.value === 'no-offer') {
        filtered = filtered.filter(product => !product.isOffer);
    }

    return filtered;
});

async function submitForm() {

    if (form.isOffer && form.discount) {
        form.offerPrice = finalPrice.value;
    } else {
        form.offerPrice = null;
    }

    try {
        if (isEditMode.value) {
            //UPDATE
            const response = await axios.put(`/dashboard/products/${editingProductId.value}/update`, form);
            message.value = response.data.message;

            //Actualizar producto en lista
            const index = productList.value.findIndex(p => p.id === editingProductId.value);
            if (index !== -1) {
                productList.value[index] = response.data.product;
            }
        } else {
            //CREATE
            const response = await axios.post(`/dashboard/products`, form);
            message.value = response.data.message
            productList.value.push(response.data.product);
        }

        closeCreateModal();
        setTimeout(() => (message.value = ''), 3000);
    } catch (error) {
        message.value = 'Error en la operación, intente nuevamente mas tarde o contacte con soporte';
        closeCreateModal();
        setTimeout(() => (message.value = ''), 3000);
    }
}
function updateCategories(cat) {
    const index = categories.value.findIndex(c => c.id === cat.id)
    if (index !== -1) {
        // Para reactividad profunda, crea un nuevo objeto
        categories.value.splice(index, 1, { ...cat })
    } else {
        categories.value.push(cat)
    }
    syncCutsWithCategories(cat)
}

function syncCutsWithCategories(cat) {
    cutsMutable.value.forEach((cut, index) => {
        if (cut.category_id === cat.id) {
            // Actualizar con reactividad
            cutsMutable.value[index] = {
                ...cut,
                category: { ...cat }
            }
        }
    });
}

function resetForm() {
    form.name = '';
    form.description = '';
    form.isActive = false;
    form.isOffer = false;
    form.stock = 0;
    form.price = null;
    form.discount = null;
    form.offerPrice = null;
}

function openCreateModal() {
    resetForm()
    isEditMode.value = false
    editingProductId.value = null
    createModal.value = true;
}

function closeCreateModal() {
    createModal.value = false;
}

function openEditModal(product) {
    //Llenamos el form con los datos del producto a editar
    form.name = product.name
    form.description = product.description
    form.isActive = product.isActive;
    form.isOffer = product.isOffer;
    form.stock = product.stock;
    form.price = product.price;
    form.discount = product.discount;
    form.offerPrice = product.offerPrice;
    form.cut_id = product.cut_id
    form.category_id = product.category_id

    isEditMode.value = true;
    editingProductId.value = product.id;
    createModal.value = true;
}

async function deleteProduct(id) {
    try {
        const response = await axios.delete(`/dashboard/product/${id}/delete`);
        message.value = response.data.message;
        productList.value = productList.value.filter(product => product.id !== id);
        setTimeout(() => {
            message.value = '';
        }, 3000);
    } catch (error) {
        message.value = error.response?.data?.message || "Error al eliminar el producto";

        setTimeout(() => {
            message.value = '';
        }, 3000);
    }
}

function confirmDelete(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
        deleteProduct(id);
    }
}

</script>

<template>

    <Head title="Productos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Gestión de Productos
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">

                        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <input type="text" v-model="searchTerm" placeholder="Buscar productos..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <select v-model="filterStatus"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Todos los estados</option>
                                    <option value="active">Activos</option>
                                    <option value="inactive">Inactivos</option>
                                </select>
                            </div>
                            <div>
                                <select v-model="filterOffer"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Todas las ofertas</option>
                                    <option value="offer">Con oferta</option>
                                    <option value="no-offer">Sin oferta</option>
                                </select>
                            </div>
                        </div>

                        <div v-if="message" class="mt-4 mb-4 p-3 rounded text-sm" :class="message.includes('Error')
                            ? 'bg-red-100 border border-red-400 text-red-700'
                            : 'bg-green-100 border border-green-400 text-green-700'">
                            {{ message }}
                        </div>

                        <div class="mb-4 mt-4">
                            <button @click="openCreateModal()"
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Crear Producto
                            </button>
                        </div>

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
                                            Nombre</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">
                                            Precio x Kilo</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">
                                            Stock</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">
                                            Descuento</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">
                                            Estado</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    <tr v-for="product in filteredProducts" :key="product.id"
                                        class="hover:bg-red-50 dark:hover:bg-gray-700 transition-colors">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            #{{ product.id }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ product.name }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            <span v-if="product.isOffer" class="text-red-600 font-semibold">${{
                                                product.offerPrice }}</span>
                                            <span v-else class="font-semibold">${{ product.price }}</span>
                                            <span v-if="product.isOffer" class="text-gray-400 line-through ml-2">${{
                                                product.price }}</span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            <span
                                                :class="product.stock <= 5 ? 'text-red-600 font-semibold' : 'text-green-600'">
                                                {{ product.stock }} kg
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            <span v-if="product.isOffer"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ product.discount }}% OFF
                                            </span>
                                            <span v-else class="text-gray-400">Sin descuento</span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            <span
                                                :class="product.isActive ? 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800' : 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800'">
                                                {{ product.isActive ? "Activo" : "Inactivo" }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                            <button @click="openEditModal(product)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-medium">
                                                Editar
                                            </button>
                                            <button @click="confirmDelete(product.id)"
                                                class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300 font-medium">
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Componente Modal Categoría -->
                        <Category :categories="categories" @created="cat => categories.push(cat)" @updated="cat => {
                            const i = categories.findIndex(c => c.id === cat.id)
                            if (i !== -1) categories[i] = cat
                            syncCutsWithCategories(cat) // 🔹 sincronizamos cortes
                        }" @deleted="id => {
                            const index = categories.findIndex(c => c.id === id)
                            if (index !== -1) categories.splice(index, 1)

                            // 🔹 Si se elimina la categoría, removemos la referencia en los cortes
                            cutsMutable.value.forEach(cut => {
                                if (cut.category_id === id) cut.category_id = null
                            })
                        }" />


                        <!-- Componente Modal Corte -->
                        <Cuts :cuts="cuts" :categories="categories" @created="cut => cuts.push(cut)" @updated="cut => {
                            const i = cuts.findIndex(c => c.id === cut.id)
                            if (i !== -1) cuts[i] = cut
                        }" @deleted="id => {
                            const index = cuts.findIndex(c => c.id === id)
                            if (index !== -1) cuts.splice(index, 1) // ✅ muta el array
                        }" />



                    </div>

                </div>
            </div>
        </div>

        <!-- Modal para Crear Producto -->
        <transition name="fade" mode="out-in">
            <div v-if="createModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center p-4"
                @click.self="closeCreateModal">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg w-full max-w-md sm:max-w-lg md:max-w-2xl">
                    <h2 class="text-lg font-bold mb-4 dark:text-white">
                        {{ isEditMode ? "Editar Producto" : "Crear Nuevo Producto" }}
                    </h2>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="mb-4 mx-10">
                            <label class="block text-dark text-sm font-bold mb-2" for="name">
                                Nombre
                            </label>
                            <input v-model="form.name"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-dark leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                                id="name" type="text" placeholder="Nombre del producto">
                        </div>

                        <div class="mb-4 mx-10">
                            <label class="block text-dark text-sm font-bold mb-2" for="description">
                                Descripción
                            </label>
                            <textarea v-model="form.description"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-dark leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                                id="description" placeholder="Descripción del producto">
                    </textarea>
                        </div>

                        <div class="mb-4 mx-10">
                            <label class="block text-dark text-sm font-bold mb-2" for="isOffer">
                                ¿Tiene Descuento?
                            </label>
                            <select v-model="form.isOffer"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-dark leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                                id="isOffer">
                                <option :value="false">No</option>
                                <option :value="true">Sí</option>
                            </select>
                        </div>

                        <div v-if="!form.isOffer" class="mb-4 mx-10">
                            <label class="block text-dark text-sm font-bold mb-2" for="price">
                                Precio x Kilo
                            </label>
                            <input v-model.number="form.price"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-dark leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                                id="price" type="number" placeholder="Precio del producto por kilo">
                        </div>
                        <div v-else>
                            <div class="mb-4 mx-10">
                                <label class="block text-dark text-sm font-bold mb-2" for="price">
                                    Precio x Kilo
                                </label>
                                <input v-model.number="form.price"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-dark leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                                    id="price" type="number" placeholder="Precio del producto por kilo">
                            </div>
                            <div class="mb-4 mx-10">
                                <label class="block text-dark text-sm font-bold mb-2" for="discount">
                                    Descuento (%)
                                </label>
                                <input v-model.number="form.discount"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-dark leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                                    id="discount" type="number" placeholder="Descuento a aplicar">
                            </div>
                            <!-- Mostrar precio final -->
                            <div class="mb-4 text-dark">
                                Precio con descuento: <strong class="text-blue-400">${{ finalPrice }}</strong>
                            </div>
                        </div>
                        <div class="mb-4 mx-10">
                            <label class="block text-dark text-sm font-bold mb-2" for="stock">
                                Stock
                            </label>
                            <input v-model.number="form.stock"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-dark leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                                id="stock" type="number" placeholder="Cantidad en stock">
                        </div>

                        <div class="mb-4 mx-10">
                            <label class="block text-dark text-sm font-bold mb-2" for="category_id">
                                Categoría
                            </label>
                            <select v-model="form.category_id"
                                class="shadow appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                                id="category_id">
                                <option disabled value="">-- Seleccionar categoría --</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-4 mx-10">
                            <label class="block text-dark text-sm font-bold mb-2" for="cut_id">
                                Corte
                            </label>
                            <select v-model="form.cut_id"
                                class="shadow appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                                id="cut_id">
                                <option disabled value="">-- Seleccionar corte --</option>
                                <option v-for="cut in cuts" :key="cut.id" :value="cut.id">
                                    {{ cut.name }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-4 mx-10">
                            <span class="mr-2 text-sm text-gray-600 dark:text-white">Esta Activo</span>
                            <input type="checkbox" v-model="form.isActive" id="isActive" class="mr-2">
                        </div>


                        <div class="flex flex-col sm:flex-row justify-end gap-2">
                            <button type="button" @click="closeCreateModal"
                                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancelar</button>
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                {{ isEditMode ? "Guardar Cambios" : "Crear" }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>

    </AuthenticatedLayout>
</template>
