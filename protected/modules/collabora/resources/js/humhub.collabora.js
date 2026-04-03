humhub.module('collabora', function (module, require, $) {
    var client = require('client');
    var modal = require('ui.modal');
    var object = require('util').object;
    var Widget = require('ui.widget').Widget;
    var event = require('event');

    var Editor = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Editor, Widget);

    Editor.prototype.init = function () {
        this.modal = modal.get('#collabora-modal');

        var that = this;
        var wopiUrl = this.data('wopi-url');
        var iframe = this.modal.$.find("#collabora-online-viewer");

        event.trigger('humhub:ready');
        iframe.attr('src', wopiUrl);

        /*
        // Example shows we should POST submit into Iframe with access token
        var formElem = this.modal.$.find("#collabora-submit-form");
        if (formElem === null) {
            alert("err");
        }
        formElem.action = wopiUrl;
        formElem.submit();
        */

        this.modal.$.on('hidden.bs.modal', function (evt) {
            that.modal.clear();
        });

    };
    var createSubmit = function (evt) {
        client.submit(evt).then(function (response) {
            var modalWindow = modal.get('#collabora-modal');
            if (response.success) {
                event.trigger('humhub:file:created', [response.file]);
                modalWindow.load(response.editFormUrl);
                modalWindow.show();
            } else {
                modalWindow.setDialog(response.output);
            }
        }).catch(function (e) {
            module.log.error(e, true);
        });
    };

    module.export({
        Editor: Editor,
        createSubmit: createSubmit,
    });

});
