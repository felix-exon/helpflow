<template>
    <AppLayout>
        <Head title="Tickets" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight"> Tickets </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                    <div class="">
                        <ul>
                            <li v-for="ticket in tickets.data" :key="ticket.id">
                                <InertiaLink :href="route('tickets.show', ticket.id)">
                                    <div class="p-4">
                                        <div class="flex justify-between">
                                            <div class="font-semibold">#{{ ticket.id}} -  {{ ticket.reference_number }} - {{ ticket.title }}</div>
                                            <div class="rounded-full bg-blue-200 px-2 py-1 text-xs">{{ ticket.status }}</div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <div class="rounded-full bg-black text-white text-xs px-2 py-0.5">
                                                {{ ticket.category }}
                                            </div>
                                            <div :class="[ ticket.priority == 'high' ? 'bg-red-600' : 'bg-orange-600', 'rounded-full text-white text-xs px-2 py-0.5']">
                                                {{ ticket.priority }}
                                            </div>
                                        </div>
                                        <div class="text-sm">{{ ticket.description }}</div>
                                    </div>
                                </InertiaLink>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link as InertiaLink } from "@inertiajs/vue3";
import type { Ticket } from "@/models";

const props = defineProps<{
    tickets: Array<Ticket>
}>();

</script>
