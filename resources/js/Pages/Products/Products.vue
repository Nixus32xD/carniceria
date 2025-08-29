    <script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head, Link } from '@inertiajs/vue3';
    import axios from 'axios';
    import { reactive, computed, ref, watch } from 'vue';

    const props = defineProps({
        products: Array, // Assuming you pass products as a prop
        message: String
    });

    const createModal = ref(false);
    const message = ref('');
    const productList = ref([...props.products])
    const isEditMode = ref(false)
    const editingProductId = ref(null)


    const form = reactive({
        name: '',
        description: '',
        isActive: false,   // por defecto inactivo
        isOffer: false,    // por defecto sin oferta
        stock: 0,
        price: null,
        offerPrice: null,  // este se calcula si hay descuento
        discount: null,
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

</script>

    <template>

        <Head title="Dashboard" />

        <AuthenticatedLayout>
            <template #header>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Productos
                </h2>


            </template>

            <div class="py-12">
                <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h1>Lista de Productos</h1>

                            <!-- Div donde se muestra el mensaje de exito o error -->
                            <div v-if="message" class="mt-4 mb-4 p-3 rounded text-sm" :class="message.includes('Error')
                                ? 'bg-red-100 border border-red-400 text-red-700'
                                : 'bg-green-100 border border-green-400 text-green-700'">
                                {{ message }}
                            </div>

                            <!-- Boton Para crear Nuevos Producto -->

                            <div class="mb-4 mt-4">
                                <button @click="openCreateModal()"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Crear Producto
                                </button>
                            </div>
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                            ID</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                            Nombre</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                            Precio x Kilo</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                            Stock</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                            Descuento</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                            ¿Esta Activo?</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    <tr v-for="product in productList" :key="product.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ product.id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ product.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">$ {{ (product.isOffer) ?
                                            product.offerPrice
                                            :
                                            product.price }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ product.stock }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ (product.isOffer) ?
                                            product.discount + "%"
                                            : "No tiene Descuento" }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ (product.isActive) ? "Si" : "No" }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap flex space-x-5">
                                            <!-- Actions can be added here -->
                                            <button @click="openEditModal(product)"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">Editar</button>
                                            <button @click="deleteProduct(product.id)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Eliminar</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Modal para Crear Producto -->
            <transition name="fade" mode="out-in">
                <div v-if="createModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center"
                    @click.self="closeCreateModal">

                    <div class="bg-white p-6 rounded-lg w-1/3 dark:bg-gray-800">
                        <h2 class="text-lg font-bold mb-4 dark:text-white">
                            {{ isEditMode ? "Editar Producto" : "Crear Nuevo Producto" }}
                        </h2>

                        <form @submit.prevent="submitForm">
                            <div class="mb-4 mx-10">
                                <label class="block text-white text-sm font-bold mb-2" for="name">
                                    Nombre
                                </label>
                                <input v-model="form.name"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                                    id="name" type="text" placeholder="Nombre del producto">
                            </div>

                            <div class="mb-4 mx-10">
                                <label class="block text-white text-sm font-bold mb-2" for="description">
                                    Descripción
                                </label>
                                <textarea v-model="form.description"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                                    id="description" placeholder="Descripción del producto">
                    </textarea>
                            </div>

                            <!-- Condicion de un select si poner descuento o no, y segun elegijo elegir el precio o el precio y el descuento a aplicar -->

                            <div class="mb-4 mx-10">
                                <label class="block text-white text-sm font-bold mb-2" for="isOffer">
                                    ¿Tiene Descuento?
                                </label>
                                <select v-model="form.isOffer"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                                    id="isOffer">
                                    <option :value="false">No</option>
                                    <option :value="true">Sí</option>
                                </select>
                            </div>

                            <div v-if="!form.isOffer" class="mb-4 mx-10">
                                <label class="block text-white text-sm font-bold mb-2" for="price">
                                    Precio x Kilo
                                </label>
                                <input v-model.number="form.price"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                                    id="price" type="number" placeholder="Precio del producto por kilo">
                            </div>
                            <div v-else>
                                <div class="mb-4 mx-10">
                                    <label class="block text-white text-sm font-bold mb-2" for="price">
                                        Precio x Kilo
                                    </label>
                                    <input v-model.number="form.price"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                                        id="price" type="number" placeholder="Precio del producto por kilo">
                                </div>
                                <div class="mb-4 mx-10">
                                    <label class="block text-white text-sm font-bold mb-2" for="discount">
                                        Descuento (%)
                                    </label>
                                    <input v-model.number="form.discount"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                                        id="discount" type="number" placeholder="Descuento a aplicar">
                                </div>
                                <!-- Mostrar precio final -->
                                <div class="mb-4 text-white">
                                    Precio con descuento: <strong class="text-blue-400">${{ finalPrice }}</strong>
                                </div>
                            </div>
                            <div class="mb-4 mx-10">
                                <label class="block text-white text-sm font-bold mb-2" for="stock">
                                    Stock
                                </label>
                                <input v-model.number="form.stock"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                                    id="stock" type="number" placeholder="Cantidad en stock">
                            </div>

                            <div class="mb-4 mx-10">
                                <span class="mr-2 text-sm text-gray-600 dark:text-white">Esta Activo</span>
                                <input type="checkbox" v-model="form.isActive" id="isActive" class="mr-2">
                            </div>


                            <div class="flex justify-end">
                                <button type="button" @click="closeCreateModal"
                                    class="bg-gray-500 text-white px-4 py-2 rounded mr-2 hover:bg-gray-600">Cancelar</button>
                                <button type="submit"
                                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    {{ isEditMode ? "Guardar Cambios" : "Crear" }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </transition>
        </AuthenticatedLayout>
    </template>
