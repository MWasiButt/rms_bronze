(function () {
    const script = document.currentScript;
    const tenantId = script && script.dataset ? script.dataset.tenantId : null;
    const channels = script && script.dataset && script.dataset.channels
        ? script.dataset.channels.split(',').map((channel) => channel.trim()).filter(Boolean)
        : [];

    if (!tenantId || !channels.length || !window.Echo) {
        return;
    }

    const events = {
        orders: ['.order.created', '.order.sent_to_kitchen', '.order.paid', '.kitchen.ticket.status_changed'],
        kitchen: ['.order.sent_to_kitchen', '.kitchen.ticket.status_changed'],
        print: ['.print.job.created'],
    };

    let timer = null;
    const refresh = function () {
        window.clearTimeout(timer);
        timer = window.setTimeout(function () {
            window.location.reload();
        }, 700);
    };

    channels.forEach(function (channel) {
        const echoChannel = window.Echo.private('tenant.' + tenantId + '.' + channel);
        (events[channel] || []).forEach(function (eventName) {
            echoChannel.listen(eventName, refresh);
        });
    });
})();
