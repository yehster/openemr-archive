var frameProxies={};
function goPid(pid)
{
    top.restoreSession();
    top.RTop.location = pathWebroot+'/patient_file/summary/demographics.php' + '?set_pid=' + pid; 
    top.confirmFrameVisible(top.frameProxies["patient"]);                
}
function updateFrameInfo()
{
    var titleText=null;
    var proxy=frameProxies[$(this).attr("name")];
    var title=$(".title:first",proxy.frame.document);
    if(typeof proxy.frame.goPid!='undefined')
    {
        proxy.frame.goPid= top.goPid;
    }
    if(typeof proxy.frame.openNewForm!='undefined') {
        proxy.frame.openNewForm=function(sel)
        {
            proxy.frame.location.href=sel;
        }
    }
    if(title.length==1)
    {
        titleText=title.text();
    }
    else
    {
        title=$(".main_title:first",proxy.frame.document);
        if(title.length==1)
        {
            titleText=title.text();
        }        
        else
        {
            var subFrames=proxy.frame.frames;
            if(subFrames.length==1) {
            subFrameName=subFrames[0].name;
            $(this).attr("src",subFrames[0].location.href);
            parent[subFrameName]=proxy.frame;                                        
            }
            else
                {
                    if($("title",proxy.frame.document).length>0)
                        {
                            titleText=$("title",proxy.frame.document).text();
                        }
                    else
                        {
                            if(proxy.frame.location.href.indexOf("calendar")>0)
                                {
                                    titleText="Calendar";
                                }
                        }
                }
        }
}
    if(titleText!=null)
    {
        proxy.displayControl.find(".label").text(titleText);    
    }
    registerSwipe($("body",proxy.frame.document));
}
function syncCBVisibility(idx,elem)
{
    proxy=frameProxies[$(elem).attr("frameName")];
    if($(elem).is(".active"))
        {
           proxy.jqDiv.removeClass("hidden");            
        }
        else
        {
          proxy.jqDiv.addClass("hidden");

        }
    
}
function useSingleTab()
{
    return !$("#multiTabs").is(":checked");
}
function updateFramesVisibility(displayControl)
{
    if(useSingleTab())
    {
        displayControl.siblings("span").removeClass("active");    
    }
    var active=$("#navButtons span.active").length;
    if(active==0) {active=1;}
    var size=100/active;
    $("#navButtons > span").each(syncCBVisibility);
    $("div.main").css("width",size+"%");
//    $("#divframes div.hidden").css("width",0);
}

function clickTab()
{
    if(useSingleTab())
    {
        $(this).addClass("active");
    }
    else
    {
        if($(this).is(".active"))
            {
                var active=$("#navButtons span.active").length;
                // If this is the only active tab, then we don't want to hide it.
                if(active>1)
                {
                    $(this).removeClass("active");
                }
            }
            else
            {
                $(this).addClass("active");
            }
    }
    updateFramesVisibility($(this));
}

function frameProxy(proxyName,frameName,frame,displayControl)
{
   this.proxyName=proxyName
   this.frameName=frameName;
   frameProxies[frameName]=this;
   this.displayControl=displayControl;
   displayControl.attr("frameName",frameName);
   displayControl.click(clickTab);
   displayControl.addClass("tab");
   this.displayLabel=$("<span></span>");
   this.displayLabel.text(displayControl.text());
   this.displayLabel.addClass("label");
   displayControl.text("");
   displayControl.append(this.displayLabel);
   this.tabControls=$("<span></span>");
   this.tabControls.addClass("controls")
   displayControl.append(this.tabControls);
   this.refreshButton=$("<span>&#xe030;</span>").appendTo(this.tabControls);
   this.refreshButton.addClass("iconic")
   this.refreshButton.attr("function","refresh");
   this.refreshButton.click(function() {
       var fn=$(this).parents("[framename]:first").attr("framename"); 
       var proxy=frameProxies[fn];
       proxy.frame.location.reload();
   });
   this.location=frame.location;
   this.frame=frame;
   this.jqFrame=$("iframe[name='"+frameName+"']");
   this.jqDiv=this.jqFrame.parent("div");
   this.watch("location", function (property, oldval, newval)
                    {
                        this.frame.location=newval;
                    });
    this.jqFrame.load(updateFrameInfo);
    
    this.addCloseButton=function()
    {
       this.closeButton=$("<span>&#x2713</span>").appendTo(this.tabControls);
       this.closeButton.attr("function","refresh");
       this.closeButton.addClass("iconic")
       this.closeButton.click(function() {
       var fn=$(this).parents("[framename]:first").attr("framename"); 
       var proxy=frameProxies[fn];
       proxy.displayControl.each(removeElement);
       proxy.jqDiv.each(removeElement);
       // need to set focus on a new tab
       confirmFrameVisible(frameProxies['patient']);
    });
    };

    return this;
}
function confirmFrameVisible(proxyFrame)
{
    if(!proxyFrame.displayControl.is(".active"))
        {
            proxyFrame.displayControl.click();
        }
        updateFramesVisibility(proxyFrame.displayControl);
}

function registerTabsEventsLeftnav()
{
    var lnav = frames['left_nav'];
    var scriptInfo = "<script src='"+pathWebroot+"main/left_nav_tab.js'></script>";
    lnav.$("head").append(scriptInfo);
}
function registerTabsEventsMainTitle()
{
    var main_title = frames['Title'];
    var scriptInfo = "<script src='"+pathWebroot+"main/main_title_tab.js'></script>";
    var doc=main_title.document;
    var head=doc.getElementsByTagName("head");
    if(head.length>0)
        {
            var script=doc.createElement("script");
            script.setAttribute("src",pathWebroot+"main/main_title_tab.js");
            head.item(0).appendChild(script);
        }
    
}

function setupMainScreenTabs()
{
    // Initialize Frame Proxies
    Cal=new frameProxy("Cal","calendar",frames["calendar"],$("#butTab1"));

    RTop=new frameProxy("RTop","patient",frames["patient"],$("#butTab2"));

    RBot=new frameProxy("RBot","messages",frames['messages'],$("#butTab3"));

    registerSwipe($("body"));
    $("iframe[name='left_nav']").load(registerTabsEventsLeftnav);
    $("iframe[name='Title']").load(registerTabsEventsMainTitle);
    $(window).resize(resizeBottomDiv);
    $(document).ready(resizeBottomDiv);
}

function createNewTab(label,url)
{
    var divFrames=$("#divframes");
    var buttonsDiv=$("#divMain").find("#navButtons");
    var newID="Tab"+(new Date().getTime()).toString();
    var newButton=$("<span></span>");
    newButton.attr("id",newID);
    newButton.addClass("created");
    newButton.appendTo(buttonsDiv);
    newButton.text(label);
    var newDiv=$("<div class='main'></div>");
    newDiv.addClass("created");
    var iframe=$("<iframe class='main'></iframe>").attr("src",url).attr("name",newID).appendTo(newDiv);
    newDiv.appendTo(divFrames);
    newFrame=frames[newID];
//    newButton.button();
    var Proxy=new frameProxy(newID,newID,newFrame,newButton);
    Proxy.addCloseButton();
    confirmFrameVisible(Proxy);
    
}

function removeElement(idx,elem)
{
    elem.parentNode.removeChild(elem);
}
function removeCreatedTabs()
{
    var buttonsDiv=$("#divMain").find("#navButtons");
    buttonsDiv.find("span.created").each(removeElement);
    var divFrames=$("#divframes");
    divFrames.find("div.created").each(removeElement);
}

function framesState(show)
{
    if(show)
        {
            $("#divHeader").removeClass("hidden");
            $("#divNav").removeClass("hidden");
        }
        else
            {
                $("#divHeader").addClass("hidden");
                $("#divNav").addClass("hidden");
            }
  resizeBottomDiv();
}
function swipeMenuChange(event,direction)
{
    if(direction=="right")
    {
        framesState(true);
    }
    else
    {
        framesState(false);
    }
}

function registerSwipe(element)
{
    element.swipe({
        swipeLeft: swipeMenuChange
        ,swipeRight: swipeMenuChange
    });
}

function displayInFrame(fname,url)
{
    var frameProxy=this[fname];
    this[fname].frame.location=url;
    confirmFrameVisible(frameProxy);
    
}

function resizeBottomDiv()
{
    var deviceAgent = navigator.userAgent.toLowerCase();
    var iOS = deviceAgent.match(/(iphone|ipod|ipad)/);
    if(!iOS)
    {
        var botHeight=$(window).height() - ($("#divHeader").is(":visible") ? $("#divHeader").height() : 0 )-4;
        $("#divBottom").height(botHeight);
        var iframesHeight = botHeight - $("#navButtons").height();
        $("#divframes").height(iframesHeight);           
    }
}