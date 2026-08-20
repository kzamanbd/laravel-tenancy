import HorizontalMenuNode from '@/components/horizontal-menu-node';
import { menu  } from '@/lib/menu';
import type {MenuNode} from '@/lib/menu';

// Keep the bar to one row: show the first few top-level groups inline and
// fold the remainder into a "More" dropdown.
const VISIBLE = 5;
const items = menu.filter(n => !n.heading);
const visible = items.slice(0, VISIBLE);
const overflow = items.slice(VISIBLE);

const moreNode: MenuNode = {
    label: 'More',
    icon: 'icon-[mdi--dots-horizontal-circle-outline]',
    children: overflow,
};

export default function HorizontalMenu() {
    return (
        <ul className='horizontal-menu'>
            {visible.map((node, i) => (
                <HorizontalMenuNode key={i} node={node} level={0} />
            ))}
            {overflow.length > 0 && <HorizontalMenuNode node={moreNode} level={0} />}
        </ul>
    );
}
