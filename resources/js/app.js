import Alpine from 'alpinejs';

const uniqueKey = () => `${Date.now()}-${Math.random().toString(36).slice(2)}`;

window.packageItinerary = (initialItems = []) => ({
    items: initialItems.map((item) => ({ ...item, key: uniqueKey() })),
    add() {
        const nextDay = this.items.length > 0
            ? Math.max(...this.items.map((item) => Number(item.day_number) || 0)) + 1
            : 1;

        this.items.push({ key: uniqueKey(), day_number: nextDay, title: '', description: '' });
    },
    remove(index) {
        this.items.splice(index, 1);
    },
    move(index, direction) {
        const target = index + direction;
        if (target < 0 || target >= this.items.length) return;
        [this.items[index], this.items[target]] = [this.items[target], this.items[index]];
    },
});

window.packageGalleryUploads = () => ({
    files: [],
    select(event) {
        this.files = Array.from(event.target.files).map((file) => ({ name: file.name, alt: '' }));
    },
});

window.packageExistingImages = (initialItems = []) => ({
    items: initialItems.map((item) => ({ ...item })),
    move(index, direction) {
        const target = index + direction;
        if (target < 0 || target >= this.items.length) return;
        [this.items[index], this.items[target]] = [this.items[target], this.items[index]];
    },
});

window.socialLinksEditor = (initialItems = []) => ({
    platforms: ['facebook', 'instagram', 'youtube', 'linkedin', 'tiktok', 'x'],
    items: initialItems.map((item, index) => ({ ...item, key: `existing-${index}` })),
    add() {
        this.items.push({ key: crypto.randomUUID(), platform: 'facebook', label: '', url: '', is_active: true, sort_order: this.items.length });
    },
    remove(index) {
        this.items.splice(index, 1);
    },
});

window.Alpine = Alpine;
Alpine.start();
