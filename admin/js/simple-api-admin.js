(function($) {

    console.log('script inited ');

    function uniquekey(length) {

        if (!length) {
            length = 32;
        }

        var key = '';
        do {
            key += Math.random().toString(36).substr(2, length);

        } while (key.length < length);

        return key.substr(0, length);
    }

    $(document).ready(function() {

        var $generatebtn = $('#sapi-access-generate-key-btn');
        if ($generatebtn.length) {
            $generatebtn.click(function() {

                var $input = $('#sapi-access-key');
                if ($input.length) {
                    $input.val(uniquekey(16));
                }

                return false;
            });
        }

    });

})(jQuery);