(function () {
    const bodyNoScrollClass = 'sle-offcanvas-no-scroll';

    function getWrapperById(id) {
        return document.querySelector('[data-offcanvas-id="' + id + '"]');
    }

    function getPanelById(id) {
        return document.getElementById('sle-offcanvas-' + id);
    }

    function getContainerById(id) {
        return document.querySelector('[data-sle-offcanvas-container="' + id + '"]');
    }

    function openOffcanvas(id) {
        const panel = getPanelById(id);
        const wrapper = getWrapperById(id);
        const container = getContainerById(id);

        if (!panel || !wrapper || !container) return;

        panel.setAttribute('aria-hidden', 'false');
        container.classList.add('sle-offcanvas--visible');

        // Update trigger aria-expanded
        const triggers = document.querySelectorAll('[data-sle-offcanvas-open="' + id + '"]');
        triggers.forEach(function (btn) {
            btn.setAttribute('aria-expanded', 'true');
        });

        // Prevent scroll
        const preventScroll = wrapper.getAttribute('data-prevent-scroll') === 'true';
        if (preventScroll) {
            document.body.classList.add(bodyNoScrollClass);
        }

        // Focus panel for ESC handling
        panel.focus();
    }

    function closeOffcanvas(id) {
        const panel = getPanelById(id);
        const wrapper = getWrapperById(id);
        const container = getContainerById(id);

        if (!panel || !wrapper || !container) return;

        panel.setAttribute('aria-hidden', 'true');
        container.classList.remove('sle-offcanvas--visible');

        // Update trigger aria-expanded
        const triggers = document.querySelectorAll('[data-sle-offcanvas-open="' + id + '"]');
        triggers.forEach(function (btn) {
            btn.setAttribute('aria-expanded', 'false');
        });

        const preventScroll = wrapper.getAttribute('data-prevent-scroll') === 'true';
        if (preventScroll) {
            document.body.classList.remove(bodyNoScrollClass);
        }
    }

    // Click handling
    document.addEventListener('click', function (event) {
        const openTrigger = event.target.closest('[data-sle-offcanvas-open]');
        if (openTrigger) {
            event.preventDefault();
            const id = openTrigger.getAttribute('data-sle-offcanvas-open');
            openOffcanvas(id);
            return;
        }

        const closeTrigger = event.target.closest('[data-sle-offcanvas-close]');
        if (closeTrigger) {
            event.preventDefault();
            const id = closeTrigger.getAttribute('data-sle-offcanvas-close');
            closeOffcanvas(id);
        }
    });

    // ESC handling
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        const visiblePanels = document.querySelectorAll('.sle-offcanvas--visible .sle-offcanvas__panel');
        if (!visiblePanels.length) return;

        visiblePanels.forEach(function (panel) {
            const id = panel.id.replace('sle-offcanvas-', '');
            closeOffcanvas(id);
        });
    });
})();
