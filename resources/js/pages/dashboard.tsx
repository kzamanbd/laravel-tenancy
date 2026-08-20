import { Head, Link } from '@inertiajs/react';
import ApexChart from '@/components/apex-chart';
import EmptyState from '@/components/empty-state';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';
import { index as tenants } from '@/routes/tenants';

type PageSummary = {
    id: number;
    name: string;
    slug: string | null;
    status: string;
    statusLabel: string;
    componentCount: number;
    degradedCount: number;
    openIncidents: number;
};

type IncidentSummary = {
    id: number;
    title: string;
    statusLabel: string;
    impact: string;
    impactLabel: string;
    startedAt: string | null;
    updateCount: number;
    awaitingApproval: boolean;
};

type MaintenanceSummary = {
    id: number;
    title: string;
    statusLabel: string;
    scheduledStartAt: string;
    scheduledEndAt: string;
};

type StatusSlice = { status: string; label: string; value: number };

type Props = {
    tenant?: { id: number; name: string | null } | null;
    overview: {
        scope: 'tenant' | 'portfolio';
        pages: PageSummary[];
        totals: { pages: number; components: number; openIncidents: number; subscribers: number };
        statusBreakdown: StatusSlice[];
        incidents: IncidentSummary[];
        maintenances: MaintenanceSummary[];
    };
};

/** Worst-status-wins colouring, matching the public page's banner. */
const statusTone: Record<string, string> = {
    operational: 'bg-green-500/15 text-green-700 dark:text-green-300',
    under_maintenance: 'bg-sky-500/15 text-sky-700 dark:text-sky-300',
    degraded_performance: 'bg-amber-500/15 text-amber-700 dark:text-amber-300',
    partial_outage: 'bg-orange-500/15 text-orange-700 dark:text-orange-300',
    major_outage: 'bg-destructive/15 text-destructive',
};

/** ApexCharts needs literal colours; these track the badge tones above. */
const statusHex: Record<string, string> = {
    operational: '#22c55e',
    under_maintenance: '#0ea5e9',
    degraded_performance: '#f59e0b',
    partial_outage: '#f97316',
    major_outage: '#ef4444',
};

const impactTone: Record<string, string> = {
    none: 'bg-muted text-muted-foreground',
    minor: 'bg-amber-500/15 text-amber-700 dark:text-amber-300',
    major: 'bg-orange-500/15 text-orange-700 dark:text-orange-300',
    critical: 'bg-destructive/15 text-destructive',
};

const formatDate = (value: string | null) =>
    value
        ? new Date(value).toLocaleString(undefined, {
              month: 'short',
              day: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
          })
        : '—';

function StatCard({ label, value, icon, tone }: { label: string; value: number; icon: string; tone: string }) {
    return (
        <Card>
            <CardContent className="flex items-center gap-4 p-5">
                <span className={cn('flex size-11 shrink-0 items-center justify-center rounded-xl', tone)}>
                    <span className={cn('size-5', icon)} />
                </span>
                <div>
                    <p className="text-2xl font-semibold tabular-nums">{value}</p>
                    <p className="text-sm text-muted-foreground">{label}</p>
                </div>
            </CardContent>
        </Card>
    );
}

export default function Dashboard({ tenant, overview }: Props) {
    const { totals, pages, incidents, maintenances, statusBreakdown, scope } = overview;

    // Slice colours mirror the status badges so the chart and the table agree.
    const donut = {
        series: statusBreakdown.map((slice) => slice.value),
        options: {
            chart: { type: 'donut', sparkline: { enabled: false } },
            labels: statusBreakdown.map((slice) => slice.label),
            colors: statusBreakdown.map((slice) => statusHex[slice.status] ?? '#94a3b8'),
            legend: { position: 'bottom' },
            dataLabels: { enabled: false },
            stroke: { width: 0 },
            tooltip: { y: { formatter: (value: number) => `${value} component${value === 1 ? '' : 's'}` } },
            plotOptions: { pie: { donut: { size: '70%' } } },
        },
    };

    return (
        <>
            <Head title="Dashboard" />

            <div className="flex flex-col gap-4">
                <div>
                    <h2 className="text-xl font-semibold">
                        {tenant ? (tenant.name ?? `Page #${tenant.id}`) : 'Your status pages'}
                    </h2>
                    <p className="text-sm text-muted-foreground">
                        {scope === 'tenant'
                            ? 'Live health for this status page.'
                            : 'Health across every status page you can reach.'}
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard
                        label={scope === 'tenant' ? 'Status page' : 'Status pages'}
                        value={totals.pages}
                        icon="icon-[mdi--server-network-outline]"
                        tone="bg-primary/10 text-primary"
                    />
                    <StatCard
                        label="Components"
                        value={totals.components}
                        icon="icon-[mdi--view-grid-outline]"
                        tone="bg-sky-500/15 text-sky-600 dark:text-sky-300"
                    />
                    <StatCard
                        label="Open incidents"
                        value={totals.openIncidents}
                        icon="icon-[mdi--alert-outline]"
                        tone={
                            totals.openIncidents > 0
                                ? 'bg-destructive/15 text-destructive'
                                : 'bg-green-500/15 text-green-600 dark:text-green-300'
                        }
                    />
                    <StatCard
                        label="Confirmed subscribers"
                        value={totals.subscribers}
                        icon="icon-[mdi--email-outline]"
                        tone="bg-amber-500/15 text-amber-600 dark:text-amber-300"
                    />
                </div>

                <div className="grid items-start gap-4 lg:grid-cols-3">
                    <Card className="lg:col-span-2">
                        <CardHeader className="flex-row items-center justify-between">
                            <CardTitle>Pages</CardTitle>
                            <Link href={tenants()} className="text-sm text-primary hover:underline">
                                Manage
                            </Link>
                        </CardHeader>
                        <CardContent>
                            {pages.length === 0 ? (
                                <EmptyState
                                    title="No status pages yet"
                                    description="Create your first status page to start reporting uptime."
                                />
                            ) : (
                                <div className="table-responsive">
                                    <table className="tw-table">
                                        <thead>
                                            <tr>
                                                <th>Page</th>
                                                <th>Status</th>
                                                <th className="text-right">Components</th>
                                                <th className="text-right">Open incidents</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {pages.map((page) => (
                                                <tr key={page.id}>
                                                    <td className="font-medium">{page.name}</td>
                                                    <td>
                                                        <Badge
                                                            className={cn(
                                                                'border-transparent',
                                                                statusTone[page.status],
                                                            )}
                                                        >
                                                            {page.statusLabel}
                                                        </Badge>
                                                    </td>
                                                    <td className="text-right tabular-nums">
                                                        {page.degradedCount > 0 && (
                                                            <span className="text-destructive">
                                                                {page.degradedCount}/
                                                            </span>
                                                        )}
                                                        {page.componentCount}
                                                    </td>
                                                    <td className="text-right tabular-nums">{page.openIncidents}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Component health</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {statusBreakdown.length === 0 ? (
                                <EmptyState title="No components" description="Add components to start reporting." />
                            ) : (
                                <ApexChart
                                    options={donut.options}
                                    series={donut.series}
                                    type="donut"
                                    height={260}
                                />
                            )}
                        </CardContent>
                    </Card>


                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>Recent incidents</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {incidents.length === 0 ? (
                                <EmptyState
                                    title="No incidents"
                                    description="Nothing has gone wrong yet. The page reports all clear."
                                />
                            ) : (
                                <div className="table-responsive">
                                    <table className="tw-table">
                                        <thead>
                                            <tr>
                                                <th>Incident</th>
                                                <th>Impact</th>
                                                <th>Status</th>
                                                <th>Started</th>
                                                <th className="text-right">Updates</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {incidents.map((incident) => (
                                                <tr key={incident.id}>
                                                    <td className="font-medium">
                                                        {incident.title}
                                                        {incident.awaitingApproval && (
                                                            <Badge className="ms-2 border-transparent bg-primary/10 text-primary">
                                                                Draft awaiting review
                                                            </Badge>
                                                        )}
                                                    </td>
                                                    <td>
                                                        <Badge
                                                            className={cn(
                                                                'border-transparent',
                                                                impactTone[incident.impact],
                                                            )}
                                                        >
                                                            {incident.impactLabel}
                                                        </Badge>
                                                    </td>
                                                    <td className="text-muted-foreground">{incident.statusLabel}</td>
                                                    <td className="text-muted-foreground">
                                                        {formatDate(incident.startedAt)}
                                                    </td>
                                                    <td className="text-right tabular-nums">{incident.updateCount}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>Upcoming maintenance</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {maintenances.length === 0 ? (
                                <EmptyState title="Nothing scheduled" description="No maintenance windows planned." />
                            ) : (
                                <ul className="space-y-4">
                                    {maintenances.map((window) => (
                                        <li key={window.id} className="flex gap-3">
                                            <span className="icon-[mdi--calendar-clock-outline] mt-0.5 size-4 shrink-0 text-muted-foreground" />
                                            <div className="min-w-0">
                                                <p className="truncate text-sm font-medium">{window.title}</p>
                                                <p className="text-xs text-muted-foreground">
                                                    {formatDate(window.scheduledStartAt)} →{' '}
                                                    {formatDate(window.scheduledEndAt)}
                                                </p>
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </CardContent>
                    </Card>
                </div>

            </div>
        </>
    );
}

Dashboard.layout = () => ({
    breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
});
