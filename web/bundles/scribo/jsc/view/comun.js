// Funsion de inicio maestro 
window.onload=$init;

function $init()
{
    gId("flash-zone").onclick = hideFlash;
}

function hideFlash()
{
    this.innerHTML = '';
}
