var tom = ( function()
{    
    return { // start of return
        
        waitForFinalEvent : ( function() 
        {
            var timers = {};
            return function (callback, ms, uniqueId) 
            {
                // must always include a unique ID.
                if (!uniqueId) uniqueId = "Don't call this twice without a uniqueId";
                // always clear it before setting it.
                if (timers[uniqueId]) clearTimeout (timers[uniqueId]);
                // set a new timer to trigger your resize function.
                timers[uniqueId] = setTimeout(callback, ms);
            };
        })()
        
        // this is how you call the above.
        // $(window).resize(function () 
        // {
        //     tom.waitForFinalEvent(function()
        //     {
        //       alert('Resize...');
        //       //...
        //     }, 500, "some unique string");
        // });
        
    }; // end of return.
}());

/* This is the main code for the website */
$(document).ready(function()
{    
    $('.key').hide();
    
    $(window).scroll(function () 
    {
        var pos = $(window).scrollTop();
        // $('.debug').text(pos+'');
        if (pos > 1750 && pos < 2850)
        {
            $('.key').fadeIn().addClass('showing');
            
        } else if ($('.key').hasClass('showing')) {
            
            $('.key').fadeOut().removeClass('showing');
        }
        
        // tom.waitForFinalEvent(function()
        //         {
        //             var pos = $(window).scrollTop();
        //             // $('.debug').text(pos+'');
        //             if (pos > 1750)
        //             {
        //                 $('.key').fadeIn();
        //             } else {
        //                 $('.key').fadeOut();
        //             }
        //           //...
        //         }, 100, "some unique string");
    });
});