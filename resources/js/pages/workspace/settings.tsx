import { Form, Head, router } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { formatDateTime } from '@/lib/dates';
import { cn } from '@/lib/utils';
import { publish } from '@/routes/workspace';
import { update } from '@/routes/workspace/settings';

type Props = {
    page: {
        name: string | null;
        headline: string | null;
        supportUrl: string | null;
        logoPath: string | null;
        primaryColor: string;
        customCss: string | null;
        timezone: string;
        showPoweredBy: boolean;
        lastPublishedAt: string | null;
        publicUrl: string;
    };
    timezones: string[];
    can: { update: boolean };
};

export default function PageSettings({ page, timezones, can }: Props) {

    return (
        <>
            <Head title="Page settings" />

            <div className="flex flex-col gap-4">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 className="text-xl font-semibold">Page settings</h2>
                        <p className="text-sm text-muted-foreground">
                            How your status page looks to the people reading it.
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <a
                            href={page.publicUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-sm text-primary hover:underline"
                        >
                            View public page
                        </a>
                        {can.update && (
                            <Button
                                variant="light"
                                size="sm"
                                onClick={() => router.post(publish().url, {}, { preserveScroll: true })}
                            >
                                Republish
                            </Button>
                        )}
                    </div>
                </div>

                <p className="text-xs text-muted-foreground">
                    {page.lastPublishedAt
                        ? `Last published ${formatDateTime(page.lastPublishedAt)}`
                        : 'This page has never been published.'}
                </p>

                <Form
                    {...update.form()}
                    options={{ preserveScroll: true }}
                    className="grid items-start gap-4 lg:grid-cols-2"
                >
                    {({ processing, errors }) => (
                        <>
                            <Card>
                                <CardHeader>
                                    <CardTitle>Identity</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div>
                                        <label htmlFor="name" className="form-label label-required">
                                            Page name
                                        </label>
                                        <input
                                            id="name"
                                            name="name"
                                            required
                                            defaultValue={page.name ?? ''}
                                            className={cn('form-control', errors.name && 'is-invalid')}
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="my-2">
                                        <label htmlFor="headline" className="form-label">
                                            Headline
                                        </label>
                                        <input
                                            id="headline"
                                            name="headline"
                                            defaultValue={page.headline ?? ''}
                                            placeholder="Live availability for the Acme platform"
                                            className={cn('form-control', errors.headline && 'is-invalid')}
                                        />
                                        <InputError message={errors.headline} />
                                    </div>

                                    <div className="my-2">
                                        <label htmlFor="support_url" className="form-label">
                                            Support URL
                                        </label>
                                        <input
                                            id="support_url"
                                            name="support_url"
                                            type="url"
                                            defaultValue={page.supportUrl ?? ''}
                                            placeholder="https://acme.test/support"
                                            className={cn('form-control', errors.support_url && 'is-invalid')}
                                        />
                                        <InputError message={errors.support_url} />
                                    </div>

                                    <div className="my-2">
                                        <label htmlFor="logo_path" className="form-label">
                                            Logo URL
                                        </label>
                                        <input
                                            id="logo_path"
                                            name="logo_path"
                                            defaultValue={page.logoPath ?? ''}
                                            className={cn('form-control', errors.logo_path && 'is-invalid')}
                                        />
                                        <InputError message={errors.logo_path} />
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Appearance</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div>
                                        <label htmlFor="primary_color" className="form-label label-required">
                                            Primary colour
                                        </label>
                                        <div className="flex items-center gap-2">
                                            <input
                                                id="primary_color"
                                                name="primary_color"
                                                type="color"
                                                required
                                                defaultValue={page.primaryColor}
                                                className="h-9 w-14 shrink-0 rounded-md border border-border bg-card p-1"
                                            />
                                            <span className="text-sm text-muted-foreground">{page.primaryColor}</span>
                                        </div>
                                        <InputError message={errors.primary_color} />
                                    </div>

                                    <div className="my-2">
                                        <label htmlFor="timezone" className="form-label label-required">
                                            Timezone
                                        </label>
                                        <select
                                            id="timezone"
                                            name="timezone"
                                            defaultValue={page.timezone}
                                            className="form-control"
                                        >
                                            {timezones.map((zone) => (
                                                <option key={zone} value={zone}>
                                                    {zone}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError message={errors.timezone} />
                                    </div>

                                    <div className="my-2">
                                        <label htmlFor="custom_css" className="form-label">
                                            Custom CSS
                                        </label>
                                        <textarea
                                            id="custom_css"
                                            name="custom_css"
                                            rows={6}
                                            defaultValue={page.customCss ?? ''}
                                            placeholder=".banner { border-radius: 0; }"
                                            className={cn('form-control font-mono text-xs', errors.custom_css && 'is-invalid')}
                                        />
                                        <p className="help-text mt-1">
                                            Injected into the published page. <code>@import</code> and anything that
                                            could close the style block are stripped.
                                        </p>
                                        <InputError message={errors.custom_css} />
                                    </div>

                                    <label className="my-3 inline-flex items-center">
                                        <input
                                            type="checkbox"
                                            name="show_powered_by"
                                            value="1"
                                            defaultChecked={page.showPoweredBy}
                                            className="form-check-input"
                                        />
                                        <span className="ms-2 text-sm">Show the “powered by” badge</span>
                                    </label>

                                    <Button type="submit" disabled={processing || !can.update} className="w-full">
                                        {processing && <Spinner />}
                                        Save and publish
                                    </Button>
                                </CardContent>
                            </Card>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}
