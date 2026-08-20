/**
 * Sets the document title, mirroring the Nuxt `titleTemplate: '%s - Orbin'`.
 * React 19 hoists a `<title>` rendered anywhere in the tree into `<head>`.
 */
export default function PageTitle({ title }: { title?: string }) {
    return <title>{title ? `${title} - Orbin` : 'Orbin'}</title>;
}
