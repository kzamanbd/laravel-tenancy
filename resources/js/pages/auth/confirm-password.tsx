import { Form, Head } from '@inertiajs/react';
import PasswordField from '@/components/password-field';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/password/confirm';

export default function ConfirmPassword() {
    return (
        <>
            <Head title="Confirm password" />

            <Form {...store.form()} resetOnSuccess={['password']} className="my-3">
                {({ processing, errors }) => (
                    <>
                        <PasswordField
                            id="password"
                            name="password"
                            label="Password"
                            required
                            autoFocus
                            autoComplete="current-password"
                            placeholder="Enter password"
                            error={errors.password}
                        />

                        <Button
                            type="submit"
                            disabled={processing}
                            className="mt-4 w-full"
                            data-test="confirm-password-button"
                        >
                            {processing && <Spinner />}
                            Confirm password
                        </Button>
                    </>
                )}
            </Form>
        </>
    );
}

ConfirmPassword.layout = {
    title: 'Confirm your password',
    description: 'This is a secure area — please confirm your password to continue',
};
