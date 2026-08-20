import { Form, Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import PasswordField from '@/components/password-field';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { cn, slugify } from '@/lib/utils';
import { login } from '@/routes';
import { store } from '@/routes/register';

type Props = {
    passwordRules?: string;
    baseDomain: string;
};

export default function Register({ passwordRules, baseDomain }: Props) {
    const [subdomain, setSubdomain] = useState('');

    return (
        <>
            <Head title="Register" />

            <Form
                {...store.form()}
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
                className="my-3"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="block">
                            <label htmlFor="name" className="form-label label-required">
                                Name
                            </label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="name"
                                placeholder="Full name"
                                aria-invalid={!!errors.name}
                                className={cn('form-control', errors.name && 'is-invalid')}
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="my-2">
                            <label htmlFor="email" className="form-label label-required">
                                Email
                            </label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                required
                                tabIndex={2}
                                autoComplete="email"
                                placeholder="Email address"
                                aria-invalid={!!errors.email}
                                className={cn('form-control', errors.email && 'is-invalid')}
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="my-2">
                            <label htmlFor="subdomain" className="form-label label-required">
                                Workspace URL
                            </label>
                            <input
                                id="subdomain"
                                type="text"
                                name="subdomain"
                                required
                                tabIndex={3}
                                placeholder="your-company"
                                value={subdomain}
                                onChange={(event) => setSubdomain(slugify(event.target.value, true))}
                                aria-invalid={!!errors.subdomain}
                                aria-describedby="subdomain-help"
                                className={cn('form-control', errors.subdomain && 'is-invalid')}
                            />
                            <p id="subdomain-help" className="help-text mt-1 break-all">
                                Your status page will live at{' '}
                                <span className="font-medium text-foreground">
                                    {slugify(subdomain) || 'your-company'}.{baseDomain}
                                </span>
                            </p>
                            <InputError message={errors.subdomain} />
                        </div>

                        <PasswordField
                            id="password"
                            name="password"
                            label="Password"
                            required
                            tabIndex={4}
                            autoComplete="new-password"
                            placeholder="Password"
                            passwordrules={passwordRules}
                            error={errors.password}
                        />

                        <PasswordField
                            id="password_confirmation"
                            name="password_confirmation"
                            label="Confirm password"
                            required
                            tabIndex={5}
                            autoComplete="new-password"
                            placeholder="Confirm password"
                            passwordrules={passwordRules}
                            error={errors.password_confirmation}
                        />

                        <Button
                            type="submit"
                            disabled={processing}
                            tabIndex={6}
                            className="mt-4 w-full"
                            data-test="register-user-button"
                        >
                            {processing && <Spinner />}
                            Create account
                        </Button>
                    </>
                )}
            </Form>

            <p className="text-sm">
                Already have an account?{' '}
                <Link href={login()} data-test="login-link" className="text-primary hover:underline">
                    Log in
                </Link>
            </p>
        </>
    );
}

Register.layout = {
    title: 'Create an account',
    description: 'Enter your details below to create your status page',
};
