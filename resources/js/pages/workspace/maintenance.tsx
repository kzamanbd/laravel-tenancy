import { Form, Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import EmptyState from '@/components/empty-state';
import InputError from '@/components/input-error';
import StatusBadge, { maintenanceStatusTone } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { formatDateTime, toDateTimeLocal } from '@/lib/dates';
import { cn } from '@/lib/utils';
import { destroy, store } from '@/routes/workspace/maintenance';

type Option = { value: string; label: string };

type Maintenance = {
    id: number;
    title: string;
    description: string | null;
    status: string;
    statusLabel: string;
    isPublished: boolean;
    autoTransition: boolean;
    notifySubscribers: boolean;
    scheduledStartAt: string;
    scheduledEndAt: string;
    components: { id: number; name: string }[];
};

type Props = {
    maintenances: Maintenance[];
    components: { id: number; name: string }[];
    statuses: Option[];
    can: { create: boolean };
};

export default function MaintenancePage({ maintenances, components, can }: Props) {
    const tenant = usePage<{ tenant: { id: number } }>().props.tenant;

    // Read the clock once, in a lazy initialiser: calling it during render is
    // impure, and under SSR the server's `now` would not match the client's.
    const [defaultStart, defaultEnd] = useState(() => {
        const hour = 60 * 60 * 1000;
        const now = Date.now();

        return [toDateTimeLocal(new Date(now + 24 * hour)), toDateTimeLocal(new Date(now + 26 * hour))];
    })[0];

    return (
        <>
            <Head title="Maintenance" />

            <div className="flex flex-col gap-4">
                <div>
                    <h2 className="text-xl font-semibold">Maintenance</h2>
                    <p className="text-sm text-muted-foreground">
                        Scheduled windows transition themselves unless you turn that off.
                    </p>
                </div>

                {can.create && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Schedule a window</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Form
                                {...store.form({ tenant: tenant.id })}
                                resetOnSuccess={['title', 'description']}
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
                                                    placeholder="Database failover rehearsal"
                                                    className={cn('form-control', errors.title && 'is-invalid')}
                                                />
                                                <InputError message={errors.title} />
                                            </div>

                                            <div>
                                                <label htmlFor="scheduled_start_at" className="form-label label-required">
                                                    Starts
                                                </label>
                                                <input
                                                    id="scheduled_start_at"
                                                    name="scheduled_start_at"
                                                    type="datetime-local"
                                                    required
                                                    defaultValue={defaultStart}
                                                    className={cn(
                                                        'form-control',
                                                        errors.scheduled_start_at && 'is-invalid',
                                                    )}
                                                />
                                                <InputError message={errors.scheduled_start_at} />
                                            </div>

                                            <div>
                                                <label htmlFor="scheduled_end_at" className="form-label label-required">
                                                    Ends
                                                </label>
                                                <input
                                                    id="scheduled_end_at"
                                                    name="scheduled_end_at"
                                                    type="datetime-local"
                                                    required
                                                    defaultValue={defaultEnd}
                                                    className={cn(
                                                        'form-control',
                                                        errors.scheduled_end_at && 'is-invalid',
                                                    )}
                                                />
                                                <InputError message={errors.scheduled_end_at} />
                                            </div>
                                        </div>

                                        <div>
                                            <label htmlFor="description" className="form-label">
                                                What to expect
                                            </label>
                                            <textarea
                                                id="description"
                                                name="description"
                                                rows={2}
                                                placeholder="Brief connection resets while we exercise the standby."
                                                className={cn('form-control', errors.description && 'is-invalid')}
                                            />
                                            <InputError message={errors.description} />
                                        </div>

                                        {components.length > 0 && (
                                            <fieldset>
                                                <legend className="form-label">Affected components</legend>
                                                <div className="flex flex-wrap gap-3">
                                                    {components.map((component) => (
                                                        <label key={component.id} className="inline-flex items-center">
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
                                        )}

                                        <div className="flex flex-wrap items-center gap-4">
                                            <label className="inline-flex items-center">
                                                <input
                                                    type="checkbox"
                                                    name="auto_transition"
                                                    value="1"
                                                    defaultChecked
                                                    className="form-check-input"
                                                />
                                                <span className="ms-2 text-sm">Transition automatically</span>
                                            </label>

                                            <label className="inline-flex items-center">
                                                <input
                                                    type="checkbox"
                                                    name="notify_subscribers"
                                                    value="1"
                                                    defaultChecked
                                                    className="form-check-input"
                                                />
                                                <span className="ms-2 text-sm">Notify subscribers</span>
                                            </label>

                                            <Button type="submit" disabled={processing} className="ms-auto">
                                                {processing && <Spinner />}
                                                Schedule
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
                            {maintenances.length} window{maintenances.length === 1 ? '' : 's'}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {maintenances.length === 0 ? (
                            <EmptyState
                                title="Nothing scheduled"
                                description="Planned work appears here and on the public page."
                            />
                        ) : (
                            <div className="table-responsive">
                                <table className="tw-table">
                                    <thead>
                                        <tr>
                                            <th>Window</th>
                                            <th>Status</th>
                                            <th>Scheduled</th>
                                            <th>Components</th>
                                            <th className="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {maintenances.map((window) => (
                                            <tr key={window.id}>
                                                <td>
                                                    <span className="font-medium">{window.title}</span>
                                                    {!window.isPublished && (
                                                        <span className="ms-2 text-xs text-muted-foreground">
                                                            Draft
                                                        </span>
                                                    )}
                                                </td>
                                                <td>
                                                    <StatusBadge
                                                        value={window.status}
                                                        label={window.statusLabel}
                                                        tones={maintenanceStatusTone}
                                                    />
                                                </td>
                                                <td className="text-muted-foreground">
                                                    {formatDateTime(window.scheduledStartAt)} →{' '}
                                                    {formatDateTime(window.scheduledEndAt)}
                                                </td>
                                                <td className="text-muted-foreground">
                                                    {window.components.map((c) => c.name).join(', ') || '—'}
                                                </td>
                                                <td className="text-right">
                                                    <Button
                                                        variant="soft-destructive"
                                                        size="xs"
                                                        onClick={() =>
                                                            router.delete(
                                                                destroy({
                                                                    tenant: tenant.id,
                                                                    maintenance: window.id,
                                                                }).url,
                                                                { preserveScroll: true },
                                                            )
                                                        }
                                                    >
                                                        Delete
                                                    </Button>
                                                </td>
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
