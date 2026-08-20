import type { InertiaLinkProps } from '@inertiajs/react';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}

/**
 * Convert a string into a URL friendly slug, mirroring Laravel's `Str::slug()`.
 *
 * Pass `keepTrailingSeparator` while the value is still being typed so a
 * separator the user just entered is not stripped away mid-word.
 */
export function slugify(value: string, keepTrailingSeparator = false): string {
    const slug = value
        .normalize('NFKD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+/, '');

    return keepTrailingSeparator
        ? slug.replace(/-{2,}$/, '-')
        : slug.replace(/-+$/, '');
}
