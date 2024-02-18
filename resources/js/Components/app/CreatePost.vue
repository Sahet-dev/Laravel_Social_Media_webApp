<script setup>

import {ref} from "vue";
import TextInput from "@/Components/TextInput.vue";
import InputTextArea from "@/Components/app/InputTextArea.vue";
import {useForm} from "@inertiajs/vue3";

const postCreating = ref(false);
const newPostForm = useForm({
    body: '',
})
function submit(){
    newPostForm.post(route('post.create'),{
        onSuccess: ()=>{
            newPostForm.reset()
        }
    })
}

</script>

<template>
    <div class="p-4 bg-white rounded-lg border mb-3">
        <InputTextArea @click="postCreating = true" class="mb-3 w-full" placeholder="Click here to create new Post" rows="1" v-model="newPostForm.body"/>
        <div v-if="postCreating" class="flex justify-between">
            <button type="button" class="rounded-md bg-indigo-600 px-3 py-2
             text-sm font-semibold text-white
            shadow-sm hover:bg-indigo-500 focus-visible:outline
            focus-visible:outline-2 focus-visible:outline-offset-2
            focus-visible:outline-indigo-600 relative">Attach File
                <input type="file" class="absolute left-0 top-0 right-0 bottom-0 opacity-0">
            </button>

            <button @click="submit" type="submit" class="rounded-md bg-indigo-600 px-3 py-2
             text-sm font-semibold text-white
            shadow-sm hover:bg-indigo-500 focus-visible:outline
            focus-visible:outline-2 focus-visible:outline-offset-2
            focus-visible:outline-indigo-600">Submit</button>
        </div>
    </div>
</template>

<style scoped>

</style>
