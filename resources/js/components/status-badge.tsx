import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

/**
 * One place deciding what each status looks like, so the dashboard, the admin
 * tables, and the public page can never drift apart on colour.
 */
export const componentStatusTone: Record<string, string> = {
    operational: 'bg-green-500/15 text-green-700 dark:text-green-300',
    under_maintenance: 'bg-sky-500/15 text-sky-700 dark:text-sky-300',
    degraded_performance: 'bg-amber-500/15 text-amber-700 dark:text-amber-300',
    partial_outage: 'bg-orange-500/15 text-orange-700 dark:text-orange-300',
    major_outage: 'bg-destructive/15 text-destructive',
};

export const incidentStatusTone: Record<string, string> = {
    investigating: 'bg-destructive/15 text-destructive',
    identified: 'bg-orange-500/15 text-orange-700 dark:text-orange-300',
    monitoring: 'bg-amber-500/15 text-amber-700 dark:text-amber-300',
    resolved: 'bg-green-500/15 text-green-700 dark:text-green-300',
};

export const impactTone: Record<string, string> = {
    none: 'bg-muted text-muted-foreground',
    minor: 'bg-amber-500/15 text-amber-700 dark:text-amber-300',
    major: 'bg-orange-500/15 text-orange-700 dark:text-orange-300',
    critical: 'bg-destructive/15 text-destructive',
};

export const maintenanceStatusTone: Record<string, string> = {
    scheduled: 'bg-sky-500/15 text-sky-700 dark:text-sky-300',
    in_progress: 'bg-amber-500/15 text-amber-700 dark:text-amber-300',
    completed: 'bg-green-500/15 text-green-700 dark:text-green-300',
    cancelled: 'bg-muted text-muted-foreground',
};

export default function StatusBadge({
    value,
    label,
    tones,
    className,
}: {
    value: string;
    label: string;
    tones: Record<string, string>;
    className?: string;
}) {
    return (
        <Badge className={cn('border-transparent', tones[value] ?? 'bg-muted text-muted-foreground', className)}>
            {label}
        </Badge>
    );
}
