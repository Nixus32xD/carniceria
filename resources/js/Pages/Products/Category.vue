<script setup>
import { ref, reactive } from 'vue'
import axios from 'axios'

const emit = defineEmits(['created', 'updated', 'deleted'])
const categoryModal = ref(false)
const isEditCategory = ref(false)
const editingCategoryId = ref(null)
const props = defineProps({
    categories: Array // Assuming you pass categories as a prop
})
const categories = ref([...props.categories])
const message = ref('')
const categoryForm = reactive({
    name: ''
})

function resetCategoryForm() {
    categoryForm.name = ''
}

function openCategoryModal(category = null) {
    if (category) {
        categoryForm.name = category.name
        isEditCategory.value = true
        editingCategoryId.value = category.id
    } else {
        resetCategoryForm()
        isEditCategory.value = false
        editingCategoryId.value = null
    }
    categoryModal.value = true
}

function closeCategoryModal() {
    categoryModal.value = false
}

async function submitCategory() {
    try {
        if (isEditCategory.value) {
            const response = await axios.put(`/dashboard/categories/${editingCategoryId.value}`, categoryForm)
            const index = categories.value.findIndex(c => c.id === editingCategoryId.value)
            if (index !== -1) {
                // Usar splice para mantener reactividad
                categories.value.splice(index, 1, { ...response.data.category })
            }
            message.value = response.data.message;
            emit('updated', response.data.category)
        } else {
            const response = await axios.post(`/dashboard/categories`, categoryForm)
            categories.value.push(response.data.category)
            message.value = response.data.message;
            emit('created', response.data.category)
        }
        closeCategoryModal()
        setTimeout(() => (message.value = ''), 3000);
    } catch (e) {
        message.value = 'Error en la operación: ' + e.response?.data?.message || e.message;
        setTimeout(() => (message.value = ''), 3000);
    }
}

async function deleteCategory(id) {
    if (!confirm("¿Eliminar esta categoría?")) return
    try {
        await axios.delete(`/dashboard/categories/${id}/delete`)
        const index = categories.value.findIndex(c => c.id === id)
        if (index !== -1) {
            categories.value.splice(index, 1) // ✅ Mantiene reactividad
        }
        message.value = 'Categoría eliminada exitosamente';
        emit('deleted', id)
        setTimeout(() => (message.value = ''), 3000);
    } catch (e) {
        message.value = 'Error al eliminar: ' + e.response?.data?.message || e.message;
        setTimeout(() => (message.value = ''), 3000);
    }
}
</script>

<template>
    <div>
        <div class="mb-4 mt-4">
            <button @click="openCategoryModal()"
                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                    </path>
                </svg>
                Nueva Categoría
            </button>
        </div>
        <div v-if="message" class="mt-4 mb-4 p-3 rounded text-sm" :class="message.includes('Error')
            ? 'bg-red-100 border border-red-400 text-red-700'
            : 'bg-green-100 border border-green-400 text-green-700'">
            {{ message }}
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gradient-to-r from-red-50 to-red-100 dark:from-gray-700 dark:to-gray-600">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">
                            ID</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">
                            Nombre</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-gray-300 uppercase tracking-wider">
                            Acciones</th>
                    </tr>
                </thead>
                <tr v-for="cat in categories" :key="cat.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ cat.id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ cat.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                        <button @click="openCategoryModal(cat)"
                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-medium">Editar</button>
                        <button @click="deleteCategory(cat.id)"
                            class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300 font-medium">Eliminar</button>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Modal Categoría -->
        <div v-if="categoryModal" class="fixed inset-0 flex items-center justify-center bg-black/50">
            <div class="bg-white p-6 rounded-lg w-1/3 dark:bg-gray-800">
                <h2 class="text-lg font-bold mb-4 dark:text-white">
                    {{ isEditCategory ? "Editar Categoría" : "Nueva Categoría" }}
                </h2>
                <form @submit.prevent="submitCategory">
                    <label class="block text-white text-sm font-bold mb-2" for="name">
                        Nombre de la Categoria
                    </label>
                    <input v-model="categoryForm.name"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-white leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                        type="text" placeholder="Nombre categoría" />
                    <div class="mt-4 flex justify-end">
                        <button type="button" @click="closeCategoryModal"
                            class="bg-gray-500 text-white px-4 py-2 rounded mr-2 hover:bg-gray-600">Cancelar</button>
                        <button type="submit"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
