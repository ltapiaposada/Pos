import './bootstrap';
import 'admin-lte/dist/js/adminlte.min.js';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const alertIcons = { success: '✓', error: '!', warning: '!', info: 'i' };

function showCenterAlert(message, options = {}) {
    const type = ['success', 'error', 'warning', 'info'].includes(options.type) ? options.type : 'info';
    const duration = Number(options.duration) || 3200;
    const root = document.createElement('div');
    root.className = 'center-alert-root';
    root.setAttribute('role', type === 'error' ? 'alert' : 'status');
    root.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');

    const alertBox = document.createElement('div');
    alertBox.className = `center-alert center-alert--${type}`;
    const icon = document.createElement('span');
    icon.className = 'center-alert__icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = alertIcons[type];
    const text = document.createElement('span');
    text.className = 'center-alert__text';
    text.textContent = String(message || '');
    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'center-alert__close';
    close.setAttribute('aria-label', 'Cerrar alerta');
    close.textContent = '×';

    let timer;
    const dismiss = () => {
        clearTimeout(timer);
        alertBox.classList.add('is-leaving');
        alertBox.addEventListener('animationend', () => root.remove(), { once: true });
        setTimeout(() => root.remove(), 250);
    };
    close.addEventListener('click', dismiss);
    alertBox.append(icon, text, close);
    root.append(alertBox);
    document.body.append(root);
    timer = setTimeout(dismiss, duration);
}

function showCenterConfirm(message, options = {}) {
    return new Promise((resolve) => {
        const previousFocus = document.activeElement;
        const root = document.createElement('div');
        root.className = 'center-confirm';
        root.setAttribute('role', 'dialog');
        root.setAttribute('aria-modal', 'true');
        root.setAttribute('aria-labelledby', 'system-confirm-title');
        root.setAttribute('aria-describedby', 'system-confirm-message');
        root.innerHTML = `
            <div class="center-confirm__backdrop" data-confirm-cancel></div>
            <div class="center-confirm__card">
                <div class="center-confirm__icon" aria-hidden="true">!</div>
                <div class="center-confirm__content">
                    <h2 id="system-confirm-title" class="center-confirm__title"></h2>
                    <p id="system-confirm-message" class="center-confirm__message"></p>
                    <div class="center-confirm__actions">
                        <button type="button" class="btn btn-outline btn-sm" data-confirm-cancel></button>
                        <button type="button" class="btn btn-primary btn-sm" data-confirm-accept></button>
                    </div>
                </div>
            </div>`;

        root.querySelector('.center-confirm__title').textContent = options.title || 'Confirmar acción';
        root.querySelector('.center-confirm__message').textContent = String(message || '¿Deseas continuar?');
        root.querySelector('button[data-confirm-cancel]').textContent = options.cancelText || 'Cancelar';
        root.querySelector('[data-confirm-accept]').textContent = options.confirmText || 'Continuar';

        const finish = (accepted) => {
            document.removeEventListener('keydown', onKeydown);
            document.documentElement.classList.remove('center-confirm-open');
            document.body.classList.remove('center-confirm-open');
            root.remove();
            if (previousFocus instanceof HTMLElement) previousFocus.focus();
            resolve(accepted);
        };
        const onKeydown = (event) => {
            if (event.key === 'Escape') finish(false);
            if (event.key === 'Tab') {
                const buttons = [...root.querySelectorAll('button')];
                const current = buttons.indexOf(document.activeElement);
                const next = event.shiftKey
                    ? (current <= 0 ? buttons.length - 1 : current - 1)
                    : (current === buttons.length - 1 ? 0 : current + 1);
                event.preventDefault();
                buttons[next].focus();
            }
        };

        root.querySelectorAll('[data-confirm-cancel]').forEach((element) => element.addEventListener('click', () => finish(false)));
        root.querySelector('[data-confirm-accept]').addEventListener('click', () => finish(true));
        document.addEventListener('keydown', onKeydown);
        document.documentElement.classList.add('center-confirm-open');
        document.body.classList.add('center-confirm-open');
        document.body.append(root);
        root.querySelector('[data-confirm-accept]').focus();
    });
}

window.showCenterAlert = showCenterAlert;
window.showSystemAlert = showCenterAlert;
window.showSystemConfirm = showCenterConfirm;
window.PosAlert = { notify: showCenterAlert, confirm: showCenterConfirm };

document.addEventListener('click', async (event) => {
    const trigger = event.target.closest('[data-confirm]');
    if (!trigger || trigger.dataset.confirmAccepted === 'true') return;

    event.preventDefault();
    event.stopImmediatePropagation();
    const accepted = await showCenterConfirm(trigger.dataset.confirm?.trim(), {
        title: trigger.dataset.confirmTitle,
        confirmText: trigger.dataset.confirmAction,
    });
    if (!accepted) return;

    trigger.dataset.confirmAccepted = 'true';
    const form = trigger.closest('form');
    if (form) {
        form.requestSubmit(trigger instanceof HTMLButtonElement ? trigger : undefined);
    } else if (trigger instanceof HTMLAnchorElement && trigger.href) {
        window.location.assign(trigger.href);
    } else {
        trigger.click();
    }
}, true);

function makeTablesResponsive(scope = document) {
    scope.querySelectorAll('table.table').forEach((table) => {
        const container = table.closest('.overflow-x-auto, .table-responsive, .responsive-table-shell');
        if (container) {
            container.classList.add('responsive-table-shell');
            return;
        }
        if (table.closest('.print-layout, [data-no-responsive-table]')) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'responsive-table-shell';
        wrapper.setAttribute('role', 'region');
        wrapper.setAttribute('tabindex', '0');
        wrapper.setAttribute('aria-label', table.getAttribute('aria-label') || 'Tabla desplazable');
        table.before(wrapper);
        wrapper.append(table);
    });
}

document.addEventListener('DOMContentLoaded', () => makeTablesResponsive());
