<script>
    function setSelect2Selected($element, id, text) {
        if (!id) return;
        var option = new Option(text, id, true, true);
        $element.append(option).trigger('change');
    }

    function initSelect2(container, type = 'all') {
        const $container = $(container);
        const $modal = $container.closest('.modal');

        if (type === 'all' || type === 'service-time') {
            const $select = $container.find('.service-time-select');
            const $modal = $container.closest('.modal');

            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            $select.select2({
                dropdownParent: $modal,
                width: '100%',
                placeholder: "Select Service Time",
                allowClear: true,
                ajax: {
                    url: "{{ route('locations.churchmatrix.integration.times') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        let campus_id = $container.find('select[name="campus_id"]').val();
                        return {
                            search: params.term || "",
                            page: params.page || 1,
                            campus_id: campus_id
                        };
                    },
                    transport: function(params, success, failure) {
                        // campus not selected → block call
                        if (!params.data.campus_id && !serverSideCall) {
                            toastr.error('Select campus first');
                            return; // return without calling AJAX
                        }
                        // normal AJAX call
                        var $request = $.ajax(params);
                        $request.then(success);
                        $request.fail(failure);
                        return $request;
                    },
                    processResults: function(res, params) {
                        params.page = params.page || 1;
                        return {
                            results: $.map(res.data, item => ({
                                id: item.cm_id ?? item.id,
                                text: item.time_of_day
                            })),
                            pagination: {
                                more: res.more ?? false
                            }
                        };
                    }
                }
            });
        }


        if (type === 'all' || type === 'events') {

            const $select = $container.find('.events-select');
            const $modal = $container.closest('.modal');

            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            $select.select2({
                dropdownParent: $('#serviceTimeModal'),
                width: '100%',
                placeholder: "Select Event",
                allowClear: true,
                ajax: {
                    url: "{{ route('locations.churchmatrix.integration.events') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term || "",
                            page: params.page || 1
                        };
                    },
                    processResults: function(res, params) {
                        params.page = params.page || 1;
                        return {
                            results: $.map(res.data, item => ({
                                id: item.id,
                                text: item.name
                            })),
                            pagination: {
                                more: res.more ?? false
                            }
                        };
                    }
                }
            });
        }

        if (type === 'all' || type === 'campuses') {
            $container.find('.campus-select').select2({
                dropdownParent: $modal,
                width: '100%',
                placeholder: "Select Campus",
                allowClear: true,
                ajax: {
                    url: "{{ route('locations.churchmatrix.integration.campuses') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term || "",
                            page: params.page || 1
                        };
                    },
                    processResults: function(res, params) {
                        params.page = params.page || 1;
                        return {
                            results: $.map(res.data, item => ({
                                id: item.id,
                                text: item.name
                            })),
                            pagination: {
                                more: res.more ?? false
                            }
                        };
                    }
                }
            });
        }
    }
</script>
