<script setup>

defineProps({
    modelValue: String | Number,
    label: String,
    required: Boolean,
    invalid: String,
})

defineEmits(['update:modelValue'])

const id = _.uniqueId('datalist_')
const redTick = "after:content-['*'] after:ml-0.5 after:text-red-500"

const vFocus = {
    mounted: (el) => el.focus()
}

</script>

<template>
    <div class="w-full">
        <template v-if="label">
            <label :for="id" class="form-label inline-block mb-1 text-gray-700">
                <span :class="{ [redTick]: required }">
                    {{ label }}
                </span>
            </label>
        </template>
        <input type="text" :value="modelValue" v-bind="$attrs"
            class="form-control block w-full px-3 py-1 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-200 focus:outline-none"
            :class="{ 'border-red-500': invalid }" :list="id" onfocus="this.select()"
            @change="$emit('update:modelValue', $event.target.value)" v-focus>
        <div class="text-red-500" v-if="invalid">{{ invalid }}</div>

        <datalist :id="id">
            <slot />
        </datalist>
    </div>
</template>

<script>
export default {
    inheritAttrs: false
}
</script>