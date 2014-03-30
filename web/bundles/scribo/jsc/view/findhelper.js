/* ####### Variables ####### */

// var ctlUrl = 'uno'                                       // Url del controlador base.

var findHelpIndex = -1;
var findHelpBlur = true;

/* ####### Helper####### */

function FindHelp(event)
{
    var camp = event.target.id.substring(1);
    
    if(gId('x'+camp).onblur == null && gId('l'+camp).onmouseover == null && gId('l'+camp).onmouseout == null)
    {
        gId('x'+camp).onblur = fFindHelp;
        gId('l'+camp).onmouseover= hFindHelp;
        gId('l'+camp).onmouseout= iFindHelp;
    }
    
    if(event.keyCode == 13)
        aFindHelp(camp);
    else if(event.keyCode == 8)
        dFindHelp(camp);
    else if(event.keyCode == 38 || event.keyCode == 40)
    {
        eFindHelp(event.keyCode, camp);
        return false;
    }
}

function aFindHelp(camp)
{
    ajaxAction
    (
        new Hash(['*param => '+gId('x'+camp).value]),
        $basePath+ctlUrl+'/'+camp.toLowerCase(),
        bFindHelp,
        camp
    );
}

function bFindHelp(response, camp)
{
    findHelpIndex = -1;
    
    showB('l'+camp);
    gId('l'+camp).innerHTML = toTable(response.responseText, 'cFindHelp', camp);
}

function cFindHelp(elem)
{
    var camp = remConsecutive(elem.id);
    findHelpIndex = getConsecutive(elem.id);
    
    gFindHelp(elem);
    hide('l'+camp);
}

function dFindHelp(camp)
{
    findHelpIndex = -1;
    
    gId(camp).value = '';
    gId('x'+camp).value = '';
    gId('l'+camp).innerHTML = '';
    hide('l'+camp);
    gId('x'+camp).focus();
}

function eFindHelp(code, camp)
{
    var tab = gId('dinamicTable_'+camp);
    
    if(tab != null)
    {
        var lim = tab.rows.length;
        
        for(i = 0; i < lim; i++)
            tab.rows[i].style.background = '';
        
        if(code == 38)
        {
            findHelpIndex -= 1;
            findHelpIndex = findHelpIndex < 0 ? lim-1 : findHelpIndex;
        }
        else if(code == 40)
        {
            findHelpIndex += 1;
            findHelpIndex = findHelpIndex >= lim ? 0 : findHelpIndex;
        }
        
        gFindHelp(tab.rows[findHelpIndex]);
    }
}

function fFindHelp(event)
{
    if(findHelpBlur)
    {
        var camp = event.target.id.substring(1);
    
        findHelpIndex = -1;
        hide('l'+camp);
    }
}

function gFindHelp(elem)
{
    var camp = remConsecutive(elem.id);
    
    var dis = elem.parentNode.parentNode.parentNode;
    
    elem.style.background = '#ADD8E6';
    dis.scrollTop = elem.offsetTop;

    gId(camp).value = elem.cells[0].innerHTML;
    gId('x'+camp).value = elem.cells[1].innerHTML;
    gId('x'+camp).focus();
    
    if( typeof gId("x"+camp).secondAction != "undefined" )
        gId("x"+camp).secondAction(elem);
}

function hFindHelp(event)
{
    var camp = event.target.id.substring(1);
    var tab = gId('dinamicTable_'+camp);
    
    if(tab != null)
    {
        var lim = tab.rows.length;
        
        for(i = 0; i < lim; i++)
            tab.rows[i].style.background = '';
    }
    
    iFindHelp();
}

function iFindHelp(event)
{
    findHelpBlur = findHelpBlur ? false : true;
}
