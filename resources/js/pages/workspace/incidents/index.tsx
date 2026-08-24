import { Form, Head, Link } from '@inertiajs/react';
import EmptyState from '@/components/empty-state';
import InputError from '@/components/input-error';
import StatusBadge, { impactTone, incidentStatusTone } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { formatDateTime } from '@/lib/dates';
import { cn } from '@/lib/utils';
import { show, store } from '@/routes/workspace/incidents';

type Option = { value: string; label: string };

type Incident = {
    id: number;
    title: string;
    status: string;
    statusLabel: string;
    impact: string;
    impactLabel: string;
    isPublished: boolean;
    startedAt: string | null;
    resolvedAt: string | null;
    updateCount: number;
};

type Props = {
    incidents: Incident[];
    components: { id: number; name: string }[];
    statuses: Option[];
    impacts: Option[];
    componentStatuses: Option[];
    can: { create: boolean };
};

export default function Incidents({ incidents, components, statuses, impacts, componentStatuses, can }: Props) {

    return (
        <>
            <Head title="Incidents" />

            <div className="flex flex-col gap-4">
                <div>
                    <h2 className="text-xl font-semibold">Incidents</h2>
                    <p className="text-sm text-muted-foreground">
                        Opening an incident posts its first update at the same time.
                    </p>
                </div>

                {can.create && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Open an incident</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Form
                                {...store.form()}
                                resetOnSuccess={['title', 'body']}
                                className="grid gap-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-4 sm:grid-cols-[2fr_1fr_1fr]">
                                            <div>
                                                <label htmlFor="title" className="form-label label-required">
                                                    Title
                                                </label>
                                                <input
                                                    id="title"
                                                    name="title"
                                                    required
                                                    placeholder="Elevated API error rate"
                                                    className={cn('form-control', errors.title && 'is-invalid')}
                                                />
                                                <InputError message={errors.title} />
                                            </div>

                                            <div>
                                                <label htmlFor="status" className="form-label">
                                                    Status
                                                </label>
                                                <select
                                                    id="status"
                                                    name="status"
                                                    defaultValue="investigating"
                                                    className="form-control"
                                                >
                                                    {statuses.map((option) => (
                                                        <option key={option.value} value={option.value}>
                                                            {option.label}
                                                        </option>
                                                    ))}
                                                </select>
                                                <InputError message={errors.status} />
                                            </div>

                                            <div>
                                                <label htmlFor="impact" className="form-label">
                                                    Impact
                                                </label>
                                                <select
                                                    id="impact"
                                                    name="impact"
                                                    defaultValue="minor"
                                                    className="form-control"
                                                >
                                                    {impacts.map((option) => (
                                                        <option key={option.value} value={option.value}>
                                                            {option.label}
                                                        </option>
                                                    ))}
                                                </select>
                                                <InputError message={errors.impact} />
                                            </div>
                                        </div>

                                        <div>
                                            <label htmlFor="body" className="form-label label-required">
                                                First update
                                            </label>
                                            <textarea
                                                id="body"
                                                name="body"
                                                required
                                                rows={3}
                                                placeholder="What customers are seeing, and what you are doing about it."
                                                className={cn('form-control', errors.body && 'is-invalid')}
                                            />
                                            <InputError message={errors.body} />
                                        </div>

                                        {components.length > 0 && (
                                            <div className="grid gap-4 sm:grid-cols-[2fr_1fr]">
                                                <fieldset>
                                                    <legend className="form-label">Affected components</legend>
                                                    <div className="flex flex-wrap gap-3">
                                                        {components.map((component) => (
                                                            <label
                                                                key={component.id}
                                                                className="inline-flex items-center"
                                                            >
                                                                <input
                                                                    type="checkbox"
                                                                    name="component_ids[]"
                                                                    value={component.id}
                                                                    className="form-check-input"
                                                                />
                                                                <span className="ms-2 text-sm">{component.name}</span>
                                                            </label>
                                                        ))}
                                                    </div>
                                                </fieldset>

                                                <div>
                                                    <label htmlFor="component_status" className="form-label">
                                                        Set those components to
                                                    </label>
                                                    <select
                                                        id="component_status"
                                                        name="component_status"
                                                        defaultValue="degraded_performance"
                                                        className="form-control"
                                                    >
                                                        {componentStatuses.map((option) => (
                                                            <option key={option.value} value={option.value}>
                                                                {option.label}
                                                            </option>
                                                        ))}
                                                    </select>
                                                    <InputError message={errors.component_status} />
                                                </div>
                                            </div>
                                        )}

                                        <div>
                                            <Button type="submit" disabled={processing}>
                                                {processing && <Spinner />}
                                                Open incident
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>
                )}

                <Card>
                    <CardHeader>
                        <CardTitle>
                            {incidents.length} incident{incidents.length === 1 ? '' : 's'}
                        </CardTitle>
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
                                                <td>
                                                    <Link
                                                        href={show({ incident: incident.id })}
                                                        className="font-medium hover:underline"
                                                    >
                                                        {incident.title}
                                                    </Link>
                                                    {!incident.isPublished && (
                                                        <span className="ms-2 text-xs text-muted-foreground">
                                                            Draft
                                                        </span>
                                                    )}
                                                </td>
                                                <td>
                                                    <StatusBadge
                                                        value={incident.impact}
                                                        label={incident.impactLabel}
                                                        tones={impactTone}
                                                    />
                                                </td>
                                                <td>
                                                    <StatusBadge
                                                        value={incident.status}
                                                        label={incident.statusLabel}
                                                        tones={incidentStatusTone}
                                                    />
                                                </td>
                                                <td className="text-muted-foreground">
                                                    {formatDateTime(incident.startedAt)}
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
            </div>
        </>
    );
}
