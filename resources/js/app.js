import './bootstrap';
import Alpine from 'alpinejs';
import Sortable from 'sortablejs';

window.Alpine = Alpine;
window.Sortable = Sortable;

window.taskBoard = function () {
    return {
        error: '',
        dragging: false,
        counts: {
            pending:     window.__taskCounts?.pending     ?? 0,
            in_progress: window.__taskCounts?.in_progress ?? 0,
            for_review:  window.__taskCounts?.for_review  ?? 0,
            completed:   window.__taskCounts?.completed   ?? 0,
        },

        init() {
            const csrf = document.querySelector('meta[name=csrf-token]').content;

            ['pending', 'in_progress', 'for_review', 'completed'].forEach(col => {
                const el = this.$refs['column_' + col];
                if (!el) return;

                new window.Sortable(el, {
                    group: 'tasks',
                    animation: 150,
                    ghostClass: 'opacity-40',
                    dragClass: 'shadow-lg',
                    forceFallback: false,
                    touchStartThreshold: 5,
                    // Ignore native anchor drags (in case any <a> is inside)
                    draggable: '[data-task-id]',
                    filter: 'a, button',
                    preventOnFilter: false,

                    onStart: () => {
                        this.dragging = true;
                    },

                    onEnd: async (evt) => {
                        // Keep dragging=true for 150ms so click handler can detect & suppress
                        setTimeout(() => { this.dragging = false; }, 150);

                        const taskId  = evt.item.dataset.taskId;
                        const fromCol = evt.from.dataset.column;
                        const toCol   = evt.to.dataset.column;

                        if (fromCol === toCol) return;

                        // Optimistic UI update
                        evt.item.dataset.status = toCol;
                        this.counts[fromCol] = Math.max(0, this.counts[fromCol] - 1);
                        this.counts[toCol]   = this.counts[toCol] + 1;
                        this.refreshEmptyStates();

                        try {
                            const res = await fetch(`/tasks/${taskId}/move`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({ status: toCol }),
                            });

                            const data = await res.json().catch(() => ({}));

                            if (!res.ok || !data.ok) {
                                throw new Error(data.error || `HTTP ${res.status}`);
                            }
                        } catch (e) {
                            // Revert DOM
                            if (evt.oldIndex != null && evt.from.children[evt.oldIndex]) {
                                evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
                            } else {
                                evt.from.appendChild(evt.item);
                            }
                            evt.item.dataset.status = fromCol;
                            this.counts[fromCol] = this.counts[fromCol] + 1;
                            this.counts[toCol]   = Math.max(0, this.counts[toCol] - 1);
                            this.refreshEmptyStates();

                            this.error = 'Failed to update task status. Please try again.';
                            setTimeout(() => { this.error = ''; }, 3500);
                        }
                    },
                });
            });

            this.refreshEmptyStates();
        },

        refreshEmptyStates() {
            document.querySelectorAll('[data-column]').forEach(col => {
                const key     = col.dataset.column;
                const empty   = col.querySelector('[data-empty="' + key + '"]');
                const hasTask = col.querySelectorAll('[data-task-id]').length > 0;

                if (hasTask && empty) empty.remove();

                if (!hasTask && !empty) {
                    const p = document.createElement('p');
                    p.className = 'text-xs text-slate-400 text-center py-8';
                    p.dataset.empty = key;
                    p.textContent = 'No tasks';
                    col.appendChild(p);
                }
            });
        },

        // Navigate on click, but ONLY if the user wasn't dragging
        handleCardClick(e) {
            if (this.dragging) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            const url = e.currentTarget.dataset.taskUrl;
            if (url) {
                window.location.href = url;
            }
        },
    };
};

Alpine.start();