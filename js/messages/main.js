(function (global) {
    const App = global.BeezyMessages || {};

    const ctx = App.initContext ? App.initContext() : null;
    if (!ctx) {
        return;
    }

    App.bindEvents(ctx);
    App.loadChats(ctx).then(function () {
        App.refreshMessages(ctx);
    });
})(window);
