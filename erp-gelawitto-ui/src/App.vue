<script setup lang="ts">
import { RouterView } from 'vue-router';
import { cargandoVista } from '@/router';
</script>

<template>
    <div>
        <div
            v-if="cargandoVista"
            class="fixed top-0 left-0 right-0 h-0.5 bg-fresa z-50 animate-pulse"
        />
        <RouterView />
    </div>
</template>
