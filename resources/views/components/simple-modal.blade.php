<!-- Simple Modal Component for Laravel Blade -->
<div class="simple-modal" id="{{ $modalId }}" style="display: none;">
    <div class="simple-modal-backdrop" onclick="closeModal('{{ $modalId }}')"></div>
    <div class="simple-modal-dialog">
        <div class="simple-modal-content">
            <div class="simple-modal-header">
                <h5 class="simple-modal-title">{{ $title }}</h5>
                <button type="button" class="simple-modal-close" onclick="closeModal('{{ $modalId }}')">&times;</button>
            </div>
            <div class="simple-modal-body">
                {{ $slot }}
            </div>
            <div class="simple-modal-footer">
                {{ $footer ?? '' }}
            </div>
        </div>
    </div>
</div>

<style>
.simple-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1050;
}

.simple-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.simple-modal-dialog {
    position: relative;
    width: 90%;
    max-width: 600px;
    margin: 50px auto;
    z-index: 1051;
}

.simple-modal-content {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    overflow: hidden;
}

.simple-modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: between;
    align-items: center;
    background-color: #f8f9fa;
}

.simple-modal-title {
    margin: 0;
    color: #212529;
    font-weight: 600;
    flex: 1;
}

.simple-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6c757d;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.simple-modal-close:hover {
    color: #000;
}

.simple-modal-body {
    padding: 1.5rem;
    max-height: 60vh;
    overflow-y: auto;
}

.simple-modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
    background-color: #f8f9fa;
    text-align: right;
}

.simple-modal input,
.simple-modal select,
.simple-modal textarea,
.simple-modal button {
    pointer-events: auto !important;
    opacity: 1 !important;
    background-color: white !important;
    color: #212529 !important;
    border: 1px solid #ced4da !important;
}

.simple-modal .form-control,
.simple-modal .form-select {
    pointer-events: auto !important;
    opacity: 1 !important;
    background-color: white !important;
    color: #212529 !important;
}
</style>

<script>
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Focus first input
        setTimeout(() => {
            const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        }, 100);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
        // Reset form
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
        }
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const visibleModals = document.querySelectorAll('.simple-modal[style*="block"]');
        visibleModals.forEach(modal => {
            modal.style.display = 'none';
        });
        document.body.style.overflow = '';
    }
});
</script>
