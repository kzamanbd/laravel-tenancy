import { createContext, use } from 'react';

/**
 * `sectioned` flag shared from a DialogContent/SheetContent down to its
 * Header/Body/Footer parts — the React port of the Vue `provide('dialogSectioned')`.
 */
export const DialogSectionedContext = createContext(false);

export const useDialogSectioned = () => {
    return use(DialogSectionedContext);
};
