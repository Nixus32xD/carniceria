<script setup>
import { ref, reactive, watch } from 'vue'
import axios from 'axios'

const emit = defineEmits(['created', 'updated', 'deleted'])
const props = defineProps({
    cuts: Array,
    categories: Array // 👈 recibimos las categorías
})

// Watch para actualizar cuando cambien las categorías desde el padre
watch(() => props.categories, (newCategories) => {
    categories.value = [...newCategories]
}, { deep: true })

// Watch para actualizar cuando cambien los cortes desde el padre
watch(() => props.cuts, (newCuts) => {
    cuts.value = [...newCuts]
}, { deep: true })

const categories = ref([...props.categories])
const cuts = ref([...props.cuts])

const cutModal = ref(false)
const isEditCut = ref(false)
const editingCutId = ref(null)
const message = ref('')

const cutForm = reactive({
    name: '',
    category_id: '' // 👈 selecciona categoría
})

function resetCutForm() {
    cutForm.name = ''
    cutForm.category_id = ''
}

function openCutModal(cut = null) {
    if (cut) {
        cutForm.name = cut.name
        cutForm.category_id = cut.category_id
        isEditCut.value = true
        editingCutId.value = cut.id
    } else {
        resetCutForm()
        isEditCut.value = false
        editingCutId.value = null
    }
    cutModal.value = true
}

function closeCutModal() {
    cutModal.value = false
}

async function submitCut() {
    try {
        if (isEditCut.value) {
            const response = await axios.put(`/dashboard/cuts/${editingCutId.value}`, cutForm)
            const index = cuts.value.findIndex(c => c.id === editingCutId.value)
            if (index !== -1) {
                cuts.value.splice(index, 1, { ...response.data.cut }) // ✅ Reactividad
            }
            message.value = response.data.message
            emit('updated', response.data.cut)
        } else {
            const response = await axios.post(`/dashboard/cuts`, cutForm)
            cuts.value.push(response.data.cut)
            message.value = response.data.message
            emit('created', response.data.cut)
        }
        closeCutModal()
        setTimeout(() => (message.value = ''), 3000)
    } catch (e) {
        message.value = 'Error en la operación: ' + e.response?.data?.message || e.message;
        closeCutModal()
        setTimeout(() => (message.value = ''), 3000)
    }
}

async function deleteCut(id) {
    if (!confirm("¿Eliminar este corte?")) return
    try {
        await axios.delete(`/dashboard/cuts/${id}/delete`)
        const index = cuts.value.findIndex(c => c.id === id)
        if (index !== -1) {
            cuts.value.splice(index, 1) // ✅ Mantiene reactividad
        }
        message.value = 'Corte eliminado exitosamente'
        emit('deleted', id)
        setTimeout(() => (message.value = ''), 3000)
    } catch (e) {
        message.value = 'Error al eliminar: ' + e.response?.data?.message || e.message;
        setTimeout(() => (message.value = ''), 3000)
    }
}
</script>

<template>
    <div>
        <div class="mb-4 mt-4">
            <button @click="openCutModal()"
                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                    </path>
                </svg>
                Nuevo Corte
            </button>
        </div>

        <div v-if="message" class="mt-4 mb-4 p-3 rounded text-sm"
            :class="message.includes('Error') ? 'bg-red-100 border border-red-400 text-red-700' : 'bg-green-100 border border-green-400 text-green-700'">
            {{ message }}
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gradient-to-r from-red-50 to-red-100 dark:from-gray-700 dark:to-gray-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-red-800 uppercase tracking-wider">ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-red-800 uppercase tracking-wider">Nombre
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-red-800 uppercase tracking-wider">
                            Categoría</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-red-800 uppercase tracking-wider">
                            Acciones</th>
                    </tr>
                </thead>
                <tr v-for="cut in cuts" :key="cut.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{
                        cut.id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{
                        cut.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{categories.find(cat => cat.id === cut.category_id)?.name || 'Sin categoría'}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                        <button @click="openCutModal(cut)"
                            class="text-red-600 hover:text-red-900 dark:text-red-400 font-medium">Editar</button>
                        <button @click="deleteCut(cut.id)"
                            class="text-gray-600 hover:text-gray-900 dark:text-gray-400 font-medium">Eliminar</button>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Modal Corte -->
        <div v-if="cutModal" class="fixed inset-0 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg w-full max-w-md sm:max-w-lg md:max-w-xl">
                <h2 class="text-lg font-bold mb-4 dark:text-white">
                    {{ isEditCut ? "Editar Corte" : "Nuevo Corte" }}
                </h2>
                <form @submit.prevent="submitCut">
                    <label class="block text-sm font-bold mb-2 dark:text-white">Nombre del Corte</label>
                    <input v-model="cutForm.name"
                        class="shadow border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline dark:text-black"
                        type="text" placeholder="Nombre corte" />

                    <label class="block text-sm font-bold mb-2 mt-4 dark:text-white">Categoría</label>
                    <select v-model="cutForm.category_id"
                        class="shadow border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline dark:text-black">
                        <option disabled value="">Seleccione una categoría</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>

                    <div class="mt-4 flex justify-end flex-wrap gap-2">
                        <button type="button" @click="closeCutModal"
                            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancelar</button>
                        <button type="submit"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
