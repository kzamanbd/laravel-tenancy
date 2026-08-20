import { useFlashToast } from '@/hooks/use-flash-toast';
import { useTheme } from '@/context/theme-context';
import { Toaster as Sonner, type ToasterProps } from 'sonner';

function Toaster({ ...props }: ToasterProps) {
    const { settings } = useTheme();

    useFlashToast();

    return (
        <Sonner
            theme={settings.theme}
            className="toaster group"
            position="bottom-right"
            style={
                {
                    '--normal-bg': 'var(--popover)',
                    '--normal-text': 'var(--popover-foreground)',
                    '--normal-border': 'var(--border)',
                } as React.CSSProperties
            }
            {...props}
        />
    );
}

export { Toaster };
