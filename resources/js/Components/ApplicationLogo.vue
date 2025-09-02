<template>
    <Link :href="route('home')" class="flex-shrink-0">
    <!-- agregando más tonos de rojo al logo -->
    <h1 class="text-2xl font-bold bg-gradient-to-r from-red-600 to-red-800 bg-clip-text text-transparent">
        🥩 Carnicería El Buen Corte</h1>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
