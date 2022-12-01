class UserLoginActivity {
    constructor(){
        this.user_details();
        // console.log(this.user_details);
        if(this.user_details !== undefined && this.user_details.is_user_logged_in == true)
        {
            this.update_user_login_activity();
        }
        // let browser = this.get_browser();
        // console.log('browser',browser);
    }

    user_details()
    {
        this.user_details =  ua_user_details !== undefined  ? ua_user_details : undefined
    }

    get_operating_system()
    {
        let OSName="Unknown OS";
        if (navigator.appVersion.indexOf("Win")!=-1) OSName="Windows";
        if (navigator.appVersion.indexOf("Mac")!=-1) OSName="MacOS";
        if (navigator.appVersion.indexOf("X11")!=-1) OSName="UNIX";
        if (navigator.appVersion.indexOf("Linux")!=-1) OSName="Linux";

        return OSName;
    }

    get_browser() {
        var ua=navigator.userAgent,tem,M=ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || []; 
        if(/trident/i.test(M[1])){
            tem=/\brv[ :]+(\d+)/g.exec(ua) || []; 
            return {name:'IE',version:(tem[1]||'')};
            }   
        if(M[1]==='Chrome'){
            tem=ua.match(/\bOPR|Edge\/(\d+)/)
            if(tem!=null)   {return {name:'Opera', version:tem[1]};}
            }   
        M=M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
        if((tem=ua.match(/version\/(\d+)/i))!=null) {M.splice(1,1,tem[1]);}
        return {
          name: M[0],
          version: M[1]
        };
     }

    update_user_login_activity() {
        let browser = this.get_browser();
        let os = this.get_operating_system();
        setInterval(function(){
            
            console.log('lets update.....');
            jQuery.ajax({
                url:ua_admin_ajax,
                method:'POST',
                data:{'action':'update_user_login_activity','browser':browser,'os':os},
                success:function(data){
                    // console.log('data',data);
                }
            });
        },60000);
    }
    
}

jQuery(window).on('load',function(){
    
    let LoginActivity = new UserLoginActivity();
    // console.log('loaded');
    // console.log(LoginActivity.user_details);
    // console.log(LoginActivity.user_details.is_user_logged_in);
    let os = LoginActivity.get_operating_system();
    let browser = LoginActivity.get_browser();
    if(LoginActivity.user_details !== undefined && LoginActivity.user_details.is_user_logged_in == true)
    {
        jQuery.ajax({
            url:ua_admin_ajax,
            method:'POST',
            data:{'action':'update_user_login_activity','browser':browser,'os':os},
            success:function(data){
                // console.log('data',data);
            }
        });
    }
});