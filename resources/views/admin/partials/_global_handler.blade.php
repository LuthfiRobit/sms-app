<style>
    .swal2-container {
        z-index: 999999 !important;
    }
</style>
<script>
    /**
     * Handles various types of AJAX responses including success, errors, and validation errors.
     * Provides user-friendly feedback using SweetAlert.
     */
    const ResponseHandler = {
        /**
         * Displays a success message with an optional callback.
         */
        handleSuccess: function(message, callback = null, response = null) {
            if (!message) {
                if (typeof callback === 'function') callback(response);
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                if (typeof callback === 'function') callback(response);
            });
        },

        /**
         * Displays an informational message.
         */
        handleInfo: function(message, callback = null) {
            Swal.fire({
                icon: 'info',
                title: 'Informasi',
                text: message || 'Berikut informasi untuk Anda.',
                showConfirmButton: true,
                confirmButtonText: 'Oke',
            }).then(() => {
                if (typeof callback === 'function') callback();
            });
        },

        /**
         * Displays an error message with an optional callback.
         */
        handleError: function(message, callback = null) {
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: message || 'Terjadi kesalahan pada sistem.',
                showConfirmButton: true,
                confirmButtonText: 'Tutup'
            }).then(() => {
                if (typeof callback === 'function') callback();
            });
        },

        /**
         * Clears all validation error messages and classes from a form.
         */
        clearValidationErrors: function(form) {
            if (!form) return;
            const $form = $(form);
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.invalid-feedback').remove();
            // Special handling for selectpicker
            $form.find('.bootstrap-select').removeClass('is-invalid');
        },

        /**
         * Handles form validation errors.
         * Returns true if errors were displayed on the form.
         */
        handleValidationErrors: function(errors, form) {
            if (!form) return false;

            this.clearValidationErrors(form);
            const $form = $(form);
            let count = 0;

            $.each(errors, function(field, messages) {
                let input = $form.find(`[name="${field}"], [name="${field}[]"]`);
                if (input.length) {
                    input.addClass('is-invalid');
                    
                    let target = input;
                    // Handle bootstrap-select (selectpicker)
                    if (input.hasClass('selectpicker') || input.closest('.bootstrap-select').length) {
                        target = input.closest('.bootstrap-select');
                        target.addClass('is-invalid');
                    }

                    // Handle input-group or standard input
                    if (target.parent('.input-group').length) {
                        target.parent().after(`<div class="invalid-feedback d-block">${messages[0]}</div>`);
                    } else {
                        target.after(`<div class="invalid-feedback">${messages[0]}</div>`);
                    }
                    count++;
                }
            });
            return count > 0;
        },

        /**
         * Displays a confirmation dialog before performing an action.
         */
        confirm: function(options) {
            const {
                title = 'Apakah Anda yakin?',
                text = 'Tindakan ini tidak dapat dikembalikan!',
                icon = 'warning',
                confirmButtonText = 'Ya, Lanjutkan!',
                cancelButtonText = 'Batal',
                confirmButtonColor = '#3085d6',
                cancelButtonColor = '#d33',
                onConfirm
            } = options;

            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: confirmButtonColor,
                cancelButtonColor: cancelButtonColor,
                confirmButtonText: confirmButtonText,
                cancelButtonText: cancelButtonText
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof onConfirm === 'function') onConfirm();
                }
            });
        },

        /**
         * Handles JSON responses and updates DOM elements.
         */
        handleResponse: function(response, target = null) {
            if (response.status === 200) {
                if (typeof response.data === "string") {
                    $(target ?? 'body').html(response.data);
                } else if (typeof response.data === "object" && response.data !== null) {
                    $.each(response.data, function(key, value) {
                        let field = $(`[name="${key}"], #${key}`);
                        if (field.is(":radio")) {
                            $(`[name="${key}"][value="${value}"]`).prop('checked', true);
                        } else if (field.is(":checkbox")) {
                            field.prop('checked', !!value);
                        } else if (field.is("input, textarea, select")) {
                            if (!field.is("input[type='file']")) {
                                field.val(value).trigger('change');
                                // Do NOT refresh selectpicker here as the modal might be hidden.
                                // It will be handled globally in the shown.bs.modal listener.
                            }
                        } else {
                            field.html(value);
                        }
                    });
                }
            } else {
                this.handleError(response.message);
            }
        },

        /**
         * Handles HTTP errors and displays appropriate error messages.
         */
        handleHttpError: function(xhr, errorCallback = null, form = null) {
            let message = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';

            // If no message from server, fallback to status-based defaults
            if (!xhr.responseJSON?.message) {
                switch (xhr.status) {
                    case 401: message = 'Sesi telah berakhir. Silakan login kembali.'; break;
                    case 403: message = 'Anda tidak memiliki hak akses untuk tindakan ini.'; break;
                    case 404: message = 'Data atau resource tidak ditemukan.'; break;
                    case 422: message = 'Validasi gagal. Periksa kembali inputan Anda.'; break;
                    case 429: message = 'Terlalu banyak percobaan. Silakan coba lagi nanti.'; break;
                    case 500: message = 'Internal Server Error. Silakan hubungi admin.'; break;
                }
            }

            // If it's a validation error and we have a form, try to show them inline
            const errors = xhr.responseJSON?.data || xhr.responseJSON?.errors;
            if (xhr.status === 422 && errors) {
                const handled = this.handleValidationErrors(errors, form);
                // If we couldn't show errors on the form (e.g. no matching fields), show alert instead
                if (!handled) {
                    this.handleError(message, () => {
                        if (typeof errorCallback === 'function') errorCallback(xhr);
                    });
                    return;
                }
            } else {
                this.handleError(message, () => {
                    if (typeof errorCallback === 'function') errorCallback(xhr);
                });
                return;
            }

            // If handled inline, still call the error callback
            if (typeof errorCallback === 'function') {
                errorCallback(xhr);
            }
        }
    };

    /**
     * AJAX handler for sending requests with built-in response handling.
     */
    const AjaxHandler = {
        sendRequest: function(url, method, data, successCallback, errorCallback, form = null, headers = {}) {
            $.ajax({
                url: url,
                method: method,
                data: data,
                dataType: 'json',
                processData: data instanceof FormData ? false : true,
                contentType: data instanceof FormData ? false : 'application/x-www-form-urlencoded; charset=UTF-8',
                headers: headers,
                success: function(response) {
                    if (response.status === 200) {
                        ResponseHandler.handleSuccess(response.message, successCallback, response);
                    } else if (response.status === 422) {
                        // Handle manual 422 returned with 200 OK
                        const errors = response.data || response.errors;
                        const handled = ResponseHandler.handleValidationErrors(errors, form);
                        if (!handled) {
                            ResponseHandler.handleError(response.message || 'Validasi gagal.');
                        }
                    } else {
                        ResponseHandler.handleError(response.message || 'Operasi gagal.');
                    }
                },
                error: function(xhr) {
                    ResponseHandler.handleHttpError(xhr, errorCallback, form);
                }
            });
        },

        sendStoreRequest: function(url, form, successCallback, errorCallback, headers = {}) {
            let formData = new FormData(form);
            this.sendRequest(url, 'POST', formData, successCallback, errorCallback, form, headers);
        },

        sendUpdateRequest: function(url, form, successCallback, errorCallback, headers = {}) {
            let formData = new FormData(form);
            formData.append('_method', 'PUT');
            this.sendRequest(url, 'POST', formData, successCallback, errorCallback, form, headers);
        },

        sendGetRequest: function(url, successCallback, errorCallback) {
            if (typeof showLoading === 'function') showLoading();
            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (typeof hideLoading === 'function') hideLoading();
                    ResponseHandler.handleResponse(response);
                    if (typeof successCallback === 'function') successCallback(response);
                },
                error: function(xhr) {
                    if (typeof hideLoading === 'function') hideLoading();
                    ResponseHandler.handleHttpError(xhr, errorCallback);
                }
            });
        },

        sendDeleteRequest: function(url, successCallback, errorCallback, headers = {}) {
            this.sendRequest(url, 'DELETE', null, successCallback, errorCallback, null, headers);
        },

        sendPostRequest: function(url, data, successCallback, errorCallback, headers = {}) {
            this.sendRequest(url, 'POST', data, successCallback, errorCallback, null, headers);
        },
    };

    /**
     * Helper wrappers for common CRUD scenarios
     */
    function handleAjax(url, method, data = {}, onSuccess = null, onError = null) {
        if (method.toUpperCase() === 'GET') {
            AjaxHandler.sendGetRequest(url, onSuccess, onError);
        } else {
            let headers = {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            };
            AjaxHandler.sendRequest(url, method, data, onSuccess, onError, null, headers);
        }
    }

    function setupCrudHandlers(config) {
        const {
            tableId,
            createFormId,
            editFormId,
            createModalId,
            editModalId,
            editUrl, // Template URL like /admin/data/{id}
            updateUrl,
            deleteUrl,
            onEditSuccess = null
        } = config;

        // Handle Create Form Submit
        if ($(createFormId).length) {
            $(createFormId).on('submit', function(e) {
                e.preventDefault();
                let form = this;
                let btn = $(this).find('button[type="submit"]');
                let originalText = btn.html();
                
                ResponseHandler.clearValidationErrors(form);
                btn.prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Menyimpan...');

                AjaxHandler.sendStoreRequest(
                    $(this).attr('action'),
                    form,
                    function(response) { // successCallback
                        $(createModalId).modal('hide');
                        form.reset();
                        $(tableId).DataTable().ajax.reload();
                        btn.prop('disabled', false).html(originalText);
                    },
                    function(xhr) { // errorCallback
                        btn.prop('disabled', false).html(originalText);
                    }
                );
            });
        }

        // Handle Edit Click Component
        if ($(tableId).length) {
            $(tableId).on('click', '.btn-edit', function() {
                let id = $(this).data('id');
                let url = editUrl.replace('{id}', id);
                
                AjaxHandler.sendGetRequest(url, function(response) {
                    if(response.status === 200) {
                        if (typeof onEditSuccess === 'function') {
                            onEditSuccess(response.data);
                        } else {
                            ResponseHandler.handleResponse(response, editFormId);
                        }
                        
                        // Set the correct form action method inside JS
                        $(editFormId).attr('action', updateUrl.replace('{id}', id));
                        $(editModalId).modal('show');
                    }
                });
            });

            // Handle Delete Click
            $(tableId).on('click', '.btn-delete', function() {
                let id = $(this).data('id');
                let url = deleteUrl.replace('{id}', id);

                ResponseHandler.confirm({
                    onConfirm: function() {
                        AjaxHandler.sendDeleteRequest(url, function(response) {
                            $(tableId).DataTable().ajax.reload(null, false);
                        }, null, {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        });
                    }
                });
            });
        }

        // Handle Edit Form Submit
        if ($(editFormId).length) {
            $(editFormId).on('submit', function(e) {
                e.preventDefault();
                let form = this;
                let btn = $(this).find('button[type="submit"]');
                let originalText = btn.html();
                
                ResponseHandler.clearValidationErrors(form);
                btn.prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Menyimpan...');

                AjaxHandler.sendUpdateRequest(
                    $(this).attr('action'),
                    form,
                    function(response) { // success
                        $(editModalId).modal('hide');
                        $(tableId).DataTable().ajax.reload(null, false); // Keep current paging
                        btn.prop('disabled', false).html(originalText);
                    },
                    function(xhr) { // error
                        btn.prop('disabled', false).html(originalText);
                    }
                );
            });
        }
    }

    /**
     * Global listener to reset forms and clear validation errors when any modal is hidden.
     */
    $(document).on('hidden.bs.modal', '.modal', function () {
        const $modal = $(this);
        const $form = $modal.find('form');
        
        if ($form.length) {
            $form.each(function() {
                this.reset();
                ResponseHandler.clearValidationErrors(this);
            });
        }
    });

    /**
     * Definitive fix for Selectpicker duplication:
     * 1. On hidden: Completely destroy the selectpicker to return it to a clean HTML state.
     * 2. On shown: Re-initialize fresh from the actual HTML options.
     */
    $(document).on('hidden.bs.modal', '.modal', function () {
        const $modal = $(this);
        const $form = $modal.find('form');
        
        if ($form.length) {
            $form.each(function() {
                this.reset();
                ResponseHandler.clearValidationErrors(this);
                if ($.fn.selectpicker) {
                    $(this).find('select.selectpicker').selectpicker('destroy');
                }
            });
        }
    });

    $(document).on('shown.bs.modal', '.modal', function () {
        if ($.fn.selectpicker) {
            $(this).find('select.selectpicker').each(function() {
                // Initialize then refresh to ensure it captures any values set while it was uninitialized
                $(this).selectpicker().selectpicker('refresh');
            });
        }
    });
</script>
<script>
    window.DropdownHelper = {
        /**
         * Bind dropdown dependent (umum, reusable)
         *
         * @param {string} parentSelector - Selector dropdown induk (ex: '#tahun_anggaran_id')
         * @param {string} childSelector - Selector dropdown target yang diisi (ex: '#jenis_kegiatan_id')
         * @param {string} routeTemplate - Template URL dengan placeholder :id (ex: '/api/kegiatan/by-tahun/:id')
         * @param {function} formatOption - (Optional) Callback function untuk format <option>. Params: item, selectedId
         * @param {string|number|null} selectedId - (Optional) Value yang harus diseleksi setelah isi
         */
        bindDependentDropdown: function(parentSelector, childSelector, urlTemplate, optionFormatter, selectedValue =
            null) {
            const $parent = $(parentSelector);
            const $child = $(childSelector);

            function loadOptions(parentVal) {
                if (!parentVal) {
                    $child.html('<option value="">Pilih pilihan</option>').selectpicker('refresh');
                    return;
                }

                const url = urlTemplate.replace(':id', parentVal);

                AjaxHandler.sendGetRequest(url, response => {
                    if (response.status === 200 && Array.isArray(response.data)) {
                        let options = '<option value="">Pilih pilihan</option>';
                        response.data.forEach(item => {
                            options += optionFormatter(item, selectedValue);
                        });
                        $child.html(options);
                        $child.selectpicker('refresh');
                    } else {
                        ResponseHandler.handleError("Gagal memuat data.");
                        $child.html('<option value="">Pilih pilihan</option>').selectpicker('refresh');
                    }
                });
            }

            // Bind event change parent
            $parent.off('change.dependentDropdown').on('change.dependentDropdown', function() {
                loadOptions($(this).val());
            });

            // Load once on init (optional)
            loadOptions($parent.val());
        }

    };
</script>
