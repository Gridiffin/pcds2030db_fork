/**
 * Program Ordering functionality
 * Handles drag and drop ordering of programs in the report generator
 */

// Prevent multiple instantiations
if (typeof window.ProgramOrderManager !== 'undefined') {
    console.log('ProgramOrderManager already loaded, skipping redeclaration');
} else {
    
class ProgramOrderManager {
    constructor() {
        this.draggedElement = null;
        this.dragOverElement = null;
        this.onOrderChange = null;
        this.boundHandlers = {
            dragStart: this.handleDragStart.bind(this),
            dragEnd: this.handleDragEnd.bind(this),
            dragOver: this.handleDragOver.bind(this),
            drop: this.handleDrop.bind(this),
            dragLeave: this.handleDragLeave.bind(this)
        };
        this.init();
    }

    init() {
        this.container = document.querySelector('.program-selector-container');
        if (!this.container) return;

        // Attach event listeners
        this.container.addEventListener('dragstart', this.boundHandlers.dragStart);
        this.container.addEventListener('dragend', this.boundHandlers.dragEnd);
        this.container.addEventListener('dragover', this.boundHandlers.dragOver);
        this.container.addEventListener('drop', this.boundHandlers.drop);
        this.container.addEventListener('dragleave', this.boundHandlers.dragLeave);

        // Add checkbox change listeners
        document.querySelectorAll('.program-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.toggleOrderElements(checkbox);
                this.updateOrderNumbers();
            });
        });

        this.initOrderInputs();
        this.updateOrderNumbers();
    }

    destroy() {
        if (this.container) {
            // Remove event listeners
            this.container.removeEventListener('dragstart', this.boundHandlers.dragStart);
            this.container.removeEventListener('dragend', this.boundHandlers.dragEnd);
            this.container.removeEventListener('dragover', this.boundHandlers.dragOver);
            this.container.removeEventListener('drop', this.boundHandlers.drop);
            this.container.removeEventListener('dragleave', this.boundHandlers.dragLeave);
        }
    }

    toggleOrderElements(checkbox) {
        const container = checkbox.closest('.program-checkbox-container');
        if (container) {
            const orderBadge = container.querySelector('.program-order-badge');
            const orderInput = container.querySelector('.program-order-input');
            const dragHandle = container.querySelector('.drag-handle');
            
            if (checkbox.checked) {
                container.draggable = true;
                if (orderBadge) orderBadge.style.display = 'flex';
                if (dragHandle) dragHandle.style.display = 'block';
                if (orderInput) {
                    orderInput.style.display = 'none';
                    if (!orderInput.value) {
                        const checkedCount = document.querySelectorAll('.program-checkbox:checked').length;
                        orderInput.value = checkedCount;
                        orderBadge.textContent = checkedCount;
                    }
                }
            } else {
                container.draggable = false;
                if (orderBadge) orderBadge.style.display = 'none';
                if (dragHandle) dragHandle.style.display = 'none';
                if (orderInput) {
                    orderInput.style.display = 'none';
                    orderInput.value = '';
                    orderBadge.textContent = '#';
                }
            }
        }
    }

    handleDragStart(e) {
        const programContainer = e.target.closest('.program-checkbox-container');
        if (!programContainer || !programContainer.querySelector('.program-checkbox').checked) {
            e.preventDefault();
            return;
        }

        this.draggedElement = programContainer;
        programContainer.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', programContainer.dataset.programId);
    }

    handleDragEnd(e) {
        const programContainer = e.target.closest('.program-checkbox-container');
        if (programContainer) {
            programContainer.classList.remove('dragging');
        }
        this.removeDragClasses();
    }

    handleDragOver(e) {
        e.preventDefault();
        const programContainer = e.target.closest('.program-checkbox-container');
        
        if (programContainer && programContainer !== this.draggedElement && 
            programContainer.querySelector('.program-checkbox').checked) {
            
            this.dragOverElement = programContainer;
            this.removeDragClasses();
            
            // Determine drop position (before or after)
            const rect = programContainer.getBoundingClientRect();
            const midY = rect.top + rect.height / 2;
            
            if (e.clientY < midY) {
                programContainer.classList.add('drop-target');
            } else {
                const nextElement = programContainer.nextElementSibling;
                if (nextElement) {
                    nextElement.classList.add('drop-target');
                }
            }
        }
    }

    handleDragLeave() {
        this.removeDragClasses();
    }

    handleDrop(e) {
        e.preventDefault();
        const programContainer = e.target.closest('.program-checkbox-container');
        
        if (programContainer && this.draggedElement && 
            programContainer !== this.draggedElement) {
            
            // Get the container where we'll insert the dragged element
            const rect = programContainer.getBoundingClientRect();
            const insertBefore = e.clientY < (rect.top + rect.height / 2);
            
            // Insert the dragged element
            const parent = programContainer.parentNode;
            if (insertBefore) {
                parent.insertBefore(this.draggedElement, programContainer);
            } else {
                parent.insertBefore(this.draggedElement, programContainer.nextSibling);
            }
            
            // Update order numbers
            this.updateOrderNumbers();
            
            // Show success message
            this.showOrderUpdateMessage();

            // Notify of order change
            if (typeof this.onOrderChange === 'function') {
                this.onOrderChange();
            }
        }
        
        this.removeDragClasses();
    }

    removeDragClasses() {
        document.querySelectorAll('.program-checkbox-container').forEach(container => {
            container.classList.remove('dragging', 'drop-target');
        });
    }

    initOrderInputs() {
        // Update order numbers when input values change
        document.querySelectorAll('.program-order-input').forEach(input => {
            input.addEventListener('change', () => {
                this.updateOrderNumbers();
                input.style.display = 'none';
                const badge = input.previousElementSibling;
                if (badge) {
                    badge.style.display = 'flex';
                    badge.textContent = input.value;
                }
            });

            input.addEventListener('blur', () => {
                input.style.display = 'none';
                const badge = input.previousElementSibling;
                if (badge) {
                    badge.style.display = 'flex';
                    badge.textContent = input.value || '#';
                }
            });

            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    input.blur();
                }
            });
        });

        // Handle badge clicks
        document.querySelectorAll('.program-order-badge').forEach(badge => {
            badge.addEventListener('click', () => {
                badge.style.display = 'none';
                const input = badge.nextElementSibling;
                if (input) {
                    input.style.display = 'block';
                    input.focus();
                    input.select();
                }
            });
        });
    }

    updateOrderNumbers() {
        const checkedPrograms = document.querySelectorAll('.program-checkbox-container .program-checkbox:checked');
        let order = 1;
        
        checkedPrograms.forEach(checkbox => {
            const container = checkbox.closest('.program-checkbox-container');
            const orderInput = container.querySelector('.program-order-input');
            const orderBadge = container.querySelector('.program-order-badge');
            
            if (orderInput && orderBadge) {
                orderInput.value = order;
                orderBadge.textContent = order;
                orderBadge.style.display = 'flex';
                orderInput.style.display = 'none';
                order++;
            }
        });
    }

    showOrderUpdateMessage() {
        const container = document.querySelector('.program-selector-container');
        const notice = document.createElement('div');
        notice.className = 'alert alert-success mb-2 sort-notice';
        notice.innerHTML = '<i class="fas fa-check-circle me-2"></i>Program order updated successfully!';
        
        const existingNotice = container.querySelector('.sort-notice');
        if (existingNotice) {
            existingNotice.remove();
        }
        
        if (container.firstChild) {
            container.insertBefore(notice, container.firstChild);
            setTimeout(() => {
                notice.classList.add('fade');
                setTimeout(() => notice.remove(), 500);
            }, 3000);
        }
    }
}

// Do not auto-initialize on DOMContentLoaded
// Let report-generator.js handle initialization when programs are loaded

// Clean up resources when the page is unloaded
window.addEventListener('unload', () => {
    if (window.programOrderManager) {
        window.programOrderManager.destroy();
        delete window.programOrderManager;
    }
});

} // End ProgramOrderManager guard
