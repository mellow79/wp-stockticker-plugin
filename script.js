// stock-ticker/script.js

jQuery(document).ready(function ($) {
    $('.stock-ticker').each(function () {
        var $ticker = $(this);
        var symbols = $ticker.data('symbols').split(',');
        var speed = $ticker.data('speed');

        symbols.forEach(function (symbol) {
            var $span = $('<span>').data('symbol', symbol);
            $ticker.append($span);
        });

        var speedMultiplier = 1;
        switch (speed) {
            case 'slow':
                speedMultiplier = 2;
                break;
            case 'medium':
                speedMultiplier = 1;
                break;
            case 'fast':
                speedMultiplier = 0.5;
                break;
        }

        var totalWidth = $ticker.width();
        var duration = (totalWidth / speedMultiplier) * 10000;

        $ticker.css('animation-duration', duration + 'ms');
    });

    // Fetch stock data using AJAX
    function fetchStockData(symbol) {
        $.ajax({
            url: stock_ticker_data.api_url,
            type: 'POST',
            data: {
                action: 'fetch_stock_data',
                security: stock_ticker_nonce,
                symbol: symbol,
            },
            success: function (response) {
                if (response.success) {
                    var price = response.data.price;
                    $('[data-symbol="' + symbol + '"]').text(symbol + ': ' + price);
                }
            },
        });
    }

    // Update stock data every 60 seconds
    setInterval(function () {
        symbols.forEach(function (symbol) {
            fetchStockData(symbol);
        });
    }, 60000);
});
