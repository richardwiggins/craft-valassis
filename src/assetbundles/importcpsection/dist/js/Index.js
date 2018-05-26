/**
 * Valassis plugin for Craft CMS
 *
 * Index Field JS
 *
 * @author    Superbig
 * @copyright Copyright (c) 2018 Superbig
 * @link      https://superbig.co
 * @package   Valassis
 * @since     1.0.0
 */
/**
 * CSV uploader for importing coupons
 */
ValassisUpload = Garnish.Base.extend(
    {
        $container: null,
        progressBar: null,
        uploader: null,

        init: function (settings) {
            this.setSettings(settings, ValassisUpload.defaults);

            this.initUploader();
        },

        initUploader: function () {
            this.$container = $(this.settings.containerSelector);
            this.$results = $(this.settings.resultSelector);
            this.$resultContainer = $(this.settings.resultSelector).parent();
            this.$importButton = $(this.settings.importButtonSelector);
            this.progressBar = new Craft.ProgressBar($('<div class="progress-shade"></div>').appendTo(this.$resultContainer));

            var options = {
                url: Craft.getActionUrl(this.settings.uploadAction),
                formData: this.settings.postParameters,
                fileInput: this.$container.find(this.settings.fileInputSelector),
                paramName: this.settings.uploadParamName
            };

            // If CSRF protection isn't enabled, these won't be defined.
            if (typeof Craft.csrfTokenName !== 'undefined' && typeof Craft.csrfTokenValue !== 'undefined') {
                // Add the CSRF token
                options.formData[Craft.csrfTokenName] = Craft.csrfTokenValue;
            }

            options.events = {};
            options.events.fileuploadstart = $.proxy(this, '_onUploadStart');
            options.events.fileuploadprogressall = $.proxy(this, '_onUploadProgress');
            options.events.fileuploaddone = $.proxy(this, '_onUploadComplete');
            options.events.fileuploadfail = $.proxy(this, '_onUploadError');

            this.uploader = new Craft.Uploader(this.$container, options);

            this.initButtons();

        },

        initButtons: function () {
            this.$container.find(this.settings.uploadButtonSelector).on('click', $.proxy(function (ev) {
                this.$container.find(this.settings.fileInputSelector).trigger('click');
            }, this));
        },

        refreshResult: function (response) {
            $(this.settings.resultSelector).replaceWith(response.html);
            this.settings.onAfterRefreshResult(response);
            //this.initUploader();
        },

        /**
         * On upload start.
         */
        _onUploadStart: function (event) {
            this.progressBar.$progressBar.css({
                top: Math.round(this.$container.outerHeight() / 2) - 6
            });

            this.$container.addClass('uploading');
            this.$results.addClass('hidden');
            this.progressBar.resetProgressBar();
            this.progressBar.showProgressBar();
        },

        /**
         * On upload progress.
         */
        _onUploadProgress: function (event, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            this.progressBar.setProgressPercentage(progress);
        },

        /**
         * On a file being uploaded.
         */
        _onUploadComplete: function (event, data) {
            if (!data.result.success) {
                this.$importButton.prop('disabled', true).addClass('disabled');
                this.displayErrors(data.result.errors);
            } else {
                var html = $(data.result.html);
                this.$importButton.prop('disabled', false).removeClass('disabled');

                this.refreshResult(data.result);
            }

            // Last file
            if (this.uploader.isLastUpload()) {
                this.progressBar.hideProgressBar();
                this.$container.removeClass('uploading');
                this.$results.removeClass('hidden');
            }
        },

        /**
         * On a file being uploaded.
         */
        _onUploadError: function (event, data) {
            if (!data.jqXHR.responseJSON.success) {
                this.displayErrors(data.jqXHR.responseJSON.errors);
                this.$container.removeClass('uploading');
                this.progressBar.hideProgressBar();
                this.progressBar.resetProgressBar();
            }
        },

        displayErrors: function (errorMap) {
            Object.keys(errorMap).map((attribute) => {
                let errors = errorMap[attribute];

                errors.map(error => Craft.cp.displayError(error));
            });
        }
    },
    {
        defaults: {
            postParameters: {},
            uploadAction: "",
            deleteAction: "",
            fileInputSelector: "",
            importButtonSelector: "",

            onAfterRefreshResult: $.noop,
            containerSelector: null,
            resultSelector: null,

            uploadButtonSelector: null,
            deleteButtonSelector: null,

            uploadParamName: 'files'
        }
    }
);

//(function($) {})(jQuery);
