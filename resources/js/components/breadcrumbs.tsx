import { Link } from '@inertiajs/react';
import { Fragment } from 'react';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { useBreadcrumbs } from '@/hooks/use-breadcrumbs';
import { cn } from '@/lib/utils';

export function Breadcrumbs({ className }: { className?: string }) {
    const breadcrumbs = useBreadcrumbs();

    return (
        <Breadcrumb className={cn('min-w-0 print:hidden', className)}>
            <BreadcrumbList className='flex-nowrap'>
                {breadcrumbs.map((crumb, index) => (
                    <Fragment key={index}>
                        <BreadcrumbItem className={crumb.disabled ? '' : 'hidden sm:inline-flex'}>
                            {crumb.disabled ? (
                                <BreadcrumbPage className='font-semibold'>{crumb.title}</BreadcrumbPage>
                            ) : crumb.link ? (
                                <BreadcrumbLink asChild>
                                    <Link href={crumb.to}>{crumb.title}</Link>
                                </BreadcrumbLink>
                            ) : (
                                <span className='text-muted-foreground'>{crumb.title}</span>
                            )}
                        </BreadcrumbItem>
                        {index < breadcrumbs.length - 1 && <BreadcrumbSeparator className='mx-1 hidden sm:flex' />}
                    </Fragment>
                ))}
            </BreadcrumbList>
        </Breadcrumb>
    );
}

export default Breadcrumbs;
