import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { index as tenants, store } from '@/routes/tenants';

type Tenant = {
    id: string;
    name: string | null;
    domains: string[];
    users: { name: string; email: string }[];
};

type Props = {
    tenants: Tenant[];
    baseDomain: string;
};

export default function Tenants({ tenants: list, baseDomain }: Props) {
    return (
        <>
            <Head title="Tenants" />

            <div className="flex flex-col gap-6 p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>Create a tenant</CardTitle>
                        <CardDescription>
                            Provision a new tenant with its own subdomain.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            {...store.form()}
                            resetOnSuccess={['name', 'subdomain']}
                            className="grid gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-start"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">Name</Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            required
                                            placeholder="Acme Inc."
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="subdomain">
                                            Subdomain
                                        </Label>
                                        <div className="flex items-center">
                                            <Input
                                                id="subdomain"
                                                name="subdomain"
                                                required
                                                placeholder="acme"
                                                className="rounded-r-none"
                                            />
                                            <span className="inline-flex h-9 items-center rounded-r-md border border-l-0 border-input bg-muted px-3 text-sm text-muted-foreground">
                                                .{baseDomain}
                                            </span>
                                        </div>
                                        <InputError message={errors.subdomain} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label className="sm:opacity-0">
                                            Action
                                        </Label>
                                        <Button type="submit" disabled={processing}>
                                            {processing && <Spinner />}
                                            Create
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Tenants</CardTitle>
                        <CardDescription>
                            {list.length} tenant{list.length === 1 ? '' : 's'}{' '}
                            registered.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="text-xs text-muted-foreground uppercase">
                                    <tr className="border-b">
                                        <th className="px-4 py-3">Name</th>
                                        <th className="px-4 py-3">Users</th>
                                        <th className="px-4 py-3">Domains</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {list.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan={3}
                                                className="px-4 py-6 text-center text-muted-foreground"
                                            >
                                                No tenants yet.
                                            </td>
                                        </tr>
                                    ) : (
                                        list.map((tenant) => (
                                            <tr
                                                key={tenant.id}
                                                className="border-b last:border-0"
                                            >
                                                <td className="px-4 py-3 font-medium">
                                                    {tenant.name ?? tenant.id}
                                                </td>
                                                <td className="px-4 py-3 text-muted-foreground">
                                                    {tenant.users
                                                        .map((u) => u.email)
                                                        .join(', ') || '—'}
                                                </td>
                                                <td className="px-4 py-3 text-muted-foreground">
                                                    {tenant.domains.join(', ') ||
                                                        '—'}
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

Tenants.layout = () => ({
    breadcrumbs: [
        {
            title: 'Tenants',
            href: tenants(),
        },
    ],
});
