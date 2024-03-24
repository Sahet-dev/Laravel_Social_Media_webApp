<script setup>
import TextInput from "@/Components/TextInput.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputTextArea from "@/Components/app/InputTextArea.vue";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
defineProps({
    form: Object
})
const editor = ClassicEditor;

const editorConfig = {
    toolbar: ['heading', '|', 'bold', 'italic', '|', 'link', 'numberedList', '|', 'outdent', 'indent', '|', 'bulletList', '|', 'blockQuote']
}

</script>

<template>
    <div class="mb-3">
        <label>Group Name</label>
        <TextInput
            type="text"
            class="mt-1 block w-full"
            v-model="form.name"
            required
            autofocus
        />
    </div>
    <div class="mb-3">
        <label class="mb-3">
            <Checkbox name="remember" v-model:checked="form.auto_approval" />
            Enable Auto Approval
        </label>
    </div>
    <div class="mb-3">
        <label>About Group</label>
        <ckeditor :editor="editor" v-model="form.about" :config="editorConfig"></ckeditor>

    </div>
</template>

<style scoped>

</style>
