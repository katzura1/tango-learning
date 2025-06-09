<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ChevronLeft, HeartIcon, Shuffle } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

interface Vocabulary {
    id: number;
    hiragana: string;
    meaning: string;
    romaji: string;
    example: string;
    is_favorited: boolean;
    status: string;
    last_reviewed_at: string | null;
}

interface Category {
    id: number;
    name: string;
    description: string;
    slug: string;
}

interface Props {
    category: Category;
    vocabularies: Vocabulary[];
}

const props = defineProps<Props>();

// Breadcrumb items
const breadcrumbItems = [
    { title: 'Learning', href: '/learning' },
    { title: props.category.name, href: `/learning/${props.category.slug}` },
];

const statusOptions = [
    { value: 'learning', label: 'Learning' },
    { value: 'familiar', label: 'Familiar' },
    { value: 'mastered', label: 'Mastered' },
];

// For toggling between table and card view
const viewMode = ref('card'); // 'table' or 'card'

// Create a shuffled copy of vocabularies
const shuffledVocabularies = ref<Vocabulary[]>([]);

// Flag to track if list is shuffled
const isShuffled = ref(false);

// Initialize with original order
shuffledVocabularies.value = props.vocabularies.map((vocab) => ({
    ...vocab,
    showStatusDropdown: false,
}));

// Function to shuffle vocabularies
function shuffleVocabularies() {
    const array = [...props.vocabularies];
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    shuffledVocabularies.value = array;
    currentCardIndex.value = 0;
    isShuffled.value = true;
}

// Function to reset original order
function resetOrder() {
    shuffledVocabularies.value = props.vocabularies.map((vocab) => ({
        ...vocab,
        showStatusDropdown: false,
    }));
    currentCardIndex.value = 0;
    isShuffled.value = false;
}

// For flashcard functionality
const currentCardIndex = ref(0);
const showMeaning = ref(false);

function nextCard() {
    currentCardIndex.value = (currentCardIndex.value + 1) % shuffledVocabularies.value.length;
    showMeaning.value = false;
}

function prevCard() {
    currentCardIndex.value = (currentCardIndex.value - 1 + shuffledVocabularies.value.length) % shuffledVocabularies.value.length;
    showMeaning.value = false;
}

function toggleMeaning() {
    showMeaning.value = !showMeaning.value;
}

async function toggleFavorite(vocabularyId: number) {
    try {
        const response = await axios.post(`/vocabulary/${vocabularyId}/favorite`);

        // Update local state
        const vocabIndex = shuffledVocabularies.value.findIndex((v) => v.id === vocabularyId);
        if (vocabIndex !== -1) {
            shuffledVocabularies.value[vocabIndex].is_favorited = response.data.favorited;
        }

        // Show toast
        toast.success(response.data.message);
    } catch (error) {
        console.error('Error toggling favorite:', error);
        toast.error('Failed to update favorite status');
    }
}

function onUpdateStatus(vocabularyId: number, newStatus: string) {
    console.log('onUpdateStatus called with:', vocabularyId, newStatus);
    updateStatus(vocabularyId, newStatus);
}

async function updateStatus(vocabularyId: number, newStatus: string) {
    console.log('updateStatus called with:', vocabularyId, newStatus);
    try {
        const response = await axios.post(`/vocabulary/${vocabularyId}/status`, {
            status: newStatus,
        });
        console.log('Response from server:', response.data);

        // Update local state
        const vocabIndex = shuffledVocabularies.value.findIndex((v) => v.id === vocabularyId);
        if (vocabIndex !== -1) {
            shuffledVocabularies.value[vocabIndex].status = newStatus;
        }

        toast.success('Status updated successfully');
    } catch (error) {
        console.error('Error updating status:', error);
        toast.error('Failed to update status');
    }
}
</script>

<template>
    <Head :title="category.name" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6">
            <div class="mb-6 space-y-4 sm:flex sm:items-center sm:justify-between sm:space-y-0">
                <HeadingSmall :title="category.name" :description="category.description" />

                <div class="flex flex-wrap justify-end gap-2">
                    <Button as="a" href="/learning" variant="outline" size="sm" class="flex items-center gap-1">
                        <ChevronLeft class="h-4 w-4" />
                        <span class="sm:inline">Back to Categories</span>
                    </Button>

                    <div class="flex gap-1">
                        <Button @click="viewMode = 'table'" size="sm" :variant="viewMode === 'table' ? 'default' : 'outline'"> Table View </Button>
                        <Button @click="viewMode = 'card'" size="sm" :variant="viewMode === 'card' ? 'default' : 'outline'"> Flashcards </Button>
                    </div>

                    <Button @click="isShuffled ? resetOrder() : shuffleVocabularies()" size="sm" variant="outline" class="flex items-center gap-1">
                        <Shuffle class="h-4 w-4" />
                        {{ isShuffled ? 'Reset' : 'Shuffle' }}
                    </Button>
                </div>
            </div>

            <!-- Table View -->
            <div v-if="viewMode === 'table'">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>No.</TableHead>
                            <TableHead>Hiragana</TableHead>
                            <TableHead>Romaji</TableHead>
                            <TableHead>Arti</TableHead>
                            <TableHead>Contoh</TableHead>
                            <TableHead class="w-10">Favorite</TableHead>
                            <TableHead>Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(vocab, index) in shuffledVocabularies" :key="vocab.id">
                            <TableCell>{{ index + 1 }}</TableCell>
                            <TableCell class="text-lg font-bold">{{ vocab.hiragana }}</TableCell>
                            <TableCell>{{ vocab.romaji }}</TableCell>
                            <TableCell>{{ vocab.meaning }}</TableCell>
                            <TableCell>{{ vocab.example }}</TableCell>
                            <TableCell class="w-10">
                                <Button variant="ghost" size="icon" @click.stop="toggleFavorite(vocab.id)" class="h-8 w-8">
                                    <HeartIcon class="h-5 w-5" :class="vocab.is_favorited ? 'fill-red-500 text-red-500' : 'text-gray-400'" />
                                </Button>
                            </TableCell>
                            <TableCell>
                                <div class="flex space-x-1">
                                    <Button
                                        v-for="option in statusOptions"
                                        :key="option.value"
                                        size="sm"
                                        :variant="vocab.status === option.value ? 'default' : 'outline'"
                                        @click="onUpdateStatus(vocab.id, option.value)"
                                        class="px-2 py-1 text-xs"
                                    >
                                        {{ option.label }}
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Empty State -->
                <div v-if="shuffledVocabularies.length === 0" class="py-12 text-center">
                    <h3 class="mb-1 text-lg font-medium">No vocabulary words</h3>
                    <p class="text-gray-500">There are no vocabulary words in this category yet.</p>
                </div>
            </div>

            <!-- Flashcard View -->
            <div v-else-if="viewMode === 'card' && shuffledVocabularies.length > 0" class="relative flex flex-col items-center">
                <div class="mb-4 text-center">
                    <p class="text-sm text-gray-500">Card {{ currentCardIndex + 1 }} of {{ shuffledVocabularies.length }}</p>
                </div>

                <Card class="relative flex h-64 w-full max-w-md cursor-pointer justify-between" @click="toggleMeaning">
                    <div class="absolute top-4 right-4">
                        <Button
                            variant="ghost"
                            size="icon"
                            @click.stop="toggleFavorite(shuffledVocabularies[currentCardIndex].id)"
                            class="flex h-12 w-12 items-center justify-center"
                        >
                            <HeartIcon
                                class="h-8 w-8"
                                :class="shuffledVocabularies[currentCardIndex].is_favorited ? 'fill-red-500 text-red-500' : 'text-gray-400'"
                            />
                        </Button>
                    </div>
                    <div class="absolute top-20 right-4">
                        <div class="flex flex-col items-end space-y-1">
                            <span class="text-xs text-gray-500">Status:</span>
                            <div class="flex space-x-1 rounded-md bg-white/90 p-1 shadow-sm dark:bg-gray-800/90">
                                <button
                                    v-for="option in statusOptions"
                                    :key="option.value"
                                    @click.stop="onUpdateStatus(shuffledVocabularies[currentCardIndex].id, option.value)"
                                    class="rounded px-2 py-0.5 text-xs font-medium transition-colors"
                                    :class="[
                                        shuffledVocabularies[currentCardIndex].status === option.value
                                            ? {
                                                  learning: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                  familiar: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                                  mastered: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                              }[option.value] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-white'
                                            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700',
                                    ]"
                                >
                                    {{ option.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <CardHeader>
                        <CardTitle class="text-center">
                            <span v-if="!showMeaning" class="text-4xl">{{ shuffledVocabularies[currentCardIndex].hiragana }}</span>
                            <span v-else class="text-2xl">{{ shuffledVocabularies[currentCardIndex].meaning }}</span>
                        </CardTitle>
                    </CardHeader>

                    <CardContent v-if="!showMeaning" class="text-center text-gray-500">
                        {{ shuffledVocabularies[currentCardIndex].romaji }}
                    </CardContent>

                    <CardContent v-else class="text-center">
                        <div class="mb-2">
                            <span class="text-gray-500">Romaji: </span>
                            <span>{{ shuffledVocabularies[currentCardIndex].romaji }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Contoh: </span>
                            <span>{{ shuffledVocabularies[currentCardIndex].example }}</span>
                        </div>
                    </CardContent>
                </Card>

                <div class="mt-6 flex gap-4">
                    <Button @click="prevCard" variant="outline">Previous</Button>
                    <Button @click="toggleMeaning" variant="outline">
                        {{ showMeaning ? 'Show Hiragana' : 'Show Meaning' }}
                    </Button>
                    <Button @click="nextCard">Next</Button>
                </div>
            </div>

            <!-- Empty Flashcard State -->
            <div v-else-if="viewMode === 'card' && shuffledVocabularies.length === 0" class="py-12 text-center">
                <h3 class="mb-1 text-lg font-medium">No flashcards available</h3>
                <p class="text-gray-500">There are no vocabulary words to create flashcards from.</p>
            </div>
        </div>
    </AppLayout>
</template>
