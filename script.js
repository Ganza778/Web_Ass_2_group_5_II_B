// Live price estimator (also in book.php)
document.addEventListener('DOMContentLoaded', function() {
    // Any global JavaScript can go here
    
    // Add confirmation for delete actions
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this?')) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-hide messages after 5 seconds
    const messages = document.querySelectorAll('.success, .error');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.display = 'none';
        }, 5000);
    });
});