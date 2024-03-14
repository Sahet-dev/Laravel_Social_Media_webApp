<script setup>
import {Link} from "@inertiajs/vue3";

defineProps(
        {
            user: Object,
            forApprove: {
                type: Boolean,
                default: false
            },
            showRoleDropdown: {
                type: Boolean,
                default: false
            },
            disableRoleDropdown: {
                type: Boolean,
                default: false
            },
        }
    )


defineEmits(['approve', 'reject', 'roleChange'])
</script>
<template>
    <div class="mb-3  bg-white border-2 transition-all border-transparent hover:border-indigo-500">

        <div class="flex items-center gap-2 py-2 px-3">
            <Link :href="route('profile', user.username)">
                <img :src="user.avatar_url || '/img/cover.jpg'" class="w-[32px] rounded-full"/>
            </Link>
            <div class="flex justify-between flex-1">
                <Link :href="route('profile', user.username)" class="hover:underline">
                    <h3 class="font-black">{{user.name}}</h3>
                </Link>
                <div v-if="forApprove" class="flex gap-1">
                    <button
                            @click.prevent.stop="$emit('approve', user)"
                            class="text-xs py-1 px-2 rounded bg-emerald-500 hover:bg-emerald-600 text-white">Accept</button>
                    <button  @click.prevent.stop="$emit('reject', user)"
                            class="text-xs py-1 px-2
                            rounded bg-red-500 hover:bg-red-600
                            text-white">Reject</button>
                </div>
                <div v-if="showRoleDropdown" class="">
                        <select @change="$emit('roleChange', user, $event.target.value)" :disabled="disableRoleDropdown" class="rounded-md border-0 py-1 text-gray-900 shadow-sm
                        ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset
                        focus:ring-indigo-600 max-w-xs text-sm leading-6">
                            <option :selected="user.role === 'admin'">admin</option>
                            <option :selected="user.role === 'user'">user</option>
                        </select>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
