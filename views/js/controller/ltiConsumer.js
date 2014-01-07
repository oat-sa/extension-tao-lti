define(['jquery', 'iframeNotifier'], function($, iframeNotifier){
   
    return {
        start : function(){
            $("#ltiLaunchFormSubmitArea").hide();
            $("form[name='ltiLaunchForm']").submit();

            //ask the parent to hide the loader
            iframeNotifier.parent('unloading');
             
            //set a fix size to the iframe as we are not allowed to check content size
            iframeNotifier.parent('heightchange', [600]);
        }
    };
});
