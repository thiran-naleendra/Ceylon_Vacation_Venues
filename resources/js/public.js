const navigationToggle = document.querySelector('[data-navigation-toggle]');
const navigation = document.querySelector('[data-mobile-navigation]');

if (navigationToggle && navigation) {
    const openIcon = navigationToggle.querySelector('[data-navigation-open-icon]');
    const closeIcon = navigationToggle.querySelector('[data-navigation-close-icon]');

    const setOpen = (open) => {
        navigation.hidden = !open;
        navigationToggle.setAttribute('aria-expanded', String(open));
        openIcon?.toggleAttribute('hidden', open);
        closeIcon?.toggleAttribute('hidden', !open);
    };

    navigationToggle.addEventListener('click', () => {
        setOpen(navigationToggle.getAttribute('aria-expanded') !== 'true');
    });

    navigation.addEventListener('click', (event) => {
        if (event.target.closest('a')) {
            setOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
            navigationToggle.focus();
        }
    });
}

const galleryDialog = document.querySelector('[data-gallery-dialog]');

if (galleryDialog instanceof HTMLDialogElement) {
    const dialogImage = galleryDialog.querySelector('[data-gallery-dialog-image]');
    const dialogTitle = galleryDialog.querySelector('[data-gallery-dialog-title]');
    const dialogCaption = galleryDialog.querySelector('[data-gallery-dialog-caption]');

    document.querySelectorAll('[data-gallery-preview]').forEach((button) => {
        button.addEventListener('click', () => {
            dialogImage.src = button.dataset.fullImage ?? '';
            dialogImage.alt = button.dataset.imageAlt ?? '';
            dialogTitle.textContent = button.dataset.imageTitle ?? '';
            dialogCaption.textContent = button.dataset.imageCaption ?? '';
            dialogTitle.hidden = dialogTitle.textContent === '';
            dialogCaption.hidden = dialogCaption.textContent === '';
            galleryDialog.showModal();
        });
    });

    galleryDialog.querySelector('[data-gallery-close]')?.addEventListener('click', () => galleryDialog.close());
    galleryDialog.addEventListener('click', (event) => {
        if (event.target === galleryDialog) {
            galleryDialog.close();
        }
    });
    galleryDialog.addEventListener('close', () => {
        dialogImage.removeAttribute('src');
    });
}
