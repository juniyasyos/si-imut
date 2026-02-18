<div>
    {{ $this->table }}
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('openUrlInNewTab', (url) => {
            window.open(url, '_blank');
        });

        // Listen for server-side request to auto-open a table action for a specific record.
        // The server will dispatchBrowserEvent('open-table-action', { action, recordId }).
        window.addEventListener('open-table-action', (ev) => {
            const detail = ev?.detail || {};
            const action = detail.action;
            const recordId = detail.recordId;
            if (!action || !recordId) return;

            const labelMap = {
                'isi_penilaian': 'Isi Penilaian',
                'detail_info': 'Detail Info Profil',
            };
            const actionLabel = labelMap[action] ?? action;

            const fallbackClick = () => {
                const tryClick = (attempt = 0) => {
                    const row = document.querySelector(`[data-record-id="${recordId}"]`) || document.querySelector(`[data-record-key="${recordId}"]`) || document.querySelector(`tr[data-record-id="${recordId}"]`);
                    if (row) {
                        const selectors = [
                            `button[aria-label*="${actionLabel}"], button[title*="${actionLabel}"]`,
                            `button[data-action="${action}"]`,
                            `a[role="button"][data-action="${action}"]`,
                            'button',
                            'a[role="button"]'
                        ];

                        let clicked = false;
                        for (const sel of selectors) {
                            const el = row.querySelector(sel);
                            if (el && el.offsetParent !== null) {
                                el.click();
                                clicked = true;
                                break;
                            }
                        }

                        if (!clicked) {
                            const possible = Array.from(row.querySelectorAll('button, a')).find(el => el.textContent && el.textContent.trim().toLowerCase().includes(actionLabel.toLowerCase()));
                            if (possible) possible.click();
                        }

                        try {
                            const u = new URL(window.location.href);
                            u.searchParams.delete('action');
                            u.searchParams.delete('open_action');
                            u.searchParams.delete('record');
                            window.history.replaceState({}, '', u.toString());
                        } catch (e) {
                            // ignore
                        }

                        return;
                    }

                    if (attempt < 12) {
                        setTimeout(() => tryClick(attempt + 1), 250);
                    }
                };

                tryClick();
            };

            // 1) Prefer calling Filament's Alpine `table` component (most reliable for client-side slide-overs)
            try {
                const scriptEl = document.currentScript || Array.from(document.scripts).pop();
                const livewireRoot = scriptEl ? scriptEl.closest('[wire\\:id]') : document.querySelector('[wire\\:id]');

                // find the Filament table Alpine root inside this Livewire subtree (x-data="table")
                const tableEl = livewireRoot ? livewireRoot.querySelector('[x-data="table"]') : document.querySelector('[x-data="table"]');

                if (tableEl) {
                    // Alpine internals are available via __x (Alpine v3). Try several common method names.
                    const alpineInstance = tableEl.__x ? tableEl.__x.$data : (window.Alpine ? Alpine.find(tableEl) : null);
                    const mountFn = alpineInstance?.mountTableAction ?? alpineInstance?.mountAction ?? alpineInstance?.openAction ?? alpineInstance?.openTableAction ?? alpineInstance?.open;

                    if (typeof mountFn === 'function') {
                        try {
                            mountFn.call(alpineInstance, action, recordId);

                            // cleanup URL
                            const u = new URL(window.location.href);
                            u.searchParams.delete('action');
                            u.searchParams.delete('open_action');
                            u.searchParams.delete('record');
                            window.history.replaceState({}, '', u.toString());

                            return; // done
                        } catch (err) {
                            // continue to other fallbacks
                            console.warn('Alpine mountTableAction failed', err);
                        }
                    }

                    // As a graceful fallback, dispatch a DOM event that the Filament table may listen for.
                    try {
                        tableEl.dispatchEvent(new CustomEvent('mount-table-action', {
                            detail: {
                                action,
                                record: recordId
                            },
                            bubbles: true
                        }));
                        const u2 = new URL(window.location.href);
                        u2.searchParams.delete('action');
                        u2.searchParams.delete('open_action');
                        u2.searchParams.delete('record');
                        window.history.replaceState({}, '', u2.toString());
                        return;
                    } catch (err) {
                        // ignore and continue
                    }
                }
            } catch (e) {
                // ignore and continue to Livewire / DOM fallbacks
            }

            // 2) Next try calling Livewire method on the Livewire component (server-side mount)
            try {
                const scriptEl = document.currentScript || Array.from(document.scripts).pop();
                const livewireRoot = scriptEl ? scriptEl.closest('[wire\\:id]') : document.querySelector('[wire\\:id]');
                const lwId = livewireRoot ? livewireRoot.getAttribute('wire:id') : null;

                if (lwId && window.Livewire && Livewire.find(lwId)) {
                    Livewire.find(lwId).call('mountTableAction', action, recordId)
                        .then(() => {
                            const u = new URL(window.location.href);
                            u.searchParams.delete('action');
                            u.searchParams.delete('open_action');
                            u.searchParams.delete('record');
                            window.history.replaceState({}, '', u.toString());
                        })
                        .catch(() => {
                            // fallback to DOM click (e.g. permission denied or method absent)
                            fallbackClick();
                        });

                    return;
                }
            } catch (e) {
                // continue to fallback
            }

            // 3) final fallback: try to click the action in DOM
            fallbackClick();
        });

        // Replace URL query keys when server requests (cleanup)
        window.addEventListener('replaceUrlQuery', (ev) => {
            const keys = ev?.detail?.keys || [];
            const url = new URL(window.location.href);
            keys.forEach(k => url.searchParams.delete(k));
            window.history.replaceState({}, '', url.toString());
        });
    });
</script>