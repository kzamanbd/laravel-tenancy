import { Form, Head, router } from '@inertiajs/react';
import { useState } from 'react';
import EmptyState from '@/components/empty-state';
import InputError from '@/components/input-error';
import StatusBadge, { componentStatusTone } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { formatDateTime } from '@/lib/dates';
import { cn } from '@/lib/utils';
import { destroy, store, update } from '@/routes/workspace/components';

type Option = { value: string; label: string };
type Group = { id: number; name: string };

type Component = {
    id: number;
    name: string;
    description: string | null;
    status: string;
    statusLabel: string;
    isPublic: boolean;
    showUptime: boolean;
    group: Group | null;
    statusChangedAt: string | null;
};

type Props = {
    components: Component[];
    groups: Group[];
    statuses: Option[];
    can: { create: boolean };
};

export default function Components({ components, groups, statuses, can }: Props) {
    const [editing, setEditing] = useState<number | null>(null);

    // Changing a status is the most frequent action on this screen, so it is a
    // single select rather than an edit-then-save round trip.
    const changeStatus = (component: Component, status: string) => {
        router.put(
            update({ component: component.id }).url,
            {
                name: component.name,
                description: component.description,
                status,
                is_public: component.isPublic,
                show_uptime: component.showUptime,
                component_group_id: component.group?.id ?? null,
            },
            { preserveScroll: true },
        );
    };

    return (
        <>
            <Head title="Components" />

            <div className="flex flex-col gap-4">
                <div>
                    <h2 className="text-xl font-semibold">Components</h2>
                    <p className="text-sm text-muted-foreground">
                        The pieces of your service whose health the page reports.
                    </p>
                </div>

                {can.create && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Add a component</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Form
                                {...store.form()}
                                resetOnSuccess={['name', 'description']}
                                className="grid gap-4 sm:grid-cols-[2fr_2fr_1fr_auto] sm:items-start"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div>
                                            <label htmlFor="name" className="form-label label-required">
                                                Name
                                            </label>
                                            <input
                                                id="name"
                                                name="name"
                                                required
                                                placeholder="REST API"
                                                className={cn('form-control', errors.name && 'is-invalid')}
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div>
                                            <label htmlFor="description" className="form-label">
                                                Description
                                            </label>
                                            <input
                                                id="description"
                                                name="description"
                                                placeholder="What customers rely on this for"
                                                className={cn('form-control', errors.description && 'is-invalid')}
                                            />
                                            <InputError message={errors.description} />
                                        </div>

                                        <div>
                                            <label htmlFor="status" className="form-label">
                                                Status
                                            </label>
                                            <select id="status" name="status" defaultValue="operational" className="form-control">
                                                {statuses.map((status) => (
                                                    <option key={status.value} value={status.value}>
                                                        {status.label}
                                                    </option>
                                                ))}
                                            </select>
                                            <InputError message={errors.status} />
                                        </div>

                                        <div>
                                            <label className="form-label sm:opacity-0">Action</label>
                                            <Button type="submit" disabled={processing}>
                                                {processing && <Spinner />}
                                                Add
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
                            {components.length} component{components.length === 1 ? '' : 's'}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {components.length === 0 ? (
                            <EmptyState
                                title="No components yet"
                                description="Add the parts of your service you want to report on."
                            />
                        ) : (
                            <div className="table-responsive">
                                <table className="tw-table">
                                    <thead>
                                        <tr>
                                            <th>Component</th>
                                            <th>Status</th>
                                            <th>Visibility</th>
                                            <th>Changed</th>
                                            <th className="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {components.map((component) => (
                                            <tr key={component.id}>
                                                <td>
                                                    <span className="font-medium">{component.name}</span>
                                                    {component.description && (
                                                        <span className="block text-xs text-muted-foreground">
                                                            {component.description}
                                                        </span>
                                                    )}
                                                </td>
                                                <td>
                                                    {editing === component.id ? (
                                                        <select
                                                            autoFocus
                                                            defaultValue={component.status}
                                                            className="form-control form-control-sm"
                                                            onBlur={() => setEditing(null)}
                                                            onChange={(event) => {
                                                                changeStatus(component, event.target.value);
                                                                setEditing(null);
                                                            }}
                                                        >
                                                            {statuses.map((status) => (
                                                                <option key={status.value} value={status.value}>
                                                                    {status.label}
                                                                </option>
                                                            ))}
                                                        </select>
                                                    ) : (
                                                        <button
                                                            type="button"
                                                            onClick={() => setEditing(component.id)}
                                                            title="Change status"
                                                        >
                                                            <StatusBadge
                                                                value={component.status}
                                                                label={component.statusLabel}
                                                                tones={componentStatusTone}
                                                            />
                                                        </button>
                                                    )}
                                                </td>
                                                <td className="text-muted-foreground">
                                                    {component.isPublic ? 'Public' : 'Hidden'}
                                                </td>
                                                <td className="text-muted-foreground">
                                                    {formatDateTime(component.statusChangedAt)}
                                                </td>
                                                <td className="text-right">
                                                    <Button
                                                        variant="soft-destructive"
                                                        size="xs"
                                                        onClick={() =>
                                                            router.delete(
                                                                destroy({
                                                                    component: component.id,
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

                {groups.length > 0 && (
                    <p className="text-xs text-muted-foreground">
                        {groups.length} group{groups.length === 1 ? '' : 's'} defined.
                    </p>
                )}
            </div>
        </>
    );
}
