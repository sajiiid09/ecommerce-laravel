import './bootstrap';
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import rover from '@sheaf/rover';

window.Alpine = Alpine;
Alpine.plugin(rover);

import './globals/modals';
import './components/carousel';
import './components/select';
import './components/combobox';
import './components/rich-text-editor';

Livewire.start();
