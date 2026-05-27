// js/components/literatures/form.js

function parseConfig() {
    const el = document.getElementById('literature-form-config');
    if (!el) {
        return { orders: [], selectedOrderIds: [] };
    }
    return JSON.parse(el.textContent);
}

function initEnglishToggles() {
    document.querySelectorAll('[data-literature-en-toggle]').forEach((btn) => {
        const targetId = btn.getAttribute('data-bs-target')?.replace('#', '');
        const target = targetId ? document.getElementById(targetId) : null;
        if (!target) {
            return;
        }

        const showLabel = btn.dataset.labelShow ?? '英語表記を入力';
        const hideLabel = btn.dataset.labelHide ?? '英語表記を隠す';

        const syncLabel = () => {
            const expanded = target.classList.contains('show');
            btn.textContent = expanded ? hideLabel : showLabel;
            btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            btn.classList.toggle('collapsed', !expanded);
        };

        target.addEventListener('shown.bs.collapse', syncLabel);
        target.addEventListener('hidden.bs.collapse', syncLabel);
        syncLabel();
    });
}

function initOrderPicker(config) {
    const picker = document.getElementById('order-picker');
    const tagsEl = document.getElementById('order-selected-tags');
    const hiddenEl = document.getElementById('order-hidden-inputs');
    const errorEl = document.getElementById('order-picker-error');
    const form = document.getElementById('main');

    if (!picker || !tagsEl || !hiddenEl) {
        return;
    }

    const ordersById = new Map(
        config.orders.map((o) => [Number(o.id), o])
    );
    const selected = new Set(
        (config.selectedOrderIds ?? []).map((id) => Number(id)).filter((id) => ordersById.has(id))
    );

    function render() {
        tagsEl.innerHTML = '';
        hiddenEl.innerHTML = '';

        if (selected.size === 0) {
            tagsEl.innerHTML = '<span class="text-muted small">対象の目が未選択です</span>';
        } else {
            [...selected]
                .sort((a, b) => {
                    const codeA = ordersById.get(a)?.code ?? '';
                    const codeB = ordersById.get(b)?.code ?? '';
                    return String(codeA).localeCompare(String(codeB));
                })
                .forEach((id) => {
                    const order = ordersById.get(id);
                    if (!order) {
                        return;
                    }

                    const tag = document.createElement('span');
                    tag.className = 'badge text-bg-primary d-inline-flex align-items-center gap-1 literature-order-tag';
                    tag.dataset.orderId = String(id);

                    const label = document.createElement('span');
                    label.textContent = `${order.order_ja}（${order.order}）`;

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn-close btn-close-white btn-close-sm';
                    removeBtn.setAttribute('aria-label', `${order.order_ja}を削除`);
                    removeBtn.addEventListener('click', () => {
                        selected.delete(id);
                        render();
                        refreshPickerOptions();
                    });

                    tag.append(label, removeBtn);
                    tagsEl.appendChild(tag);

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'order_ids_array[]';
                    input.value = String(id);
                    hiddenEl.appendChild(input);
                });
        }

        if (errorEl) {
            errorEl.classList.add('d-none');
        }
        refreshPickerOptions();
    }

    function refreshPickerOptions() {
        [...picker.options].forEach((opt) => {
            if (!opt.value) {
                return;
            }
            const id = Number(opt.value);
            opt.disabled = selected.has(id);
        });
        picker.value = '';
    }

    picker.addEventListener('change', () => {
        const id = Number(picker.value);
        if (!id || !ordersById.has(id)) {
            return;
        }
        selected.add(id);
        render();
    });

    if (form) {
        form.addEventListener('submit', (e) => {
            if (selected.size > 0) {
                return;
            }
            e.preventDefault();
            if (errorEl) {
                errorEl.classList.remove('d-none');
            }
            picker.focus();
        });
    }

    render();
}

function flexTextarea(el) {
    const dummy = el.querySelector('.FlexTextarea__dummy');
    const textarea = el.querySelector('.FlexTextarea__textarea');
    if (!dummy || !textarea) {
        return;
    }
    const sync = () => {
        dummy.textContent = textarea.value + '\u200b';
    };
    textarea.addEventListener('input', sync);
    textarea.addEventListener('focus', sync);
    sync();
}

document.addEventListener('DOMContentLoaded', () => {
    const config = parseConfig();
    initEnglishToggles();
    initOrderPicker(config);
    document.querySelectorAll('.FlexTextarea').forEach(flexTextarea);
});
