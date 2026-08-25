@verbatim
  window.addEventListener('error', (e) => {
    if (
      e.message?.includes('lastElementChild') &&
      e.filename?.includes('sortablejs')
    ) {
      e.preventDefault();
    }
  }, true);

  Alpine.data('kanbanBoard', (canManage = true) => ({
    _taskSortables: new Map(),
    _columnSortable: null,
    _hookCleanup: null,
    _taskDebounce: null,
    _colDebounce: null,
    _dragFrame: null,
    _isDraggingTask: false,
    _canManage: canManage,
    _pendingReinit: [],
    _pendingFullReinit: false,

    isDown: false,
    startX: 0,
    scrollLeft: 0,

    init() {
      this.$nextTick(() => this._initAll());

      this._hookCleanup = Livewire.hook('morph.updated', ({
        el
      }) => {
        if (el?.classList?.contains('kanban-column')) {
          clearTimeout(this._taskDebounce);
          this._taskDebounce = setTimeout(() => {
            if (this._isDraggingTask) {
              this._pendingReinit.push(el);
              return;
            }
            this.$nextTick(() => this._reinitTaskSortable(el));
          }, 30);
        } else if (el?.classList?.contains('kanban-col-wrapper')) {
          clearTimeout(this._colDebounce);
          this._colDebounce = setTimeout(() => {
            if (this._isDraggingTask) {
              this._pendingFullReinit = true;
              return;
            }
            this.$nextTick(() => {
              this._columnSortable?.destroy();
              this._columnSortable = null;
              this._initColumnSortable();
            });
          }, 30);
        }
      });

      Livewire.on('task-updated', () => {
        this.$nextTick(() => this._initAll());
      });
    },

    destroy() {
      this._taskSortables.forEach(s => s?.destroy());
      this._taskSortables.clear();
      this._columnSortable?.destroy();
      this._columnSortable = null;
      clearTimeout(this._taskDebounce);
      clearTimeout(this._colDebounce);
      if (this._dragFrame) cancelAnimationFrame(this._dragFrame);
      if (typeof this._hookCleanup === 'function') this._hookCleanup();
    },

    _initAll() {
      if (typeof window.Sortable === 'undefined') {
        setTimeout(() => this._initAll(), 50);
        return;
      }
      this._taskSortables.forEach(s => s?.destroy());
      this._taskSortables.clear();
      this._columnSortable?.destroy();
      this._columnSortable = null;

      this.$el.querySelectorAll('.kanban-column').forEach(col => {
        this._createTaskSortable(col);
      });
      this._initColumnSortable();
    },

    _createTaskSortable(columnEl) {
      const stale = this._taskSortables.get(columnEl);
      if (stale) {
        stale.destroy();
        this._taskSortables.delete(columnEl);
      }

      const instance = new window.Sortable(columnEl, {
        group: 'kanban-tasks',
        animation: 150,
        easing: 'cubic-bezier(0.2, 0, 0, 1)',
        ghostClass: 'kanban-ghost',
        chosenClass: 'kanban-chosen',
        dragClass: 'kanban-drag',
        draggable: '.task-card',
        filter: '.task-locked',
        preventOnFilter: true,
        emptyInsertThreshold: 8,
        scroll: true,
        scrollSensitivity: 60,
        scrollSpeed: 10,
        bubbleScroll: true,
        swapThreshold: 0.65,
        delay: 150,
        delayOnTouchOnly: true,
        touchStartThreshold: 3,
        forceFallback: false,

        onChoose: (evt) => {
          evt.item.style.willChange = 'transform, box-shadow';
        },
        onStart: (evt) => {
          document.body.classList.add('is-dragging');
          this._isDraggingTask = true;
        },
        onEnd: (evt) => {
          document.body.classList.remove('is-dragging');
          evt.item.style.willChange = '';

          const taskId = parseInt(evt.item.dataset.taskId);
          const newStatusId = parseInt(evt.to.dataset.statusId);
          const destType = evt.to.dataset.statusType ?? '';
          const orderedIds = Array.from(evt.to.querySelectorAll('.task-card'))
            .map(el => parseInt(el.dataset.taskId));

          this._syncColumnChrome(evt.from, evt.to);
          this._updateDueBadge(evt.item, destType);
          this.$wire.moveTask(taskId, newStatusId, orderedIds);

          setTimeout(() => {
            this._isDraggingTask = false;
            if (this._pendingFullReinit) {
              this._pendingFullReinit = false;
              this._pendingReinit = [];
              this.$nextTick(() => this._initAll());
            } else if (this._pendingReinit.length) {
              const pending = this._pendingReinit.splice(0);
              this.$nextTick(() => pending.forEach(el => this._reinitTaskSortable(el)));
            }
          }, 150);
        },
      });

      this._taskSortables.set(columnEl, instance);
      return instance;
    },

    _reinitTaskSortable(columnEl) {
      this._createTaskSortable(columnEl);
    },

    _initColumnSortable() {
      if (!this._canManage) return;
      const board = this.$el;
      if (!board) return;

      this._columnSortable = new window.Sortable(board, {
        animation: 220,
        easing: 'cubic-bezier(0.2, 0, 0, 1)',
        draggable: '.kanban-col-wrapper',
        handle: '.kanban-col-handle',
        ghostClass: 'kanban-col-ghost',
        chosenClass: 'kanban-col-chosen',
        dragClass: 'kanban-col-drag',
        direction: 'horizontal',
        delay: 80,
        delayOnTouchOnly: false,
        touchStartThreshold: 8,
        swapThreshold: 0.5,
        scroll: true,
        scrollSensitivity: 120,
        scrollSpeed: 14,
        fallbackOnBody: true,
        forceFallback: false,

        onStart: () => {
          document.body.classList.add('is-dragging-column');
        },
        onEnd: () => {
          document.body.classList.remove('is-dragging-column');
          const ids = Array.from(board.querySelectorAll('.kanban-col-wrapper'))
            .map(el => parseInt(el.dataset.columnId))
            .filter(Number.isFinite);
          if (ids.length) this.$wire.updateColumnOrder(ids);
        },
      });
    },

    _updateDueBadge(cardEl, destStatusType) {
      const badge = cardEl.querySelector('[data-due-badge]');
      if (!badge) return;

      const isClosed = destStatusType === 'closed';
      const closedClass = 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400';
      const openClass = badge.dataset.openClass || '';
      const base = 'inline-flex items-center gap-0.5 rounded-md px-1.5 py-0.5 text-[10px] ';

      badge.setAttribute('class', base + (isClosed ? closedClass : openClass));

      const labelEl = badge.querySelector('[data-due-label]');
      if (labelEl) {
        labelEl.textContent = isClosed ?
          (badge.dataset.closedLabel || labelEl.textContent) :
          (badge.dataset.openLabel || labelEl.textContent);
      }
    },

    _syncColumnChrome(fromCol, toCol) {
      [fromCol, toCol].forEach(col => {
        if (!col) return;
        const count = col.querySelectorAll('.task-card').length;

        const badge = col.closest('.kanban-col-wrapper')?.querySelector('[data-task-count]');
        if (badge) badge.textContent = count;

        const emptyHint = col.querySelector('[data-empty-hint]');
        if (emptyHint) emptyHint.classList.toggle('hidden', count > 0);
      });
    },

    startDrag(e) {
      if (e.pointerType !== 'mouse' || e.button !== 0) return;
      if (document.body.classList.contains('is-dragging-column')) return;
      if (e.target.closest('.task-card') || e.target.closest('button') ||
        e.target.closest('input') || e.target.closest('.kanban-col-handle')) return;
      this.isDown = true;
      this.startX = e.pageX - this.$el.offsetLeft;
      this.scrollLeft = this.$el.scrollLeft;
      this.$el.classList.add('cursor-grabbing');
    },

    stopDrag() {
      if (!this.isDown) return;
      this.isDown = false;
      if (this._dragFrame) {
        cancelAnimationFrame(this._dragFrame);
        this._dragFrame = null;
      }
      this.$el.classList.remove('cursor-grabbing');
    },

    doDrag(e) {
      if (!this.isDown) return;
      if (e.pointerType && e.pointerType !== 'mouse') return;
      if (e.buttons === 0 ||
        document.body.classList.contains('is-dragging') ||
        document.body.classList.contains('is-dragging-column')) {
        this.stopDrag();
        return;
      }
      e.preventDefault();
      if (this._dragFrame) return;
      this._dragFrame = requestAnimationFrame(() => {
        const x = e.pageX - this.$el.offsetLeft;
        this.$el.scrollLeft = this.scrollLeft - (x - this.startX) * 1.5;
        this._dragFrame = null;
      });
    },
  }));
@endverbatim
