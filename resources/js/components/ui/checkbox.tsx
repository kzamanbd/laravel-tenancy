import { cn } from '@/lib/utils';
import type { ComponentProps } from 'react';

// Thin wrapper over the project's .form-check-input so checkbox state binds like
// any other Ui control.
export function Checkbox({ className, ...props }: ComponentProps<'input'>) {
    return <input type='checkbox' className={cn('form-check-input', className)} {...props} />;
}
