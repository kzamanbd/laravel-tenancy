import { Form, Head, Link, router } from '@inertiajs/react';
import InputError from '@/components/input-error';
import StatusBadge, { impactTone, incidentStatusTone } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { formatDateTime } from '@/lib/dates';
import { cn } from '@/lib/utils';
import { index, update as updateIncident } from '@/routes/workspace/incidents';
import { publish, store as storeUpdate } from '@/routes/workspace/incidents/updates';

type Option = { value: string; label: string };

type Update = {
    id: number;
    status: string;
    statusLabel: string;
    body: string;
    isAiDrafted: boolean;
    isPublished: boolean;
    awaitingApproval: boolean;
    author: string | null;
    publishedAt: string | null;
    createdAt: string | null;
};

type Props = {
    incident: {
        id: number;
        title: string;
        status: string;
        statusLabel: string;
        impact: string;
        impactLabel: string;
        isPublished: boolean;
        startedAt: string | null;
        resolvedAt: string | null;
        components: { id: number; name: string }[];
        updates: Update[];
    };
    statuses: Option[];
    impacts: Option[];
    can: { update: boolean; comment: boolean; delete: boolean };
};

export default function IncidentShow({ incident, statuses, impacts, can }: Props) {

    return (
        <>
            <Head title={incident.title} />

            <div className="flex flex-col gap-4">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <Link
                            href={index()}
                            className="text-sm text-primary hover:underline"
                        >
                            ← All incidents
                        </Link>
                        <h2 className="mt-1 text-xl font-semibold">{incident.title}</h2>
                        <p className="text-sm text-muted-foreground">
                            Started {formatDateTime(incident.startedAt)}
                            {incident.resolvedAt && ` · Resolved ${formatDateTime(incident.resolvedAt)}`}
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <StatusBadge
                            value={incident.impact}
                            label={incident.impactLabel}
                            tones={impactTone}
                        />
                        <StatusBadge
                            value={incident.status}
                            label={incident.statusLabel}
                            tones={incidentStatusTone}
                        />
                    </div>
                </div>

                {incident.components.length > 0 && (
                    <p className="text-sm text-muted-foreground">
                        Affects {incident.components.map((component) => component.name).join(', ')}
                    </p>
                )}

                <div className="grid items-start gap-4 lg:grid-cols-3">
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>Timeline</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ul className="activity-timeline-list">
                                {incident.updates.map((update) => (
                                    <li key={update.id} className="activity-item">
                                        <div className="activity-content">
                                            <div className="activity-header flex-wrap gap-2">
                                                <StatusBadge
                                                    value={update.status}
                                                    label={update.statusLabel}
                                                    tones={incidentStatusTone}
                                                />
                                                <span className="text-xs text-muted-foreground">
                                                    {formatDateTime(update.publishedAt ?? update.createdAt)}
                                                    {update.author && ` · ${update.author}`}
                                                </span>
                                                {update.awaitingApproval && (
                                                    <span className="rounded-md bg-primary/10 px-2 py-0.5 text-xs text-primary">
                                                        Draft awaiting review
                                                    </span>
                                                )}
                                            </div>

                                            <p className="mt-1 text-sm whitespace-pre-line">{update.body}</p>

                                            {update.awaitingApproval && can.comment && (
                                                <Button
                                                    size="xs"
                                                    className="mt-2"
                                                    onClick={() =>
                                                        router.post(
                                                            publish({
                                                                incident: incident.id,
                                                                update: update.id,
                                                            }).url,
                                                            {},
                                                            { preserveScroll: true },
                                                        )
                                                    }
                                                >
                                                    Publish this update
                                                </Button>
                                            )}
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </CardContent>
                    </Card>

                    <div className="flex flex-col gap-4">
                        {can.comment && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Post an update</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <Form
                                        {...storeUpdate.form({ incident: incident.id })}
                                        resetOnSuccess={['body']}
                                        options={{ preserveScroll: true }}
                                    >
                                        {({ processing, errors }) => (
                                            <>
                                                <div>
                                                    <label htmlFor="update-status" className="form-label">
                                                        Status
                                                    </label>
                                                    <select
                                                        id="update-status"
                                                        name="status"
                                                        defaultValue={incident.status}
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

                                                <div className="my-2">
                                                    <label htmlFor="update-body" className="form-label label-required">
                                                        Update
                                                    </label>
                                                    <textarea
                                                        id="update-body"
                                                        name="body"
                                                        required
                                                        rows={4}
                                                        className={cn('form-control', errors.body && 'is-invalid')}
                                                    />
                                                    <InputError message={errors.body} />
                                                </div>

                                                <label className="mb-3 inline-flex items-center">
                                                    <input
                                                        type="checkbox"
                                                        name="publish"
                                                        value="1"
                                                        defaultChecked
                                                        className="form-check-input"
                                                    />
                                                    <span className="ms-2 text-sm">Publish immediately</span>
                                                </label>

                                                <Button type="submit" disabled={processing} className="w-full">
                                                    {processing && <Spinner />}
                                                    Post update
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                </CardContent>
                            </Card>
                        )}

                        {can.update && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Incident details</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <Form
                                        {...updateIncident.form({ incident: incident.id })}
                                        options={{ preserveScroll: true }}
                                    >
                                        {({ processing, errors }) => (
                                            <>
                                                <div>
                                                    <label htmlFor="title" className="form-label label-required">
                                                        Title
                                                    </label>
                                                    <input
                                                        id="title"
                                                        name="title"
                                                        required
                                                        defaultValue={incident.title}
                                                        className={cn('form-control', errors.title && 'is-invalid')}
                                                    />
                                                    <InputError message={errors.title} />
                                                </div>

                                                <div className="my-2">
                                                    <label htmlFor="impact" className="form-label">
                                                        Impact
                                                    </label>
                                                    <select
                                                        id="impact"
                                                        name="impact"
                                                        defaultValue={incident.impact}
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

                                                <input type="hidden" name="status" value={incident.status} />

                                                <label className="mb-3 inline-flex items-center">
                                                    <input
                                                        type="checkbox"
                                                        name="is_published"
                                                        value="1"
                                                        defaultChecked={incident.isPublished}
                                                        className="form-check-input"
                                                    />
                                                    <span className="ms-2 text-sm">Visible on the public page</span>
                                                </label>

                                                <Button
                                                    type="submit"
                                                    variant="light"
                                                    disabled={processing}
                                                    className="w-full"
                                                >
                                                    {processing && <Spinner />}
                                                    Save
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
