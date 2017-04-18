(function () {
    angular.module('App.services.Modal', []).factory('Modal', function ($timeout) {
        var Modal = {
            preloader: null,
            backdrop: null,
            msgBox: null,

            init: function () {
                if (!Modal.preloader)
                    Modal.preloader = new Spinner({
                        shadow: true,
                        hwaccel: true,
                        width: 2,
                        lines: 13,
                        scale: 2,
                        trail: 80,
                        speed: 0.7,
                        color: '#333333',
                        length: 10,
                        zIndex: 8000
                    }).spin();

                if (!Modal.backdrop) {
                    Modal.backdrop = $('.modal-bg');

                    Modal.backdrop.append(Modal.preloader.el);
                }
                if (!Modal.msgBox)
                    Modal.msgBox = $('.modal-msg');
            },

            showPreloader: function (callback) {
                Modal.init();
                Modal.msgBox.hide();
                if (callback instanceof Function)
                    Modal.backdrop.fadeIn(250, callback);
                else
                    Modal.backdrop.fadeIn(250);
                return this;
            },

            hidePreloader: function () {
                if (Modal.backdrop) {
                    Modal.backdrop.stop(true, true).hide();
                }
                return this;
            },

            closeModal: function () {
                Modal.backdrop.hide();
                Modal.msgBox.hide();
            },

            showModal: function (caption, buttons) {
                if (angular.isObject(caption)) {
                    var cText = '';
                    $.each(caption, function (key, text) {
                        cText += text + '<br/>';
                    });
                    caption = cText;
                }
                Modal.msgBox.find('.modal-text').html(caption);
                Modal.msgBox.find('.button-group').html('');
                if (!angular.isObject(buttons))
                    buttons = {
                        'OK': null
                    };
                $.each(buttons, function (caption, callback) {
                    var btn = $('<button>').html(caption);
                    btn.click(function () {
                        if (callback instanceof Function)
                            callback();
                        Modal.closeModal();
                    });
                    $('.button-group').append(btn);
                });
                Modal.msgBox.show();
                Modal.backdrop.stop(true, true).fadeIn(250);
            }


        };

        return Modal;

    });
}).call(this);
