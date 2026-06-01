<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: String,
  id: {
    type: String,
    required: true
  },
  size: String
})

const modalSize = computed(() => props.size === 'modal-lg' ? 'max-w-4xl' : 'max-w-lg')

</script>

<template>
  <div class="modal fixed top-0 left-0 hidden w-full h-full outline-none overflow-x-hidden overflow-y-auto bg-black/30 z-50"
    :id="id" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" :aria-labelledby="id + 'Label'"
    aria-hidden="true">
    <div class="relative w-auto pointer-events-none mx-auto mt-16 px-4" :class="modalSize">
      <div
        class="border-none shadow-lg relative flex flex-col w-full pointer-events-auto bg-white bg-clip-padding rounded-md outline-none text-current">
        <div
          class="flex flex-shrink-0 items-center justify-between p-4 border-b border-gray-200 rounded-t-md">
          <h5 class="text-xl font-medium leading-normal text-gray-800" :id="id + 'Label'">
            {{ title }}
          </h5>
          <button type="button"
            class="box-content w-4 h-4 p-1 text-black border-none rounded-none opacity-50 focus:shadow-none focus:outline-none focus:opacity-100 hover:text-black hover:opacity-75 hover:no-underline"
            data-bs-dismiss="modal" aria-label="Close">×</button>
        </div>
        <div class="relative p-4">
          <slot />
        </div>
        <div
          class="flex flex-shrink-0 flex-wrap items-center justify-end p-4 border-t border-gray-200 rounded-b-md">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </div>
</template>
