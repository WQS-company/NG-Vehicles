/* public/js/app.js */
// General utility scripts for NVOTS Web Application

$(document).ready(function() {
    // Inject CSRF Token in all jQuery AJAX Requests automatically
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });

    // Handle SweetAlert confirmation for deletion and forms
    $('.confirm-action').on('click', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        const text = $(this).data('confirm-text') || 'Do you want to proceed?';

        Swal.fire({
            title: 'Are you sure?',
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });
});
