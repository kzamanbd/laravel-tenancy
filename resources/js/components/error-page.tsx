import { Button } from '@/components/ui/button';
import { useNavigate } from '@/hooks/use-router';

// Rendered (outside the dashboard layout) for any unmatched route, so it doubles
// as the styled 404/401/500 page. The matching demo pages under /miscellaneous
// remain for the showcase.
const messages: Record<number, { title: string; detail: string }> = {
    401: {
        title: 'Access denied.',
        detail: "You don't have permission to view this page. Please sign in and try again.",
    },
    403: {
        title: 'Access denied.',
        detail: "You don't have permission to view this page. Please sign in and try again.",
    },
    404: {
        title: 'Oops, this page got lost.',
        detail: 'Sorry about that! Head back to the home page to get where you need to go.',
    },
};

const fallback = {
    title: 'Something went wrong on our end.',
    detail: 'An unexpected error occurred. Please try again in a moment.',
};

export default function ErrorPage({ statusCode = 500 }: { statusCode?: number }) {
    const navigate = useNavigate();
    const content = messages[statusCode] ?? fallback;

    return (
        <div className='flex min-h-screen flex-col items-center justify-center overflow-hidden bg-background text-foreground'>
            <div className='mx-auto max-w-200 px-4 py-10 text-center sm:px-6 lg:px-8'>
                <h1 className='block bg-linear-to-r from-pink-500 to-yellow-500 bg-clip-text text-7xl font-bold text-transparent sm:text-9xl dark:text-white'>
                    {statusCode}
                </h1>
                <p className='mt-3 text-muted-foreground'>{content.title}</p>
                <p className='text-muted-foreground'>{content.detail}</p>
                <div className='mt-5 flex flex-col items-center justify-center gap-2 sm:flex-row sm:gap-3'>
                    <Button onClick={() => navigate('/')}>
                        <svg
                            className='size-4 shrink-0'
                            xmlns='http://www.w3.org/2000/svg'
                            width='24'
                            height='24'
                            viewBox='0 0 24 24'
                            fill='none'
                            stroke='currentColor'
                            strokeWidth='2'
                            strokeLinecap='round'
                            strokeLinejoin='round'>
                            <path d='m15 18-6-6 6-6' />
                        </svg>
                        Back to Home
                    </Button>
                </div>
            </div>
        </div>
    );
}
