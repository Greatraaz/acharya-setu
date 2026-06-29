/****************************************************************************
 *                     CODE BY :  ASENDRA RAJ SHAKYA
 ****************************************************************************/

let base_url = window.baseUrl;

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// ── Bulk Dropdown Toggle ──────────────────────────────────────
    $('#bulkDropdownBtn').on('click', function (e) {
        e.stopPropagation();
        $('#bulkDropdownMenu').toggleClass('hidden');
    });

    $(document).on('click', function () {
        $('#bulkDropdownMenu').addClass('hidden');
    });

// Open modal helper
function openGlobalModal() {
    $("#globalModal").removeClass("hidden");
    $("body").addClass("overflow-hidden"); // prevent background scroll
}

// Close modal helper
function closeGlobalModal() {
    $("#globalModal").addClass("hidden");
    $("body").removeClass("overflow-hidden");
}

// Close on X button
$("#globalModalClose").on("click", function () {
    closeGlobalModal();
});

// Close on backdrop click
$("#globalModalBackdrop").on("click", function () {
    closeGlobalModal();
});

// Close on Escape key
$(document).on("keydown", function (e) {
    if (e.key === "Escape") closeGlobalModal();
});

// AJAX Modal trigger
$(document).on("click", ".ajaxModal", function () {
    let url = $(this).data("url");
    let title = $(this).data("title");

    $("#globalModalTitle").text(title);
    $("#globalModalBody").html(`
        <div class='text-center py-5 text-slate-500'>
            Loading...
        </div>
    `);

    openGlobalModal();

    $.ajax({
        url: url,
        type: "GET",
        success: function (response) {
            $("#globalModalBody").html(response);
        },
        error: function () {
            $("#globalModalBody").html(
                '<div class="text-red-500 text-sm">Something went wrong.</div>'
            );
        },
    });
});

$(document).on('submit','form.formsubmit', function(e) {
    e.preventDefault();
    $('.loader-container').css('display', 'inline-flex');
    var form = $(this);
    var url = form.attr('action');
    var returnurl = form.data('redirect');
    var type = form.attr('method');
    var data = new FormData(form[0]);
    
    // Include CSRF token in headers
    var token = $('meta[name="csrf-token"]').attr('content');

    // Clear previous errors
    $('.error-text').text('');

    $.ajax({
        url: url,
        method: type,
        data: data,
        success: function(response) {

            if(response.status === 200) {
                toastr.success(response.message);
                console.log(response);
                setTimeout(function(){
                    if(response.redirect && response.redirect !== '') {
                        window.location.href = response.redirect;
                    } else {
                        window.location.reload();
                    }
                }, 1000);
            }

        },
        error: function(xhr) {

            // Remove old errors
            $('.validation-error').remove();

            $('input, select, textarea').removeClass(
            'border-red-500 text-red-600 placeholder-red-300 focus:ring-red-500 focus:border-red-500'
            );

            if (xhr.status === 422) {

                let errors = xhr.responseJSON.errors;

                $.each(errors, function(key, value){

                    let inputField = $('[name="'+ key +'"]');

                    inputField.addClass(
                      'border-red-500 text-red-600 placeholder-red-300 focus:ring-red-500 focus:border-red-500'
                    );

                    inputField.after(
                      '<p class="validation-error mt-1 text-sm text-red-500">'
                      + value[0] +
                      '</p>'
                    );

                });

                toastr.error(xhr.responseJSON.message);
            }

            if (xhr.status === 419) {
                toastr.error(xhr.responseJSON.message);
            }
        
            if (xhr.status === 401) {
                toastr.error(xhr.responseJSON.message);
            }

            if (xhr.status === 500) {
              toastr.error(xhr.responseJSON.message);
          }
        }
        
    });

});

$(document).on('click', '.deleteRecord', function () {

    let button = $(this);
    let url = button.data('url');
    let title = button.data('title');

    Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: url,
                type: "DELETE",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {

                    Swal.fire(
                        'Deleted!',
                        response.message,
                        'success'
                    );

                    // Option 1: Remove row (if inside table)
                    button.closest('tr').remove();

                    // Option 2 (optional): reload page
                    // location.reload();

                },
                error: function (xhr) {

                    Swal.fire(
                        'Error!',
                        xhr.responseJSON?.message || 'Something went wrong',
                        'error'
                    );
                }
            });
        }
    });
});

// ── Check All ─────────────────────────────────────────────────
$('#checkAll').on('change', function () {
    $('.rowCheck').prop('checked', $(this).prop('checked'));
});

// ── Get Selected IDs helper ───────────────────────────────────
function getSelectedIds() {
    return $('.rowCheck:checked').map(function () {
        return $(this).val();
    }).get();
}

// ── Get Selected IDs helper ───────────────────────────────────
function getSelectedIds() {
    return $('.rowCheck:checked').map(function () {
        return $(this).val();
    }).get();
}

// ── Bulk Delete ───────────────────────────────────────────────
$('#bulkDelete').on('click', function () {
    let ids = getSelectedIds();
    let url  = $(this).data('url');

    if (!ids.length) {
        Swal.fire({
            icon: 'warning',
            title: 'No Selection',
            text: 'Please select at least one student.',
            confirmButtonColor: '#f97316',
        });
        return;
    }

    Swal.fire({
        icon:              'warning',
        title:             'Delete Students?',
        text:              `You are about to delete ${ids.length} student(s). This cannot be undone.`,
        showCancelButton:  true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText:  'Cancel',
        confirmButtonColor:'#ef4444',
        cancelButtonColor: '#94a3b8',
    }).then(result => {
        if (!result.isConfirmed) return;

        $.post(url, {
            _token: '{{ csrf_token() }}',
            ids,
        })
        .done(() => {
            Swal.fire({
                icon:               'success',
                title:              'Deleted!',
                text:               `${ids.length} student(s) deleted successfully.`,
                confirmButtonColor: '#f97316',
            }).then(() => location.reload());
        })
        .fail(() => {
            Swal.fire({
                icon:  'error',
                title: 'Failed',
                text:  'Something went wrong. Please try again.',
                confirmButtonColor: '#f97316',
            });
        });
    });
});

// ── Bulk Activate ─────────────────────────────────────────────
$('#bulkActivate').on('click', function () {
    let ids = getSelectedIds();
    let url  = $(this).data('url');
    if (!ids.length) {
        Swal.fire({
            icon:               'warning',
            title:              'No Selection',
            text:               'Please select at least one student.',
            confirmButtonColor: '#f97316',
        });
        return;
    }

    Swal.fire({
        icon:              'question',
        title:             'Activate Students?',
        text:              `Activate ${ids.length} student(s)?`,
        showCancelButton:  true,
        confirmButtonText: 'Yes, Activate',
        cancelButtonText:  'Cancel',
        confirmButtonColor:'#22c55e',
        cancelButtonColor: '#94a3b8',
    }).then(result => {
        if (!result.isConfirmed) return;
        $(this).data('url');
        $.post(url, {
            _token: '{{ csrf_token() }}',
            ids,
        })
        .done(() => {
            Swal.fire({
                icon:               'success',
                title:              'Activated!',
                text:               `${ids.length} student(s) activated successfully.`,
                confirmButtonColor: '#f97316',
            }).then(() => location.reload());
        })
        .fail(() => {
            Swal.fire({
                icon:               'error',
                title:              'Failed',
                text:               'Something went wrong. Please try again.',
                confirmButtonColor: '#f97316',
            });
        });
    });
});

// ── Bulk Deactivate ───────────────────────────────────────────
$('#bulkDeactivate').on('click', function () {
    let ids = getSelectedIds();
    let url  = $(this).data('url');
    
    if (!ids.length) {
        Swal.fire({
            icon:               'warning',
            title:              'No Selection',
            text:               'Please select at least one student.',
            confirmButtonColor: '#f97316',
        });
        return;
    }

    Swal.fire({
        icon:              'question',
        title:             'Deactivate Students?',
        text:              `Deactivate ${ids.length} student(s)?`,
        showCancelButton:  true,
        confirmButtonText: 'Yes, Deactivate',
        cancelButtonText:  'Cancel',
        confirmButtonColor:'#f59e0b',
        cancelButtonColor: '#94a3b8',
    }).then(result => {
        if (!result.isConfirmed) return;

        $.post(url, {
            _token: '{{ csrf_token() }}',
            ids,
        })
        .done(() => {
            Swal.fire({
                icon:               'success',
                title:              'Deactivated!',
                text:               `${ids.length} student(s) deactivated successfully.`,
                confirmButtonColor: '#f97316',
            }).then(() => location.reload());
        })
        .fail(() => {
            Swal.fire({
                icon:               'error',
                title:              'Failed',
                text:               'Something went wrong. Please try again.',
                confirmButtonColor: '#f97316',
            });
        });
    });
});
