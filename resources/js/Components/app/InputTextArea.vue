<script setup>
import { onMounted, ref } from 'vue';
// import {formatNodes} from "tailwindcss/src/oxide/cli/build/utils.js";

const  props = defineProps({
    modelValue: {
        type: String,
        required: true,
    },
    placeholder: String,
    autoResize: {
        type: Boolean,
        default: true,
    }
})

const model = defineModel({
    type: String,
    required: true,
});


const emit = defineEmits(['update:modelValue'])


const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });

function onInputChange($event){
    emit('update:modelValue', $event.target.value)
    adjustHeight()
}
function adjustHeight(){
    if (props.autoResize){
        input.value.style.height = 'auto';
        input.value.style.height = input.value.scrollHeight + 'px';
    }
}

onMounted(()=>{
    adjustHeight()
})

</script>

<template>
    <textarea
        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
        :value="modelValue"
        @input="onInputChange"
        ref="input"
    />
</template>
