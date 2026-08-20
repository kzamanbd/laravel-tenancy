import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordField from '@/components/password-field';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { update } from '@/routes/password';

type Props = {
    token: string;
    email: string;
    passwordRules?: string;
};

export default function ResetPassword({ token, email, passwordRules }: Props) {
    return (
        <>
            <Head title="Reset password" />

            <Form
                {...update.form()}
                transform={(data) => ({ ...data, token, email })}
                resetOnSuccess={['password', 'password_confirmation']}
                className="my-3"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="block">
                            <label htmlFor="email" className="form-label">
                                Email
                            </label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value={email}
                                readOnly
                                autoComplete="email"
                                className="form-control bg-muted"
                            />
                            <InputError message={errors.email} />
                        </div>

                        <PasswordField
                            id="password"
                            name="password"
                            label="New password"
                            required
                            autoFocus
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
                            autoComplete="new-password"
                            placeholder="Confirm password"
                            passwordrules={passwordRules}
                            error={errors.password_confirmation}
                        />

                        <Button
                            type="submit"
                            disabled={processing}
                            className="mt-4 w-full"
                            data-test="reset-password-button"
                        >
                            {processing && <Spinner />}
                            Reset password
                        </Button>
                    </>
                )}
            </Form>
        </>
    );
}

ResetPassword.layout = {
    title: 'Reset your password',
    description: 'Choose a new password for your account',
};
