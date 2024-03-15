<script setup>
import TextInput from "@/Components/TextInput.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputTextArea from "@/Components/app/InputTextArea.vue";
defineProps({
    form: Object
})


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
        <InputTextArea v-model="form.about" class="w-full" />

    </div>
</template>

<style scoped>

</style>
