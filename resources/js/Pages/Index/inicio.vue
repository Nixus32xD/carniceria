<template>
    <Head title="Inicio" />
    <div class="min-h-screen bg-background">
        <!-- Navigation Header -->
        <header class="bg-card border-b border-border shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <Link :href="route('home')" class="flex-shrink-0">
                        <!-- agregando más tonos de rojo al logo -->
                        <h1
                            class="text-2xl font-bold bg-gradient-to-r from-red-600 to-red-800 bg-clip-text text-transparent">
                            🥩 Carnicería El Buen Corte</h1>
                        </Link>
                    </div>

                    <!-- Navigation Links -->
                    <nav class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-8">
                            <!-- agregando hover con tonos de rojo -->
                            <a href="#"
                                class="text-foreground hover:text-red-600 px-3 py-2 text-sm font-medium transition-colors">Inicio</a>
                            <a href="#"
                                class="text-muted-foreground hover:text-red-600 px-3 py-2 text-sm font-medium transition-colors">Productos</a>
                            <a href="#"
                                class="text-muted-foreground hover:text-red-600 px-3 py-2 text-sm font-medium transition-colors">Ofertas</a>
                            <a href="#"
                                class="text-muted-foreground hover:text-red-600 px-3 py-2 text-sm font-medium transition-colors">Nosotros</a>
                            <a href="#"
                                class="text-muted-foreground hover:text-red-600 px-3 py-2 text-sm font-medium transition-colors">Contacto</a>
                        </div>
                    </nav>

                    <!-- Cart Button -->
                    <div class="flex items-center">
                        <button @click="toggleCart" cambiando botón del carrito a tonos de rojo
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 11-4 0v-6m4 0V9a2 2 0 10-4 0v4.01">
                                </path>
                            </svg>
                            Carrito ({{ cartItems }})
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Carousel -->
        <section class="relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="relative overflow-hidden rounded-xl bg-card shadow-lg">
                    <div class="flex transition-transform duration-500 ease-in-out"
                        :style="{ transform: `translateX(-${currentSlide * 100}%)` }">
                        <div v-for="(offer, index) in offers" :key="index" class="w-full flex-shrink-0">
                            <!-- agregando gradientes de rojo al fondo del carrusel -->
                            <div
                                class="relative h-96 flex items-center justify-center bg-gradient-to-r from-red-50 via-red-100 to-orange-50">
                                <div class="text-center px-8">
                                    <!-- títulos con gradiente de rojo -->
                                    <h2
                                        class="text-4xl font-bold bg-gradient-to-r from-red-700 to-red-900 bg-clip-text text-transparent mb-4">
                                        {{ offer.title }}</h2>
                                    <p class="text-xl text-gray-700 mb-6">{{ offer.description }}</p>
                                    <!-- botones con tonos de rojo -->
                                    <button
                                        class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-8 py-3 rounded-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl">
                                        {{ offer.buttonText }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carousel Controls -->
                    <button @click="prevSlide"
                        class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/90 hover:bg-red-50 text-red-600 p-2 rounded-full shadow-lg transition-colors border border-red-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </button>
                    <button @click="nextSlide"
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/90 hover:bg-red-50 text-red-600 p-2 rounded-full shadow-lg transition-colors border border-red-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </button>

                    <!-- Carousel Indicators -->
                    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
                        <button v-for="(offer, index) in offers" :key="index" @click="currentSlide = index" indicadores
                            con tonos de rojo
                            :class="['w-3 h-3 rounded-full transition-colors', currentSlide === index ? 'bg-red-600' : 'bg-red-200']"></button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filters Section - ACTUALIZADO -->
        <section class="bg-red-50/30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="bg-card rounded-lg p-6 shadow-sm border border-red-100">
                    <h3 class="text-lg font-semibold text-red-800 mb-4">Filtrar Productos</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Category Filter - ACTUALIZADO -->
                        <div>
                            <label class="block text-sm font-medium text-red-700 mb-2">Categoría</label>
                            <select v-model="selectedCategory"
                                class="w-full px-3 py-2 bg-input border border-red-200 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Todas las categorías</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Cut Filter - ACTUALIZADO -->
                        <div>
                            <label class="block text-sm font-medium text-red-700 mb-2">Tipo de Corte</label>
                            <select v-model="selectedCut"
                                class="w-full px-3 py-2 bg-input border border-red-200 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <option value="">Todos los cortes</option>
                                <option v-for="cut in cuts" :key="cut.id" :value="cut.id">
                                    {{ cut.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Price Range - ACTUALIZADO -->
                        <div>
                            <label class="block text-sm font-medium text-red-700 mb-2">Precio máximo</label>
                            <input v-model.number="maxPrice" type="range" min="0" max="10000" step="100"
                                class="w-full h-2 bg-red-200 rounded-lg appearance-none cursor-pointer slider-red">
                            <div class="text-sm text-red-600 mt-1 font-medium">${{ maxPrice }}</div>
                        </div>

                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-red-700 mb-2">Buscar</label>
                            <input v-model="searchTerm" type="text" placeholder="Buscar productos..."
                                class="w-full px-3 py-2 bg-input border border-red-200 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Grid - ACTUALIZADO -->
        <section>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <h2
                    class="text-3xl font-bold bg-gradient-to-r from-red-700 to-red-900 bg-clip-text text-transparent mb-8 text-center">
                    Nuestros Productos</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div v-for="product in filteredProducts" :key="product.id"
                        class="bg-card rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden group border border-red-100">
                        <div class="relative">
                            <img :src="product.image || '/placeholder.svg'" :alt="product.name"
                                class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute top-2 right-2">
                                <span v-if="product.isOffer"
                                    class="bg-gradient-to-r from-red-500 to-red-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-md">
                                    OFERTA
                                </span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-foreground mb-2">{{ product.name }}</h3>
                            <p class="text-sm text-muted-foreground mb-3">{{ product.description }}</p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span v-if="product.isOffer && product.offerPrice"
                                        class="text-sm text-muted-foreground line-through">
                                        ${{ product.price }} <!-- Precio original tachado -->
                                    </span>
                                    <span class="text-lg font-bold text-red-600">
                                        ${{ product.isOffer && product.offerPrice ? product.offerPrice : product.price
                                        }}
                                    </span>
                                </div>
                                <button @click="addToCart(product)"
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm hover:shadow-md">
                                    Agregar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- Footer -->
        <footer class="bg-card border-t border-red-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <!-- título del footer con gradiente de rojo -->
                        <h3
                            class="text-lg font-semibold bg-gradient-to-r from-red-700 to-red-900 bg-clip-text text-transparent mb-4">
                            Carnicería El Buen Corte</h3>
                        <p class="text-muted-foreground">La mejor calidad en carnes frescas desde 1985.</p>
                    </div>
                    <div>
                        <!-- subtítulos con color rojo -->
                        <h4 class="font-semibold text-red-700 mb-4">Contacto</h4>
                        <p class="text-muted-foreground">📍 Av. Principal 123</p>
                        <p class="text-muted-foreground">📞 (555) 123-4567</p>
                        <p class="text-muted-foreground">✉️ info@elbuencorte.com</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-red-700 mb-4">Horarios</h4>
                        <p class="text-muted-foreground">Lun - Vie: 8:00 - 20:00</p>
                        <p class="text-muted-foreground">Sábado: 8:00 - 18:00</p>
                        <p class="text-muted-foreground">Domingo: 9:00 - 15:00</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-red-700 mb-4">Síguenos</h4>
                        <div class="flex space-x-4">
                            <!-- enlaces de redes sociales con hover rojo -->
                            <a href="#" class="text-muted-foreground hover:text-red-600 transition-colors">Facebook</a>
                            <a href="#" class="text-muted-foreground hover:text-red-600 transition-colors">Instagram</a>
                            <a href="#" class="text-muted-foreground hover:text-red-600 transition-colors">WhatsApp</a>
                        </div>
                    </div>
                </div>
                <div class="border-t border-red-200 mt-8 pt-8 text-center">
                    <p class="text-muted-foreground">&copy; 2024 Carnicería El Buen Corte. Todos los derechos
                        reservados.</p>
                </div>
            </div>
        </footer>
    </div>
</template>


<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3';

// Props
const props = defineProps({
    products: {
        type: Array,
        required: true
    },
    categories: {
        type: Array,
        required: true
    },
    cuts: {
        type: Array,
        required: true
    }
})

// Reactive data
const currentSlide = ref(0)
const cartItems = ref(0)
const selectedCategory = ref('')
const selectedCut = ref('')
const maxPrice = ref(10000)
const searchTerm = ref('')

// Offers data
const offers = ref([
    {
        title: "¡Ofertas de Temporada!",
        description: "Hasta 30% de descuento en cortes premium",
        buttonText: "Ver Ofertas"
    },
    {
        title: "Carne Fresca Diaria",
        description: "Recibimos carne fresca todos los días",
        buttonText: "Conocer Más"
    },
    {
        title: "Embutidos Artesanales",
        description: "Elaborados con recetas tradicionales",
        buttonText: "Probar Ahora"
    }
])

const filteredProducts = computed(() => {
    return props.products.filter(product => {
        const matchesCategory = !selectedCategory.value || product.category_id == selectedCategory.value
        const matchesCut = !selectedCut.value || product.cut_id == selectedCut.value

        // Usar el precio correcto (oferta o normal) para la comparación
        const productPrice = product.isOffer && product.offerPrice ? product.offerPrice : product.price
        const matchesPrice = productPrice <= maxPrice.value

        const matchesSearch = !searchTerm.value ||
            product.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            (product.description && product.description.toLowerCase().includes(searchTerm.value.toLowerCase()))

        return matchesCategory && matchesCut && matchesPrice && matchesSearch
    })
})

// Methods
const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % offers.value.length
}

const prevSlide = () => {
    currentSlide.value = currentSlide.value === 0 ? offers.value.length - 1 : currentSlide.value - 1
}

const addToCart = (product) => {
    cartItems.value++
    console.log('Added to cart:', product.name)
}

const toggleCart = () => {
    console.log('Toggle cart')
}

// Auto-advance carousel
onMounted(() => {
    setInterval(() => {
        nextSlide()
    }, 5000)
})
</script>
