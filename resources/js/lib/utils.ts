import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

/**
 * Combine multiple class names with Tailwind CSS support
 */
export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}
/**
 * Update a reactive ref value using either a direct value or an updater function
 * Used for TanStack Table state management
 */
export function valueUpdater<T>(updaterOrValue: ((old: T) => T) | T, ref: { value: T }) {
    // Explicitly check if updaterOrValue is a function using a more specific type guard
    if (
        updaterOrValue !== null &&
        typeof updaterOrValue === 'function' &&
        !(updaterOrValue instanceof Array) &&
        !(updaterOrValue instanceof Object)
    ) {
        // Now TypeScript knows this is a function
        ref.value = (updaterOrValue as (old: T) => T)(ref.value);
    } else {
        // This is a direct value
        ref.value = updaterOrValue as T;
    }
}
