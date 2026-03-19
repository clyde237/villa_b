import './bootstrap';
import { createIcons, icons } from 'lucide';

// Initialise toutes les icônes au chargement
document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});