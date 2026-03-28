import './bootstrap';
import { createIcons, icons } from 'lucide';

window.refreshLucideIcons = () => createIcons({ icons });

document.addEventListener('DOMContentLoaded', () => {
    window.refreshLucideIcons();
});
