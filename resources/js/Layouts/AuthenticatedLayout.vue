<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import {Link, router, usePage} from '@inertiajs/vue3';
import TextInput from "@/Components/TextInput.vue";

const showingNavigationDropdown = ref(false);
const searchKeyword =ref(usePage().props.search);
const authUser = usePage().props.auth.user;

function search() {
    router.get(route('search', searchKeyword.value))
    console.log()
}

</script>

<template>
        <div class="h-full overflow-hidden flex flex-col bg-gray-100">
            <nav class="bg-white border-b border-gray-100 ">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between gap-4 h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
<!--                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">-->
<!--                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">-->
<!--                                    Dashboard-->
<!--                                </NavLink>-->
<!--                            </div>-->
                        </div>

                        <div class="flex-1">
                            <TextInput v-model="searchKeyword" aria-placeholder="Search" class="w-full" @keyup.enter="search"/>
                        </div>
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown v-if="authUser" align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {{ authUser.name }}

                                                <svg
                                                    class="ms-2 -me-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile', {username: authUser.username})"> Profile </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                                <div v-else>
                                    <Link :href="route('login')">Login</Link>
                                </div>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden"
                >
<!--                    <div class="pt-2 pb-3 space-y-1">-->
<!--                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">-->
<!--                            Dashboard-->
<!--                        </ResponsiveNavLink>-->
<!--                    </div>-->

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <template v-if="authUser">
                            <div class="px-4">
                                <div class="font-medium text-base text-gray-800">
                                    {{ authUser.name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">{{ authUser.email }}</div>
                            </div>

                            <div class="mt-3 space-y-1">
                                <ResponsiveNavLink :href="route('profile', {username: authUser.username})"> Profile </ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                    Log Out
                                </ResponsiveNavLink>
                            </div>
                        </template>
                        <template>
                            Login
                        </template>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-hidden">
                <slot />
            </main>
        </div>
</template>
