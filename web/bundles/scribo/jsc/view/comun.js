// Funsion de inicio maestro 
window.onload=$init;

function $init()
{
    gId("flash-zone").onclick = hideFlash;
    
    if(navigator.userAgent.toLowerCase().indexOf('chrome') == -1)
        show('recom');
}

function hideFlash()
{
    this.innerHTML = '';
}
