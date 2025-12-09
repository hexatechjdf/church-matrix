<script>
    $(document).on('submit', '.form-submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let url = form.attr('action');
        let res = form.data('res');
        let data_table = form.data('table');
        let method = form.attr('method') || 'POST';

        let submitBtn = form.find('button[type=submit]');
        let formData = new FormData(this);

        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,

            beforeSend: function() {
                submitBtn.prop('disabled', true).html('Processing...');
            },

            success: function(response) {
                if (response.success) {
                    toastr.success(response.success);
                } else {
                    toastr.success("Success!");
                }

                if (res == 'regions') {
                    automatRegions(response.regions);
                }

                if(data_table) {
                    $(`#${data_table}`).DataTable().ajax.reload();
                }
            },

            error: function(xhr) {

                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function(key, msg) {
                        toastr.error(msg);
                    });
                    return;
                }

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error("Something went wrong.");
                }
            },

            complete: function() {
                submitBtn.prop('disabled', false).html('Submit');
            }
        });
    });


    function automatRegions(regions) {
        alert(regions);
        let select = $("#region_id");
        if (!regions || regions.length === 0) {
            select.html('<option value="">-- Choose Region --</option>');
            return;
        }
        let oldSelected = select.val();
        select.empty();
        select.append('<option value="">-- Choose Region --</option>');
        regions.forEach(region => {
            let isSelected = (oldSelected == region.id) ? "selected" : "";
            select.append(`<option value="${region.id}" ${isSelected}>${region.name}</option>`);
        });
    }


    $(document).on('click', '.action-btn', function() {

        let url = $(this).data('url');
        let message = $(this).data('message');
        let successMsg = $(this).data('success');
        let submitBtn = $(this);
        let originalText = $(this).text();
        let funcName = $(this).data('function');
        let data_table =  $(this).data('table');

        Swal.fire({
            title: "Are you sure?",
            text: message,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Proceed",
            cancelButtonText: "Cancel"
        }).then((result) => {

            if (result.value) {
                $.ajax({
                    url: url,
                    type: "POST",
                    beforeSend: function() {
                        submitBtn.prop('disabled', true).html('Processing...');
                    },
                    success: function(res) {
                        toastr.success(successMsg || res.message);
                        if (funcName && typeof window[funcName] === 'function') {
                            window[funcName]();
                        }

                        if(data_table) {
                            $(`#${data_table}`).DataTable().ajax.reload();
                        }
                    },
                    error: function() {
                        toastr.error("Something went wrong!");
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });

            }

        });

    });
</script>
