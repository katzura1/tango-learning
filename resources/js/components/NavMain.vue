<script setup lang="ts">
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { SidebarMenuButton, SidebarMenuItem, SidebarMenuSub, SidebarMenuSubButton, SidebarMenuSubItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import SidebarMenu from './ui/sidebar/SidebarMenu.vue';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
</script>

<template>
    <SidebarMenu class="px-2 py-0">
        <!-- <SidebarGroupLabel>Platform</SidebarGroupLabel> -->
        <Collapsible v-for="item in items" :key="item.title" as-child :default-open="item.href === page.url" class="group/collapsible">
            <template v-if="item.children && item.children.length > 0">
                <SidebarMenuItem>
                    <CollapsibleTrigger as-child>
                        <SidebarMenuButton :tooltip="item.title">
                            <component :is="item.icon" v-if="item.icon" />
                            <span>{{ item.title }}</span>
                            <ChevronRight class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <SidebarMenuSub>
                            <SidebarMenuSubItem v-for="subItem in item.children" :key="subItem.title">
                                <SidebarMenuSubButton as-child>
                                    <a :href="subItem.href">
                                        <span>{{ subItem.title }}</span>
                                    </a>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        </SidebarMenuSub>
                    </CollapsibleContent>
                </SidebarMenuItem>
            </template>
            <template v-else>
                <SidebarMenuItem>
                    <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                        <a :href="item.href">
                            <component :is="item.icon" v-if="item.icon" />
                            <span>{{ item.title }}</span>
                        </a>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </template>
        </Collapsible>
        <!-- <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
                <template v-if="item.children && item.children.length">
                    <SidebarMenu class="ml-4">
                        <SidebarMenuItem v-for="child in item.children" :key="child.title">
                            <SidebarMenuButton as-child :is-active="child.href === page.url" :tooltip="child.title">
                                <Link :href="child.href">
                                    <component :is="child.icon" v-if="child.icon" />
                                    <span>{{ child.title }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </template>
            </SidebarMenuItem>
        </SidebarMenu> -->
    </SidebarMenu>
</template>
