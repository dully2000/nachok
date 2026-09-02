/**
 * CODE X - Interactive Client JavaScript Helper
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Auto-dismiss Flash Alerts
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    // 2. Generic Table Live Search Filter
    const searchInputs = document.querySelectorAll('[data-table-search]');
    searchInputs.forEach(input => {
        const targetTableId = input.getAttribute('data-table-search');
        const targetTable = document.getElementById(targetTableId);
        if (!targetTable) return;

        input.addEventListener('keyup', () => {
            const query = input.value.toLowerCase();
            const rows = targetTable.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    });

    // 3. Confirm Delete Prompts
    const deleteButtons = document.querySelectorAll('.btn-confirm-delete');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // 4. Scroll AI Chat to Bottom Automatically
    const chatBox = document.getElementById('chatBox');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
});

// Format Currency Utility for JS Charts
function formatJSAmount(amount) {
    return '$' + parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
