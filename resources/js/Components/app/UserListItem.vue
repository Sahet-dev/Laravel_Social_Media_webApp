<script setup>
import {Link} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps(
        {
            user: Object,
            forApprove: {
                type: Boolean,
                default: false
            }
        }
    )
    console.log(user.name)

defineEmits(['approve'])
</script>
<template>
    <div class="mb-3 cursor-pointer border-2 border-transparent hover:border-indigo-500">
        <Link :href="route('profile', user.username)" class="flex items-center gap-2 py-2 px-3">
            <img :src="user.avatar_url || '/img/cover.jpg'" class="w-[32px] rounded-full"/>
            <div class="flex justify-between flex-1">
                <h3 class="font-black">{{user.name}}</h3>
                <div class="flex gap-1">
                    <button v-if="forApprove" @click.prevent.stop="$emit('approve', user)"
                            class="text-xs py-1 px-2 rounded br-emerald hover:bg-emerald-100 text-white">Accept</button>
                    <button v-if="forApprove" @click.prevent.stop="$emit('reject', user)"
                            class="text-xs py-1 px-2 rounded br-red-500 hover:bg-red-100 text-white">Reject</button>
                </div>
            </div>
        </Link>
    </div>
</template>

<style scoped>

</style>
