jQuery(function ($) {
    'use strict';

    var mediaUploader;

    $('.woa-upload-btn').on('click', function (e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Get allowed types from localized script data
        var allowedTypes = woa_params.allowed_mimes || {};
        var allowedTypesKeys = Object.keys(allowedTypes);

        mediaUploader = wp.media({
            title: woa_params.i18n.select_attachment,
            button: { text: woa_params.i18n.use_this_file },
            multiple: false,
            library: {
                type: allowedTypesKeys.length > 0 ? allowedTypesKeys.join(',') : null
            }
        });

        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();

            // Check file size (2MB = 2 * 1024 * 1024 bytes)
            if (attachment.filesizeInBytes > 2097152) {
                alert(woa_params.i18n.file_too_large);
                return;
            }

            // Client-side MIME type check (backup to library filter)
            if (allowedTypesKeys.length > 0 && !allowedTypes[attachment.mime]) {
                // Check if it's a subtype match (e.g. image/jpeg matches image/)
                var isAllowed = false;
                for (var i = 0; i < allowedTypesKeys.length; i++) {
                    if (attachment.mime === allowedTypesKeys[i]) {
                        isAllowed = true;
                        break;
                    }
                }

                if (!isAllowed) {
                    console.warn('Selected file type not allowed: ' + attachment.mime);
                }
            }

            $('#woa_attachment_id').val(attachment.id);
            $('.woa-file-name').text(woa_params.i18n.current_file + ' ' + attachment.filename);

            if (!$('.woa-remove-btn').length) {
                $('.woa-upload-btn').after(' <button type="button" class="button woa-remove-btn">' + woa_params.i18n.remove + '</button>');
            }
        });

        mediaUploader.open();
    });

    $(document).on('click', '.woa-remove-btn', function (e) {
        e.preventDefault();
        $('#woa_attachment_id').val('');
        $('.woa-file-name').text('');
        $('.woa-remove-btn').remove();
    });
});
