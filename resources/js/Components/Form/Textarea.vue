<script setup>

const props = defineProps({
    id: String,
    modelValue: String,
    label: String | Boolean,
    text: String,
    invalid: String
})

const uid = props.id ?? _.uniqueId('textarea_')

defineEmits(['update:modelValue'])

</script>

<template>
    <div class="w-full">
        <template v-if="label">
            <label :for="uid" class="form-label inline-block mb-1 text-gray-700">
                <slot>{{ label }}</slot>
            </label>

        </template>
        <textarea
            class="form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none"
            :class="{ 'border-red-500': invalid }" :id="uid" :value="modelValue" v-bind="$attrs"
            @input="$emit('update:modelValue', $event.target.value)"></textarea>
        <div class="text-red-500" v-if="invalid">{{ invalid }}</div>
    </div>
</template>