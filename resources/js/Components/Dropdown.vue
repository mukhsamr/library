<script setup>
import { ChevronDownIcon } from "@heroicons/vue/24/outline";
import { ref } from 'vue';
import Button from "./Button.vue";

const props = defineProps({
    color: String,
    text: String
})

const uid = _.uniqueId('dropdown_')
const isOpen = ref(false)

</script>

<template>
    <div class="flex justify-center">
        <div>
            <div class="dropdown relative">
                <Button :color="color"
                    class="dropdown-toggle transition duration-150 ease-in-out flex items-center whitespace-nowrap"
                    type="button" :id="uid" :aria-expanded="isOpen" @click="isOpen = !isOpen">
                    {{ text }}
                    <ChevronDownIcon class="ml-4 h-4 w-4" />
                </Button>
                <ul class="dropdown-menu min-w-max absolute bg-white text-base z-50 float-left py-2 list-none text-left rounded-lg shadow-lg mt-1 m-0 bg-clip-padding border-none"
                    :class="{ 'hidden': !isOpen }" :aria-labelledby="uid" @click="isOpen = false">
                    <slot />
                </ul>
            </div>
        </div>
    </div>
</template>