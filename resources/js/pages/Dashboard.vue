<script setup lang="ts">
import Skeleton from '@/components/ui/skeleton/Skeleton.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

type DataDashboard = {
    totalVocabularyLearned: number;
    progressOverview: {
        learning: number;
        familiar: number;
        mastered: number;
    };
};

const data = ref<DataDashboard | null>(null);

const isLoading = ref(false);

async function fetchData() {
    isLoading.value = true;
    try {
        // await 5 seconds to simulate loading
        await new Promise((resolve) => setTimeout(resolve, 5000));
        // Simulate an API call
        await axios
            .get(route('data.dashboard'))
            .then((response) => {
                // Handle the response data
                console.log('Data fetched successfully:', response.data);
                data.value = {
                    totalVocabularyLearned: response.data.totalCount,
                    progressOverview: {
                        learning: response.data.learningPercentage,
                        familiar: response.data.familiarPercentage,
                        mastered: response.data.masteredPercentage,
                    },
                };
                console.log('Processed data:', data.value);
            })
            .catch((error) => {
                console.error('Error fetching data:', error);
            });
    } catch (error) {
        console.error('Error fetching data:', error);
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    fetchData();
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <h3 class="scroll-m-20 text-lg font-semibold tracking-tight">Total Vocabulary Learned</h3>
                    <template v-if="!isLoading">
                        <div class="mt-2 flex items-end justify-between">
                            <div>
                                <p class="text-3xl font-bold transition-all duration-1000 ease-out" v-text="data?.totalVocabularyLearned"></p>
                                <p class="text-sm text-muted-foreground transition-all duration-1000 ease-out">+12 this week</p>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="mt-2 space-y-2">
                            <Skeleton class="h-8 w-12" />
                            <Skeleton class="h-4 w-50" />
                        </div>
                    </template>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <h3 class="scroll-m-20 text-lg font-semibold tracking-tight">Progress Overview</h3>
                    <div class="mt-2 flex flex-col gap-2">
                        <template v-if="!isLoading">
                            <div class="flex items-center justify-between">
                                <span class="text-sm">Learning</span>
                                <div class="h-2 w-32 rounded-full bg-primary-foreground">
                                    <div
                                        class="h-full rounded-full bg-primary transition-all duration-1000 ease-out"
                                        :style="{ width: (data?.progressOverview.learning ?? 0) + '%' }"
                                    ></div>
                                </div>
                                <span class="text-sm">{{ data?.progressOverview.learning }}%</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm">Familiar</span>
                                <div class="h-2 w-32 rounded-full bg-primary-foreground">
                                    <div
                                        class="h-full rounded-full bg-primary transition-all duration-1000 ease-out"
                                        :style="{ width: (data?.progressOverview.familiar ?? 0) + '%' }"
                                    ></div>
                                </div>
                                <span class="text-sm">{{ data?.progressOverview.familiar }}%</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm">Mastered</span>
                                <div class="h-2 w-32 rounded-full bg-primary-foreground">
                                    <div
                                        class="h-full rounded-full bg-primary transition-all duration-1000 ease-out"
                                        :style="{ width: (data?.progressOverview.mastered ?? 0) + '%' }"
                                    ></div>
                                </div>
                                <span class="text-sm">{{ data?.progressOverview.mastered }}%</span>
                            </div>
                        </template>
                        <template v-else>
                            <div class="space-y-2">
                                <Skeleton class="h-4 w-full" />
                                <Skeleton class="h-4 w-full" />
                                <Skeleton class="h-4 w-full" />
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
