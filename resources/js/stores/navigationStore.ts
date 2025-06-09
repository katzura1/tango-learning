import type { NavItem } from '@/types';
import { BookOpen, Folder, LayoutGrid, Users } from 'lucide-vue-next';
import { defineStore } from 'pinia';

export const useNavigationStore = defineStore('navigation', {
    state: () => ({
        mainNavItems: [
            {
                title: 'Dashboard',
                href: '/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'Master',
                href: '#',
                icon: Users,
                children: [
                    {
                        title: 'User',
                        href: route('users.index'),
                    },
                    {
                        title: 'Category',
                        href: route('categories.index'),
                    },
                ],
            },
            {
                title: 'Learning',
                href: '/learning',
                icon: BookOpen,
            },
        ] as NavItem[],

        rightNavItems: [
            {
                title: 'Repository',
                href: 'https://github.com/laravel/vue-starter-kit',
                icon: Folder,
            },
            {
                title: 'Documentation',
                href: 'https://laravel.com/docs/starter-kits#vue',
                icon: BookOpen,
            },
        ] as NavItem[],
    }),

    // // Optional: add actions if you need to modify the navigation dynamically
    // actions: {
    //     addNavItem(item: NavItem) {
    //         this.mainNavItems.push(item);
    //     },

    //     // You could add methods to handle permissions/roles here
    //     filterByPermission(userPermissions: string[]) {
    //         // Logic to filter nav items based on permissions
    //     },
    // },
});
