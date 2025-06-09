<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ChevronLeft, Shuffle } from 'lucide-vue-next';
import { ref } from 'vue';

interface Vocabulary {
    id: number;
    hiragana: string;
    meaning: string;
    romaji: string;
    example: string;
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

// For toggling between table and card view
const viewMode = ref('card'); // 'table' or 'card'

// Create a shuffled copy of vocabularies
const shuffledVocabularies = ref<Vocabulary[]>([]);

// Flag to track if list is shuffled
const isShuffled = ref(false);

// Initialize with original order
shuffledVocabularies.value = [...props.vocabularies];

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
    shuffledVocabularies.value = [...props.vocabularies];
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
</script>

<template>
    <Head :title="category.name" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6">
            <div class="mb-6 flex items-center justify-between">
                <HeadingSmall :title="category.name" :description="category.description" />

                <div class="flex gap-2">
                    <Button as="a" href="/learning" variant="outline" size="sm" class="flex items-center gap-1">
                        <ChevronLeft class="h-4 w-4" />
                        Back to Categories
                    </Button>

                    <Button @click="viewMode = 'table'" size="sm" :variant="viewMode === 'table' ? 'default' : 'outline'"> Table View </Button>

                    <Button @click="viewMode = 'card'" size="sm" :variant="viewMode === 'card' ? 'default' : 'outline'"> Flashcards </Button>

                    <Button @click="isShuffled ? resetOrder() : shuffleVocabularies()" size="sm" variant="outline" class="flex items-center gap-1">
                        <Shuffle class="h-4 w-4" />
                        {{ isShuffled ? 'Reset Order' : 'Shuffle' }}
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
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(vocab, index) in shuffledVocabularies" :key="vocab.id">
                            <TableCell>{{ index + 1 }}</TableCell>
                            <TableCell class="text-lg font-bold">{{ vocab.hiragana }}</TableCell>
                            <TableCell>{{ vocab.romaji }}</TableCell>
                            <TableCell>{{ vocab.meaning }}</TableCell>
                            <TableCell>{{ vocab.example }}</TableCell>
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
            <div v-else-if="viewMode === 'card' && shuffledVocabularies.length > 0" class="flex flex-col items-center">
                <div class="mb-4 text-center">
                    <p class="text-sm text-gray-500">Card {{ currentCardIndex + 1 }} of {{ shuffledVocabularies.length }}</p>
                </div>

                <Card class="flex h-64 w-full max-w-md cursor-pointer justify-between" @click="toggleMeaning">
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
