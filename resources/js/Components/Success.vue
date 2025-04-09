<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="transform opacity-0"
        enter-to-class="transform opacity-100"
        leave-active-class="transition ease-in duration-300"
        leave-from-class="transform opacity-100"
        leave-to-class="transform opacity-0"
    >
        <div v-if="isVisible" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-r shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm leading-5 font-medium">
                        {{ message }}
                    </p>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    message: {
        type: String,
        default: null
    }
});

const isVisible = ref(false);
let timeout = null;

const startTimer = () => {
    if (timeout) clearTimeout(timeout);
    isVisible.value = true;
    timeout = setTimeout(() => {
        isVisible.value = false;
    }, 4000);
};

watch(() => props.message, (newValue) => {
    if (newValue) {
        startTimer();
    }
}, { immediate: true });

onBeforeUnmount(() => {
    if (timeout) clearTimeout(timeout);
});
</script>